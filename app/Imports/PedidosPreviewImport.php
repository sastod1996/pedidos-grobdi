<?php

namespace App\Imports;

use App\Models\Distritos_zonas;
use App\Models\Pedidos;
use App\Models\DetailPedidos;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterImport;
use Maatwebsite\Excel\Row;

class PedidosPreviewImport implements OnEachRow, WithChunkReading, WithEvents
{
    public $data;
    public $key;

    private int $rowsExistentes = 0;

    private int $rowsNuevos = 0;

    private int $rowsModificados = 0;

    private bool $formatError = false;

    private ?string $message = null;

    private ?string $messageKey = null;

    public function onRow(Row $row)
    {
        if ($this->formatError) {
            return;
        }

        $cells = $row->toArray();
        if (!is_array($cells) || count($cells) < 5) {
            return;
        }

        $marker = strtoupper(trim((string) ($cells[2] ?? '')));
        $section = strtoupper(trim((string) ($cells[16] ?? '')));
        if ($section === 'ARTICULO') {
            $this->formatError = true;
            $this->message = 'Formato Incorrecto';
            $this->messageKey = 'danger';

            return;
        }

        if ($marker !== 'PEDIDO') {
            return;
        }

        $orderId = isset($cells[3]) ? trim((string) $cells[3]) : '';
        if ($orderId === '') {
            $this->rowsExistentes++;

            return;
        }

        $pedidoExistente = Pedidos::withInactive()->where('orderId', $orderId)->first();

        if (!$pedidoExistente) {
            $this->createPedido($cells);

            return;
        }

        $this->updatePedido($pedidoExistente, $cells);
    }

    public function chunkSize(): int
    {
        return 1000;
    }

    public function registerEvents(): array
    {
        return [
            AfterImport::class => function () {
                if ($this->formatError) {
                    $this->data = $this->message ?? 'Formato Incorrecto';
                    $this->key = $this->messageKey ?? 'danger';

                    return;
                }

                $mensaje = 'Pedidos procesados';
                $sinCambios = max(0, $this->rowsExistentes);
                $this->data = sprintf(
                    '%s: %d nuevos, %d modificados, %d sin cambios',
                    $mensaje,
                    $this->rowsNuevos,
                    $this->rowsModificados,
                    $sinCambios
                );
                $this->key = 'success';
            },
        ];
    }

    private function createPedido(array $cells): void
    {
        $pedido = new Pedidos();

        $fechaEntrega = $this->parseExcelDate($cells[13] ?? null);
        if (!$fechaEntrega) {
            $fechaEntrega = now()->startOfDay();
        }

        $pedido->orderId = $this->normalizeString($cells[3] ?? null);
        $pedido->customerName = $this->normalizeString($cells[4] ?? null);
        $pedido->customerNumber = $this->normalizeString($cells[5] ?? null);
        $pedido->doctorName = $this->sanitizeDoctorName($cells[15] ?? null);
        $pedido->address = $this->normalizeString($cells[17] ?? null);
        $pedido->reference = $this->normalizeString($cells[18] ?? null);
        $pedido->district = $this->normalizeString($cells[16] ?? null);

        $prize = $this->normalizeDecimal($cells[8] ?? null);
        $pedido->prize = $prize ?? 0.0;
        $pedido->paymentStatus = 'PENDIENTE';
        $pedido->deliveryStatus = 'Pendiente';
        $pedido->accountingStatus = 0;

        $excelStatus = strtoupper(trim((string) ($cells[12] ?? '')));
        $pedido->productionStatus = in_array($excelStatus, ['APROBADO', 'PREPARADO'], true) ? 1 : 0;

        $pedido->zone_id = Distritos_zonas::zonificar($pedido->district);

        $fechaRegistro = $this->parseExcelDate($cells[20] ?? null, true) ?? now();

        $pedido->forceFill([
            'deliveryDate' => $fechaEntrega->toDateString(),
            'created_at' => $fechaRegistro,
            'updated_at' => $fechaRegistro,
        ]);

        $pedido->turno = 0;
        $pedido->last_data_update = now();

        $visitadora = null;
        $visitadoraName = $this->normalizeString($cells[14] ?? null);
        if ($visitadoraName !== null) {
            $visitadora = User::where('name_softlynn', $visitadoraName)->first();
        }

        $usuario = null;
        $usuarioName = $this->normalizeString($cells[19] ?? null);
        if ($usuarioName !== null) {
            $usuario = User::where('name', $usuarioName)->first();
        }

        $pedido->user_id = $usuario?->id ?? Auth::id();
        $pedido->visitadora_id = $visitadora?->id;

        $this->assignSequentialNumber($pedido, $fechaEntrega->toDateString());

        $pedido->save();
        $this->rowsNuevos++;
    }

