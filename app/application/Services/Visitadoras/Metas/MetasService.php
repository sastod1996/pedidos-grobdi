<?php

namespace App\Application\Services\Visitadoras\Metas;

use App\Application\Services\Pedidos\PedidosService;
use App\Models\GoalNotReachedConfig;
use App\Models\GoalNotReachedConfigDetail;
use App\Models\MonthlyVisitorGoal;
use App\Models\Pedidos;
use App\Models\User;
use App\Models\VisitorGoal;
use Brick\Money\Money;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use LogicException;

class MetasService
{
    public function __construct(protected readonly PedidosService $pedidosService) {}

    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {
            $month = $data['month'];
            $tipoMedico = $data['tipo_medico'];
            $isGeneralGoal = $data['is_general_goal'];

            $startDate = Carbon::parse($month)->startOfMonth();
            $endDate = Carbon::parse($month)->endOfMonth();

            $existingGoal = MonthlyVisitorGoal::where('tipo_medico', $tipoMedico)
                ->whereYear('start_date', $startDate->year)
                ->whereMonth('start_date', $startDate->month)
                ->first();

            if ($existingGoal) {
                throw new LogicException('Ya existe una meta para este mes y este tipo de doctor.');
            }

            $goalNotReachedConfig = GoalNotReachedConfig::where('state', true)->firstOrFail();

            // Se usa toDateString para no incluir la hora
            $monthlyGoal = MonthlyVisitorGoal::create([
                'goal_not_reached_config_id' => $goalNotReachedConfig->id,
                'tipo_medico' => $tipoMedico,
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
            ]);

            $visitadorasIds = User::where('role_id', 6)->pluck('id');

            if ($visitadorasIds->isEmpty()) {
                throw ValidationException::withMessages([
                    'general_goal' => 'No hay visitadoras para asignar las metas.',
                ]);
            }

            // Los porcentajes se dividen entre 100 para pasar de 62.5% => 0.625
            if ($isGeneralGoal) {
                foreach ($visitadorasIds as $id) {
                    VisitorGoal::create([
                        'user_id' => $id,
                        'monthly_visitor_goal_id' => $monthlyGoal->id,
                        'goal_amount' => $data['goal_amount'],
                        'commission_percentage' => $data['commission_percentage'] / 100,
                    ]);
                }
            } else {
                foreach ($data['visitor_goals'] as $goalData) {
                    VisitorGoal::create([
                        'user_id' => $goalData['user_id'],
                        'monthly_visitor_goal_id' => $monthlyGoal->id,
                        'goal_amount' => $goalData['goal_amount'],
                        'commission_percentage' => $goalData['commission_percentage'] / 100,
                    ]);
                }
            }

            return $monthlyGoal->load('visitorGoals');
        });
    }

    /**
     * Get paginated list of metas with optional filters.
     *
     * @param  array  $filters  ['month' => 'YYYY-MM'|'', 'tipo_medico' => 'prescriptor'|...]
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getListOfMetas(array $filters = [], int $resultsPerPage = 15)
    {
        $query = MonthlyVisitorGoal::query();

        // Filter by tipo_medico if provided
        if (! empty($filters['tipo_medico'])) {
            $query->where('tipo_medico', $filters['tipo_medico']);
        }

        // Filter by month (input from <input type="month"> returns YYYY-MM)
        if (! empty($filters['month'])) {
            try {
                $parts = explode('-', $filters['month']);
                if (count($parts) >= 2) {
                    $year = (int) $parts[0];
                    $month = (int) $parts[1];
                    $query->whereYear('start_date', $year)->whereMonth('start_date', $month);
                }
            } catch (\Throwable $e) {
                // Ignore invalid month format and return unfiltered results
            }
        }

        $paginator = $query->orderBy('start_date', 'desc')->paginate($resultsPerPage)->appends(array_filter($filters, fn ($v) => $v !== null && $v !== ''));

        $items = $paginator->getCollection()->map(function ($meta) {
            return [
                'id' => $meta->id,
                'tipo_medico' => $meta->tipo_medico,
                'date' => $meta->month,
            ];
        });

        $paginator->setCollection($items);

        return $paginator;
    }

    public function getListOfVisitorGoalByMetaId(int $metaId)
    {
        $monthlyGoal = MonthlyVisitorGoal::with([
            'visitorGoals' => function ($query) {
                $query->with('visitadora:id');
            },
        ])->findOrFail($metaId);

        $results = [];

        foreach ($monthlyGoal->visitorGoals as $visitorGoal) {
            $results[] = $this->getVisitorGoalMetrics($visitorGoal->id);
        }

        return $results;
    }

    public function getVisitorGoalMetrics(int $visitorGoalId)
    {
        $visitorGoal = VisitorGoal::with([
            'monthlyVisitorGoal:id,start_date,end_date,goal_not_reached_config_id',
            'visitadora:id,name',
        ])->findOrFail($visitorGoalId);

        $commonMetrics = $this->calculateCommonGoalMetrics($visitorGoal);

        $rawPercentage = $commonMetrics['rawPercentage'];
        $totalAmountWithoutIGV = $commonMetrics['totalAmountWithoutIGV'];
        $goalAmount = $commonMetrics['goalAmount'];

        $commission_percentage = $visitorGoal->commission_percentage;

        $currentPercentage = round($rawPercentage * 100, 2);

        $commissionRate = $this->calculateCommissionRate(
            $rawPercentage,
            $commission_percentage,
            $visitorGoal->monthlyVisitorGoal->goal_not_reached_config_id
        );

        $commissionAmount = $totalAmountWithoutIGV->multipliedBy($commissionRate, \Brick\Math\RoundingMode::HALF_UP);

        // Format debited_amount (Money) and debited_datetime (Carbon) to readable strings
        $formattedDebitedAmount = $visitorGoal->debited_amount instanceof Money ? $visitorGoal->debited_amount->getAmount()->__toString() : ($visitorGoal->debited_amount ?? 'Sin monto debitado');
        $formattedDebitedDatetime = null;
        if ($visitorGoal->debited_datetime) {
            try {
                $formattedDebitedDatetime = $visitorGoal->debited_datetime instanceof \DateTime ? Carbon::parse($visitorGoal->debited_datetime)->toDateTimeString() : (string) $visitorGoal->debited_datetime;
            } catch (\Throwable $e) {
                $formattedDebitedDatetime = (string) $visitorGoal->debited_datetime;
            }
        } else {
            $formattedDebitedDatetime = 'No se ha debitado aún';
        }

        return [
            'id' => $visitorGoal->id,
            'visitadora' => [
                'id' => $visitorGoal->visitadora->id,
                'name' => $visitorGoal->visitadora->name,
            ],
            'commission_percentage' => $commission_percentage * 100,
            'goal_amount' => $goalAmount->getAmount()->__toString(),
            'porcentaje_actual' => $currentPercentage,
            'comision_actual' => $commissionRate * 100,
            'total_sub_total_sin_igv' => $totalAmountWithoutIGV->getAmount()->__toString(),
            'monto_comisionado' => $commissionAmount->getAmount()->__toString(),
            'debited_amount' => $formattedDebitedAmount,
            'debited_datetime' => $formattedDebitedDatetime,
        ];
    }

    public function getCurrentPercentageOfGoalAmount(float|Money $totalSubTotal, float|Money $goalAmount): float
    {
        $totalSubTotalMoney = $totalSubTotal instanceof Money ? $totalSubTotal : Money::of($totalSubTotal, 'PEN');

        $goalAmountMoney = $goalAmount instanceof Money ? $goalAmount : Money::of($goalAmount, 'PEN');

        $rawPercentage = 0.0;
        if (! $goalAmountMoney->isZero()) {
            $goalDecimal = $goalAmountMoney->getAmount();
            $totalDecimal = $totalSubTotalMoney->getAmount();

            $ratio = $totalDecimal->dividedBy($goalDecimal, 6, \Brick\Math\RoundingMode::HALF_UP);
            $rawPercentage = $ratio->toFloat();
        }

        // Devolver el porcentaje actual redondeado
        return $rawPercentage;
    }

    public function getDataForChart(VisitorGoal $visitorGoal)
    {
        $commonMetrics = $this->calculateCommonGoalMetrics($visitorGoal);

        $rawPercentage = $commonMetrics['rawPercentage'];
        $totalAmountWithoutIGV = $commonMetrics['totalAmountWithoutIGV'];
        $goalAmount = $commonMetrics['goalAmount'];

        $visitorId = $visitorGoal->visitadora->id;

        $totalPedidos = Pedidos::where('visitadora_id', $visitorId)->count();

        $currentPercentage = round($rawPercentage * 100, 2);
        $debited_amount = $visitorGoal->debited_amount;
        // Format debited_amount for API output
        $formattedDebitedAmount = $debited_amount instanceof Money ? $debited_amount->getAmount()->__toString() : ($debited_amount ?? null);

        // Calcular el faltante (Asegurar que goalAmount y totalAmountWithoutIGV son objetos Money)
        $faltante = $goalAmount->minus($totalAmountWithoutIGV);
        $faltanteParaMeta = $faltante->isPositive() ? $faltante : Money::of(0, 'PEN');

        return [
            'total_pedidos' => $totalPedidos,
            'total_amount_without_igv' => $totalAmountWithoutIGV->getAmount()->__toString(),
            'faltante_para_meta' => $faltanteParaMeta->getAmount()->__toString(),
            'avance_meta_general' => $currentPercentage,
            'commissioned_amount' => $formattedDebitedAmount,
        ];
    }

    private function calculateCommonGoalMetrics(VisitorGoal $visitorGoal): array
    {
        $visitadoraId = $visitorGoal->user_id ?? $visitorGoal->visitadora->id; // Ajustar según qué relación uses
        $startDate = $visitorGoal->monthlyVisitorGoal->start_date;
        $endDate = $visitorGoal->monthlyVisitorGoal->end_date;
        // Asegurar que es un objeto Money si goal_amount no lo es
        $goalAmount = $visitorGoal->goal_amount instanceof Money ? $visitorGoal->goal_amount : Money::of($visitorGoal->goal_amount, 'PEN');

        // 1. Calcular el subtotal total
        // When calculating totals for a visitor goal, only include pedidos whose doctor's tipo_medico
        // matches the monthly goal's tipo_medico. This ensures 'prescriptor' and 'comprador' months
        // use their respective doctors/orders.
        $tipoMedicoFilter = $visitorGoal->monthlyVisitorGoal->tipo_medico ?? null;

        $totalSubTotalRaw = $this->pedidosService->calculateTotalSubTotal(function ($query) use ($visitadoraId, $startDate, $endDate, $tipoMedicoFilter) {
            // The base query in calculateTotalSubTotal already joins `pedidos`, so filter by visitadora and date
            $query->where('pedidos.visitadora_id', $visitadoraId)
                ->whereBetween('pedidos.created_at', [Carbon::parse($startDate)->startOfDay(), Carbon::parse($endDate)->endOfDay()]);

            // Add a join to doctor and filter by tipo_medico when provided
            if (!empty($tipoMedicoFilter)) {
                // ensure we join the doctor table to filter by tipo_medico
                $query->join('doctor as dr', 'pedidos.id_doctor', '=', 'dr.id')
                    ->where('dr.tipo_medico', $tipoMedicoFilter);
            }
        });

        $totalSubTotalMoney = Money::of($totalSubTotalRaw, 'PEN');

        // 2. Calcular el total sin IGV (82% del subtotal total)
        $totalAmountWithoutIGV = $totalSubTotalMoney->multipliedBy('0.82', \Brick\Math\RoundingMode::HALF_UP);

        // 3. Calcular el porcentaje de avance (usando el subtotal)
        // Nota: El método getVisitorGoalMetrics usa $totalSubTotalMoney vs $goalAmount.
        // getLoQueSea usa $totalSubTotalRaw vs $goalAmount (que es un Money), lo cual puede ser un error si $totalSubTotalRaw no es un objeto Money.
        // Asumiré que quieres comparar el total monetario *obtenido* con la meta monetaria.
        // Usaré $totalSubTotalMoney si getCurrentPercentageOfGoalAmount lo espera.
        // Si getCurrentPercentageOfGoalAmount espera un int/float, usa $totalSubTotalRaw.
        $rawPercentage = $this->getCurrentPercentageOfGoalAmount($totalSubTotalMoney, $goalAmount);

        return [
            'totalSubTotalMoney' => $totalSubTotalMoney,
            'totalAmountWithoutIGV' => $totalAmountWithoutIGV,
            'rawPercentage' => $rawPercentage,
            'goalAmount' => $goalAmount,
        ];
    }

    private function calculateCommissionRate(
        float $currentPercentage,
        float $fullCommissionPercentage,
        ?int $goalNotReachedConfigId
    ): float {
        if ($currentPercentage >= 100) {
            return $fullCommissionPercentage;
        }

        if ($goalNotReachedConfigId === null) {
            return 0.0;
        }

        // Buscar el rango correspondiente
        $configDetail = GoalNotReachedConfigDetail::where('goal_not_reached_config_id', $goalNotReachedConfigId)
            ->where('initial_percentage', '<=', $currentPercentage)
            ->where('final_percentage', '>=', $currentPercentage)
            ->first();

        return $configDetail?->commission ?? 0.0;
    }

    public function getPedidosDoctorStatsByMonthlyVisitorGoal(int $id)
    {
        $monthlyVisitorGoal = MonthlyVisitorGoal::findOrFail($id);
        $range = $monthlyVisitorGoal->month;
        if ($range['type'] === 'month') {
            return $this->pedidosService->getPedidosDetailsByTipoMedico($monthlyVisitorGoal->tipo_medico, $range['value'], $range['year']);
        } else {
            return $this->pedidosService->getPedidosDetailsByTipoMedico($monthlyVisitorGoal->tipo_medico, startDate: $range['start_date'], endDate: $range['end_date']);
        }
    }
}
