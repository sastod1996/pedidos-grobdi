<?php

namespace App\Application\Services\Reports\MuestrasReport;

use App\Application\DTOs\Reports\Muestras\ReportDoctorsDto;
use App\Application\DTOs\Reports\Muestras\ReportGeneralDto;
use App\Application\Services\Reports\ReportBaseService;
use App\Infrastructure\Repository\ReportsRepository;
use App\Models\Muestras;
use App\Models\TipoMuestra;
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
            /* 'doctorReport' => $this->getDoctorReport()->toArray(), */
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
        return new ReportDoctorsDto();
        /*  return new ReportDoctorsDto(
             count($rawData),
             $this->getMuestrasQuantity($rawData),
             $this->getMuestrasTotalAmount($rawData),
             $this->groupByTipoFrasco($rawData),
             $this->groupByTipoMuestra($rawData),
             $data,
             compact('start_date', 'end_date')
         ); */
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
            'amount' => $this->getMuestrasTotalAmount($collection)
        ];
    }

    private function getMuestrasQuantity(Collection $collection): int
    {
        return $collection->sum('cantidad_de_muestra');
    }
    private function getMuestrasTotalAmount(Collection $collection): float
    {
        return $collection->sum(fn($m) => ($m->precio ?? 0) * $m->cantidad_de_muestra);
    }
}
