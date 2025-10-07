<?php

namespace App\Imports;

use App\Models\DetailPedidos;
use App\Models\Pedidos;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;

class DetailPedidosPreviewImport implements ToCollection
{
    public $data;
    public $key;
    public $changes = [];
    public $stats = [];

    /**
     * Método collection requerido por la interfaz ToCollection
     * 
     * Este método procesa la colección de filas del archivo Excel para generar
     * una vista previa de los cambios que se realizarían en los detalles de pedidos.
     * Detecta duplicados, valida datos y genera estadísticas sin modificar la base de datos.
     * 
     * @param Collection $rows Colección de filas del archivo Excel
     * @return void
     */
    public function collection(Collection $rows)
    {
        $this->initializeCounters();
        $this->changes = [
            'new' => [],
            'modified' => [],
            'no_changes' => [],
            'not_found' => [],
            'prepared_orders' => [],
            'duplicates' => [],
            'to_delete' => [], 
            'stats' => []
        ];

        // Convertir colección a array manteniendo claves para mejor manejo
        $originalRows = $rows->toArray();
        
        $colMap = $this->detectColumns($originalRows);
        $processedRows = $this->detectDuplicates($originalRows, $colMap);

        // Almacenar información de duplicados pero NO detener el procesamiento
        if ($processedRows['has_duplicates']) {
            // Debug: Registrar qué duplicados se encontraron
            Log::info('Duplicates detected', [
                'count' => count($processedRows['duplicates']),
                'duplicates' => $processedRows['duplicates']
            ]);
            
            $this->changes['duplicates'] = $processedRows['duplicates'];
            // No retornar aquí - continuar procesamiento para mostrar vista previa con duplicados resaltados
        }

        // NUEVO: Recopilar todos los pedidos únicos del Excel para detectar eliminaciones
        $excelPedidos = $this->collectExcelOrders($originalRows, $colMap);

        foreach ($originalRows as $rowIndex => $row) {
            $this->processRow($row, $rowIndex, $colMap);
        }

        // NUEVO: Detectar artículos que deben ser eliminados
        $this->detectArticlesToDelete($excelPedidos);

        $this->finalizeResults();
    }

    /**
     * Inicializa los contadores para el proceso de vista previa
     * 
     * Este método reinicia todas las estadísticas y contadores utilizados
     * para rastrear el progreso y resultados del análisis de vista previa.
     * 
     * @return void
     */
    private function initializeCounters()
    {
        $this->stats = [
            'new_count' => 0,
            'modified_count' => 0,
            'no_changes_count' => 0,
            'not_found_count' => 0,
            'prepared_orders_count' => 0,
            'to_delete_count' => 0, 
            'total_count' => 0
        ];
    }

    /**
     * Detecta el formato de columnas del archivo Excel
     * 
     * Este método analiza las primeras filas del archivo para determinar
     * automáticamente el formato y mapeo de columnas utilizado, permitiendo
     * manejar diferentes formatos de archivo Excel de manera flexible.
     * 
     * @param array $rows Array de filas del archivo Excel
     * @return array Mapeo de columnas detectado
     */
    private function detectColumns(array $rows)
    {
        // Por defecto, usar el formato compacto nuevo (A..E => 0..4)
        $colMap = [
            'numero' => 0,
            'articulo' => 1,
            'cantidad' => 2,
            'precio' => 3,
            'subtotal' => 4,
        ];

        if (count($rows) === 0) {
            return $colMap;
        }

        // Heurística: escanea las primeras ~10 filas para detectar el formato antiguo donde col[2] == 'PEDIDO'
        // y los datos reales están en D/Q/R/S/T => 3/16/17/18/19
        $maxProbe = min(10, count($rows));
        for ($i = 0; $i < $maxProbe; $i++) {
            $row = $rows[$i];
            if (!is_array($row)) { $row = $row->toArray(); }

            // Omitir filas completamente vacías
            if (empty(array_filter($row, fn($v) => $v !== null && trim((string)$v) !== ''))) {
                continue;
            }

            $col2 = isset($row[2]) ? strtoupper(trim((string)$row[2])) : '';
            $hasOldArticleCol = array_key_exists(16, $row) && trim((string)($row[16] ?? '')) !== '';
            if ($col2 === 'PEDIDO' && $hasOldArticleCol) {
                Log::info('Detected old Excel format (D/Q/R/S/T mapping)');
                return [
                    'numero' => 3,
                    'articulo' => 16,
                    'cantidad' => 17,
                    'precio' => 18,
                    'subtotal' => 19,
                ];
            }
        }

        Log::info('Using new Excel format (A..E mapping)');
        return $colMap;
    }

