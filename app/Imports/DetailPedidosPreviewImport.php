<?php

namespace App\Imports;

use App\Models\DetailPedidos;
use App\Models\Pedidos;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterImport;
use Maatwebsite\Excel\Row;

class DetailPedidosPreviewImport implements OnEachRow, WithChunkReading, WithEvents
{
    public $data = [];
    public $key = 'info';
    public array $changes = [];
    public array $stats = [];

    private array $colMap = [
        'numero' => 0,
        'articulo' => 1,
        'cantidad' => 2,
        'precio' => 3,
        'subtotal' => 4,
    ];

    private bool $colMapLocked = false;
    private bool $headerAnalyzed = false;
    private array $excelPedidos = [];
    private array $excelPedidoOriginals = [];
    private array $rowKeyDetails = [];
    private array $duplicateOriginalAdded = [];
    private array $pedidoCache = [];

    /**
     * Inicializa el estado base del importador.
     */
    public function __construct()
    {
        $this->resetState();
    }

    /**
     * Procesa cada fila del Excel de manera incremental.
     */
    public function onRow(Row $row): void
    {
        $rowIndex = $row->getIndex();
        $values = $row->toArray();

        if ($this->isCompletelyEmpty($values)) {
            return;
        }

        $this->updateColumnMapIfNeeded($values);

        // Omitir cabeceras (las dos primeras filas suelen ser metadata/encabezados)
        if ($rowIndex <= 2) {
            return;
        }

        if ($this->shouldSkipRow($values)) {
            return;
        }

        $pedidoIdRaw = $this->getStringValue($values, 'numero');
        $articulo = $this->getStringValue($values, 'articulo');
        $cantidad = $this->getFloatValue($values, 'cantidad');
        $unit = $this->getFloatValue($values, 'precio');
        $sub = $this->getFloatValue($values, 'subtotal', round($cantidad * $unit, 2));

        if ($pedidoIdRaw === '' || $articulo === '' || $cantidad <= 0) {
            return;
        }

        $this->colMapLocked = true; // A partir de la primera fila válida, congelamos el mapeo.
        $this->stats['total_count']++;

        $normalizedKey = $this->buildDuplicateKey($pedidoIdRaw, $articulo, $cantidad, $unit);
        $this->handleDuplicate($normalizedKey, $rowIndex, $pedidoIdRaw, $articulo, $cantidad, $unit, $sub);

        $pedidoKey = strtoupper(trim($pedidoIdRaw));
        $articleKey = $this->buildArticleKey($articulo, $cantidad, $unit);
        $this->excelPedidos[$pedidoKey][$articleKey] = [
            'articulo' => $articulo,
            'cantidad' => $cantidad,
            'unit_prize' => round($unit, 2),
        ];
        $this->excelPedidoOriginals[$pedidoKey] = $pedidoIdRaw;

        $pedido = $this->findPedido($pedidoIdRaw);
        if (! $pedido) {
            $this->stats['not_found_count']++;
            $this->changes['not_found'][] = [
                'row_index' => $rowIndex,
                'pedido_id' => $pedidoIdRaw,
                'articulo' => $articulo,
            ];

            return;
        }

        if ($pedido->productionStatus === 2) {
            $this->stats['prepared_orders_count']++;
            $this->changes['prepared_orders'][] = [
                'row_index' => $rowIndex,
                'pedido_id' => $pedido->orderId ?? $pedido->nroOrder,
                'articulo' => $articulo,
            ];

            return;
        }

        $this->processArticle($rowIndex, $pedido, $articulo, $cantidad, $unit, $sub);
    }

    /**
     * Define el tamaño de chunk para lectura streaming.
     */
    public function chunkSize(): int
    {
        return 1000;
    }

    /**
     * Registra eventos del importador.
     */
    public function registerEvents(): array
    {
        return [
            AfterImport::class => function () {
                $this->detectArticlesToDelete();
                $this->finalizeResults();
            },
        ];
    }

