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
        return $this->user()->can('muestras.update') || $this->user()->can('muestras.store');
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
            'nombre_muestra.required' => 'El nombre de la muestra es obligatorio.',
            'nombre_muestra.string' => 'El nombre de la muestra debe ser un texto válido.',
            'nombre_muestra.max' => 'El nombre de la muestra no puede exceder los 255 caracteres.',

            'clasificacion_id.required' => 'La clasificación de la muestra es obligatoria.',
            'clasificacion_id.exists' => 'La clasificación seleccionada no es válida.',

            'cantidad_de_muestra.required' => 'La cantidad de muestra es obligatoria.',
            'cantidad_de_muestra.numeric' => 'La cantidad de muestra debe ser un número.',
            'cantidad_de_muestra.min' => 'La cantidad de muestra debe ser al menos 1.',
            'cantidad_de_muestra.max' => 'La cantidad de muestra no puede exceder 10,000.',

            'observacion.string' => 'La observación debe ser un texto válido.',

            'tipo_frasco.required' => 'El tipo de frasco es obligatorio.',
            'tipo_frasco.in' => 'El tipo de frasco debe ser "frasco original" o "frasco muestra".',

            'id_doctor.required' => 'El doctor responsable es obligatorio.',
            'id_doctor.exists' => 'El doctor seleccionado no es válido.',

            'clasificacion_presentacion_id.exists' => 'La presentación de clasificación seleccionada no es válida.',

            'foto.image' => 'El archivo debe ser una imagen válida.',
            'foto.mimes' => 'La imagen debe ser de tipo jpg, jpeg, png o webp.',
            'foto.max' => 'La imagen no puede superar los 2 MB de tamaño.',
        ];
    }
}