    private function detectDuplicates(array $rows, array $colMap)
    {
        $seen = [];
        $duplicates = [];
        $has_duplicates = false;

        foreach ($rows as $rowIndex => $row) {
            // Omitir las dos primeras filas como encabezados si están presentes
            if ($rowIndex < 2) { continue; }
            if (!is_array($row)) { 
                $row = $row->toArray(); 
            }

            // Omitir líneas vacías y encabezados
            if ($this->shouldSkipRow($row, $colMap)) {
                continue;
            }

            // Leer usando el mapeo de columnas detectado solamente
            $pedidoIdRaw = isset($row[$colMap['numero']]) ? trim((string)$row[$colMap['numero']]) : '';
            $articulo = isset($row[$colMap['articulo']]) ? trim((string)$row[$colMap['articulo']]) : '';
            $cantidad = isset($row[$colMap['cantidad']]) ? (float)$row[$colMap['cantidad']] : 0;
            // Usar precisión de 2 decimales para coincidir con la lógica de importación
            $precio = isset($row[$colMap['precio']]) ? round((float)$row[$colMap['precio']], 2) : 0;
            
            // Registro de depuración para filas problemáticas específicas
            if ($rowIndex >= 9 && $rowIndex <= 11) {
                Log::info('Debugging specific row data', [
                    'row_index' => $rowIndex,
                    'raw_row_data' => $row,
                    'colMap' => $colMap,
                    'extracted_numero' => $pedidoIdRaw,
                    'extracted_articulo' => $articulo,
                    'extracted_cantidad' => $cantidad,
                    'extracted_precio' => $precio,
                    'raw_cantidad_value' => isset($row[$colMap['cantidad']]) ? $row[$colMap['cantidad']] : 'NOT_SET',
                    'raw_precio_value' => isset($row[$colMap['precio']]) ? $row[$colMap['precio']] : 'NOT_SET'
                ]);
            }
            
            // Omitir filas con datos críticos faltantes
            if (empty($pedidoIdRaw) || empty($articulo)) {
                continue;
            }

            // Registrar lo que estamos procesando para depuración

            Log::info('Processing row for duplicates', [
                'row_index' => $rowIndex,
                'pedido_id' => $pedidoIdRaw,
                'articulo' => $articulo,
                'cantidad' => $cantidad,
                'precio' => $precio,
                'col_map' => $colMap
            ]);

            // Crear clave única incluyendo pedido + artículo + cantidad + precio
            // Dos filas solo son duplicadas si TODOS estos valores son exactamente iguales
            $normalizedKey = strtoupper(trim($pedidoIdRaw)) . '|' . 
                           strtoupper(trim($articulo)) . '|' . 
                           $cantidad . '|' . 
                           $precio;
            
            Log::info('Generated duplicate key', [
                'row_index' => $rowIndex,
                'normalizedKey' => $normalizedKey,
                'pedido_raw' => $pedidoIdRaw,
                'articulo' => $articulo,
                'cantidad' => $cantidad,
                'precio' => $precio
            ]);
            
            if (isset($seen[$normalizedKey])) {
                $has_duplicates = true;
                
                Log::info('Duplicate found!', [
                    'key' => $normalizedKey,
                    'original_row' => $seen[$normalizedKey] + 1,
                    'duplicate_row' => $rowIndex + 1,
                    'pedido_id' => $pedidoIdRaw,
                    'articulo' => $articulo,
                    'cantidad' => $cantidad,
                    'precio' => $precio
                ]);
                
                // Agregar la fila original si aún no está en duplicados
                $originalRowIndex = $seen[$normalizedKey];
                $alreadyAdded = false;
                foreach ($duplicates as $dup) {
                    if ($dup['row_index'] === ($originalRowIndex + 1)) {
                        $alreadyAdded = true;
                        break;
                    }
                }
                
                if (!$alreadyAdded) {
                    $duplicates[] = $this->formatDuplicateRow($rows[$originalRowIndex], $originalRowIndex + 1, $colMap);
                }
                
                // Agregar el duplicado actual
                $duplicates[] = $this->formatDuplicateRow($row, $rowIndex + 1, $colMap);
            } else {
                $seen[$normalizedKey] = $rowIndex;
            }
        }

        Log::info('Duplicate detection results', [
            'has_duplicates' => $has_duplicates,
            'duplicate_count' => count($duplicates)
        ]);

        return [
            'has_duplicates' => $has_duplicates,
            'duplicates' => $duplicates
        ];
    }

