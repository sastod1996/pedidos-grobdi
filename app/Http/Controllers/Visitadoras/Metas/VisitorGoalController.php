<?php

namespace App\Http\Controllers\Visitadoras\Metas;

use App\Application\Services\Visitadoras\Metas\MetasService;
use App\Http\Controllers\Controller;
use App\Http\Requests\visitadoras\metas\UpdateVisitorGoalDebitedFieldsRequest;
use App\Models\VisitorGoal;
use Illuminate\Http\Request;

class VisitorGoalController extends Controller
{
    public function __construct(protected readonly MetasService $metasService) {}

    /**
     * Update the specified resource in storage.
     */
    public function updateDebitedAmount(UpdateVisitorGoalDebitedFieldsRequest $request, VisitorGoal $visitorGoal)
    {
        $validated = $request->validated();

        try {
            $visitorGoal->debited_amount = $validated['debited_amount'];
            $visitorGoal->debited_datetime = $validated['debited_datetime'];
            $visitorGoal->debit_comment = $validated['debit_comment'];

            $visitorGoal->save();

            return response()->json([
                'success' => true,
                'message' => 'Monto debitado actualizado correctamente.',
                'data' => $visitorGoal->fresh(),
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error al actualizar el monto debitado.',
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function showLogged(Request $request)
    {
        if (! $request->expectsJson()) {
            return view('bonificaciones.visitadoras-view');
        }

        $tipoMedico = $request->input('tipo_medico') ?? 'Prescriptor';
        $month = $request->input('month') ?? now()->month;
        $year = $request->input('year') ?? now()->year;

        try {
            $visitorGoal = VisitorGoal::with([
                'visitadora:id,name',
                'monthlyVisitorGoal:id,start_date,end_date,tipo_medico,goal_not_reached_config_id',
            ])
                ->select('id', 'user_id', 'goal_amount', 'debited_amount', 'monthly_visitor_goal_id')
                ->where('user_id', $request->user()->id)
                ->whereHas('monthlyVisitorGoal', function ($query) use ($month, $year, $tipoMedico) {
                    $query->whereMonth('start_date', $month)
                        ->whereYear('start_date', $year)
                        ->where('tipo_medico', 'LIKE', "%$tipoMedico%");
                })
                ->first();

            if (! $visitorGoal) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontró una meta activa para este mes y tipo de médico.',
                ], 404);
            }

            $chartData = $this->metasService->getDataForChart($visitorGoal);
            $visitadoraId = $visitorGoal->visitadora?->id ?? $visitorGoal->user_id;
            $doctorsData = $this->metasService->getPedidosDoctorStatsByMonthlyVisitorGoal(
                $visitorGoal->monthlyVisitorGoal->id,
                $visitadoraId
            );
            $metaSummary = $this->metasService->mapMonthlyGoalToSummary($visitorGoal->monthlyVisitorGoal);

            $doctorsData = is_array($doctorsData) ? $doctorsData : $doctorsData->values()->all();

            return response()->json([
                'success' => true,
                'message' => 'Datos para chart obtenidos.',
                'chart-data' => $chartData,
                'meta-data' => $this->metasService->getListOfVisitorGoalByMetaId($visitorGoal->monthlyVisitorGoal->id),
                'doctors-data' => $doctorsData,
                'meta' => $metaSummary,
            ]);
        } catch (\Throwable $th) {
            return response()->json(['success' => false, 'message' => $th->getMessage()], 500);
        }
    }
}
