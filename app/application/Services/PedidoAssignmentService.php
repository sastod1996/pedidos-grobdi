<?php

namespace App\Application\Services;

use App\Models\Pedidos;
use App\Models\Zone;
use Carbon\Carbon;

class PedidoAssignmentService
{
    public function getPedidosForDate($fecha = null)
    {
        $dia = $fecha ? Carbon::parse($fecha)->startOfDay() : now()->format('Y-m-d');
        return Pedidos::whereDate('deliveryDate', $dia)->get();
    }

    public function getZonas()
    {
        return Zone::orderBy('id', 'desc')->get();
    }

    public function assignZoneToPedido($pedidoId, $zoneId)
    {
        $pedido = Pedidos::find($pedidoId);
        if ($pedido) {
            $pedido->zone_id = $zoneId;
            $pedido->save();
            return true;
        }
        return false;
    }

    public function getPedidoById($pedidoId)
    {
        return Pedidos::find($pedidoId);
    }
}
