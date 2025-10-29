<?php

namespace App\Http\Requests\muestras;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOrUpdateMuestraRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('muestras.update') || $this->user->can('muestras.store');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nombre_muestra' => 'required|string|max:255',
            'clasificacion_id' => 'required|exists:clasificaciones,id',
            'cantidad_de_muestra' => 'required|numeric|min:1|max:10000',
            'observacion' => 'nullable|string',
            'tipo_frasco' => ['required', Rule::in(['frasco original', 'frasco muestra'])],
            'id_doctor' => 'required|exists:doctor,id',
            'clasificacion_presentacion_id' => 'nullable|exists:clasificacion_presentaciones,id',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'cantidad_de_muestra.min' => 'La cantidad de muestra debe ser al menos 1.',
            'cantidad_de_muestra.max' => 'La cantidad de muestra no puede exceder 10,000.',
            'foto.image' => 'El archivo debe ser una imagen válida.',
            'foto.mimes' => 'La imagen debe ser de tipo jpg, jpeg, png o webp.',
            'tipo_frasco.in' => 'El tipo de frasco debe ser "frasco original" o "frasco muestra".',
        ];
    }
}
