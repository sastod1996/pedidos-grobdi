<?php

namespace App\Http\Requests\visitadoras\metas;

use Illuminate\Foundation\Http\FormRequest;


class UpdateVisitorGoalDebitedFieldsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'debited_amount' => 'required|numeric|min:0|decimal:0,2',
            'debited_datetime' => 'required|date',
            'debit_comment' => 'required|string',
        ];
    }
    public function messages(): array
    {
        return [
            'debited_amount.required' => 'El monto debitado es obligatorio.',
            'debited_amount.numeric' => 'El monto debitado debe ser un número.',
            'debited_amount.decimal' => 'El monto solo puede tener 2 números decimales como maximo.',
            'debited_amount.min' => 'El monto debitado minimo es 0.',

            'debited_datetime.required' => 'La fecha y hora del débito son obligatorias.',
            'debited_datetime.date' => 'La fecha y hora del débito deben tener un formato válido.',

            'debit_comment.required' => 'El comentario del débito es obligatorio.',
            'debit_comment.string' => 'El comentario del débito debe ser texto válido.',
        ];
    }

}
