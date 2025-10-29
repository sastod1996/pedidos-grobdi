<?php

namespace App\Http\Requests\muestras;

use Illuminate\Foundation\Http\FormRequest;

class DisableMuestraRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $muestra = $this->route('muestra');
        $user = $this->user();

        if ($user->hasRole('coordinador-lineas') && $muestra->aprobado_jefe_comercial) {
            return false;
        }

        return $user->can('muestras.disable');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'delete_reason' => 'required|string|min:5',
        ];
    }

    public function messages(): array
    {
        return [
            'delete_reason.required' => 'El motivo de deshabilitación es obligatorio.',
            'delete_reason.min' => 'El motivo debe tener al menos 5 caracteres.',
        ];
    }
}
