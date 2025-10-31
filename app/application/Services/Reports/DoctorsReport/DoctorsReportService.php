<?php

namespace App\Application\Services\Reports\DoctorsReport;

use App\Application\DTOs\Reports\Doctores\ReportDoctorsDto;
use App\Application\DTOs\Reports\Doctores\ReportTipoDoctorDto;
use App\Application\Services\Reports\ReportBaseService;
use App\Infrastructure\Repository\ReportsRepository;
use App\Shared\Helpers\GetPercentageHelper;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class DoctorsReportService extends ReportBaseService
{
    protected string $cachePrefix = 'doctors_report_';

    public function __construct(protected ReportsRepository $repo)
    {
    }

    public function createInitialReport(): mixed
    {
        return [
            'doctorReport' => $this->getDoctorReport()->toArray(),
            'tipoDoctorReport' => $this->getTipoDoctorReport()->toArray(),
        ];
    }

    public function getDoctorReport(array $filters = []): ReportDoctorsDto
    {
        $id_doctor = $filters['id_doctor'] ?? null;
        $start_date = Carbon::parse($filters['start_date'] ?? now()->startOfMonth())->startOfDay();
        $end_date = Carbon::parse($filters['end_date'] ?? now())->endOfDay();

        $doctorData = $id_doctor ? $this->repo->getDoctorInfo($id_doctor) : $this->repo->getTopDoctorByAmountInfo($start_date, $end_date);

        $id_doctor = $doctorData['id'];

        $data = [
            'amount_spent_anually' => $this->repo->getAmountSpentAnuallyByDoctor($start_date, $end_date, $id_doctor),
            'amount_spent_monthly_grouped_by_tipo' => $this->repo->getAmountSpentMonthlyGroupedByTipo($start_date, $end_date, $id_doctor),
            'most_consumed_products_monthly' => $this->repo->getMostConsumedProductsMonthlyByDoctor($start_date, $end_date, $id_doctor),
        ];

        return new ReportDoctorsDto(
            $doctorData['is_top_doctor'],
            $doctorData['name'],
            $doctorData['tipo_medico'],
            $data,
            compact('id_doctor', 'start_date', 'end_date')
        );
    }

    public function getTipoDoctorReport(array $filters = []): ReportTipoDoctorDto
    {
        $year = $filters['year'] ?? now()->year;

        // Obtener datos crudos
        $resumeRaw = $this->repo->getDoctoresByTipoAndYear($year);
        $detalleRaw = $this->repo->getPedidosByTipoAndMonth($year);

        // 1. Procesar resumen
        $resume = $this->buildResume($resumeRaw);

        // 2. Procesar datos por mes
        $data = $this->buildDataByMonth($detalleRaw);

        // Construir y devolver el DTO
        return new ReportTipoDoctorDto(
            $resume['total_doctores'],
            $resume['total_amount'],
            $resume['total_pedidos'],
            $resume['top_tipo_by_amount'],
            $resume['top_tipo_by_pedidos'],
            $resume['tipos'],
            $data,
            compact('year')
        );
    }

    private function buildResume(Collection $raw): array
    {
        $totalAmount = $raw->sum('total_amount');
        $totalPedidos = $raw->sum('total_pedidos');
        $totalDoctores = $raw->sum('total_doctores');

        $tiposResume = $raw->groupBy('tipo_medico')
            ->map(function ($tipo, $nombre) use ($totalAmount, $totalPedidos) {
                $total_doctores = (int) $tipo->sum('total_doctores');
                $total_amount = (float) $tipo->sum('total_amount');
                $total_pedidos = (int) $tipo->sum('total_pedidos');

                return [
                    'tipo_medico' => $nombre,
                    'total_doctores' => $total_doctores,
                    'total_amount' => $total_amount,
                    'total_pedidos' => $total_pedidos,
                    'percentage_amount' => GetPercentageHelper::calculate($total_amount, $totalAmount),
                    'percentage_pedidos' => GetPercentageHelper::calculate($total_pedidos, $totalPedidos),
                ];
            })->values();

        $topTipoByAmount = $raw->sortByDesc('total_amount')->first()?->tipo_medico ?? 'N/A';
        $topTipoByPedidos = $raw->sortByDesc('total_pedidos')->first()?->tipo_medico ?? 'N/A';

        return [
            'total_doctores' => $totalDoctores,
            'top_tipo_by_amount' => $topTipoByAmount,
            'top_tipo_by_pedidos' => $topTipoByPedidos,
            'total_amount' => $totalAmount,
            'total_pedidos' => $totalPedidos,
            'tipos' => $tiposResume->toArray(),
        ];
    }

    private function buildDataByMonth(Collection $raw): array
    {
        $range = range(1, 12);

        $grouped = [];
        foreach ($raw as $item) {
            $mes = (int) $item->month;
            $grouped[$mes][] = $item;
        }

        $data = [];
        foreach ($range as $month) {
            $items = $grouped[$month] ?? [];

            $totalAmount = array_sum(array_column($items, 'total_amount'));
            $totalPedidos = array_sum(array_column($items, 'total_pedidos'));

            $tipos = [];
            foreach ($items as $i) {
                $tipo = $i->tipo_medico;
                $tipos[$tipo]['total_amount'] = ($tipos[$tipo]['total_amount'] ?? 0) + $i->total_amount;
                $tipos[$tipo]['total_pedidos'] = ($tipos[$tipo]['total_pedidos'] ?? 0) + $i->total_pedidos;
            }

            $tiposResume = [];
            foreach ($tipos as $tipo => $vals) {
                $tiposResume[] = [
                    'tipo_medico' => $tipo,
                    'total_amount' => $vals['total_amount'],
                    'total_pedidos' => $vals['total_pedidos'],
                    'percentage_total_amount' => GetPercentageHelper::calculate($vals['total_amount'], $totalAmount),
                    'percentage_total_pedidos' => GetPercentageHelper::calculate($vals['total_pedidos'], $totalPedidos),
                ];
            }

            $data[] = [
                'month' => $month,
                'total_amount' => (float) $totalAmount,
                'total_pedidos' => (int) $totalPedidos,
                'tipos_resume' => $tiposResume,
            ];
        }

        return $data;
    }
}