    /**
     * Reinicia contadores y estructuras de cambios.
     */
    private function resetState(): void
    {
        $this->changes = [
            'new' => [],
            'modified' => [],
            'no_changes' => [],
            'not_found' => [],
            'prepared_orders' => [],
            'duplicates' => [],
            'to_delete' => [],
            'stats' => [],
        ];

        $this->stats = [
            'new_count' => 0,
            'modified_count' => 0,
            'no_changes_count' => 0,
            'not_found_count' => 0,
            'prepared_orders_count' => 0,
            'to_delete_count' => 0,
            'total_count' => 0,
        ];
    }

    /**
     * Detecta columnas dinámicamente en streaming.
     */
    private function updateColumnMapIfNeeded(array $row): void
    {
        if ($this->colMapLocked) {
            return;
        }

        if ($this->detectOldFormat($row)) {
            $this->colMap = [
                'numero' => 3,
                'articulo' => 16,
                'cantidad' => 17,
                'precio' => 18,
                'subtotal' => 19,
            ];
            Log::info('Detected old Excel format for artículos (columns D/Q/R/S/T).');

            return;
        }

        if (! $this->headerAnalyzed && $this->looksLikeHeaderRow($row)) {
            $aliases = [
                'numero' => ['numero', 'número', 'pedido', 'nro', 'nro pedido'],
                'articulo' => ['articulo', 'artículo', 'producto', 'item'],
                'cantidad' => ['cantidad', 'cant'],
                'precio' => ['preciounitario', 'precio unitario', 'precio', 'p. unitario'],
                'subtotal' => ['subtotal', 'sub total', 'total linea', 'total línea'],
            ];

            $headers = array_map(
                fn ($value) => is_string($value) ? strtolower(trim($value)) : '',
                $row
            );

            foreach ($aliases as $key => $names) {
                foreach ($headers as $index => $label) {
                    if ($label !== '' && in_array($label, $names, true)) {
                        $this->colMap[$key] = (int) $index;
                        break;
                    }
                }
            }

            $this->headerAnalyzed = true;
        }
    }

    /**
     * Determina si una fila parece pertenecer al formato antiguo.
     */
    private function detectOldFormat(array $row): bool
    {
        $col2 = isset($row[2]) ? strtoupper(trim((string) $row[2])) : '';
        $articulo = isset($row[16]) ? trim((string) $row[16]) : '';

        return $col2 === 'PEDIDO' && $articulo !== '';
    }

