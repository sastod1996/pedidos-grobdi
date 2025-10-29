<?php

namespace App\Http\Requests\visitadoras\metas;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrUpdateMetasRequest extends FormRequest
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
            'month' => 'required|date_format:Y-m', // Year & Month
            'tipo_medico' => 'required|string|max:255',
            'is_general_goal' => 'required|boolean', // If is a single Goal for Every1 or Specific Goals
            // The frontend sends '1' (true) or '0' (false) in the hidden input; use 1/0 in required_if checks
            // Required if is_general_goal === FALSE (0)
            'visitor_goals' => 'required_if:is_general_goal,0|array',
            'visitor_goals.*.user_id' => 'required_if:is_general_goal,0|exists:users,id',
            'visitor_goals.*.goal_amount' => [
                'required_if:is_general_goal,0',
                'nullable',
                'numeric',
                'min:0',
            ],
            'visitor_goals.*.commission_percentage' => [
                'required_if:is_general_goal,0',
                'nullable',
                'numeric',
                'min:0',
                'max:99.99',
            ],
            // Required if is_general_goal === TRUE (1)
            'goal_amount' => [
                'required_if:is_general_goal,1',
                'nullable',
                'numeric',
                'min:0',
            ],
            'commission_percentage' => [
                'required_if:is_general_goal,1',
                'nullable',
                'numeric',
                'min:0',
                'max:99.99',
            ],
        ];
    }
    /**
     * Prepare and normalize input data before validation.
     * - Normalize is_general_goal to 1/0
     * - Convert comma decimals to dot for numeric inputs
     */
    protected function prepareForValidation(): void
    {
        $input = $this->all();

        // Normalize the boolean-ish flag to 1 or 0
        if ($this->has('is_general_goal')) {
            $val = $this->input('is_general_goal');
            $input['is_general_goal'] = in_array($val, [1, '1', true, 'true', 'on'], true) ? 1 : 0;
        }

        $normalizeNumber = function ($v) {
            if (is_string($v)) {
                $v = trim($v);
                // Remove thousand separators (commas or spaces) but keep decimal comma/dot handling.
                // First, replace spaces
                $v = str_replace(' ', '', $v);
                // If value contains both '.' and ',', assume '.' is thousand sep and ',' is decimal -> normalize
                if (strpos($v, '.') !== false && strpos($v, ',') !== false) {
                    $v = str_replace('.', '', $v);
                    $v = str_replace(',', '.', $v);
                } else {
                    // Replace comma by dot (user may use comma as decimal separator)
                    $v = str_replace(',', '.', $v);
                }
                return $v;
            }
            return $v;
        };

        if (array_key_exists('commission_percentage', $input)) {
            $input['commission_percentage'] = $normalizeNumber($input['commission_percentage']);
        }
        if (array_key_exists('goal_amount', $input)) {
            $input['goal_amount'] = $normalizeNumber($input['goal_amount']);
        }

        if (isset($input['visitor_goals']) && is_array($input['visitor_goals'])) {
            foreach ($input['visitor_goals'] as $k => $vg) {
                if (isset($vg['commission_percentage'])) {
                    $input['visitor_goals'][$k]['commission_percentage'] = $normalizeNumber($vg['commission_percentage']);
                }
                if (isset($vg['goal_amount'])) {
                    $input['visitor_goals'][$k]['goal_amount'] = $normalizeNumber($vg['goal_amount']);
                }
            }
        }

        // Infer is_general_goal when frontend hidden input wasn't properly synchronized.
        // If there are visitor_goals with values, prefer per-visitadora mode (0).
        $hasVisitorGoalsWithValues = false;
        if (isset($input['visitor_goals']) && is_array($input['visitor_goals'])) {
            foreach ($input['visitor_goals'] as $vg) {
                if ((isset($vg['commission_percentage']) && $vg['commission_percentage'] !== '') || (isset($vg['goal_amount']) && $vg['goal_amount'] !== '')) {
                    $hasVisitorGoalsWithValues = true;
                    break;
                }
            }
        }

        if ($hasVisitorGoalsWithValues) {
            $input['is_general_goal'] = 0;
        } else {
            // If no visitor-specific values but general fields have values, prefer general mode
            if ((array_key_exists('commission_percentage', $input) && $input['commission_percentage'] !== null && $input['commission_percentage'] !== '') ||
                (array_key_exists('goal_amount', $input) && $input['goal_amount'] !== null && $input['goal_amount'] !== '')) {
                $input['is_general_goal'] = 1;
            }
        }

        $this->merge($input);
    }
    public function messages(): array
    {
        return [
            'month.required' => 'El campo mes es obligatorio.',
            'tipo_medico.required' => 'Debes seleccionar un tipo de médico.',
            'is_general_goal.required' => 'Debe indicar si la meta será para todas las visitadoras o definirá para cada una.',
            'visitor_goals.required_if' => 'Debe especificar metas para cada visitadora.',
            'visitor_goals.array' => 'El campo visitor_goals debe ser un array.',
            'visitor_goals.*.user_id.required_if' => 'Debe indicar el ID de las visitadoras',
            'visitor_goals.*.user_id.exists' => 'Algunos de los User ID no existen.',
            'visitor_goals.*.goal_amount.required_if' => 'Debe especificar una meta por cada visitadora.',
            'visitor_goals.*.goal_amount.numeric' => 'El monto de la meta debe ser numérico.',
            'visitor_goals.*.goal_amount.min' => 'El monto de la meta no puede ser negativo.',
            'visitor_goals.*.commission_percentage.required_if' => 'Cada visitadora debe tener un porcentaje de comisión.',
            'visitor_goals.*.commission_percentage.numeric' => 'El porcentaje de comisión debe ser numérico.',
            'visitor_goals.*.commission_percentage.min' => 'El porcentaje de comisión no puede ser menor que 0%.',
            'visitor_goals.*.commission_percentage.max' => 'El porcentaje de comisión no puede ser mayor que 99.99%.',
            'goal_amount.required_if' => 'Se debe definir la meta general que se aplicará a todas las visitadoras.',
            'goal_amount.numeric' => 'El monto de la meta debe ser numérico.',
            'goal_amount.min' => 'El monto de la meta no puede ser negativo.',
            'commission_percentage.required_if' => 'Se debe definir el porcentaje de comisión que se aplicará a todas las visitadoras.',
            'commission_percentage.numeric' => 'El porcentaje de comisión debe ser numérico.',
            'commission_percentage.min' => 'El porcentaje de comisión no puede ser menor que 0%.',
            'commission_percentage.max' => 'El porcentaje de comisión no puede ser mayor que 99.99%.',
        ];
    }
}
