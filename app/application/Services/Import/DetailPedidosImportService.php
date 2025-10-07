<?php

namespace App\Application\Services\Import;

use App\Models\DetailPedidos;
use App\Models\Pedidos;
use App\Models\Articulo;
use Illuminate\Support\Collection;

class DetailPedidosImportService
{
    /**
     * Busca un pedido por su ID de orden
     * 
     * Este método busca en la base de datos un pedido específico utilizando
     * su identificador único de orden (orderId). Retorna null si no encuentra
     * el pedido correspondiente.
     * 
     * @param string $orderId El identificador único de la orden
     * @return Pedidos|null El pedido encontrado o null si no existe
     */
    public function findPedido(string $orderId): ?Pedidos
    {
        // Try by orderId (string)
        $pedido = Pedidos::where('orderId', $orderId)->first();
        // Try numeric cast for orderId
        if (!$pedido && is_numeric($orderId)) {
            $pedido = Pedidos::where('orderId', (int)$orderId)->first();
        }
        // Fallback to nroOrder (string and numeric)
        if (!$pedido) {
            $pedido = Pedidos::where('nroOrder', $orderId)->first();
            if (!$pedido && is_numeric($orderId)) {
                $pedido = Pedidos::where('nroOrder', (int)$orderId)->first();
            }
        }
        return $pedido;
    }
    
    /**
     * Crea un detalle con el esquema correcto de la base de datos
     * 
     * Este método crea un nuevo registro de detalle de pedido utilizando el
     * esquema correcto de la base de datos, con los nombres de campos precisos
     * (pedidos_id en lugar de pedido_id, unit_prize en lugar de precio_unitario,
     * sub_total en lugar de precio_total). Incluye timestamps automáticos.
     * 
     * @param array $data Array con los datos usando nombres correctos ['pedidos_id', 'articulo', 'cantidad', 'unit_prize', 'sub_total']
     * @return DetailPedidos El detalle de pedido creado con esquema correcto
     */
    public function createDetailWithCorrectSchema(array $data): DetailPedidos
    {
        $detail = new DetailPedidos();
        
        // Set basic information using the correct schema
        $detail->pedidos_id = $data['pedidos_id'];  // Note: pedidos_id not pedido_id
        $detail->articulo = $data['articulo'];
        $detail->cantidad = $data['cantidad'];
        $detail->unit_prize = $data['unit_prize'];  // Note: unit_prize not precio_unitario
        $detail->sub_total = $data['sub_total'];    // Note: sub_total not precio_total
        
        // Set timestamps
        $detail->created_at = now();
        $detail->updated_at = now();
        
        $detail->save();
        
        return $detail;
    }
}
