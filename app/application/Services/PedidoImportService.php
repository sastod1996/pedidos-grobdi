<?php

namespace App\Application\Services;

use App\Imports\DetailPedidosImport;
use App\Imports\DetailPedidosPreviewImport;
use App\Imports\PedidosImport;
use App\Imports\PedidosPreviewImport;
use App\Imports\SimpleArrayImport;
use App\Models\DetailPedidos;
use App\Models\Doctor;
use App\Models\Pedidos;
use App\Models\Zone;
use App\Models\Distritos_zonas;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class PedidoImportService
{
    public function storeFile(Request $request)
    {
        $request->validate([
            'archivo' => 'required|mimes:xlsx,xls',
        ]);

        $file = $request->file('archivo');
        $fileName = 'temp_pedidos_' . time() . '.' . $file->getClientOriginalExtension();
        $storedPath = Storage::putFileAs('temp', $file, $fileName);

        if ($storedPath && Storage::exists('temp/' . $fileName)) {
            return redirect()->route('cargarpedidos.preview', ['filename' => $fileName]);
        } else {
            return redirect()->back()->with('danger', 'Error al subir el archivo.');
        }
    }

    public function previewFile($fileName)
    {
        if (!$fileName || !Storage::exists('temp/' . $fileName)) {
            return redirect()->route('cargarpedidos.create')->with('danger', 'Archivo no encontrado.');
        }

        $filePath = Storage::path('temp/' . $fileName);
        $data = Excel::toArray(new SimpleArrayImport, $filePath)[0] ?? [];
        $changes = $this->analyzeChanges($data);

        return view('pedidos.counter.cargar_pedido.preview', compact('changes', 'fileName'));
    }

    public function confirmChanges($fileName)
    {
        if (!$fileName || !Storage::exists('temp/' . $fileName)) {
            return redirect()->route('cargarpedidos.create')->with('danger', 'Archivo no encontrado.');
        }

        $filePath = Storage::path('temp/' . $fileName);
        $pedidoImport = new PedidosPreviewImport;
        Excel::import($pedidoImport, $filePath);
        Storage::delete('temp/' . $fileName);

        return redirect()->route('cargarpedidos.index')->with($pedidoImport->key, $pedidoImport->data);
    }

    public function cancelChanges($fileName)
    {
        if ($fileName && Storage::exists('temp/' . $fileName)) {
            Storage::delete('temp/' . $fileName);
        }
        $this->cleanupTempFiles();
        return redirect()->route('cargarpedidos.create')->with('warning', 'Importación cancelada');
    }

    private function cleanupTempFiles()
    {
        $tempFiles = Storage::files('temp');
        foreach ($tempFiles as $file) {
            if (basename($file) === '.gitkeep') continue;
            $lastModified = Storage::lastModified($file);
            if ($lastModified && time() - $lastModified > 3600) {
                Storage::delete($file);
            }
        }
    }

    private function analyzeChanges($data)
    {
        $changes = [
            'new' => [],
            'modified' => [],
            'stats' => ['new_count' => 0, 'modified_count' => 0, 'total_count' => 0]
        ];

        foreach ($data as $index => $row) {
            if (!is_array($row) || count($row) < 5) continue;
            $col2 = isset($row[2]) ? strtoupper(trim((string)$row[2])) : '';
            $col16 = isset($row[16]) ? strtoupper(trim((string)$row[16])) : '';
            if ($col16 === 'ARTICULO' || $col2 !== 'PEDIDO') continue;

            $changes['stats']['total_count']++;
            $orderIdRaw = isset($row[3]) ? trim((string)$row[3]) : '';
            $existingOrder = Pedidos::where('orderId', $orderIdRaw)->first();

            if (!$existingOrder) {
                $changes['new'][] = [
                    'row_index' => $index + 1,
                    'data' => $this->formatRowData($row),
                    'type' => 'new'
                ];
                $changes['stats']['new_count']++;
            } else {
                $modifications = $this->compareOrderData($existingOrder, $row);
                if (!empty($modifications)) {
                    $changes['modified'][] = [
                        'row_index' => $index + 1,
                        'existing' => $this->formatExistingOrderData($existingOrder),
                        'new' => $this->formatRowData($row),
                        'modifications' => $modifications,
                        'type' => 'modified'
                    ];
                    $changes['stats']['modified_count']++;
                }
            }
        }

        return $changes;
    }

    private function formatRowData($row)
    {
        $zoneId = Distritos_zonas::zonificar($row[16]);
        $zone = Zone::find($zoneId);

        return [
            'nroOrder' => '',
            'orderId' => $row[3],
            'customerName' => $row[4],
            'customerNumber' => $row[5],
            'doctorName' => $row[15],
            'address' => $row[17],
            'reference' => $row[18],
            'district' => $row[16],
            'prize' => $row[8],
            'paymentMethod' => $row[10],
            'deliveryDate' => $row[13] ? Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row[13]))->format('Y-m-d') : '',
            'productionStatus' => $row[12] !== 'PENDIENTE' ? 'Completado' : 'Pendiente',
            'visitadora_softlynn' => isset($row[19]) ? trim((string)$row[19]) : '',
            'created_at' => $row[20] ? Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row[20]))->format('Y-m-d H:i:s') : '',
            'zone_name' => $zone ? $zone->name : 'Sin zona',
            'user_name' => $row[19] ? (User::where('name', $row[19])->first()->name ?? Auth::user()->name) : Auth::user()->name,
            'last_data_update' => now()->format('Y-m-d H:i:s')
        ];
    }

    private function formatExistingOrderData($order)
    {
        return [
            'nroOrder' => $order->nroOrder,
            'orderId' => $order->orderId,
            'customerName' => $order->customerName,
            'customerNumber' => $order->customerNumber,
            'doctorName' => $order->doctorName,
            'address' => $order->address,
            'reference' => $order->reference,
            'district' => $order->district,
            'prize' => $order->prize,
            'paymentMethod' => $order->paymentMethod,
            'deliveryDate' => Carbon::parse($order->deliveryDate)->format('Y-m-d'),
            'productionStatus' => $order->productionStatus ? 'Completado' : 'Pendiente',
            'created_at' => $order->created_at->format('Y-m-d H:i:s'),
            'zone_name' => $order->zone->name ?? 'Sin zona',
            'user_name' => $order->user->name ?? 'Sin usuario',
            'last_data_update' => $order->last_data_update ? $order->last_data_update->format('Y-m-d H:i:s') : 'Nunca actualizado',
            'visitadora_softlynn' => $order->visitadora?->name_softlynn,
        ];
    }

    private function compareOrderData($existingOrder, $row)
    {
        $modifications = [];
        $newData = $this->formatRowData($row);
        $existingData = $this->formatExistingOrderData($existingOrder);

        $fieldsToCompare = [
            'customerName' => 'Nombre del Cliente',
            'customerNumber' => 'Número del Cliente',
            'doctorName' => 'Nombre del Doctor',
            'address' => 'Dirección',
            'reference' => 'Referencia',
            'district' => 'Distrito',
            'prize' => 'Precio',
            'paymentMethod' => 'Método de Pago',
            'deliveryDate' => 'Fecha de Entrega',
            'visitadora_softlynn' => 'Visitadora',
        ];

        foreach ($fieldsToCompare as $field => $label) {
            if ($field === 'deliveryDate') {
                $existingDate = Carbon::parse($existingData[$field])->format('Y-m-d');
                $newDate = Carbon::parse($newData[$field])->format('Y-m-d');
                if ($existingDate != $newDate) {
                    $modifications[] = [
                        'field' => $field,
                        'label' => $label,
                        'old_value' => $existingDate,
                        'new_value' => $newDate
                    ];
                }
            } else {
                if ($existingData[$field] != $newData[$field]) {
                    $modifications[] = [
                        'field' => $field,
                        'label' => $label,
                        'old_value' => $existingData[$field],
                        'new_value' => $newData[$field]
                    ];
                }
            }
        }

        return $modifications;
    }

    /**
     * Actualizar un pedido existente
     */
    public function updatePedido(Request $request, $id)
    {
        $pedido = Pedidos::findOrFail($id);
        
        // No necesitamos validar aquí porque el FormRequest ya lo hace
        $pedido->update($request->all());
        
        return $pedido->deliveryDate;
    }

    /**
     * Actualizar el turno de un pedido
     */
    public function actualizarTurno(Request $request, $id)
    {
        $request->validate([
            'turno' => 'required|in:0,1',
        ]);

        $pedido = Pedidos::findOrFail($id);
        $pedido->update([
            'turno' => $request->turno,
            'last_data_update' => now()
        ]);
    }

    /**
     * Actualizar el método de pago de un pedido
     */
    public function actualizarPago(Request $request, $id)
    {
        $request->validate([
            'paymentMethod' => 'required|string|max:255',
        ]);

        $pedido = Pedidos::findOrFail($id);
        $pedido->update([
            'paymentMethod' => $request->paymentMethod,
            'last_data_update' => now()
        ]);
    }
}