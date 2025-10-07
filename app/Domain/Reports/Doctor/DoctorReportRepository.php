<?php

namespace App\Domain\Reports\Doctor;

use App\Models\Pedidos;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;


class DoctorReportRepository implements DoctorReportRepositoryInterface
{
    public function getTopDoctor(int $year)
    {
        $currentMonth = now()->month;

        $topDoctor = Pedidos::selectRaw('
        id_doctor,
        SUM(prize) as total_amount
    ')
            ->with('doctor:id,name,tipo_medico')
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $currentMonth)
            ->groupBy('id_doctor')
            ->orderByDesc('total_amount')
            ->first();

        return [
            $topDoctor->id_doctor,
            $topDoctor->doctor->name ?? 'No disponible',
            $topDoctor->doctor->tipo_medico ?? 'No disponible'
        ];
    }

    public function getAmountSpentByDoctorGroupedByMonth(int $year, ?int $doctorId): array
    {

        $rawData = Pedidos::selectRaw('MONTH(created_at) as month, SUM(prize) as total_amount')
            ->whereYear('created_at', $year)
            ->where('id_doctor', $doctorId)
            ->groupBy('month')
            ->pluck('total_amount', 'month')
            ->all();

        return array_replace(array_fill(1, 12, 0), $rawData);
    }

    private function excludeArrayFromDataResults(Builder $query, string $columnName, array $wordsToExclude)
    {
        foreach ($wordsToExclude as $word) {
            $query->whereRaw("LOWER({$columnName}) NOT LIKE ?", [strtolower($word)]);
        }

        return $query;
    }


    public function getMostConsumedProductsInTheMonthByDoctor(int $year, int $month, ?int $doctorId = null, ?int $limit = null, ?bool $withPrices = false)
    {
        $cols = ['dp.articulo', 'dp.cantidad'];

        if ($withPrices) {
            $cols[] = 'dp.sub_total';
        }

        $query = DB::table('detail_pedidos as dp')
            ->join('pedidos as p', 'dp.pedidos_id', '=', 'p.id')
            ->select($cols)
            ->whereYear('p.created_at', $year)
            ->whereMonth('p.created_at', $month)
            ->where('p.id_doctor', $doctorId);

        $query = $this->excludeArrayFromDataResults($query, 'dp.articulo', ['%delivery%', 'bolsa%']);

        $rows = $query->get();

        $normalized = $rows->map(function ($r) use ($withPrices) {
            $articulo = strtoupper($r->articulo);
            $articulo = preg_replace('/\bVIT\b/u', 'VITAMINA', $articulo);
            $articulo = preg_replace('/[\/\-]+$/u', '', $articulo);
            $articulo = preg_replace('/[\/\-]+\s*$/u', '', $articulo);
            $articulo = preg_replace('/\s*[\/\-]+\s*/u', ' ', $articulo);
            $articulo = preg_replace('/\s+/', ' ', trim($articulo));

            $normalizedData = [
                'articulo' => $articulo,
                'cantidad' => $r->cantidad
            ];

            if ($withPrices) {
                $normalizedData['sub_total'] = $r->sub_total;
            }

            return $normalizedData;
        });

        $grouped = $normalized->groupBy('articulo')->map(function ($items) use ($withPrices) {
            $result = [
                'articulo' => $items->first()['articulo'],
                'total_cantidad' => $items->sum('cantidad')
            ];

            if ($withPrices) {
                $result['total_subtotal'] = $items->sum(function ($i) {
                    return $i['sub_total'] ?? 0;
                });
            }

            return $result;
        })->sortByDesc('total_cantidad');

        if ($limit) {
            $grouped = $grouped->take($limit);
        }

        return $grouped->values();
    }

    public function getAmountSpentByDoctorGroupedByTipo(int $year, int $month, ?int $doctorId)
    {
        return DB::table('detail_pedidos as dp')
            ->join('pedidos as p', 'dp.pedidos_id', '=', 'p.id')
            ->selectRaw('
                UPPER(SUBSTRING_INDEX(dp.articulo, " ", 1)) as tipo,
                SUM(dp.sub_total) as total_sub_total
            ')
            ->whereYear('p.created_at', $year)
            ->whereMonth('p.created_at', $month)
            ->when($doctorId, fn($q) => $q->where('p.id_doctor', $doctorId))
            ->groupBy('tipo')
            ->orderByDesc('total_sub_total')
            ->whereRaw("LOWER(dp.articulo) NOT LIKE ?", [strtolower('%delivery%')])
            ->whereRaw("LOWER(dp.articulo) NOT LIKE ?", [strtolower('bolsa%')])
            ->get()
            ->map(function ($item) {
                return [
                    'tipo' => $item->tipo,
                    'total_sub_total' => (float) $item->total_sub_total
                ];
            });
    }

    public function getDoctorInfo(int $doctorId): array
    {
        $doctor = DB::table('doctor')
            ->select('id', 'name', 'tipo_medico')
            ->where('id', $doctorId)
            ->first();

        if (!$doctor) {
            return ['name' => 'No disponible', 'tipo_medico' => 'No disponible'];
        }

        return [
            'name' => $doctor->name,
            'tipo_medico' => $doctor->tipo_medico
        ];
    }
}
