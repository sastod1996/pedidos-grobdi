<?php

namespace App\Application\Services\Import;

use App\Models\Zone;
use App\Models\Pedidos;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class PedidosImportService
{
    /**
     * Convierte una fecha de Excel a objeto Carbon
     * 
     * Este método toma un valor de fecha en formato Excel (número serial de fecha)
     * y lo convierte a un objeto Carbon para facilitar el manejo de fechas en PHP.
     * Utiliza la librería PhpOffice\PhpSpreadsheet para realizar la conversión.
     * 
     * @param mixed $excelDate El valor de fecha en formato Excel
     * @return Carbon La fecha convertida como objeto Carbon
     */
    public function convertExcelDate($excelDate): Carbon
    {
        return Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($excelDate));
    }
    
    /**
     * Obtiene el siguiente número de orden para una fecha de entrega
     * 
     * Este método calcula el siguiente número de orden disponible para una
     * fecha de entrega específica. Busca el último pedido de esa fecha y
     * retorna el siguiente número en secuencia, o 1 si no hay pedidos previos.
     * 
     * @param string $deliveryDate La fecha de entrega en formato string
     * @return int El siguiente número de orden disponible
     */
    public function getNextOrderNumber(string $deliveryDate): int
    {
        $lastOrder = Pedidos::where('deliveryDate', $deliveryDate)
            ->orderBy('nroOrder', 'desc')
            ->first();
            
        return $lastOrder ? $lastOrder->nroOrder + 1 : 1;
    }
    
    /**
     * Busca una zona por nombre
     * 
     * Este método busca una zona en la base de datos utilizando su nombre
     * como criterio de búsqueda. Retorna null si no encuentra la zona.
     * 
     * @param string $name El nombre de la zona a buscar
     * @return Zone|null La zona encontrada o null si no existe
     */
    public function findZone(string $name): ?Zone
    {
        return Zone::where('name', $name)->first();
    }
    
    /**
     * Busca una visitadora por nombre
     * 
     * Este método busca un usuario con tipo 'visitadora' en la base de datos
     * utilizando su nombre como criterio de búsqueda. Retorna null si no
     * encuentra la visitadora correspondiente.
     * 
     * @param string $name El nombre de la visitadora a buscar
     * @return User|null La visitadora encontrada o null si no existe
     */
    public function findVisitadora(string $name): ?User
    {
        return User::where('name', $name)
            ->where('type', 'visitadora')
            ->first();
    }
    
    /**
     * Crea un pedido con los datos proporcionados
     * 
     * Este método crea un nuevo registro de pedido con toda la información
     * necesaria, incluyendo relaciones con zona, usuario y visitadora.
     * Establece valores por defecto para campos de estado como 'estado',
     * 'paymentStatus', 'productionStatus' y 'accountingStatus'.
     * 
     * @param array $data Array con los datos del pedido incluyendo orderId, nroOrder, deliveryDate, etc.
     * @return Pedidos El pedido creado
     */
    public function createPedido(array $data): Pedidos
    {
        $pedido = new Pedidos();
        
        // Set order information
        $pedido->orderId = $data['orderId'];
        $pedido->nroOrder = $data['nroOrder'];
        $pedido->deliveryDate = $data['deliveryDate'];
        $pedido->cliente = $data['cliente'] ?? null;
        $pedido->total = $data['total'] ?? 0;
        
        // Set relationships
        $pedido->zone_id = $data['zone_id'] ?? null;
        $pedido->user_id = $data['user_id'] ?? Auth::id();
        $pedido->visitadora_id = $data['visitadora_id'] ?? null;
        
        // Set additional fields from spreadsheet
        $pedido->customerName = $data['customerName'] ?? null;
        $pedido->customerNumber = $data['customerNumber'] ?? null;
        $pedido->doctorName = $data['doctorName'] ?? null;
        $pedido->address = $data['address'] ?? null;
        $pedido->district = $data['district'] ?? null;
        $pedido->reference = $data['reference'] ?? null;
        $pedido->prize = $data['prize'] ?? 0;
        
        // Set default values
        $pedido->estado = $data['estado'] ?? 'Pendiente';
        $pedido->paymentStatus = 'Pendiente';
        $pedido->productionStatus = 'Pendiente';
        $pedido->accountingStatus = 'Pendiente';
        
        $pedido->save();
        
        return $pedido;
    }
    
    /**
     * Valida los datos de un pedido
     * 
     * Este método verifica que una fila de datos corresponda a un registro
     * de pedido válido. Comprueba que no sea una fila de encabezado,
     * que sea de tipo 'PEDIDO' y que contenga los campos requeridos
     * (orderId y deliveryDate).
     * 
     * @param array $row La fila de datos a validar
     * @return bool True si los datos son válidos para un pedido, false en caso contrario
     */
    public function validatePedidoData(array $row): bool
    {
        // Check if this is a header row
        if (($row[16] ?? '') === 'Articulo') {
            return false;
        }
        
        // Check if this is a pedido row
        if (($row[2] ?? '') !== 'PEDIDO') {
            return false;
        }
        
        // Check if required fields are present
        return !empty($row[3] ?? '') && !empty($row[13] ?? ''); // orderId and deliveryDate
    }
    
    /**
     * Verifica si un pedido ya existe
     * 
     * Este método comprueba si ya existe un pedido con el orderId especificado
     * en la base de datos. Es útil para evitar duplicados durante el proceso
     * de importación.
     * 
     * @param string $orderId El identificador único del pedido
     * @return bool True si el pedido ya existe, false en caso contrario
     */
    public function pedidoExists(string $orderId): bool
    {
        return Pedidos::where('orderId', $orderId)->exists();
    }
}