    private function updatePedido(Pedidos $pedido, array $cells): void
    {
        $hasChanges = false;

        $cliente = $this->normalizeString($cells[4] ?? null);
        $telefono = $this->normalizeString($cells[5] ?? null);
        $doctor = $this->sanitizeDoctorName($cells[15] ?? null, $pedido->doctorName);
        $direccion = $this->normalizeString($cells[17] ?? null);
        $referencia = $this->normalizeString($cells[18] ?? null);
        $distrito = $this->normalizeString($cells[16] ?? null);
        $precio = $this->normalizeDecimal($cells[8] ?? null);
        $metodoPago = $this->normalizeString($cells[10] ?? null);

        if ($cliente !== null && $this->stringsDiffer($pedido->customerName, $cliente)) {
            $pedido->customerName = $cliente;
            $hasChanges = true;
        }

        if ($telefono !== null && $this->stringsDiffer($pedido->customerNumber, $telefono)) {
            $pedido->customerNumber = $telefono;
            $hasChanges = true;
        }

        if ($this->stringsDiffer($pedido->doctorName, $doctor)) {
            $pedido->doctorName = $doctor;
            $hasChanges = true;
        }

        if ($direccion !== null && $this->stringsDiffer($pedido->address, $direccion)) {
            $pedido->address = $direccion;
            $hasChanges = true;
        }

        if ($referencia !== null && $this->stringsDiffer($pedido->reference, $referencia)) {
            $pedido->reference = $referencia;
            $hasChanges = true;
        }

        if ($distrito !== null && $this->stringsDiffer($pedido->district, $distrito)) {
            $pedido->district = $distrito;
            $pedido->zone_id = Distritos_zonas::zonificar($distrito);
            $hasChanges = true;
        }

        if ($precio !== null) {
            $precioActual = $this->normalizeDecimal($pedido->prize ?? null);
            if ($precioActual === null || abs($precioActual - $precio) >= 0.01) {
                $pedido->prize = $precio;
                $hasChanges = true;
            }
        }

        if ($metodoPago !== null && $this->stringsDiffer($pedido->paymentMethod, $metodoPago)) {
            $pedido->paymentMethod = $metodoPago;
            $hasChanges = true;
        }

        $nuevaFecha = $this->parseExcelDate($cells[13] ?? null)?->toDateString();
        $fechaActual = $this->currentDeliveryDateString($pedido->deliveryDate);
        if ($nuevaFecha && $fechaActual !== $nuevaFecha) {
            $pedido->forceFill(['deliveryDate' => $nuevaFecha]);
            $hasChanges = true;
        }

        $excelStatus = strtoupper(trim((string) ($cells[12] ?? '')));
        $estadoActual = (int) $pedido->productionStatus;
        if ($estadoActual !== 2) {
            $nuevoEstado = null;

            if ($excelStatus === 'PENDIENTE') {
                $nuevoEstado = 0;
            } elseif (in_array($excelStatus, ['APROBADO', 'PREPARADO'], true)) {
                if ($estadoActual !== 1) {
                    $nuevoEstado = 1;
                }
            }

            if ($nuevoEstado !== null && $estadoActual !== $nuevoEstado) {
                $pedido->productionStatus = $nuevoEstado;
                $hasChanges = true;
            }
        }

        $visitadora = null;
        $visitadoraName = $this->normalizeString($cells[14] ?? null);
        if ($visitadoraName !== null) {
            $visitadora = User::where('name_softlynn', $visitadoraName)->first();
        }
        if ($visitadora && $pedido->visitadora_id !== $visitadora->id) {
            $pedido->visitadora_id = $visitadora->id;
            $hasChanges = true;
        }

        $nuevoEstadoPedido = $this->mapExcelStatus($cells[23] ?? null);
        if ($pedido->status !== $nuevoEstadoPedido) {
            $pedido->status = $nuevoEstadoPedido;
            $hasChanges = true;

            DetailPedidos::where('pedidos_id', $pedido->id)->update(['status' => $nuevoEstadoPedido]);
        }

        if ($nuevoEstadoPedido === false) {
            DetailPedidos::where('pedidos_id', $pedido->id)->update(['estado_produccion' => 0]);
        }

        if ($hasChanges) {
            $pedido->last_data_update = now();
            $pedido->save();
            $this->rowsModificados++;

            return;
        }

        $this->rowsExistentes++;
    }