    private function formatDuplicateRow(array $row, int $rowIndex, array $colMap)
    {
        $pedidoId = isset($row[$colMap['numero']]) ? trim((string)$row[$colMap['numero']]) : '';
        $articulo = isset($row[$colMap['articulo']]) ? trim((string)$row[$colMap['articulo']]) : '';
        $cantidad = isset($row[$colMap['cantidad']]) ? (float)$row[$colMap['cantidad']] : 0;
    $precio = isset($row[$colMap['precio']]) ? round((float)$row[$colMap['precio']], 2) : 0;
    $subtotal = isset($row[$colMap['subtotal']]) ? round((float)$row[$colMap['subtotal']], 2) : 0;
        
        return [
            'row_index' => $rowIndex,
            'pedido_id' => $pedidoId,
            'articulo' => $articulo,
            'cantidad' => $cantidad,
            'unit_prize' => $precio,
            'sub_total' => $subtotal,
            'duplicate_key' => strtoupper(trim($pedidoId)) . '|' . strtoupper(trim($articulo)) . '|' . $cantidad . '|' . $precio,
            'raw_data_preview' => [
                'col_' . $colMap['numero'] => isset($row[$colMap['numero']]) ? $row[$colMap['numero']] : 'N/A',
                'col_' . $colMap['articulo'] => isset($row[$colMap['articulo']]) ? $row[$colMap['articulo']] : 'N/A',  
                'col_' . $colMap['cantidad'] => isset($row[$colMap['cantidad']]) ? $row[$colMap['cantidad']] : 'N/A',
                'col_' . $colMap['precio'] => isset($row[$colMap['precio']]) ? $row[$colMap['precio']] : 'N/A'
            ]
        ];
    }


    private function shouldSkipRow(array $row, array $colMap)
    {
        // Omitir líneas completamente vacías
        $nonEmptyValues = array_filter($row, fn($v) => $v !== null && trim((string)$v) !== '');
        if (empty($nonEmptyValues)) {
            return true;
        }

        // Omitir filas de encabezado - verifica palabras clave de encabezado en varias columnas

        $numeroRaw = isset($row[$colMap['numero']]) ? 
            strtolower(trim((string)$row[$colMap['numero']])) : '';
        $articuloRaw = isset($row[$colMap['articulo']]) ? 
            strtolower(trim((string)$row[$colMap['articulo']])) : '';
        
        $headerKeywords = ['numero', 'número', 'pedido', 'order', 'nro', 'articulo', 'artículo', 'producto', 'item'];
        
        // Si la columna de pedido contiene palabras clave de encabezado O la columna de artículo contiene palabras clave de encabezado

        if (in_array($numeroRaw, $headerKeywords) || in_array($articuloRaw, $headerKeywords)) {
            return true;
        }
        
        // Para el formato antiguo, verifica si la columna 2 contiene "PEDIDO" pero la columna 3 parece encabezado
        if (isset($row[2]) && strtoupper(trim((string)$row[2])) === 'PEDIDO') {
            $col3 = isset($row[3]) ? strtolower(trim((string)$row[3])) : '';
            if (in_array($col3, ['numero', 'número', 'pedido', 'order', 'nro'])) {
                return true;
            }
        }

        return false;
    }

