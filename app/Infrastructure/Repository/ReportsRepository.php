<?php

namespace App\Infrastructure\Repository;

use App\Domain\Interfaces\ReportsRepositoryInterface;
use App\Models\Departamento;
use App\Models\Distrito;
use App\Models\Doctor;
use App\Models\Muestras;
use App\Models\Pedidos;
use App\Models\Provincia;
use App\Models\VisitaDoctor;
use App\Traits\Query\ExcludeWordsFromQuery;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ReportsRepository implements ReportsRepositoryInterface
{
    use ExcludeWordsFromQuery;

    /* -------- Ventas -------- */
    public function getVentasGeneralReport(int $month, int $year): Collection
    {
        $periodColumn = $month > 0 ? 'DAY(created_at)' : 'MONTH(created_at)';

        return Pedidos::selectRaw("
        {$periodColumn} as period,
        SUM(prize) as total_amount,
        COUNT(*) as total_pedidos")->whereYear('created_at', $year)
            ->where('status', true)
            ->when($month > 0, fn($q) => $q->whereMonth('created_at', $month))
            ->orderBy('period')
            ->groupBy('period')
            ->get();
    }
    public function getVentasVisitadorasReport(string $startDate, string $endDate): Collection
    {
        return DB::table('pedidos as p')->join('users as u', 'u.id', '=', 'p.visitadora_id')
            ->selectRaw(
                'u.id as visitadora_id,
                u.name as visitadora,
                SUM(p.prize) as total_amount,
                COUNT(p.id) as total_pedidos'
            )->where('u.role_id', 6)->where('status', true)
            ->when($startDate && $endDate, function ($q) use ($startDate, $endDate) {
                $q->whereBetween('p.created_at', [$startDate, $endDate]);
            })->groupBy('u.id', 'u.name')
            ->get()->map(
                fn($item) => (object)
                [
                    'visitadora' => $item->visitadora,
                    'total_amount' => (float) $item->total_amount,
                    'total_pedidos' => (int) $item->total_pedidos,
                ]
            );
    }
    public function getVentasProductosReport(string $startDate, string $endDate): Collection
    {
        $query = DB::table('detail_pedidos as dp')
            ->selectRaw('
            dp.articulo as product,
            SUM(dp.sub_total) as total_amount,
            SUM(dp.cantidad) as total_products
            ')->join('pedidos as p', 'dp.pedidos_id', '=', 'p.id')
            ->where('p.status', true)
            ->whereNotNull('dp.articulo')->where('dp.articulo', '!=', '')
            ->when($startDate && $endDate, fn($q) => $q->whereBetween('p.created_at', [$startDate, $endDate]))
            ->groupBy('dp.articulo')
            ->orderBy('total_amount', 'desc')
            ->limit(100);

        $query = $this->excludeArrayFromDataResults($query, 'dp.articulo', ['%delivery%', 'bolsa%']);

        return $query->get()->map(function ($item) {
            return (object) [
                'product' => $item->product,
                'total_amount' => (float) $item->total_amount,
                'total_products' => (int) $item->total_products,
            ];
        });
    }

    /* -------- Rutas -------- */
    public function getRutasZonesReport(int $month, int $year, array $distritos): Collection
    {
        return VisitaDoctor::query()
            ->selectRaw(
                'd.id AS distrito_id,
                d.name AS distrito_name,
                visita_doctor.estado_visita_id,
                COUNT(*) AS total'
            )
            ->join('doctor AS dr', 'dr.id', '=', 'visita_doctor.doctor_id')
            ->join('distritos AS d', 'd.id', '=', 'dr.distrito_id')
            ->when($month, fn($q) => $q->whereMonth('fecha', $month))
            ->when($year, fn($q) => $q->whereYear('fecha', $year))
            ->when(!empty($distritos), fn($q) => $q->whereIn('dr.distrito_id', $distritos))
            ->groupBy('d.id', 'd.name', 'visita_doctor.estado_visita_id')
            ->get();
    }

    /* -------- Doctores -------- */
    public function getAmountSpentAnuallyByDoctor(int $year, int $doctorId): array
    {
        $rawData = Pedidos::selectRaw('MONTH(created_at) as month, SUM(prize) as total_amount')
            ->where('status', true)
            ->whereYear('created_at', $year)->where('id_doctor', $doctorId)
            ->groupBy('month')->pluck('total_amount', 'month')
            ->all();
        return array_replace(array_fill(1, 12, 0), $rawData);
    }
    public function getMostConsumedProductsMonthlyByDoctor(int $year, int $month, int $doctorId): Collection
    {
        $excludedWords = ['%delivery%', 'bolsa%'];
        $cols = ['dp.articulo', 'dp.cantidad', 'dp.sub_total'];

        $query = DB::table('detail_pedidos as dp')
            ->join('pedidos as p', 'dp.pedidos_id', '=', 'p.id')->select($cols)
            ->whereYear('p.created_at', $year)->whereMonth('p.created_at', $month)
            ->where('p.id_doctor', $doctorId)
            ->where('p.status', true);

        $this->excludeArrayFromDataResults($query, 'dp.articulo', $excludedWords);

        $rows = $query->get();

        $normalized = $rows->map(function ($r) {
            $articulo = strtoupper($r->articulo);
            $articulo = preg_replace('/\b\d+\s?(MG|MCG|G|ML|UI|UND)\b/u', '', $articulo);
            $articulo = preg_replace('/\bVIT\b/u', 'VITAMINA', $articulo);
            $articulo = preg_replace('/\bX\b/u', '', $articulo);
            $articulo = preg_replace('/[\/\-]+$/u', '', $articulo);
            $articulo = preg_replace('/[\/\-]+\s*$/u', '', $articulo);
            $articulo = preg_replace('/\s*[\/\-]+\s*/u', ' ', $articulo);
            $articulo = preg_replace('/\s+/', ' ', trim($articulo));

            $normalizedData = [
                'articulo' => $articulo,
                'cantidad' => $r->cantidad,
                'sub_total' => $r->sub_total
            ];

            return $normalizedData;
        });

        $grouped = $normalized->groupBy('articulo')->map(function ($items) {
            return [
                'articulo' => $items->first()['articulo'],
                'total_cantidad' => $items->sum('cantidad'),
                'total_sub_total' => $items->sum(fn($i) => $i['sub_total'] ?? 0)
            ];
        })->sortByDesc('total_cantidad')->take(100);

        return $grouped->values();
    }
    public function getAmountSpentMonthlyGroupedByTipo(int $year, int $month, int $doctorId): Collection
    {
        $excludedWords = ['%delivery%', 'bolsa%'];

        $query = DB::table('detail_pedidos as dp')
            ->join('pedidos as p', 'dp.pedidos_id', '=', 'p.id')
            ->selectRaw('
                UPPER(SUBSTRING_INDEX(dp.articulo, " ", 1)) as tipo,
                SUM(dp.sub_total) as total_sub_total
            ')
            ->where('p.status', true)
            ->whereYear('p.created_at', $year)
            ->whereMonth('p.created_at', $month)
            ->when($doctorId, fn($q) => $q->where('p.id_doctor', $doctorId))
            ->groupBy('tipo')
            ->orderByDesc('total_sub_total');

        $this->excludeArrayFromDataResults($query, 'dp.articulo', $excludedWords);

        return $query->get()
            ->map(function ($item) {
                return [
                    'tipo' => $item->tipo,
                    'total_sub_total' => (float) $item->total_sub_total
                ];
            });
    }
    public function getTopDoctorByAmountInfo(int $year): mixed
    {
        $topDoctor = Pedidos::selectRaw(
            'doctor.id as doctor_id,
            doctor.name,
            doctor.tipo_medico,
            SUM(pedidos.prize) as total_amount'
        )
            ->join('doctor', 'pedidos.id_doctor', '=', 'doctor.id')
            ->whereYear('pedidos.created_at', $year)
            ->groupBy('doctor.id', 'doctor.name', 'doctor.tipo_medico')
            ->orderByDesc('total_amount')
            ->first();

        return [
            'id' => $topDoctor->doctor_id,
            'name' => $topDoctor->name,
            'tipo_medico' => $topDoctor->tipo_medico,
            'is_top_doctor' => true,
        ];
    }
    public function getDoctorInfo(int $doctorId): mixed
    {
        $doctor = Doctor::select('id', 'name', 'tipo_medico')->where('id', $doctorId)->first();
        return [
            'id' => $doctor['id'],
            'name' => $doctor['name'],
            'tipo_medico' => $doctor['tipo_medico'],
            'is_top_doctor' => false,
        ];
    }
    public function getDoctoresByTipoAndYear(int $year): Collection
    {
        return DB::table('doctor as dr')
            ->leftJoin('pedidos as p', function ($join) use ($year) {
                $join->on('dr.id', '=', 'p.id_doctor')
                    ->whereYear('p.created_at', $year);
            })
            ->select(
                '
                dr.tipo_medico,
                COUNT(DISTINCT dr.id) as total_doctores,
                COALESCE(SUM(p.prize), 0) as total_amount,
                COUNT(p.id) as total_pedidos'
            )
            ->where('p.status', true)
            ->whereNotNull('dr.tipo_medico')
            ->groupBy('dr.tipo_medico')
            ->get();
    }
    public function getPedidosByTipoAndMonth(int $year): Collection
    {
        return DB::table('doctor as dr')
            ->join('pedidos as p', 'dr.id', '=', 'p.id_doctor')
            ->selectRaw(
                'MONTH(p.created_at) as month,
                dr.tipo_medico,
                SUM(p.prize) as total_amount,
                COUNT(p.id) as total_pedidos'
            )
            ->where('p.status', true)
            ->whereNotNull('dr.tipo_medico')
            ->whereYear('p.created_at', $year)
            ->groupBy('month', 'dr.tipo_medico')
            ->get();
    }

    /* -------- Provincias -------- */
    public function getRawDataGeoVentas(string $startDate, string $endDate): Collection
    {
        $query = Pedidos::query()
            ->selectRaw('district, SUM(prize) as total_amount, COUNT(*) as total_pedidos')
            ->whereNotNull('district')->where('district', '!=', '')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('zone_id', 1)
            ->where('status', true);
        $this->excludeArrayFromDataResults($query, 'district', ['%retiro en tienda%', '%recojo en tienda%']);

        return $query->groupBy('district')->get();
    }
    public function getRawDataGeoVentasDetails(string $startDate, string $endDate): Collection
    {
        $query = Pedidos::query()
            ->select([
                'pedidos.id',
                'pedidos.created_at',
                'pedidos.prize as total_amount',
                'pedidos.district',
                'users.name as created_by',
            ])
            ->leftJoin('users', 'pedidos.user_id', '=', 'users.id')
            ->whereNotNull('pedidos.district')->where('pedidos.district', '!=', '')
            ->where('pedidos.zone_id', 1)
            ->where('pedidos.status', true)
            ->whereBetween('pedidos.created_at', [$startDate, $endDate]);
        $this->excludeArrayFromDataResults($query, 'pedidos.district', ['%retiro en tienda%', '%recojo en tienda%']);

        return $query->orderBy('pedidos.created_at', 'desc')->get();
    }
    public function getDepartamentosForMap(): Collection
    {
        return Departamento::select('id', 'name')->get();
    }
    public function getProvinciasForMap(): Collection
    {
        return Provincia::select('id', 'name')->get();
    }
    public function getProvinciasWithDepartamentoForMap(): Collection
    {
        return Provincia::select('id', 'name', 'departamento_id')
            ->with(['departamento:id,name'])->get();
    }
    public function getDistritosWithProvinciaAndDepartamentoForMap(): Collection
    {
        return Distrito::select('id', 'name', 'provincia_id')
            ->with([
                'provincia:id,name,departamento_id',
                'provincia.departamento:id,name'
            ])
            ->get();
    }
    public function getDistritosWithProvinciaForMap(): Collection
    {
        return Distrito::select('id', 'name', 'provincia_id')
            ->with(['provincia:id,name'])->get();
    }

    /* -------- Muestras -------- */
    public function countMuestrasByTipo(string $startDate, string $endDate): Collection
    {
        return DB::table('tipo_muestras as tm')
            ->leftJoin('muestras as m', function ($join) use ($startDate, $endDate) {
                $join->on('tm.id', '=', 'm.id_tipo_muestra')
                    ->whereBetween('m.created_at', [$startDate, $endDate]);
            })
            ->selectRaw('tm.name as tipo, COUNT(m.id) as total')
            ->groupBy('tm.name')
            ->get();
    }

    public function countMuestrasByTipoFrasco(string $startDate, string $endDate): Collection
    {
        $result = Muestras::selectRaw('tipo_frasco, COUNT(*) as total')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('tipo_frasco')
            ->pluck('total', 'tipo_frasco');

        // Asegurar que todos los tipos del ENUM aparezcan, incluso con 0
        $allTipoFrasco = collect(Muestras::TIPOS_FRASCO)->mapWithKeys(function ($tipo) use ($result) {
            return [$tipo => $result->get($tipo, 0)];
        });

        return $allTipoFrasco->map(function ($total, $tipo) {
            return (object) [
                'tipo_frasco' => $tipo,
                'total' => $total,
            ];
        })->values();
    }
}
