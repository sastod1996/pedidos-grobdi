<?php

namespace App\Application\Services\Reports\DoctorsReport;

use App\Application\DTOs\Reports\Doctores\ReportDoctorsDto;
use App\Application\DTOs\Reports\Doctores\ReportSeguimientoDto;
use App\Application\DTOs\Reports\Doctores\ReportTipoDoctorDto;
use App\Application\Services\Reports\ReportBaseService;
use App\Infrastructure\Repository\ReportsRepository;
use App\Models\Pedidos;
use App\Shared\Helpers\GetPercentageHelper;
use Brick\Math\RoundingMode;
use Brick\Money\Money;
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
            'seguimientoReport' => $this->getSeguimientoReport()->toArray(),
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
            $doctorData['especialidad'] ?? null,
            $doctorData['distrito'] ?? null,
            $doctorData['centro_salud'] ?? null,
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

    public function getSeguimientoReport(array $filters = []): ReportSeguimientoDto
    {
        $start_date_1 = Carbon::parse($filters['start_date_1'] ?? now()->subMonths(2)->startOfMonth())->startOfDay();
        $start_date_2 = Carbon::parse($filters['start_date_2'] ?? now()->subMonths(1)->startOfMonth())->startOfDay();
        $end_date_1 = Carbon::parse($filters['end_date_1'] ?? now()->subMonths(2)->endOfMonth())->endOfDay();
        $end_date_2 = Carbon::parse($filters['end_date_2'] ?? now()->subMonths(1)->endOfMonth())->endOfDay();

        $data1 = Pedidos::selectRaw('id_doctor, SUM(prize) as total_amount, COUNT(*) as total_quantity')
            ->groupBy('id_doctor')
            ->whereNotNull('id_doctor')->where('id_doctor', '!=', '')
            ->whereBetween('created_at', [$start_date_1, $end_date_1])
            ->get()
            ->keyBy('id_doctor');

        $data2 = Pedidos::selectRaw('id_doctor, SUM(prize) as total_amount, COUNT(*) as total_quantity')
            ->groupBy('id_doctor')
            ->whereNotNull('id_doctor')->where('id_doctor', '!=', '')
            ->whereBetween('created_at', [$start_date_2, $end_date_2])
            ->get()
            ->keyBy('id_doctor');

        $totalAmount1 = $data1->sum('total_amount');
        $moneyTotal1 = Money::of($totalAmount1 ?: 0, 'PEN');
        $avgAmount1 = $data1->isEmpty()
            ? Money::of(0, 'PEN')
            : $moneyTotal1->dividedBy($data1->count(), RoundingMode::HALF_UP);

        // Monto total en período 2
        $totalAmount2 = $data2->sum('total_amount');
        $moneyTotal2 = Money::of($totalAmount2 ?: 0, 'PEN');
        $avgAmount2 = $data2->isEmpty()
            ? Money::of(0, 'PEN')
            : $moneyTotal2->dividedBy($data2->count(), RoundingMode::HALF_UP);

        // Promedios de cantidad (float)
        $avgQuantity1 = $data1->isEmpty() ? 0.0 : ($data1->sum('total_quantity') / $data1->count());
        $avgQuantity2 = $data2->isEmpty() ? 0.0 : ($data2->sum('total_quantity') / $data2->count());


        $allDoctorIds = $data1->keys()->merge($data2->keys())->unique();

        $comparison = $allDoctorIds->map(function ($id_doctor) use ($data1, $data2) {
            $prev = $data1->get($id_doctor) ?? (object) ['total_amount' => 0, 'total_quantity' => 0];
            $curr = $data2->get($id_doctor) ?? (object) ['total_amount' => 0, 'total_quantity' => 0];

            $currTotalAmount = Money::of($curr->total_amount, 'PEN');
            $prevTotalAmount = Money::of($prev->total_amount, 'PEN');

            $amountFluctuation = $currTotalAmount->minus($prevTotalAmount);
            $quantityFluctuation = $curr->total_quantity - $prev->total_quantity;

            // Opcional: porcentaje de crecimiento (evita división por cero)
            $amountFluctuationRate = $prevTotalAmount->isGreaterThan(0) ?
                ($amountFluctuation->getAmount()->toFloat() / $prevTotalAmount->getAmount()->toFloat()) * 100 :
                ($currTotalAmount->isGreaterThan(0) ? 100 : 0);

            $quantityFluctuationRate = $prev->total_quantity > 0
                ? ($quantityFluctuation / $prev->total_quantity) * 100
                : ($curr->total_quantity > 0 ? 100 : 0);

            return [
                'id_doctor' => $id_doctor,
                'prev_amount' => $prev->total_amount,
                'curr_amount' => $curr->total_amount,
                'amount_fluctuation' => $amountFluctuation->getAmount()->toFloat(),
                'amount_fluctuation_rate' => number_format($amountFluctuationRate, 2),
                'prev_quantity' => $prev->total_quantity,
                'curr_quantity' => $curr->total_quantity,
                'quantity_fluctuation' => $quantityFluctuation,
                'quantity_fluctuation_rate' => number_format($quantityFluctuationRate, 2),
            ];
        });

        $topAmountIncrease = $comparison
            ->filter(fn($item) => $item['amount_fluctuation'] > 0)
            ->sortByDesc('amount_fluctuation')
            ->take(10)
            ->values();

        // 2. Top 10: mayor disminución en MONTO (solo negativos, orden ascendente → más negativo primero)
        $topAmountDecrease = $comparison
            ->filter(fn($item) => $item['amount_fluctuation'] < 0)
            ->sortBy('amount_fluctuation') // más negativo primero
            ->take(10)
            ->values();

        // 3. Top 10: mayor aumento en CANTIDAD (solo positivos)
        $topQuantityIncrease = $comparison
            ->filter(fn($item) => $item['quantity_fluctuation'] > 0)
            ->sortByDesc('quantity_fluctuation')
            ->take(10)
            ->values();

        // 4. Top 10: mayor disminución en CANTIDAD (solo negativos)
        $topQuantityDecrease = $comparison
            ->filter(fn($item) => $item['quantity_fluctuation'] < 0)
            ->sortBy('quantity_fluctuation') // más negativo primero
            ->take(10)
            ->values();

        return new ReportSeguimientoDto(
            $topAmountIncrease->toArray(),
            $topAmountDecrease->toArray(),
            $topQuantityIncrease->toArray(),
            $topQuantityDecrease->toArray(),
            $avgAmount1,
            $avgAmount2,
            $avgQuantity1,
            $avgQuantity2,
            $comparison->toArray(),
            compact('start_date_1', 'end_date_1', 'start_date_2', 'end_date_2')
        );
    }
}