    private function processRow($row, int $rowIndex, array $colMap)
    {
        if (!is_array($row)) { 
            $row = $row->toArray(); 
        }

        // Omitir las dos primeras filas como encabezados si están presentes
        if ($rowIndex < 2) {
            return;
        }

        if ($this->shouldSkipRow($row, $colMap)) {
            return;
        }

    // Extraer y validar datos usando las columnas detectadas
    $pedidoIdRaw = isset($row[$colMap['numero']]) ? trim((string)$row[$colMap['numero']]) : '';
    $articulo    = isset($row[$colMap['articulo']]) ? trim((string)$row[$colMap['articulo']]) : '';
    $cantidad    = isset($row[$colMap['cantidad']]) ? (float)$row[$colMap['cantidad']] : 0;
        
        // Omitir filas con datos críticos vacíos
        if (empty($pedidoIdRaw) || empty($articulo) || $cantidad <= 0) {
            return;
        }

        $this->stats['total_count']++;

        $pedido = $this->findPedido($pedidoIdRaw);
        
        if (!$pedido) {
            $this->stats['not_found_count']++;
            $this->changes['not_found'][] = [
                'row_index' => $rowIndex + 1,
                'pedido_id' => $pedidoIdRaw,
                'articulo' => $articulo,
            ];
            return;
        }

        // Verificar si el pedido está preparado (estado 2)
        if ($pedido->productionStatus === 2) {
            $this->stats['prepared_orders_count']++;
            $this->changes['prepared_orders'][] = [
                'row_index' => $rowIndex + 1,
                'pedido_id' => $pedido->orderId ?? $pedido->nroOrder,
                'articulo' => $articulo,
            ];
            return;
        }

    $this->processArticle($row, $rowIndex, $colMap, $pedido);
    }

    /**
     * Busca un pedido por su ID de orden
     * 
     * Este método busca un pedido existente en la base de datos utilizando
     * primero el orderId y luego el nroOrder como alternativa si no se encuentra.
     * 
     * @param string $pedidoIdRaw El ID del pedido a buscar
     * @return Pedidos|null El pedido encontrado o null si no existe
     */
    private function findPedido(string $pedidoIdRaw)
    {
        // Intentar buscar por orderId
        $pedido = Pedidos::where('orderId', $pedidoIdRaw)->first();
        if (!$pedido && is_numeric($pedidoIdRaw)) {
            $pedido = Pedidos::where('orderId', (int)$pedidoIdRaw)->first();
        }
        
        // Intentar buscar por nroOrder si no se encuentra por orderId

        if (!$pedido) {
            $pedido = Pedidos::where('nroOrder', $pedidoIdRaw)->first();
            if (!$pedido && is_numeric($pedidoIdRaw)) {
                $pedido = Pedidos::where('nroOrder', (int)$pedidoIdRaw)->first();
            }
        }

        return $pedido;
    }

    /**
     * Procesa un artículo específico del pedido
     * 
     * Este método analiza un artículo individual, determina si es nuevo o existente,
     * calcula las modificaciones necesarias y actualiza las estadísticas correspondientes.
     * 
     * @param array $row La fila de datos que contiene el artículo
     * @param int $rowIndex El índice de la fila en el archivo
     * @param array $colMap El mapeo de columnas
     * @param mixed $pedido El pedido al que pertenece el artículo
     * @return void
     */
    private function processArticle(array $row, int $rowIndex, array $colMap, $pedido)
    {
        $articulo = isset($row[$colMap['articulo']]) ? trim((string)$row[$colMap['articulo']]) : '';
        $cantidad = isset($row[$colMap['cantidad']]) ? (float)$row[$colMap['cantidad']] : 0;
        $unit = isset($row[$colMap['precio']]) && $row[$colMap['precio']] !== '' ? 
            round((float)$row[$colMap['precio']], 2) : 0.0;
        $sub = isset($row[$colMap['subtotal']]) && $row[$colMap['subtotal']] !== '' ? 
            round((float)$row[$colMap['subtotal']], 2) : 
            round($cantidad * $unit, 2);
        // Regla: solo considerar como SIN CAMBIOS cuando existe una línea exactamente igual (artículo + cantidad + precio unitario)
        $exactExists = DetailPedidos::where('pedidos_id', $pedido->id)
            ->whereRaw('UPPER(TRIM(articulo)) = UPPER(TRIM(?))', [$articulo])
            ->where('cantidad', $cantidad)
            ->whereRaw('ROUND(unit_prize, 2) = ?', [$unit])
            ->exists();

        if ($exactExists) {
            $this->stats['no_changes_count']++;
            $this->changes['no_changes'][] = [
                'row_index' => $rowIndex + 1,
                'pedido_id' => $pedido->orderId ?? $pedido->nroOrder,
                'articulo' => $articulo,
                'cantidad' => $cantidad,
                'unit_prize' => $unit,
            ];
            return;
        }

        // De lo contrario, tratar como un detalle NUEVO para agregar (incluso si existe otra línea con el mismo artículo)
        $this->addNewArticle($row, $rowIndex, $colMap, $pedido, $articulo, $cantidad, $unit, $sub);
    }

