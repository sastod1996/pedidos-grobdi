<?php

namespace App\Application\Services;

use App\Models\DetailPedidos;
use App\Models\Pedidos;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PedidoHistoryService
{
    public function getPedidos(Request $request)
    {
        $ordenarPor = $request->get('sort_by', 'orderId');
        $direccion = $request->get('direction', 'asc');

        if($request->query("fecha_inicio")){
            $request->validate([
                'fecha_inicio' => 'required|date',
                'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            ]);
            $fechaInicio = Carbon::parse($request->fecha_inicio)->startOfDay();
            $fechaFin = Carbon::parse($request->fecha_fin)->endOfDay();
            $pedidos = Pedidos::whereBetween('deliveryDate', [$fechaInicio, $fechaFin])
                ->orderBy($ordenarPor, $direccion)
                ->latest()
                ->paginate(25);
        } elseif($request->query("buscar")){
            $pedidos = Pedidos::where('orderId', $request->buscar)
                ->orWhere('customerName', 'like', '%' . $request->buscar . '%')
                ->orderBy($ordenarPor, $direccion)
                ->latest()
                ->paginate(25);
        } else {
            $pedidos = Pedidos::where('deliveryDate', date('Y-m-d'))
                ->orderBy($ordenarPor, $direccion)
                ->latest()
                ->paginate(25);
        }

        return $pedidos;
    }

    public function updateDetailPedido($id, Request $request)
    {
        $detailpedido = DetailPedidos::find($id);
        $detailpedido->cantidad = $request->cantidad;
        $sub_total_actual = $request->cantidad * $detailpedido->unit_prize;
        $diferencia = $sub_total_actual - $detailpedido->sub_total;
        $detailpedido->sub_total = $sub_total_actual;
        $detailpedido->save();

        $pedido = Pedidos::find($detailpedido->pedidos_id);
        $pedido->prize += $diferencia;
        $pedido->save();

        return response()->json(['success' => true]);
    }

    public function destroyDetailPedido($id_detailpedido)
    {
        $detailpedido = DetailPedidos::find($id_detailpedido);
        $pedido = Pedidos::find($detailpedido->pedidos_id);
        $pedido->prize -= $detailpedido->sub_total;
        $pedido->save();
        $detailpedido->delete();

        return back()->with('success', 'producto eliminado correctamente');
    }
}
