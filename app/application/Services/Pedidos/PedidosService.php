<?php

namespace App\Application\Services\Pedidos;

use App\Models\DetailPedidos;
use App\Traits\Query\ExcludeWordsFromQuery;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PedidosService
{
    use ExcludeWordsFromQuery;

    public function getPedidosDetailsByTipoMedico(
        string $tipoMedico,
        ?int $month = null,
        ?int $year = null,
        ?string $startDate = null,
        ?string $endDate = null
    ) {
        // Build date range based on either startDate/endDate or month/year
        if ($startDate !== null && $endDate !== null) {
            $start = Carbon::parse($startDate)->startOfDay();
            $end = Carbon::parse($endDate)->endOfDay();
        } else {
            $m = $month ?? now()->month;
            $y = $year ?? now()->year;
            $start = Carbon::createFromDate($y, $m, 1)->startOfDay();
            $end = Carbon::createFromDate($y, $m, 1)->endOfMonth()->endOfDay();
        }

        $excludedWords = ['%delivery%', 'bolsa%'];

        // Start from doctors and left join pedidos and detail_pedidos so doctors with zero pedidos are returned
        $query = DB::table('doctor as dr')
            ->where('dr.tipo_medico', $tipoMedico)
            ->leftJoin('pedidos as p', function ($join) use ($start, $end) {
                $join->on('p.id_doctor', '=', 'dr.id')
                    ->where('p.status', true)
                    ->whereBetween('p.created_at', [$start, $end]);
            })
            ->leftJoin('detail_pedidos as dp', function ($join) use ($excludedWords) {
                $join->on('dp.pedidos_id', '=', 'p.id')
                    ->where('dp.status', true);

                // apply excluded words to the join so excluded detail rows are not considered
                foreach ($excludedWords as $w) {
                    $join->where('dp.articulo', 'not like', $w);
                }
            });

        $rows = $query->select([
            'dr.id',
            'dr.name',
            'dr.first_lastname',
            'dr.second_lastname',
            DB::raw('COALESCE(SUM(dp.sub_total), 0) as total_sub_total'),
            DB::raw('COUNT(DISTINCT p.id) as total_pedidos')
        ])
            ->groupBy('dr.id', 'dr.name', 'dr.first_lastname', 'dr.second_lastname')
            ->get();

        return $rows->map(function ($item) {
            $parts = array_filter([
                $item->name ?? '',
                $item->first_lastname ?? '',
                $item->second_lastname ?? ''
            ], fn($part) => !empty(trim($part)));

            $subTotal = is_numeric($item->total_sub_total) ? (float) $item->total_sub_total : 0.0;

            return (object) [
                'id' => $item->id,
                'name' => implode(' ', $parts),
                'total_sub_total' => $subTotal,
                'total_pedidos' => (int) ($item->total_pedidos ?? 0),
                'monto_sin_igv' => $subTotal * 0.82,
                // alias used by frontend JS
                'total_amount_without_igv' => $subTotal * 0.82,
            ];
        });
    }

    private function applyDateFilter($query, string $column, ?string $startDate, ?string $endDate, ?int $month, ?int $year)
    {
        if ($startDate !== null && $endDate !== null) {
            $start = Carbon::parse($startDate)->startOfDay();
            $end = Carbon::parse($endDate)->endOfDay();
            $query->whereBetween($column, [$start, $end]);
        } else {
            $query->whereMonth($column, $month ?? now()->month)
                ->whereYear($column, $year ?? now()->year);
        }
    }

    public function calculateTotalSubTotal(callable $filterCallBack)
    {
        $query = DetailPedidos::query()
            ->where('detail_pedidos.status', true)
            ->join('pedidos', 'detail_pedidos.pedidos_id', '=', 'pedidos.id');

        $excludedWords = ['%delivery%', 'bolsa%'];
        $this->excludeArrayFromDataResults($query, 'detail_pedidos.articulo', $excludedWords);

        $filterCallBack($query);

        return (float) $query->sum('detail_pedidos.sub_total');

    }

}
