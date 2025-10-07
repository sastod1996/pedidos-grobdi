<?php

namespace App\Http\Controllers\pedidos\counter;

use App\Http\Controllers\Controller;
use App\Http\Requests\counter\CargarPedidosUpdateRequest;
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
use App\Services\PedidoImportService;
use App\Services\DoctorSyncService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class CargarPedidosController extends Controller
{
    protected $pedidoImportService;
    protected $doctorSyncService;

    public function __construct(PedidoImportService $pedidoImportService, DoctorSyncService $doctorSyncService)
    {
        $this->pedidoImportService = $pedidoImportService;
        $this->doctorSyncService = $doctorSyncService;
    }

    public function index(Request $request)
    {
        if($request->query("fecha")){
            $request->validate(['fecha' => 'required|date']);
            $dia = Carbon::parse($request->fecha)->startOfDay();
        } else {
            $dia = now()->format('Y-m-d');
        }
        $filtro = $request->filtro ?: "deliveryDate";
        $pedidos = Pedidos::whereDate($filtro, $dia);
        $turno = $request->query('turno');
        if ($turno !== null && $turno !== '') {
            $pedidos = $pedidos->where('turno', $turno);
        }
        $pedidos = $pedidos->orderBy('nroOrder')->get();
        return view('pedidos.counter.cargar_pedido.index', compact('pedidos'));
    }

    public function create(){
        return view('pedidos.counter.cargar_pedido.create');
    }

    public function store(Request $request)
    {
        return $this->pedidoImportService->storeFile($request);
    }

    public function preview(Request $request)
    {
        $fileName = $request->get('filename');
        return $this->pedidoImportService->previewFile($fileName);
    }

    public function confirmChanges(Request $request)
    {
        $fileName = $request->get('filename');
        return $this->pedidoImportService->confirmChanges($fileName);
    }

    public function cancelChanges(Request $request)
    {
        $fileName = $request->get('filename');
        return $this->pedidoImportService->cancelChanges($fileName);
    }

    // Other methods can be moved to service similarly...
    // For brevity, keeping some here, but ideally move all logic to service.

    public function show($pedido){
        $pedido = Pedidos::find($pedido);
        return view('pedidos.counter.cargar_pedido.show', compact('pedido'));
    }

    public function edit($pedido){
        $pedido = Pedidos::find($pedido);
        $zonas = Zone::all();
        $doctores = Doctor::where('state', 1)->orderBy('name')->get();
        return view('pedidos.counter.cargar_pedido.edit', compact('pedido','zonas','doctores'));
    }

    public function update(CargarPedidosUpdateRequest $request, $id)
    {
        $fecha = $this->pedidoImportService->updatePedido($request, $id);
        return redirect()->route('cargarpedidos.index',$fecha)
                        ->with('success','Pedido modificado exitosamente');
    }

    // Other methods...

    private function cleanupTempFiles()
    {
        try {
            // Get all temp files
            $tempFiles = Storage::files('temp');
            foreach ($tempFiles as $file) {
                // Skip .gitkeep file
                if (basename($file) === '.gitkeep') {
                    continue;
                }
                // Check if file is older than 1 hour
                $lastModified = Storage::lastModified($file);
                if ($lastModified && time() - $lastModified > 3600) { // 1 hour
                    Storage::delete($file);
                }
            }
        } catch (\Exception $e) {
            // Log error but don't fail the request
            logger('Error cleaning temp files: ' . $e->getMessage());
        }
    }

    private function analyzeChanges($data)
    {
        $changes = [
            'new' => [],
            'modified' => [],
            'stats' => [
                'new_count' => 0,
                'modified_count' => 0,
                'total_count' => 0
            ]
        ];

        foreach ($data as $index => $row) {
            if (!is_array($row) || count($row) < 5) {
                continue;
            }
            // Normalize some cells to strings for checks
            $col2 = isset($row[2]) ? strtoupper(trim((string) $row[2])) : '';
            $col16 = isset($row[16]) ? strtoupper(trim((string) $row[16])) : '';
            // Skip header rows or non-PEDIDO sections
            if ($col16 === 'ARTICULO' || $col2 !== 'PEDIDO') {
                continue;
            }

            $changes['stats']['total_count']++;

            // Check if order exists
            $orderIdRaw = isset($row[3]) ? trim((string) $row[3]) : '';
            $existingOrder = Pedidos::where('orderId', $orderIdRaw)->first();

            if (!$existingOrder) {
                // New order|
                $changes['stats']['new_count']++;
            } else {
                // Check for modifications
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
            'nroOrder' => '', // Will be auto-generated
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
            'created_at' => $row[20] ? Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row[20]))->format('Y-m-d H:i:s') : '',
            'zone_name' => $zone ? $zone->name : 'Sin zona',
            'user_name' => $row[19] ? (User::where('name', $row[19])->first()->name ?? Auth::user()->name) : Auth::user()->name,
            'last_data_update' => now()->format('Y-m-d H:i:s') // Nueva fecha de actualización
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
            'deliveryDate' => Carbon::parse($order->deliveryDate)->format('Y-m-d'), // Solo fecha, sin hora
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
            'deliveryDate' => 'Fecha de Entrega'
        ];

        foreach ($fieldsToCompare as $field => $label) {
            // Para fechas, comparar solo la fecha sin hora
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

    public function previewArticulos(Request $request)
    {
        Log::info('previewArticulos method called', ['request' => $request->all()]);

        $fileName = $request->get('filename');

        Log::info('fileName from request', ['fileName' => $fileName]);

        if (!$fileName || !Storage::exists('temp/' . $fileName)) {
            Log::error('File not found', ['fileName' => $fileName, 'exists' => Storage::exists('temp/' . $fileName)]);
            return redirect()->route('cargarpedidos.create')
                ->with('danger', 'Archivo no encontrado. Por favor, sube el archivo nuevamente.');
        }

        try {
            // Get full path to the file
            $filePath = Storage::path('temp/' . $fileName);
            Log::info('File path', ['filePath' => $filePath]);

            // Use the DetailPedidosPreviewImport class instead of the old method
            $previewImport = new DetailPedidosPreviewImport();
            Excel::import($previewImport, $filePath);

            $changes = $previewImport->data;
            $key = $previewImport->key;

            // Always show the preview, even with duplicates
            // The view will handle disabling the confirm button if there are duplicates
            Log::info('Changes analyzed using DetailPedidosPreviewImport', ['key' => $key]);

            return view('pedidos.counter.cargar_pedido.preview-articulos', compact('changes', 'fileName'));
        } catch (\Exception $e) {
            Log::error('Error in previewArticulos', ['error' => $e->getMessage()]);
            // Delete temporary file if there's an error
            if (Storage::exists('temp/' . $fileName)) {
                Storage::delete('temp/' . $fileName);
            }
            return redirect()->route('cargarpedidos.create')
                ->with('danger', 'Error al procesar el archivo: ' . $e->getMessage());
        }
    }

    public function confirmArticulos(Request $request)
    {
        try {
            $fileName = $request->input('filename');
            if (!$fileName) {
                return redirect()->route('cargarpedidos.create')->with('danger', 'Archivo no especificado');
            }

            if (!Storage::exists('temp/' . $fileName)) {
                return redirect()->route('cargarpedidos.create')->with('danger', 'Archivo temporal no encontrado');
            }

            $filePath = Storage::path('temp/' . $fileName);


            // Importar directamente; el import gestiona si hay cambios o no

            // Import articles using the writer import to persist changes and return a string message
            $detailImport = new DetailPedidosImport;
            Excel::import($detailImport, $filePath);

            // Clean up temp file
            Storage::delete('temp/' . $fileName);

            return redirect()->route('cargarpedidos.create')->with($detailImport->key, $detailImport->data);
        } catch (\Exception $e) {
            // Clean up temp file
            if (isset($fileName)) {
                Storage::delete('temp/' . $fileName);
            }
            return redirect()->route('cargarpedidos.create')->with('danger', 'Error al importar artículos: ' . $e->getMessage());
        }
    }

    public function cancelArticulos(Request $request)
    {
        try {
            $fileName = $request->input('filename');
            if ($fileName) {
                Storage::delete('temp/' . $fileName);
            }
            return redirect()->route('cargarpedidos.create')->with('info', 'Importación de artículos cancelada');
        } catch (\Exception $e) {
            return redirect()->route('cargarpedidos.create')->with('warning', 'Importación cancelada');
        }
    }

    private function analyzeArticulosChanges($filePath)
    {
        // Read Excel data using a simple array import to avoid any processing
        $data = Excel::toArray(new SimpleArrayImport, $filePath);
        $rows = $data[0] ?? [];

        // Column map with correct positions for your Excel format
        $colMap = [
            'numero' => 3,    // Columna D: Numero
            'articulo' => 16, // Columna Q: Articulo  
            'cantidad' => 17, // Columna R: Cantidad
            'precio' => 18,   // Columna S: PrecioUnitario
            'subtotal' => 19, // Columna T: SubTotal
        ];

        // Try to detect headers from row 1 (index 1, since row 2 has headers)
        if (!empty($rows) && isset($rows[1])) {
            $headerRow = $rows[1] ?? [];
            $headers = array_map(function ($v) {
                return is_string($v) ? strtolower(trim($v)) : $v;
            }, $headerRow);
            $nameToKey = [
                'numero' => ['numero', 'número', 'pedido', 'nro', 'nro pedido'],
                'articulo' => ['articulo', 'artículo', 'producto', 'item'],
                'cantidad' => ['cantidad', 'cant'],
                'precio' => ['preciounitario', 'precio unitario', 'precio', 'p. unitario'],
                'subtotal' => ['subtotal', 'sub total', 'total linea', 'total línea'],
            ];
            foreach ($nameToKey as $key => $aliases) {
                foreach ($headers as $idx => $label) {
                    if (!is_string($label))
                        continue;
                    if (in_array($label, $aliases, true)) {
                        $colMap[$key] = $idx;
                        break;
                    }
                }
            }
        }

        $stats = [
            'total_count' => 0,
            'new_count' => 0,
            'modified_count' => 0,
            'duplicates_excel_count' => 0,
            'unchanged_count' => 0,
        ];

        $newArticles = [];
        $modifiedArticles = [];
        $duplicates = [];
        $unchanged = [];
        $seenExcelRows = [];

        // Start from row 3 (index 2) since headers are in row 2 (index 1)
        foreach ($rows as $index => $row) {
            if ($index < 2)
                continue; // Skip rows 1 and 2 (headers)
            if (!is_array($row) || count($row) === 0)
                continue;

            $numeroIdx = (int) $colMap['numero'];
            $artIdx = (int) $colMap['articulo'];
            $cantIdx = (int) $colMap['cantidad'];
            $precioIdx = (int) $colMap['precio'];
            $subIdx = (int) $colMap['subtotal'];

            if (!isset($row[$numeroIdx]) || !isset($row[$artIdx]) || !isset($row[$cantIdx]))
                continue;
            $numeroRaw = is_string($row[$numeroIdx]) ? strtolower(trim($row[$numeroIdx])) : $row[$numeroIdx];
            if ($numeroRaw === 'numero' || $numeroRaw === 'número' || $numeroRaw === 'pedido')
                continue;

            $stats['total_count']++;

            $pedidoIdRaw = trim((string) $row[$numeroIdx]);
            $pedido = Pedidos::where('orderId', $pedidoIdRaw)->first();
            if (!$pedido && is_numeric($pedidoIdRaw))
                $pedido = Pedidos::where('orderId', (int) $pedidoIdRaw)->first();
            if (!$pedido) {
                $pedido = Pedidos::where('nroOrder', $pedidoIdRaw)->first();
                if (!$pedido && is_numeric($pedidoIdRaw))
                    $pedido = Pedidos::where('nroOrder', (int) $pedidoIdRaw)->first();
            }
            if (!$pedido)
                continue;

            // Validación del estado de producción
            // Si el pedido ya está preparado (productionStatus = 2), no permitir modificaciones
            if ($pedido->productionStatus == 2) {
                continue; // Saltar este pedido en el análisis ya que no se pueden hacer cambios
            }

            $articulo = trim((string) $row[$artIdx]);
            $cantidad = (float) ($row[$cantIdx] ?? 0);
            $unit = isset($row[$precioIdx]) ? round((float) $row[$precioIdx], 3) : 0.0;
            $sub = isset($row[$subIdx]) ? round((float) $row[$subIdx], 3) : round($cantidad * $unit, 3);

            $excelKey = strtoupper(trim($pedido->orderId)) . '|' . strtoupper(trim($articulo)) . '|' . number_format($cantidad, 3, '.', '') . '|' . number_format($unit, 3, '.', '') . '|' . number_format($sub, 3, '.', '');
            if (isset($seenExcelRows[$excelKey])) {
                $stats['duplicates_excel_count']++;
                $duplicates[] = [
                    'row_index' => $index + 1,
                    'pedido_id' => $pedido->orderId,
                    'articulo' => $articulo,
                    'cantidad' => $cantidad,
                    'unit_prize' => number_format($unit, 3, '.', ''),
                    'sub_total' => number_format($sub, 3, '.', ''),
                ];
                continue;
            }
            $seenExcelRows[$excelKey] = true;

            $existingArticle = DetailPedidos::where('pedidos_id', $pedido->id)
                ->whereRaw('UPPER(TRIM(articulo)) = UPPER(TRIM(?))', [$articulo])
                ->first();

            if (!$existingArticle) {
                $newArticles[] = [
                    'row_index' => $index + 1,
                    'pedido_id' => $pedido->orderId,
                    'articulo' => $articulo,
                    'data' => $this->formatArticleRowData([
                        $pedido->orderId,
                        $articulo,
                        $cantidad,
                        $unit,
                        $sub
                    ], $pedido, $unit, $sub)
                ];
                $stats['new_count']++;
            } else {
                $modifications = $this->compareArticleData($existingArticle, [2 => $cantidad, 3 => $unit, 4 => $sub], $unit, $sub);
                if (!empty($modifications)) {
                    $modifiedArticles[] = [
                        'row_index' => $index + 1,
                        'pedido_id' => $pedido->orderId,
                        'articulo' => $articulo,
                        'existing' => $this->formatExistingArticleData($existingArticle),
                        'new' => $this->formatArticleRowData([
                            $pedido->orderId,
                            $articulo,
                            $cantidad,
                            $unit,
                            $sub
                        ], $pedido, $unit, $sub),
                        'modifications' => $modifications
                    ];
                    $stats['modified_count']++;
                } else {
                    $unchanged[] = [
                        'row_index' => $index + 1,
                        'pedido_id' => $pedido->orderId,
                        'articulo' => $articulo,
                        'data' => $this->formatExistingArticleData($existingArticle),
                    ];
                    $stats['unchanged_count']++;
                }
            }
        }

        return [
            'stats' => $stats,
            'new' => $newArticles,
            'modified' => $modifiedArticles,
            'duplicates' => $duplicates,
            'unchanged' => $unchanged,
        ];
    }
    private function formatArticleRowData($row, $pedido, $precioUnitario = null, $subTotal = null)
    {
        // Handle both old structure (row[16], row[17], etc.) and new structure (row[0], row[1], etc.)
        if ($precioUnitario !== null && $subTotal !== null) {
            // New structure: [0]Numero, [1]Articulo, [2]Cantidad, [3]PrecioUnitario, [4]SubTotal
            return [
                'pedido_id' => $row[0],
                'pedido_cliente' => $pedido->customerName,
                'articulo' => $row[1],
                'cantidad' => $row[2],
                'unit_prize' => number_format($precioUnitario, 3, '.', ''),
                'sub_total' => number_format($subTotal, 3, '.', ''),
                'created_at' => now()->format('Y-m-d H:i:s'),
                'last_data_update' => now()->format('Y-m-d H:i:s')
            ];
        } else {
            // Old structure for compatibility
            return [
                'pedido_id' => $row[3],
                'pedido_cliente' => $pedido->customerName,
                'articulo' => $row[16],
                'cantidad' => $row[17],
                'unit_prize' => number_format(floatval($row[18]), 3, '.', ''),
                'sub_total' => number_format(floatval($row[19]), 3, '.', ''),
                'created_at' => $row[21] ? Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row[21]))->format('Y-m-d H:i:s') : now()->format('Y-m-d H:i:s'),
                'last_data_update' => now()->format('Y-m-d H:i:s')
            ];
        }
    }
    private function formatExistingArticleData($article)
    {
        return [
            'pedido_id' => $article->pedido->orderId,
            'pedido_cliente' => $article->pedido->customerName,
            'articulo' => $article->articulo,
            'cantidad' => $article->cantidad,
            'unit_prize' => $article->unit_prize,
            'sub_total' => $article->sub_total,
            'created_at' => $article->created_at->format('Y-m-d H:i:s'),
            'last_data_update' => $article->pedido->last_data_update ? $article->pedido->last_data_update->format('Y-m-d H:i:s') : 'Nunca actualizado'
        ];
    }
    private function compareArticleData($existingArticle, $row, $precioUnitario = null, $subTotal = null)
    {
        $modifications = [];

        // Handle both old structure (row[17], row[18], row[19]) and new structure (row[2], row[3], row[4])
        if ($precioUnitario !== null && $subTotal !== null) {
            // New structure: [0]Numero, [1]Articulo, [2]Cantidad, [3]PrecioUnitario, [4]SubTotal
            $newCantidad = floatval($row[2]);
            $newUnitPrize = round(floatval($precioUnitario), 3);
            $newSubTotal = round(floatval($subTotal), 3);
        } else {
            // Old structure for compatibility
            $newCantidad = floatval($row[17]);
            $newUnitPrize = round(floatval($row[18]), 3);
            $newSubTotal = round(floatval($row[19]), 3);
        }

        // Compare Cantidad - normalize both to float for comparison
        $existingCantidad = floatval($existingArticle->cantidad);
        if ($existingCantidad != $newCantidad) {
            $modifications[] = [
                'field' => 'cantidad',
                'label' => 'Cantidad',
                'old_value' => $existingArticle->cantidad,
                'new_value' => $newCantidad
            ];
        }
        // Compare Unit Prize - use epsilon for float comparison
        $existingUnitPrize = round(floatval($existingArticle->unit_prize), 3);
        if (abs($existingUnitPrize - $newUnitPrize) >= 0.001) {
            $modifications[] = [
                'field' => 'unit_prize',
                'label' => 'Precio Unitario',
                'old_value' => 'S/ ' . number_format($existingUnitPrize, 3, '.', ''),
                'new_value' => 'S/ ' . number_format($newUnitPrize, 3, '.', '')
            ];
        }
        // Compare Sub Total - use epsilon for float comparison
        $existingSubTotal = round(floatval($existingArticle->sub_total), 3);
        if (abs($existingSubTotal - $newSubTotal) >= 0.001) {
            $modifications[] = [
                'field' => 'sub_total',
                'label' => 'Sub Total',
                'old_value' => 'S/ ' . number_format($existingSubTotal, 3, '.', ''),
                'new_value' => 'S/ ' . number_format($newSubTotal, 3, '.', '')
            ];
        }

        return $modifications;
    }
    public function storeArticulos(Request $request)
    {
        Log::info('storeArticulos method called', ['request' => $request->all()]);

        // Validate the uploaded file
        $request->validate([
            'archivo' => 'required|mimes:xlsx,xls',
        ]);

        try {
            // Get the uploaded file
            $file = $request->file('archivo');

            // Store file temporarily for preview using Storage facade
            $fileName = 'temp_articulos_' . time() . '.' . $file->getClientOriginalExtension();
            $storedPath = Storage::putFileAs('temp', $file, $fileName);

            Log::info('File stored', ['fileName' => $fileName, 'storedPath' => $storedPath]);

            if ($storedPath) {
                // Verify file was stored correctly
                if (Storage::exists('temp/' . $fileName)) {
                    // Redirect to preview
                    Log::info('Redirecting to preview', ['route' => 'cargarpedidos.preview-articulos', 'filename' => $fileName]);
                    return redirect()->route('cargarpedidos.preview-articulos', ['filename' => $fileName]);
                } else {
                    return redirect()->back()->with('danger', 'Error: El archivo no se guardó correctamente.');
                }
            } else {
                return redirect()->back()->with('danger', 'Error al subir el archivo. Inténtalo nuevamente.');
            }
        } catch (\Exception $e) {
            Log::error('Error in storeArticulos', ['error' => $e->getMessage()]);
            return redirect()->back()->with('danger', 'Error al procesar el archivo: ' . $e->getMessage());
        }
    }

    public function cargarExcelArticulos(Request $request)
    {
        Log::info('cargarExcelArticulos (old method) called - redirecting to new flow');

        // Redirect to the new method for consistency
        return $this->storeArticulos($request);
    }

    private function normalizarYOrdenarPalabras(string $nombre): Collection
    {
        // Asegura que esté bien codificado a UTF-8
        if (!mb_check_encoding($nombre, 'UTF-8')) {
            $nombre = mb_convert_encoding($nombre, 'UTF-8', 'ISO-8859-1');
        }

        // Transliterar a ASCII, eliminando tildes, Ñ -> N, etc.
        $nombre = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $nombre);

        if ($nombre === false) {
            // Si falla la conversión, regresamos colección vacía o lanza excepción según tu lógica
            return collect();
        }

        $nombre = strtolower($nombre); // a minúsculas
        $nombre = preg_replace('/[^\p{L}\s]/u', '', $nombre); // elimina puntuación
        $nombre = preg_replace('/\s+/', ' ', $nombre); // espacios múltiples a uno

        return collect(explode(' ', trim($nombre)))
            ->filter()
            ->sort()
            ->values();
    }
    public function sincronizarDoctoresPedidos()
    {
        try {
            $resultados = $this->doctorSyncService->sincronizarDoctoresPedidos();

            $mensaje = "Sincronización completada: {$resultados['sincronizados']} pedidos sincronizados de {$resultados['procesados']} procesados.";

            if ($resultados['no_encontrados'] > 0) {
                $mensaje .= " {$resultados['no_encontrados']} doctores no encontrados.";
            }

            if ($resultados['errores'] > 0) {
                $mensaje .= " {$resultados['errores']} errores ocurridos.";
            }

            $tipo = 'success';
            if ($resultados['sincronizados'] === 0 && $resultados['procesados'] > 0) {
                $tipo = 'warning';
                $mensaje = 'No se pudo sincronizar ningún pedido. Verifique que los nombres de doctores coincidan.';
            } elseif ($resultados['procesados'] === 0) {
                $mensaje = 'No hay pedidos sin doctor asignado para sincronizar.';
                $tipo = 'info';
            }
            return response()->json([
                'success' => true,
                'message' => $mensaje,
                'type' => $tipo,
                'data' => $resultados
            ]);

        } catch (\Exception $e) {
            Log::error('Error en sincronización de doctores: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al sincronizar doctores: ' . $e->getMessage(),
                'type' => 'error'
            ], 500);
        }
    }

    public function searchDoctores(Request $request)
    {
        $search = $request->get('search', '');

        $doctores = Doctor::where('state', 1)
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($query) use ($search) {
                    $like = '%' . $search . '%';
                    $query->where('name_softlynn', 'LIKE', $like)
                        ->orWhere('name', 'LIKE', $like)
                        ->orWhere('first_lastname', 'LIKE', $like)
                        ->orWhere('second_lastname', 'LIKE', $like)
                        ->orWhereRaw("CONCAT(COALESCE(name,''),' ',COALESCE(first_lastname,''),' ',COALESCE(second_lastname,'')) LIKE ?", [$like]);
                });
            })
            // Prefer records with name_softlynn first in ordering, then by name
            ->orderByRaw("CASE WHEN name_softlynn IS NULL OR name_softlynn = '' THEN 1 ELSE 0 END")
            ->orderBy('name')
            ->limit(10)
            ->get(['id', 'name', 'first_lastname', 'second_lastname', 'name_softlynn']);

        return response()->json($doctores);
    }

    public function DownloadWord(Request $request)
    {
        $fecha = $request->fecha;
        $fecha_format = Carbon::parse($fecha)->format('d-m-Y');
        $turno = $request->turno;
        if ($turno == 'vacio') {
            $pedidos = Pedidos::where('deliveryDate', $fecha)->orderBy('nroOrder', 'asc')->get();
        } else {
            $pedidos = Pedidos::where('deliveryDate', $fecha)->where('turno', $turno)->orderBy('nroOrder', 'asc')->get();
        }
        $zonas = Zone::get();
        $phpWord = new \PhpOffice\PhpWord\PhpWord();
        $section = $phpWord->addSection();
        foreach ($zonas as $zona) {
            $arrayWord = [];
            $numero_ordenes = "";
            $manana = 0;
            $tarde = 0;
            array_push($arrayWord, $zona->name);
            foreach ($pedidos as $pedido) {
                if ($pedido->zone_id === $zona->id) {
                    if ($manana == 0 && $pedido->turno == 0) {
                        $manana = 1;
                        array_push($arrayWord, 'TURNO MAÑANA');
                    } else if ($tarde == 0 && $pedido->turno == 1) {
                        array_push($arrayWord, 'TURNO TARDE');
                        $tarde = 1;
                    }
                    $numero_ordenes = $numero_ordenes . $pedido->nroOrder . ", ";
                    array_push($arrayWord, $pedido->nroOrder . " PED " . $pedido->orderId);
                    array_push($arrayWord, $pedido->customerName . " - " . $pedido->customerNumber);
                    foreach ($pedido->detailpedidos as $orden) {
                        array_push($arrayWord, '• ' . $orden->articulo . ' - ' . $orden->cantidad . ' unid.');
                    }
                    array_push($arrayWord, $pedido->district);
                }
            }
            $arrayWord[0] = $arrayWord[0] . ": " . $numero_ordenes;
            $text = $section->addText('FECHA DE ENTREGA: ' . $fecha_format, array('name' => 'Arial', 'size' => 18, 'bold' => true));
            foreach ($arrayWord as $id => $text) {
                if ($id == 0) {
                    $text = $section->addText($text, array('name' => 'Arial', 'size' => 11, 'bold' => true));
                } elseif (strpos($text, ' PED ')) {
                    $text = $section->addText($text, array('name' => 'Arial', 'size' => 11, 'bold' => true));
                } else {
                    $text = $section->addText(
                        $text,
                        array('bold' => false),
                        array(
                            'space' => array('before' => 0, 'after' => 0)
                        )
                    );
                }
            }
            $section->addPageBreak();
        }



        $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        if (file_exists('docs\pedidos-' . $fecha . '.docx')) {
            unlink('docs\pedidos-' . $fecha . '.docx');
            $objWriter->save('docs\pedidos-' . $fecha . '.docx');

        } else {
            $objWriter->save('docs\pedidos-' . $fecha . '.docx');
        }
        return response()->download(public_path('docs\pedidos-'.$fecha.'.docx'));
    }
    public function actualizarTurno(Request $request, $id)
    {
        $this->pedidoImportService->actualizarTurno($request, $id);
        return back()->with('success', 'Turno modificado exitosamente');
    }
    public function uploadfile(Pedidos $pedido)
    {
        $images = explode(",", $pedido->voucher);
        $nro_operaciones = explode(",", $pedido->operationNumber);
        $recetas = explode(",", $pedido->receta);
        $array_voucher = [];
        foreach ($images as $key => $voucher) {
            array_push($array_voucher, ['nro_operacion' => $nro_operaciones[$key], 'voucher' => $voucher]);
        }
        return view('pedidos.counter.cargar_pedido.uploadfile', compact('pedido','array_voucher','recetas'));
    }
    public function actualizarPago(Request $request, $id){
        $this->pedidoImportService->actualizarPago($request, $id);
        return back()->with('success','Pedido modificado exitosamente');
    }
    public function cargarImagen(Request $request, $id)
    {

        $request->validate([
            'operationNumber' => 'required',
            'voucher' => 'required|image|mimes:jpeg,png,jpg,gif|max:3048',
        ]);
        // dd(request()->all());
        $pedidos = Pedidos::find($id);
        $imageName = $pedidos->orderId . '_' . time() . '.' . $request->voucher->extension();
        $request->voucher->move(public_path('images/voucher_pedidos'), $imageName);

        if ($pedidos->voucher) {
            $pedidos->voucher = $pedidos->voucher . ',images/voucher_pedidos/' . $imageName;
            $pedidos->operationNumber = $pedidos->operationNumber . ',' . $request->operationNumber;
        } else {
            $pedidos->voucher = 'images/voucher_pedidos/' . $imageName;
            $pedidos->operationNumber = $request->operationNumber;
        }
        $pedidos->save();
        return back()->with('success', 'Imagen cargada exitosamente');
    }
    public function cargarImagenReceta(Request $request, $id)
    {
        $request->validate([
            'receta' => 'required',
            'receta.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ], [
            'receta.required' => 'Debes seleccionar al menos una imagen.',
            'receta.*.image' => 'Cada archivo debe ser una imagen.',
            'receta.*.mimes' => 'Solo se permiten imágenes con formato jpeg, png, jpg, gif o webp.',
            'receta.*.max' => 'Cada imagen no puede superar los 2 MB.',
        ]);
        $pedidos = Pedidos::find($id);
        if ($request->hasFile('receta')) {
            $urls = '';
            $contador = 1;
            foreach ($request->file('receta') as $imagen) {
                $imageNameReceta = $pedidos->orderId . '_' . $contador . '_' . time() . '.' . $imagen->extension();
                ++$contador;
                $imagen->move(public_path('images/receta_pedidos'), $imageNameReceta);
                if ($urls) {
                    $urls = $urls . ',' . 'images/receta_pedidos/' . $imageNameReceta;
                } else {
                    $urls = 'images/receta_pedidos/' . $imageNameReceta;
                }
            }
        }
        $pedidos->receta = $urls;
        $pedidos->save();
        return back()->with('success', 'Receta cargada exitosamente');
    }
    public function destroy(string $id)
    {
        //
    }
    public function eliminarFotoVoucher(Request $request, $id)
    {
        $pedido = Pedidos::find($id);
        $array_voucher = explode(',', $pedido->voucher);
        $nro_operaciones = explode(",", $pedido->operationNumber);
        $urls = '';
        $text_nro_operacion = '';
        foreach ($array_voucher as $key => $voucher) {
            if ($voucher == $request->voucher) {
                if (file_exists($voucher)) {
                    unlink($voucher);
                }
                unset($nro_operaciones[$key]);
                unset($array_voucher[$key]);
            }
        }
        foreach ($array_voucher as $key => $voucher) {
            if (count($array_voucher) > 1) {
                if ($urls) {
                    $urls = $urls . ',' . $voucher;
                    $text_nro_operacion = $text_nro_operacion . ',' . $nro_operaciones[$key];
                } else {
                    $urls = $voucher;
                    $text_nro_operacion = $nro_operaciones[$key];
                }
            } else {
                $urls = $voucher;
                $text_nro_operacion = $nro_operaciones[$key];
            }
        }
        $pedido->voucher = $urls;
        $pedido->operationNumber = $text_nro_operacion;
        $pedido->save();

        return back()->with('success', 'imagen eliminada exitosamente');
    }
}