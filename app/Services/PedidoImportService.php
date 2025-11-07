<?php

namespace App\Services;

use App\Imports\DetailPedidosImport;
use App\Imports\DetailPedidosPreviewImport;
use App\Imports\PedidosImport;
use App\Imports\PedidosPreviewImport;
use App\Imports\PedidosPreviewAnalyzerImport;
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
use Illuminate\Validation\ValidationException;
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
    $changes = $this->analyzeChangesFromFile($filePath);

        return view('pedidos.counter.cargar_pedido.preview', compact('changes', 'fileName'));
    }

    public function confirmChanges($fileName)
    {
        if (!$fileName || !Storage::exists('temp/' . $fileName)) {
            return redirect()->route('cargarpedidos.create')->with('danger', 'Archivo no encontrado.');
        }

        $filePath = Storage::path('temp/' . $fileName);
    $changes = $this->analyzeChangesFromFile($filePath);

        $pedidoImport = new PedidosPreviewImport;
        Excel::import($pedidoImport, $filePath);
        Storage::delete('temp/' . $fileName);
        $this->cleanupTempFiles();

        $summaryCounts = [
            'total' => $changes['stats']['total_count'] ?? 0,
            'new' => $changes['stats']['new_count'] ?? 0,
            'modified' => $changes['stats']['modified_count'] ?? 0,
        ];
        $summaryCounts['unchanged'] = max(0, ($summaryCounts['total'] - $summaryCounts['new'] - $summaryCounts['modified']));

        $stats = $changes['stats'] ?? [];
        $summarySnapshot = [
            'total' => $summaryCounts['total'],
            'new' => $summaryCounts['new'],
            'modified' => $summaryCounts['modified'],
            'unchanged' => $summaryCounts['unchanged'],
            'inactive' => $stats['inactive_count'] ?? 0,
            'status_changes' => $stats['status_changes'] ?? 0,
        ];

        return redirect()->route('cargarpedidos.create')
            ->with($pedidoImport->key, $pedidoImport->data)
            ->with('processed_summary', $summarySnapshot)
            ->with('processed_summary_generated_at', now()->format('Y-m-d H:i:s'));
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

    private function analyzeChangesFromFile(string $filePath): array
    {
        $changes = $this->initializeChangesSummary();

        Excel::import(new PedidosPreviewAnalyzerImport(function (array $row, int $rowIndex) use (&$changes) {
            $this->applyRowToChanges($row, $rowIndex, $changes);
        }), $filePath);

        return $changes;
    }

    private function initializeChangesSummary(): array
    {
        return [
            'new' => [],
            'modified' => [],
            'stats' => [
                'new_count' => 0,
                'modified_count' => 0,
                'total_count' => 0,
                'inactive_count' => 0,
                'status_changes' => 0,
            ],
        ];
    }

    private function applyRowToChanges(array $row, int $rowIndex, array &$changes): void
    {
        if (!is_array($row) || count($row) < 5) {
            return;
        }

        $col2 = isset($row[2]) ? strtoupper(trim((string) $row[2])) : '';
        $col16 = isset($row[16]) ? strtoupper(trim((string) $row[16])) : '';
        if ($col16 === 'ARTICULO' || $col2 !== 'PEDIDO') {
            return;
        }

        $changes['stats']['total_count']++;
        $orderIdRaw = isset($row[3]) ? trim((string) $row[3]) : '';
        if ($orderIdRaw === '') {
            return;
        }

        $existingOrder = Pedidos::where('orderId', $orderIdRaw)->first();

        if (!$existingOrder) {
            $changes['new'][] = [
                'row_index' => $rowIndex,
                'data' => $this->formatRowData($row, null),
                'type' => 'new',
            ];
            $changes['stats']['new_count']++;

            return;
        }

        $modifications = $this->compareOrderData($existingOrder, $row);
        if (!empty($modifications)) {
            $changes['modified'][] = [
                'row_index' => $rowIndex,
                'existing' => $this->formatExistingOrderData($existingOrder),
                'new' => $this->formatRowData($row, $existingOrder),
                'modifications' => $modifications,
                'type' => 'modified',
            ];
            $changes['stats']['modified_count']++;
        }
    }

    private function formatRowData($row, ?Pedidos $existingOrder = null)
    {
        $districtRaw = $row[16] ?? null;
        $existingAddress = $existingOrder ? $existingOrder->address : '';
        $existingReference = $existingOrder ? $existingOrder->reference : '';
        $existingDistrict = $existingOrder ? $existingOrder->district : '';
        $existingDeliveryDate = $existingOrder ? $existingOrder->deliveryDate : null;
        $existingCreatedAt = $existingOrder ? $existingOrder->created_at : null;
        $existingUserName = ($existingOrder && $existingOrder->user) ? $existingOrder->user->name : null;
        $existingDoctorName = $existingOrder ? $existingOrder->doctorName : null;

        $existingDeliveryDateString = ($existingDeliveryDate instanceof Carbon)
            ? $existingDeliveryDate->format('Y-m-d')
            : (is_string($existingDeliveryDate) ? $existingDeliveryDate : '');
        $existingCreatedAtString = ($existingCreatedAt instanceof Carbon)
            ? $existingCreatedAt->format('Y-m-d H:i:s')
            : (is_string($existingCreatedAt) ? $existingCreatedAt : null);

        $districtValue = $this->valueOrFallback($districtRaw, $existingDistrict);
        $zoneId = Distritos_zonas::zonificar($districtValue);
        $zone = Zone::find($zoneId);

        $deliveryDate = $this->resolveExcelDate($row[13] ?? null, $existingDeliveryDate);
        $createdAt = $this->resolveExcelDate($row[20] ?? null, $existingCreatedAt);
        $userNameRaw = $row[19] ?? null;
        $resolvedUser = null;
        if ($userNameRaw) {
            $resolvedUser = optional(User::where('name', $userNameRaw)->first())->name;
        }

        return [
            'nroOrder' => '',
            'orderId' => $row[3],
            'customerName' => $row[4],
            'customerNumber' => $row[5],
            'doctorName' => $this->normalizeDoctorName($row[15] ?? null, $existingDoctorName),
            'address' => $this->valueOrFallback($row[17] ?? null, $existingAddress),
            'reference' => $this->valueOrFallback($row[18] ?? null, $existingReference),
            'district' => $districtValue,
            'prize' => $row[8],
            'paymentMethod' => $row[10],
            'deliveryDate' => $deliveryDate
                ? $deliveryDate->format('Y-m-d')
                : $existingDeliveryDateString,
            'productionStatus' => $row[12] !== 'PENDIENTE' ? 'Completado' : 'Pendiente',
            'created_at' => $createdAt
                ? $createdAt->format('Y-m-d H:i:s')
                : ($existingCreatedAtString ?? now()->format('Y-m-d H:i:s')),
            'zone_name' => $zone ? $zone->name : 'Sin zona',
            'user_name' => $resolvedUser ?: ($existingUserName ?? Auth::user()->name),
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
            'last_data_update' => $order->last_data_update ? $order->last_data_update->format('Y-m-d H:i:s') : 'Nunca actualizado'
        ];
    }

    private function compareOrderData($existingOrder, $row)
    {
        $modifications = [];
        $newData = $this->formatRowData($row, $existingOrder);
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
            'deliveryDate' => 'Fecha de Entrega'
        ];

        foreach ($fieldsToCompare as $field => $label) {
            if ($field === 'deliveryDate') {
                $existingDate = $existingData[$field] ?? null;
                $newDate = $newData[$field] ?? null;

                if ($existingDate && $newDate && Carbon::parse($existingDate)->format('Y-m-d') !== Carbon::parse($newDate)->format('Y-m-d')) {
                    $modifications[] = [
                        'field' => $field,
                        'label' => $label,
                        'old_value' => Carbon::parse($existingDate)->format('Y-m-d'),
                        'new_value' => Carbon::parse($newDate)->format('Y-m-d')
                    ];
                }
            } else {
                if (($existingData[$field] ?? null) != ($newData[$field] ?? null)) {
                    $modifications[] = [
                        'field' => $field,
                        'label' => $label,
                        'old_value' => $existingData[$field] ?? '',
                        'new_value' => $newData[$field] ?? ''
                    ];
                }
            }
        }

        return $modifications;
    }

    private function normalizeDoctorName($rawName, ?string $fallback = null): string
    {
        $value = is_string($rawName) ? trim($rawName) : trim((string)($rawName ?? ''));

        if ($value !== '') {
            return $value;
        }

        if ($fallback !== null && trim($fallback) !== '') {
            return trim($fallback);
        }

        return 'Sin doctor';
    }

    private function resolveExcelDate($value, $fallback = null): ?Carbon
    {
        if ($value === null || $value === '') {
            if ($fallback instanceof Carbon) {
                return $fallback->copy();
            }

            if (is_string($fallback) && trim($fallback) !== '') {
                try {
                    return Carbon::parse($fallback);
                } catch (\Throwable $th) {
                    return null;
                }
            }

            return null;
        }

        try {
            if ($value instanceof Carbon) {
                return $value->copy();
            }

            if (is_numeric($value)) {
                return Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value));
            }

            return Carbon::parse($value);
        } catch (\Throwable $th) {
            if ($fallback instanceof Carbon) {
                return $fallback->copy();
            }

            if (is_string($fallback) && trim($fallback) !== '') {
                try {
                    return Carbon::parse($fallback);
                } catch (\Throwable $e) {
                    return null;
                }
            }

            return null;
        }
    }

    private function valueOrFallback($value, $fallback = '')
    {
        if ($value === null) {
            return $fallback;
        }

        if (is_string($value)) {
            $trimmed = trim($value);
            return $trimmed === '' ? $fallback : $trimmed;
        }

        return $value;
    }

    public function updatePedido($request, $id)
    {
        $pedidos = Pedidos::find($id);

        if (!$pedidos) {
            throw ValidationException::withMessages([
                'pedido' => 'El pedido que intentas actualizar no existe.',
            ]);
        }

        $fecha = $pedidos->deliveryDate;
        $existingDeliveryDate = $pedidos->deliveryDate instanceof Carbon
            ? $pedidos->deliveryDate->format('Y-m-d')
            : (string) $pedidos->deliveryDate;

        $currentStatusNormalized = strtolower((string) ($pedidos->deliveryStatus ?? ''));
        $newStatusRaw = $request->deliveryStatus ?? ($request['deliveryStatus'] ?? $pedidos->deliveryStatus);
        $newStatusNormalized = strtolower((string) ($newStatusRaw ?? ''));

        if (!in_array($newStatusNormalized, ['pendiente', 'entregado'], true)) {
            $newStatusNormalized = $currentStatusNormalized ?: 'pendiente';
        }

        if ($currentStatusNormalized === 'entregado' && $newStatusNormalized !== 'entregado') {
            throw ValidationException::withMessages([
                'deliveryStatus' => 'No es posible modificar el estado de un pedido marcado como entregado.',
            ]);
        }

        // Access validated payload
        $address = $request->address ?? ($request['address'] ?? null);
        $district = $request->district ?? ($request['district'] ?? null);
        $deliveryDateNew = $request->deliveryDate ?? ($request['deliveryDate'] ?? null);
        $zoneId = $request->zone_id ?? ($request['zone_id'] ?? null);
        $customerNumber = $request->customerNumber ?? ($request['customerNumber'] ?? null);
        $idDoctor = $request->id_doctor ?? ($request['id_doctor'] ?? null);
        $doctorName = $request->doctorName ?? ($request['doctorName'] ?? null);

        $pedidos->address = $address;
        $pedidos->district = $district;
        $pedidos->customerNumber = $customerNumber;
        $pedidos->id_doctor = $idDoctor;
        $pedidos->doctorName = $doctorName;

        $deliveryDateChanged = $existingDeliveryDate !== $deliveryDateNew;

        if($deliveryDateChanged){
            $pedidos->deliveryDate = $deliveryDateNew;
            $contador_registro = Pedidos::where('deliveryDate',$deliveryDateNew)->orderBy('nroOrder','desc')->first();
            $ultimo_nro = 0;
            if($contador_registro){
                $ultimo_nro = $contador_registro->nroOrder;
            }
            $nroOrder = $ultimo_nro +1;
            $pedidos->nroOrder = $nroOrder;
            if ($currentStatusNormalized !== 'entregado') {
                $pedidos->deliveryStatus = $newStatusNormalized === 'entregado' ? 'Entregado' : 'Reprogramado';
                $pedidos->turno = 0;
            }
        }

        if ($currentStatusNormalized !== 'entregado' && !$deliveryDateChanged) {
            $pedidos->deliveryStatus = $newStatusNormalized === 'entregado' ? 'Entregado' : 'Pendiente';
        }

        $pedidos->zone_id = $zoneId;
        $pedidos->user_id = Auth::user()->id;
        $pedidos->save();

        return $fecha;
    }

    public function actualizarTurno($request, $id)
    {
        $pedidos = Pedidos::find($id);
        $pedidos->update($request->all());
        return true;
    }

    public function actualizarPago($request, $id)
    {
        $request->validate([
            'paymentStatus' => 'required',
            'paymentMethod' => 'required',
        ]);

        $pedidos = Pedidos::find($id);
        $pedidos->paymentStatus = $request->paymentStatus;
        $pedidos->paymentMethod = $request->paymentMethod;
        $pedidos->save();

        return true;
    }

    // Similar methods for articles...
    // For brevity, I'll assume we move all related methods here.
}
