<?php

namespace App\Application\Services\Reports\MuestrasReport;

use App\Application\DTOs\Reports\Muestras\ReportDoctorsDto;
use App\Application\DTOs\Reports\Muestras\ReportGeneralDto;
use App\Application\Services\Reports\ReportBaseService;
use App\Infrastructure\Repository\ReportsRepository;
use App\Models\Muestras;
use App\Models\TipoMuestra;
use Brick\Math\RoundingMode;
use Brick\Money\Money;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class MuestrasReportService extends ReportBaseService
{
    protected string $cachePrefix = 'muestras_report_';

    public function __construct(protected ReportsRepository $repo)
    {
    }

    public function createInitialReport(): mixed
    {
        return [
            'generalReport' => $this->getGeneralReport()->toArray(),
            'doctorReport' => $this->getDoctorReport()->toArray(),
        ];
    }

    public function getGeneralReport(array $filters = []): ReportGeneralDto
    {
        $start_date = Carbon::parse($filters['start_date'] ?? now()->startOfMonth())->startOfDay();
        $end_date = Carbon::parse($filters['end_date'] ?? now())->endOfDay();

        $rawData = $this->repo->getRawMuestrasData($start_date, $end_date);

        $data = [
            'frasco_original' => $rawData->where('tipo_frasco', 'Frasco Original')->values(),
            'frasco_muestra' => $rawData->where('tipo_frasco', 'Frasco Muestra')->values(),
        ];

        return new ReportGeneralDto(
            count($rawData),
            $this->getMuestrasQuantity($rawData),
            $this->getMuestrasTotalAmount($rawData),
            $this->groupByTipoFrasco($rawData),
            $this->groupByTipoMuestra($rawData),
            $data,
            compact('start_date', 'end_date')
        );
    }

    public function getDoctorReport(array $filters = []): ReportDoctorsDto
    {
        $id_doctor = $filters['id_doctor'] ?? null;
        $start_date = Carbon::parse($filters['start_date'] ?? now()->startOfMonth())->startOfDay();
        $end_date = Carbon::parse($filters['end_date'] ?? now())->endOfDay();

        $doctorData = $id_doctor ? $this->repo->getDoctorInfo($id_doctor) : $this->repo->muestrasGetTopDoctorByAmountInfo($start_date, $end_date);

        $id_doctor = $doctorData['id'];

        $rawData = $this->repo->getMuestrasByDoctorRawData($start_date, $end_date, $id_doctor);

        $muestrasData = $rawData->map(function ($muestra) {
            $price = Money::of($muestra->precio ?? 0, 'PEN');
            $quantity = (int) $muestra->cantidad_de_muestra;
            return [
                'id' => $muestra->id,
                'name' => $muestra->nombre_muestra,
                'quantity' => $quantity,
                'price' => $price->getAmount()->__toString(),
                'total_price' => $price->multipliedBy($quantity)->getAmount()->__toString(),
                'tipo_frasco' => $muestra->tipo_frasco,
            ];
        });

        $data = [
            'anual' => $this->buildDataGroupedByMonthInYear($rawData),
            'by_tipo_frasco' => $this->groupByTipoFrasco($rawData),
            'by_tipo_muestra' => $this->groupByTipoMuestra($rawData),
            'muestras' => $muestrasData
        ];

        return new ReportDoctorsDto(
            $doctorData['is_top_doctor'],
            $doctorData['name'],
            $doctorData['tipo_medico'],
            $doctorData['especialidad'] ?? null,
            $doctorData['distrito'] ?? null,
            $doctorData['centro_salud'] ?? null,
            $data,
            compact('id_doctor', 'start_date', 'end_date')
        );
    }

    private function groupByTipoFrasco(Collection $muestras): array
    {
        return collect(Muestras::TIPOS_FRASCO)->mapWithKeys(function (string $tipoFrasco) use ($muestras) {
            $group = $muestras->where('tipo_frasco', $tipoFrasco);

            return [
                $tipoFrasco => $this->getPartialGeneralStats($group)
            ];
        })->all();
    }

    private function groupByTipoMuestra(Collection $muestras): array
    {
        $allTipos = TipoMuestra::all()->keyBy('id');

        $muestrasPorTipo = $muestras
            ->filter(fn($m) => $m->tipoMuestra)
            ->groupBy(fn($m) => $m->tipoMuestra->id);

        return $allTipos->mapWithKeys(function (TipoMuestra $tipo) use ($muestrasPorTipo) {
            $group = $muestrasPorTipo->get($tipo->id, collect());

            return [
                $tipo->name => $this->getPartialGeneralStats($group)
            ];
        })->all();
    }

    private function getPartialGeneralStats(Collection $collection): array
    {
        return [
            'count' => count($collection),
            'quantity' => $this->getMuestrasQuantity($collection),
            'amount' => $this->getMuestrasTotalAmount($collection)->getAmount()->__toString()
        ];
    }

    private function getMuestrasQuantity(Collection $collection): int
    {
        return $collection->sum('cantidad_de_muestra');
    }
    private function getMuestrasTotalAmount(Collection $collection): Money
    {
        return $collection->reduce(function (Money $total, $muestra) {
            $precio = $muestra->precio ? Money::of($muestra->precio, 'PEN') : Money::zero('PEN');
            $cantidad = $muestra->cantidad_de_muestra ?? 0;

            $subTotal = $precio->multipliedBy($cantidad, RoundingMode::HALF_UP);

            return $total->plus($subTotal);
        }, Money::zero('PEN'));
    }

    private function buildDataGroupedByMonthInYear(Collection $collection): array
    {
        $res = [];

        // ✅ Si la colección está vacía, usar el año actual y 12 meses en cero
        if ($collection->isEmpty()) {
            $currentYear = now()->year;
            for ($month = 1; $month <= 12; $month++) {
                $res[$currentYear][$month] = [
                    'amount' => Money::zero('PEN'),
                    'quantity' => 0,
                    'count' => 0,
                ];
            }

            // Convertir amount a string antes de retornar
            foreach ($res[$currentYear] as $month => $data) {
                $res[$currentYear][$month]['amount'] = $data['amount']->getAmount()->__toString();
            }

            return $res;
        }

        // 🔄 Si hay datos, seguir con la lógica normal
        $years = $collection->pluck('created_at')
            ->map(fn($date) => Carbon::parse($date)->year)
            ->unique()
            ->sort()
            ->values();

        foreach ($years as $year) {
            for ($month = 1; $month <= 12; $month++) {
                $res[$year][$month] = [
                    'amount' => Money::zero('PEN'),
                    'quantity' => 0,
                    'count' => 0,
                ];
            }
        }

        $collection->each(function ($item) use (&$res) {
            $carbonDate = Carbon::parse($item->created_at);
            $year = $carbonDate->year;
            $month = $carbonDate->month;

            // Este bloque ya no debería ser necesario si inicializamos todos los años arriba,
            // pero lo dejamos como respaldo
            if (!isset($res[$year])) {
                for ($m = 1; $m <= 12; $m++) {
                    $res[$year][$m] = [
                        'amount' => Money::zero('PEN'),
                        'quantity' => 0,
                        'count' => 0, // ⚠️ corregido: debe ser 0, no 1
                    ];
                }
            }

            $precio = Money::of($item->precio ?? 0, 'PEN');
            $cantidad = (int) ($item->cantidad_de_muestra ?? 0);

            $res[$year][$month]['quantity'] += $cantidad;
            $res[$year][$month]['count'] += 1;
            $res[$year][$month]['amount'] = $res[$year][$month]['amount']->plus(
                $precio->multipliedBy($cantidad)
            );
        });

        // Convertir todos los 'amount' a string
        foreach ($res as $year => $months) {
            foreach ($months as $month => $data) {
                $res[$year][$month]['amount'] = $data['amount']->getAmount()->__toString();
            }
        }

        return $res;
    }

}