    /**
     * Agrega un nuevo artículo a la lista de cambios
     * 
     * Este método registra un artículo nuevo que será creado durante la importación,
     * incluyendo toda la información necesaria para su creación.
     * 
     * @param array $row La fila de datos del artículo
     * @param int $rowIndex El índice de la fila
     * @param array $colMap El mapeo de columnas
     * @param mixed $pedido El pedido al que pertenece
     * @param string $articulo El nombre del artículo
     * @param float $cantidad La cantidad del artículo
     * @param float $unit El precio unitario
     * @param float $sub El subtotal
     * @return void
     */
    private function addNewArticle($row, int $rowIndex, array $colMap, $pedido, string $articulo, float $cantidad, float $unit, float $sub)
    {
    $this->stats['new_count']++;
    $this->changes['new'][] = [
            'row_index' => $rowIndex + 1,
            'data' => [
        'pedido_id' => $pedido->orderId ?? $pedido->nroOrder,
        'pedido_cliente' => $pedido->customerName ?? $pedido->customer_name ?? 'N/A',
                'articulo' => $articulo,
                'cantidad' => $cantidad,
                'unit_prize' => $unit,
                'sub_total' => $sub,
            ]
        ];
    }

    /**
<<<<<<< HEAD
     * Verifica las modificaciones necesarias en un artículo existente
     * 
     * Este método compara los valores actuales de un artículo con los nuevos valores
     * del archivo Excel y determina qué campos necesitan ser actualizados.
     * 
     * @param array $row La fila de datos nueva
     * @param int $rowIndex El índice de la fila
     * @param array $colMap El mapeo de columnas
     * @param mixed $pedido El pedido al que pertenece
     * @param mixed $existing El artículo existente en la base de datos
     * @param float $cantidad La nueva cantidad
     * @param float $unit El nuevo precio unitario
     * @param float $sub El nuevo subtotal
     * @return void
     */
    private function checkModifications($row, int $rowIndex, array $colMap, $pedido, $existing, float $cantidad, float $unit, float $sub)
    {
        $modifications = [];

        if ((float)$existing->cantidad != $cantidad) {
            $modifications[] = [
                'field' => 'cantidad',
                'label' => 'Cantidad',
                'old_value' => (float)$existing->cantidad,
                'new_value' => $cantidad,
            ];
        }

    if (round((float)$existing->unit_prize, 2) !== $unit) {
            $modifications[] = [
                'field' => 'unit_prize', 
                'label' => 'Precio Unitario',
                'old_value' => 'S/ ' . round((float)$existing->unit_prize, 2),
                'new_value' => 'S/ ' . $unit,
            ];
        }

    if (round((float)$existing->sub_total, 2) !== $sub) {
            $modifications[] = [
                'field' => 'sub_total',
                'label' => 'Sub Total',
                'old_value' => 'S/ ' . round((float)$existing->sub_total, 2),
                'new_value' => 'S/ ' . $sub,
            ];
        }

        if (!empty($modifications)) {
            $this->stats['modified_count']++;
            $this->changes['modified'][] = [
                'row_index' => $rowIndex + 1,
                'pedido_id' => $pedido->orderId ?? $pedido->nroOrder,
                'modifications' => $modifications,
                'existing' => [
                    'pedido_id' => $pedido->orderId ?? $pedido->nroOrder,
                    'pedido_cliente' => $pedido->customerName ?? $pedido->customer_name ?? 'N/A',
                    'articulo' => $existing->articulo,
                    'cantidad' => (float)$existing->cantidad,
                    'unit_prize' => round((float)$existing->unit_prize, 2),
                    'sub_total' => round((float)$existing->sub_total, 2),
                    'last_data_update' => $existing->updated_at ? $existing->updated_at->format('Y-m-d H:i:s') : 'N/A',
                ],
                'new' => [
                    'pedido_id' => $pedido->orderId ?? $pedido->nroOrder,
                    'pedido_cliente' => $pedido->customerName ?? $pedido->customer_name ?? 'N/A',
                    'articulo' => trim((string)$row[$colMap['articulo']]),
                    'cantidad' => $cantidad,
                    'unit_prize' => $unit,
                    'sub_total' => $sub,
                ]
            ];
        } else {
            $this->stats['no_changes_count']++;
            $this->changes['no_changes'][] = [
                'row_index' => $rowIndex + 1,
                'pedido_id' => $pedido->orderId ?? $pedido->nroOrder,
                'articulo' => $existing->articulo,
            ];
        }
    }

