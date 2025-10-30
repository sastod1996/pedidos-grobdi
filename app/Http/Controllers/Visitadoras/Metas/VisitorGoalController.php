<?php

namespace App\Http\Controllers\Visitadoras\Metas;

use App\Application\Services\Visitadoras\Metas\MetasService;
use App\Http\Controllers\Controller;
use App\Http\Requests\visitadoras\metas\UpdateVisitorGoalDebitedFieldsRequest;
use App\Models\VisitorGoal;

class VisitorGoalController extends Controller
{
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
}


