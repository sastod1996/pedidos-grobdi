<?php

namespace App\Http\Requests\pedidos\comercial;

use App\Models\Doctor;
use Illuminate\Foundation\Http\FormRequest;

class PedidosComercialFilterRequest extends FormRequest
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
            'fecha_inicio' => ['nullable', 'date'],
            'fecha_fin' => ['nullable', 'date', 'after_or_equal:fecha_inicio'],
            'doctor' => [
                'nullable',
                'string',
                'max:255',
                function (string $attribute, mixed $value, callable $fail): void {
                    if (is_numeric($value) && ! Doctor::whereKey((int) $value)->exists()) {
                        $fail('El doctor seleccionado no existe.');
                    }
                },
            ],
            'visitadora' => ['nullable', 'string', 'max:255'],
            'cliente' => ['nullable', 'string', 'max:255'],
            'order_id' => ['nullable', 'string', 'max:255'],
            'distrito' => ['nullable', 'integer', 'exists:distritos,id'],
        ];
    }
}