    private function assignSequentialNumber(Pedidos $pedido, string $fechaEntrega): void
    {
        $ultimo = Pedidos::withInactive()
            ->whereDate('deliveryDate', $fechaEntrega)
            ->orderByDesc('nroOrder')
            ->first();

        $pedido->nroOrder = $ultimo ? (int) $ultimo->nroOrder + 1 : 1;
    }

    private function parseExcelDate($value, bool $keepTime = false): ?Carbon
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            if ($value instanceof Carbon) {
                return $keepTime ? $value->copy() : $value->copy()->startOfDay();
            }

            if (is_numeric($value)) {
                $dateTime = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value);
                if (!$keepTime) {
                    $dateTime->setTime(0, 0, 0);
                }

                return Carbon::instance($dateTime);
            }

            $parsed = Carbon::parse($value);

            return $keepTime ? $parsed : $parsed->startOfDay();
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function normalizeString($value): ?string
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof \Stringable) {
            $value = (string) $value;
        }

        if (is_array($value)) {
            $value = implode(' ', $value);
        }

        if (!is_string($value)) {
            $value = (string) $value;
        }

        $normalized = trim($value);
        if ($normalized === '') {
            return null;
        }

        $normalized = preg_replace('/\s+/u', ' ', $normalized);

        return $normalized === '' ? null : $normalized;
    }

    private function normalizeDecimal($value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($value instanceof \Stringable) {
            $value = (string) $value;
        }

        if (is_string($value)) {
            $value = trim($value);
            if ($value === '') {
                return null;
            }

            $value = str_replace(['S/', '$', '€', ' '], '', $value);

            $hasComma = str_contains($value, ',');
            $hasDot = str_contains($value, '.');

            if ($hasComma && $hasDot) {
                $lastComma = strrpos($value, ',');
                $lastDot = strrpos($value, '.');
                if ($lastComma !== false && $lastDot !== false && $lastComma > $lastDot) {
                    $value = str_replace('.', '', $value);
                    $value = str_replace(',', '.', $value);
                } else {
                    $value = str_replace(',', '', $value);
                }
            } elseif ($hasComma) {
                $parts = explode(',', $value);
                $decimalPart = end($parts);
                if (strlen($decimalPart) <= 2) {
                    $value = implode('', array_slice($parts, 0, -1)) . '.' . $decimalPart;
                } else {
                    $value = implode('', $parts);
                }
            } elseif ($hasDot) {
                $parts = explode('.', $value);
                $decimalPart = end($parts);
                if (strlen($decimalPart) > 2) {
                    $value = implode('', $parts);
                }
            }
        }

        if (!is_numeric($value)) {
            return null;
        }

        return round((float) $value, 2);
    }

    private function stringsDiffer(?string $current, ?string $incoming): bool
    {
        $normalizedCurrent = $current === null ? null : $this->normalizeString($current);
        $normalizedIncoming = $incoming === null ? null : $this->normalizeString($incoming);

        if ($normalizedCurrent !== $normalizedIncoming) {
            return true;
        }

        if ($normalizedIncoming === null) {
            return false;
        }

        return $current !== $normalizedIncoming;
    }

    private function currentDeliveryDateString($value): ?string
    {
        if ($value instanceof Carbon) {
            return $value->format('Y-m-d');
        }

        if ($value instanceof \DateTimeInterface) {
            return Carbon::instance($value)->format('Y-m-d');
        }

        if (is_string($value) && trim($value) !== '') {
            try {
                return Carbon::parse($value)->format('Y-m-d');
            } catch (\Throwable $e) {
                return null;
            }
        }

        if (is_numeric($value)) {
            try {
                $dateTime = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float) $value);

                return Carbon::instance($dateTime)->format('Y-m-d');
            } catch (\Throwable $e) {
                return null;
            }
        }

        return null;
    }

    private function mapExcelStatus($value): bool
    {
        if (is_null($value)) {
            return true;
        }

        $normalized = strtoupper(trim((string) $value));
        $normalized = preg_replace('/[^A-Z0-9]/', '', $normalized) ?? '';

        if ($normalized === 'ANULADO' || $normalized === '0') {
            return false;
        }

        return true;
    }

    private function sanitizeDoctorName($value, ?string $fallback = null): string
    {
        $normalized = is_string($value) ? trim($value) : trim((string)($value ?? ''));

        if ($normalized !== '') {
            return $normalized;
        }

        if ($fallback !== null && trim($fallback) !== '') {
            return trim($fallback);
        }

        return 'Sin doctor';
    }
}