    /**
     * Valida si la fila es completamente vacía.
     */
    private function isCompletelyEmpty(array $row): bool
    {
        foreach ($row as $value) {
            if ($value !== null && trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }

    /**
     * Detecta filas de encabezado basándose en palabras clave.
     */
    private function looksLikeHeaderRow(array $row): bool
    {
        $keywords = ['numero', 'número', 'pedido', 'articulo', 'artículo', 'producto', 'cantidad'];
        $matches = 0;

        foreach ($row as $value) {
            if (! is_string($value)) {
                continue;
            }

            $normalized = strtolower(trim($value));
            if (in_array($normalized, $keywords, true)) {
                $matches++;
            }
        }

        return $matches >= 2;
    }

    /**
     * Determina si se debe omitir la fila (cabecerás u otras filas no válidas).
     */
    private function shouldSkipRow(array $row): bool
    {
        if ($this->isCompletelyEmpty($row)) {
            return true;
        }

        $numero = strtolower($this->getStringValueByIndex($row, $this->colMap['numero'] ?? 0));
        $articulo = strtolower($this->getStringValueByIndex($row, $this->colMap['articulo'] ?? 1));

        $headerKeywords = ['numero', 'número', 'pedido', 'order', 'nro', 'articulo', 'artículo', 'producto', 'item'];

        if ($numero !== '' && in_array($numero, $headerKeywords, true)) {
            return true;
        }

        if ($articulo !== '' && in_array($articulo, $headerKeywords, true)) {
            return true;
        }

        if (isset($row[2]) && strtoupper(trim((string) $row[2])) === 'PEDIDO') {
            $col3 = strtolower($this->getStringValueByIndex($row, 3));
            if (in_array($col3, ['numero', 'número', 'pedido', 'order', 'nro'], true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Obtiene un valor string usando el mapeo de columnas actual.
     */
    private function getStringValue(array $row, string $key): string
    {
        $index = $this->colMap[$key] ?? null;
        if ($index === null || ! array_key_exists($index, $row)) {
            return '';
        }

        return trim((string) $row[$index]);
    }

    /**
     * Obtiene un valor string por índice directo.
     */
    private function getStringValueByIndex(array $row, ?int $index): string
    {
        if ($index === null || ! array_key_exists($index, $row)) {
            return '';
        }

        return trim((string) $row[$index]);
    }

    /**
     * Obtiene un valor numérico usando el mapeo actual.
     */
    private function getFloatValue(array $row, string $key, ?float $fallback = null): float
    {
        $index = $this->colMap[$key] ?? null;
        if ($index !== null && array_key_exists($index, $row) && $row[$index] !== null && $row[$index] !== '') {
            return (float) $row[$index];
        }

        return $fallback ?? 0.0;
    }

    /**
     * Genera una clave única para detección de duplicados.
     */
    private function buildDuplicateKey(string $pedidoId, string $articulo, float $cantidad, float $unit): string
    {
        return strtoupper(trim($pedidoId)).'|'.strtoupper(trim($articulo)).'|'.$this->formatNumber($cantidad).'|'.$this->formatNumber($unit);
    }

    /**
     * Genera la clave única para agrupar artículos del Excel.
     */
    private function buildArticleKey(string $articulo, float $cantidad, float $unit): string
    {
        return strtoupper(trim($articulo)).'|'.$this->formatNumber($cantidad).'|'.$this->formatNumber($unit);
    }

    /**
     * Formatea números asegurando consistencia al construir claves.
     */
    private function formatNumber(float $value): string
    {
        return number_format($value, 6, '.', '');
    }

    /**
     * Maneja la detección y almacenamiento de filas duplicadas.
     */
    private function handleDuplicate(string $normalizedKey, int $rowIndex, string $pedidoId, string $articulo, float $cantidad, float $unit, float $sub): void
    {
        $detail = [
            'row_index' => $rowIndex,
            'pedido_id' => $pedidoId,
            'articulo' => $articulo,
            'cantidad' => $cantidad,
            'unit_prize' => round($unit, 2),
            'sub_total' => round($sub, 2),
        ];

        if (isset($this->rowKeyDetails[$normalizedKey])) {
            if (! isset($this->duplicateOriginalAdded[$normalizedKey])) {
                $this->changes['duplicates'][] = $this->rowKeyDetails[$normalizedKey];
                $this->duplicateOriginalAdded[$normalizedKey] = true;
            }

            $this->changes['duplicates'][] = $detail;

            return;
        }

        $this->rowKeyDetails[$normalizedKey] = $detail;
    }

    /**
     * Busca un pedido en caché o base de datos.
     */
    private function findPedido(string $pedidoIdRaw): ?Pedidos
    {
        $normalized = strtoupper(trim($pedidoIdRaw));
        if (array_key_exists($normalized, $this->pedidoCache)) {
            return $this->pedidoCache[$normalized];
        }

        $pedido = Pedidos::where('orderId', $pedidoIdRaw)->first();
        if (! $pedido && is_numeric($pedidoIdRaw)) {
            $pedido = Pedidos::where('orderId', (int) $pedidoIdRaw)->first();
        }

        if (! $pedido) {
            $pedido = Pedidos::where('nroOrder', $pedidoIdRaw)->first();
            if (! $pedido && is_numeric($pedidoIdRaw)) {
                $pedido = Pedidos::where('nroOrder', (int) $pedidoIdRaw)->first();
            }
        }

        $this->pedidoCache[$normalized] = $pedido;

        return $pedido;
    }

    /**
     * Procesa una fila válida y determina si es nueva o sin cambios.
     */
    private function processArticle(int $rowIndex, Pedidos $pedido, string $articulo, float $cantidad, float $unit, float $sub): void
    {
        $matchingDetail = DetailPedidos::where('pedidos_id', $pedido->id)
            ->whereRaw('UPPER(TRIM(articulo)) = UPPER(TRIM(?))', [$articulo])
            ->where('cantidad', $cantidad)
            ->whereRaw('ROUND(unit_prize, 2) = ?', [round($unit, 2)])
            ->exists();

        if ($matchingDetail) {
            $this->stats['no_changes_count']++;
            $this->changes['no_changes'][] = [
                'row_index' => $rowIndex,
                'pedido_id' => $pedido->orderId ?? $pedido->nroOrder,
                'articulo' => $articulo,
                'cantidad' => $cantidad,
                'unit_prize' => round($unit, 2),
            ];

            return;
        }

        $this->stats['new_count']++;
        $this->changes['new'][] = [
            'row_index' => $rowIndex,
            'data' => [
                'pedido_id' => $pedido->orderId ?? $pedido->nroOrder,
                'pedido_cliente' => $pedido->customerName ?? $pedido->customer_name ?? 'N/A',
                'articulo' => $articulo,
                'cantidad' => $cantidad,
                'unit_prize' => round($unit, 2),
                'sub_total' => round($sub, 2),
            ],
        ];
    }

    /**
     * Identifica artículos existentes que deben eliminarse al confirmar.
     */
    private function detectArticlesToDelete(): void
    {
        foreach ($this->excelPedidos as $pedidoKey => $excelArticles) {
            $originalId = $this->excelPedidoOriginals[$pedidoKey] ?? $pedidoKey;
            $pedido = $this->findPedido($originalId);

            if (! $pedido) {
                continue;
            }

            if ($pedido->productionStatus === 2) {
                continue;
            }

            $currentArticles = DetailPedidos::where('pedidos_id', $pedido->id)->get();

            foreach ($currentArticles as $currentArticle) {
                $currentKey = $this->buildArticleKey(
                    $currentArticle->articulo,
                    (float) $currentArticle->cantidad,
                    (float) $currentArticle->unit_prize
                );

                if (! isset($excelArticles[$currentKey])) {
                    $this->stats['to_delete_count']++;
                    $this->changes['to_delete'][] = [
                        'id' => $currentArticle->id,
                        'pedido_id' => $pedido->orderId ?? $pedido->nroOrder,
                        'pedido_cliente' => $pedido->customerName ?? $pedido->customer_name ?? 'N/A',
                        'articulo' => $currentArticle->articulo,
                        'cantidad' => (float) $currentArticle->cantidad,
                        'unit_prize' => round((float) $currentArticle->unit_prize, 2),
                        'sub_total' => round((float) $currentArticle->sub_total, 2),
                        'current_bd_data' => [
                            'created_at' => $currentArticle->created_at,
                            'updated_at' => $currentArticle->updated_at,
                        ],
                    ];
                }
            }
        }
    }

    /**
     * Completa los resultados y determina el tipo de alerta para la vista.
     */
    private function finalizeResults(): void
    {
        $this->changes['stats'] = $this->stats;

        $processedSum = $this->stats['new_count']
            + $this->stats['modified_count']
            + $this->stats['no_changes_count']
            + $this->stats['not_found_count']
            + $this->stats['prepared_orders_count']
            + $this->stats['to_delete_count'];

        if ($processedSum === 0 && ! empty($this->changes['duplicates'])) {
            $this->changes['info_message'] = 'Solo se detectaron filas duplicadas. Revisa la tabla de duplicados para corregir tu Excel.';
            $this->data = $this->changes;
            $this->key = 'warning';

            return;
        }

        if ($processedSum === 0) {
            $this->data = 'No se encontraron filas válidas para procesar en el archivo de artículos.';
            $this->key = 'warning';

            return;
        }

        $this->data = $this->changes;

        if (($this->stats['new_count'] + $this->stats['modified_count'] + $this->stats['to_delete_count']) > 0) {
            $this->key = $this->stats['not_found_count'] > 0 ? 'warning' : 'success';

            return;
        }

        $this->key = $this->stats['not_found_count'] > 0 ? 'warning' : 'info';
    }
}
