<?php

namespace App\Application\Services\Reports\RutasReport;

use App\Application\DTOs\Reports\Rutas\ReportZonesDto;
use App\Application\Services\Reports\ReportBaseService;
use App\Infrastructure\Repository\ReportsRepository;
use App\Models\EstadoVisita;

class RutasReportService extends ReportBaseService
{
    protected string $cachePrefix = 'rutas_report_';

    public function __construct(protected ReportsRepository $repo)
    {
    }

    public function createInitialReport(): mixed
    {
        return [
            'zonesReport' => $this->getZonesReport()->toArray(),
        ];
    }

    public function getZonesReport(array $filters = []): ReportZonesDto
    {
        $month = $filters['month'] ?? now()->month;
        $year = $filters['year'] ?? now()->year;
        $distritos = $filters['distritos'] ?? [];

        $dataRaw = $this->repo->getRutasZonesReport($month, $year, $distritos);

        $estadosVisitasKeys = array_fill_keys(EstadoVisita::pluck('id')->toArray(), 0);

        $data = $dataRaw->groupBy('distrito_id')
            ->map(function ($rows) use ($estadosVisitasKeys) {
                $estados = collect($rows)->pluck('total', 'estado_visita_id')->toArray();
                $allEstados = array_replace($estadosVisitasKeys, $estados);
                return [
                    'distrito_id' => $rows->first()['distrito_id'],
                    'distrito' => $rows->first()['distrito_name'],
                    'estados' => $allEstados,
                    'total_visitas' => collect($rows)->sum('total'),
                ];
            })->values()->toArray();

        $totalPerEstado = $dataRaw->groupBy('estado_visita_id')->map->sum('total')->toArray();
        $totalPerEstado = array_replace($estadosVisitasKeys, $totalPerEstado);
        $totalVisitas = array_sum($totalPerEstado);

        return new ReportZonesDto(
            $totalVisitas,
            $totalPerEstado,
            $data,
            compact('month', 'year', 'distritos')
        );
    }
}
