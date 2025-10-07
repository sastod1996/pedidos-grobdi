<?php

namespace App\Http\Controllers\pedidos\counter;

use App\Http\Controllers\Controller;
use App\Services\PedidoAssignmentService;
use Illuminate\Http\Request;

class AsignarPedidoController extends Controller
{
    protected $pedidoAssignmentService;

    public function __construct(PedidoAssignmentService $pedidoAssignmentService)
    {
        $this->pedidoAssignmentService = $pedidoAssignmentService;
    }

    public function index(Request $request)
    {
        $zonas = $this->pedidoAssignmentService->getZonas();
        $pedidos = $this->pedidoAssignmentService->getPedidosForDate($request->query("fecha"));

        // Filtrar por nroOrder si se proporciona
        if ($request->query("orderId")) {
            $pedidos = $pedidos->filter(function ($pedido) use ($request) {
                return strpos($pedido->orderId, $request->query("orderId")) !== false;
            });
        }

        return view('pedidos.counter.asignar_pedido.index', compact("zonas", "pedidos"));
    }

    public function update(Request $request, $id)
    {
        $this->pedidoAssignmentService->assignZoneToPedido($id, $request->zone_id);
        return back()->with('success', 'Pedido modificado exitosamente');
    }
}
