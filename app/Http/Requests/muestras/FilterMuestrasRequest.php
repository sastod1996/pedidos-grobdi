<?php

namespace App\Http\Requests\muestras;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class FilterMuestrasRequest extends FormRequest
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
            'search' => 'nullable|string|max:255',
            'date_since' => 'nullable|date',
            'date_to' => 'nullable|date',
            'lab_state' => 'nullable|in:Elaborado,Pendiente',
            'order_by' => 'nullable|in:fecha_entrega,fecha_registro',
            'filter_by_date' => 'nullable|in:entrega,registro',
        ];
    }

    /**
     * Get processed filters
     */
    public function getFilters(): array
    {
        $search = $this->filled('search') ? trim($this->search) : null;
        $filterByDate = $this->filter_by_date === 'entrega' ? 'datetime_scheduled' : 'created_at';

        $dateSince = Carbon::parse($this->date_since ?? now()->startOfMonth())->startOfDay();
        $dateTo = Carbon::parse($this->date_to ?? now())->endOfDay();

        $labState = match ($this->lab_state) {
            'Elaborado' => true,
            'Pendiente' => false,
            default => null,
        };


        $orderBy = match (strtolower($this->order_by ?? '')) {
            'fecha_entrega' => 'datetime_scheduled',
            default => 'created_at',
        };

        return [
            'search' => $search,
            'filter_by_date_field' => $filterByDate,
            'date_since' => $dateSince,
            'date_to' => $dateTo,
            'lab_state' => $labState,
            'order_by' => $orderBy,
        ];
    }
}
