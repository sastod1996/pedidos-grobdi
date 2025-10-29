<?php

namespace App\Http\Requests\visitadoras\metas;

use Illuminate\Foundation\Http\FormRequest;

class StoreGoalNotReachedConfigRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'details' => 'required|array|min:1',
            'details.*.initial_percentage' => 'required|numeric|min:0|max:99.99',
            'details.*.final_percentage' => 'required|numeric|min:0|max:99.99',
            'details.*.commission' => 'required|numeric|min:0|max:99.99'
        ];
    }
    public function messages(): array
    {
        return [
            'name.required' => 'El nombre de la configuración es obligatorio.',
            'name.max' => 'El nombre de la configuración es muy largo.',
            'details.required|details.min' => 'Debe agregar al menos un rango de porcentajes.',
            'details.array' => 'El campo de detalles debe ser un arreglo válido.',
            'details.*.initial_percentage.required' => 'El porcentaje inicial es obligatorio.',
            'details.*.initial_percentage.numeric' => 'El porcentaje inicial debe ser un número.',
            'details.*.initial_percentage.min' => 'El porcentaje inicial no puede ser menor que 0.',
            'details.*.initial_percentage.max' => 'El porcentaje inicial no puede ser mayor que 99.99.',
            'details.*.final_percentage.required' => 'El porcentaje final es obligatorio.',
            'details.*.final_percentage.numeric' => 'El porcentaje final debe ser un número.',
            'details.*.final_percentage.min' => 'El porcentaje final no puede ser menor que 0.',
            'details.*.final_percentage.max' => 'El porcentaje final no puede ser mayor que 99.99.',
            'details.*.commission.required' => 'La comisión de la visitadora por meta no cumplida es obligatoria.',
            'details.*.commission.numeric' => 'La comisión debe ser un número.',
            'details.*.commission.min' => 'La comisión no puede ser menor que 0.',
            'details.*.commission.max' => 'La comisión no puede ser mayor que 99.99.',
        ];
    }
}