    /**
=======
>>>>>>> f76f4ac7a11c11334cc0a0e9b770a16c887d9683
     * Finaliza los resultados del análisis de vista previa
     * 
     * Este método completa el proceso de vista previa, genera el resumen final,
     * actualiza las estadísticas y prepara los datos para ser retornados al usuario.
     * 
     * @return void
     */
    private function finalizeResults()
    {
        $this->changes['stats'] = $this->stats;

        $processedSum = $this->stats['new_count'] + $this->stats['modified_count'] + 
                        $this->stats['no_changes_count'] + $this->stats['not_found_count'] + 
                        $this->stats['prepared_orders_count'] + $this->stats['to_delete_count']; // NUEVO: incluir eliminaciones

        // Si no se procesó nada pero hay duplicados detectados, aún así devolver vista previa para que el usuario pueda corregir el Excel
        if ($processedSum === 0 && !empty($this->changes['duplicates'])) {
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

        // Generar resumen
        $summary = "RESUMEN DE PROCESAMIENTO:\n";
        $summary .= "Artículos nuevos: {$this->stats['new_count']}\n";
        $summary .= "Artículos modificados: {$this->stats['modified_count']}\n";
        $summary .= "Sin cambios: {$this->stats['no_changes_count']}\n";
        $summary .= "Artículos a eliminar: {$this->stats['to_delete_count']}\n"; // NUEVO
        $summary .= "Pedidos no encontrados: {$this->stats['not_found_count']}\n";
        $summary .= "Pedidos preparados (sin cambios): {$this->stats['prepared_orders_count']}\n";
        $summary .= "Total filas procesadas: {$this->stats['total_count']}\n";

        $this->data = $this->changes;
        
        if ($this->stats['new_count'] + $this->stats['modified_count'] + $this->stats['to_delete_count'] > 0) {
            $this->key = $this->stats['not_found_count'] > 0 ? 'warning' : 'success';
        } else {
            $this->key = $this->stats['not_found_count'] > 0 ? 'warning' : 'info';
        }
    }

    /**
     * Recopila todos los pedidos únicos del Excel con sus artículos
     * 
     * Este método analiza todas las filas del Excel y agrupa los artículos
     * por pedido para poder comparar después con la base de datos.
     * 
     * @param array $rows Filas del Excel
     * @param array $colMap Mapeo de columnas
     * @return array Array asociativo [pedidoId => [artículos]]
     */
    private function collectExcelOrders(array $rows, array $colMap): array
    {
        $excelPedidos = [];
        
        foreach ($rows as $rowIndex => $row) {
            // Omitir las dos primeras filas como encabezados si están presentes
            if ($rowIndex < 2) {
                continue;
            }
            
            if (!is_array($row)) {
                $row = $row->toArray();
            }
            
            if ($this->shouldSkipRow($row, $colMap)) {
                continue;
            }
            
            $pedidoIdRaw = isset($row[$colMap['numero']]) ? trim((string)$row[$colMap['numero']]) : '';
            $articulo = isset($row[$colMap['articulo']]) ? trim((string)$row[$colMap['articulo']]) : '';
            $cantidad = isset($row[$colMap['cantidad']]) ? (float)$row[$colMap['cantidad']] : 0;
            $unit = isset($row[$colMap['precio']]) && $row[$colMap['precio']] !== '' ? 
                round((float)$row[$colMap['precio']], 2) : 0.0;
            
            // Omitir filas con datos críticos vacíos
            if (empty($pedidoIdRaw) || empty($articulo) || $cantidad <= 0) {
                continue;
            }
            
            // Normalizar pedido ID
            $pedidoKey = strtoupper(trim($pedidoIdRaw));
            
            if (!isset($excelPedidos[$pedidoKey])) {
                $excelPedidos[$pedidoKey] = [];
            }
            
            // Crear clave única para el artículo (artículo + cantidad + precio)
            $articleKey = strtoupper(trim($articulo)) . '|' . $cantidad . '|' . $unit;
            
            $excelPedidos[$pedidoKey][$articleKey] = [
                'articulo' => $articulo,
                'cantidad' => $cantidad,
                'unit_prize' => $unit,
            ];
        }
        
        Log::info('Excel orders collected', [
            'pedidos_count' => count($excelPedidos),
            'pedidos_keys' => array_keys($excelPedidos)
        ]);
        
        return $excelPedidos;
    }

    /**
     * Detecta artículos existentes en BD que deben ser eliminados
     * 
     * Este método compara los artículos actuales de cada pedido en la base de datos
     * con los artículos que vienen en el nuevo Excel. Los que están en BD pero no
     * en Excel se marcan para eliminación.
     * 
     * @param array $excelPedidos Pedidos del Excel con sus artículos
     * @return void
     */
    private function detectArticlesToDelete(array $excelPedidos): void
    {
        foreach ($excelPedidos as $pedidoKey => $excelArticles) {
            // Buscar el pedido en BD
            $pedido = $this->findPedido($pedidoKey);
            
            if (!$pedido) {
                continue; // Ya se maneja en not_found
            }
            
            // Omitir pedidos preparados
            if ($pedido->productionStatus === 2) {
                continue; // Ya se maneja en prepared_orders
            }
            
            // Obtener todos los artículos actuales de este pedido en BD
            $currentArticles = DetailPedidos::where('pedidos_id', $pedido->id)->get();
            
            foreach ($currentArticles as $currentArticle) {
                // Crear clave única para comparar con Excel
                $currentKey = strtoupper(trim($currentArticle->articulo)) . '|' . 
                             $currentArticle->cantidad . '|' . 
                             round((float)$currentArticle->unit_prize, 2);
                
                // Si este artículo NO está en el Excel, marcarlo para eliminación
                if (!isset($excelArticles[$currentKey])) {
                    $this->stats['to_delete_count']++;
                    $this->changes['to_delete'][] = [
                        'id' => $currentArticle->id,
                        'pedido_id' => $pedido->orderId ?? $pedido->nroOrder,
                        'pedido_cliente' => $pedido->customerName ?? $pedido->customer_name ?? 'N/A',
                        'articulo' => $currentArticle->articulo,
                        'cantidad' => $currentArticle->cantidad,
                        'unit_prize' => round((float)$currentArticle->unit_prize, 2),
                        'sub_total' => round((float)$currentArticle->sub_total, 2),
                        'current_bd_data' => [
                            'created_at' => $currentArticle->created_at,
                            'updated_at' => $currentArticle->updated_at,
                        ]
                    ];
                }
            }
        }
        
        Log::info('Articles to delete detected', [
            'count' => $this->stats['to_delete_count']
        ]);
    }
}