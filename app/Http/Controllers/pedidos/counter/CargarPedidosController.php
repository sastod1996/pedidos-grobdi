<?php

namespace App\Http\Controllers\pedidos\counter;

use App\Http\Controllers\Controller;
use App\Http\Requests\counter\CargarPedidosUpdateRequest;
use App\Imports\DetailPedidosImport;
use App\Imports\DetailPedidosPreviewImport;
use App\Models\Doctor;
use App\Models\Pedidos;
use App\Models\Zone;
use App\Services\DoctorSyncService;
use App\Services\PedidoImportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
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
        if ($request->query('fecha')) {
            $request->validate(['fecha' => 'required|date']);
            $dia = Carbon::parse($request->fecha)->startOfDay();
        } else {
            $dia = now()->format('Y-m-d');
        }
        $filtro = $request->filtro ?: 'deliveryDate';
        $pedidos = Pedidos::whereDate($filtro, $dia);
        $turno = $request->query('turno');
        if ($turno !== null && $turno !== '') {
            $pedidos = $pedidos->where('turno', $turno);
        }

        $selectedZoneId = $request->query('zone_id');
        $zoneOptions = Zone::query()
            ->whereIn('id', [1, 2, 3, 4, 5])
            ->orderBy('name')
            ->get(['id', 'name']);

        $validZoneIds = $zoneOptions->pluck('id')->all();
        if ($selectedZoneId !== null && $selectedZoneId !== '' && in_array((int) $selectedZoneId, $validZoneIds, true)) {
            $pedidos = $pedidos->where('zone_id', $selectedZoneId);
        } else {
            $selectedZoneId = '';
        }

        $statusesFromPedidos = Pedidos::query()
            ->select('deliveryStatus')
            ->whereNotNull('deliveryStatus')
            ->distinct()
            ->pluck('deliveryStatus')
            ->map(function ($status) {
                return trim($status);
            })
            ->filter(function ($status) {
                return $status !== '';
            })
            ->unique(function ($status) {
                return strtolower($status);
            })
            ->sortBy(function ($status) {
                return strtolower($status);
            })
            ->values();

        $selectedDeliveryStatus = strtolower(trim($request->query('delivery_status', '')));
        if ($selectedDeliveryStatus !== '' && ! $statusesFromPedidos->contains(function ($status) use ($selectedDeliveryStatus) {
            return strtolower($status) === $selectedDeliveryStatus;
        })) {
            $selectedDeliveryStatus = '';
        }

        if ($selectedDeliveryStatus !== '') {
            $pedidos = $pedidos->whereRaw('LOWER(deliveryStatus) = ?', [$selectedDeliveryStatus]);
        }

        $deliveryStatuses = $statusesFromPedidos->mapWithKeys(function ($status) {
            $normalized = strtolower($status);

            return [$normalized => ucwords($normalized)];
        });
        $pedidos = $pedidos->orderBy('nroOrder')->get();

        return view('pedidos.counter.cargar_pedido.index', compact(
            'pedidos',
            'deliveryStatuses',
            'selectedDeliveryStatus',
            'zoneOptions',
            'selectedZoneId'
        ));
    }

    public function create()
    {
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

    public function show(Request $request, $pedido)
    {
        $pedido = Pedidos::with(['detailpedidos', 'zone', 'user'])->findOrFail($pedido);

        if ($request->ajax()) {
            $html = view('pedidos.counter.cargar_pedido.partials.order-details', compact('pedido'))->render();

            return response()->json([
                'success' => true,
                'html' => $html,
            ]);
        }

        return view('pedidos.counter.cargar_pedido.show', compact('pedido'));
    }

    public function edit($pedido)
    {
        $pedido = Pedidos::find($pedido);
        $zonas = Zone::all();
        $doctores = Doctor::where('state', 1)->orderBy('name')->get();

        return view('pedidos.counter.cargar_pedido.edit', compact('pedido', 'zonas', 'doctores'));
    }

    public function update(CargarPedidosUpdateRequest $request, $id)
    {
        $fecha = $this->pedidoImportService->updatePedido($request, $id);

        return redirect()->route('cargarpedidos.index', $fecha)
            ->with('success', 'Pedido modificado exitosamente');
    }

    // Other methods...

    public function previewArticulos(Request $request)
    {
        Log::info('previewArticulos method called', ['request' => $request->all()]);

        $fileName = $request->get('filename');

        Log::info('fileName from request', ['fileName' => $fileName]);

        if (! $fileName || ! Storage::exists('temp/'.$fileName)) {
            Log::error('File not found', ['fileName' => $fileName, 'exists' => Storage::exists('temp/'.$fileName)]);

            return redirect()->route('cargarpedidos.create')
                ->with('danger', 'Archivo no encontrado. Por favor, sube el archivo nuevamente.');
        }

        try {
            // Get full path to the file
            $filePath = Storage::path('temp/'.$fileName);
            Log::info('File path', ['filePath' => $filePath]);

            // Use the DetailPedidosPreviewImport class instead of the old method
            $previewImport = new DetailPedidosPreviewImport;
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
            if (Storage::exists('temp/'.$fileName)) {
                Storage::delete('temp/'.$fileName);
            }

            return redirect()->route('cargarpedidos.create')
                ->with('danger', 'Error al procesar el archivo: '.$e->getMessage());
        }
    }

    public function confirmArticulos(Request $request)
    {
        try {
            $fileName = $request->input('filename');
            if (! $fileName) {
                return redirect()->route('cargarpedidos.create')->with('danger', 'Archivo no especificado');
            }

            if (! Storage::exists('temp/'.$fileName)) {
                return redirect()->route('cargarpedidos.create')->with('danger', 'Archivo temporal no encontrado');
            }

            $filePath = Storage::path('temp/'.$fileName);

            // Importar directamente; el import gestiona si hay cambios o no

            // Import articles using the writer import to persist changes and return a string message
            $detailImport = new DetailPedidosImport;
            Excel::import($detailImport, $filePath);

            // Clean up temp file
            Storage::delete('temp/'.$fileName);

            return redirect()->route('cargarpedidos.create')->with($detailImport->key, $detailImport->data);
        } catch (\Exception $e) {
            // Clean up temp file
            if (isset($fileName)) {
                Storage::delete('temp/'.$fileName);
            }

            return redirect()->route('cargarpedidos.create')->with('danger', 'Error al importar artículos: '.$e->getMessage());
        }
    }

    public function cancelArticulos(Request $request)
    {
        try {
            $fileName = $request->input('filename');
            if ($fileName) {
                Storage::delete('temp/'.$fileName);
            }

            return redirect()->route('cargarpedidos.create')->with('info', 'Importación de artículos cancelada');
        } catch (\Exception $e) {
            return redirect()->route('cargarpedidos.create')->with('warning', 'Importación cancelada');
        }
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
            $fileName = 'temp_articulos_'.time().'.'.$file->getClientOriginalExtension();
            $storedPath = Storage::putFileAs('temp', $file, $fileName);

            Log::info('File stored', ['fileName' => $fileName, 'storedPath' => $storedPath]);

            if ($storedPath) {
                // Verify file was stored correctly
                if (Storage::exists('temp/'.$fileName)) {
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

            return redirect()->back()->with('danger', 'Error al procesar el archivo: '.$e->getMessage());
        }
    }

    public function cargarExcelArticulos(Request $request)
    {
        Log::info('cargarExcelArticulos (old method) called - redirecting to new flow');

        // Redirect to the new method for consistency
        return $this->storeArticulos($request);
    }

    public function sincronizarDoctoresPedidos(Request $request)
    {
        try {
            $start = $request->get('start');
            $end = $request->get('end');

            // Pasar las fechas tal cual al servicio; el servicio las parseará/validará
            $resultados = $this->doctorSyncService->sincronizarDoctoresPedidos($start, $end);

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
                'data' => $resultados,
            ]);

        } catch (\Exception $e) {
            Log::error('Error en sincronización de doctores: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al sincronizar doctores: '.$e->getMessage(),
                'type' => 'error',
            ], 500);
        }
    }

    public function searchDoctores(Request $request)
    {
        $search = $request->get('search', '');

        $doctores = Doctor::where('state', 1)
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($query) use ($search) {
                    $like = '%'.$search.'%';
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
        $phpWord = new \PhpOffice\PhpWord\PhpWord;
        $section = $phpWord->addSection();
        foreach ($zonas as $zona) {
            $arrayWord = [];
            $numero_ordenes = '';
            $manana = 0;
            $tarde = 0;
            array_push($arrayWord, $zona->name);
            foreach ($pedidos as $pedido) {
                if ($pedido->zone_id === $zona->id) {
                    if ($manana == 0 && $pedido->turno == 0) {
                        $manana = 1;
                        array_push($arrayWord, 'TURNO MAÑANA');
                    } elseif ($tarde == 0 && $pedido->turno == 1) {
                        array_push($arrayWord, 'TURNO TARDE');
                        $tarde = 1;
                    }
                    $numero_ordenes = $numero_ordenes.$pedido->nroOrder.', ';
                    array_push($arrayWord, $pedido->nroOrder.' PED '.$pedido->orderId);
                    array_push($arrayWord, $pedido->customerName.' - '.$pedido->customerNumber);
                    foreach ($pedido->detailpedidos as $orden) {
                        array_push($arrayWord, '• '.$orden->articulo.' - '.$orden->cantidad.' unid.');
                    }
                    array_push($arrayWord, $pedido->district);
                }
            }
            $arrayWord[0] = $arrayWord[0].': '.$numero_ordenes;
            $text = $section->addText('FECHA DE ENTREGA: '.$fecha_format, ['name' => 'Arial', 'size' => 18, 'bold' => true]);
            foreach ($arrayWord as $id => $text) {
                if ($id == 0) {
                    $text = $section->addText($text, ['name' => 'Arial', 'size' => 11, 'bold' => true]);
                } elseif (strpos($text, ' PED ')) {
                    $text = $section->addText($text, ['name' => 'Arial', 'size' => 11, 'bold' => true]);
                } else {
                    $text = $section->addText(
                        $text,
                        ['bold' => false],
                        [
                            'space' => ['before' => 0, 'after' => 0],
                        ]
                    );
                }
            }
            $section->addPageBreak();
        }

        $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        if (file_exists('docs\pedidos-'.$fecha.'.docx')) {
            unlink('docs\pedidos-'.$fecha.'.docx');
            $objWriter->save('docs\pedidos-'.$fecha.'.docx');

        } else {
            $objWriter->save('docs\pedidos-'.$fecha.'.docx');
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
        $images = explode(',', $pedido->voucher);
        $nro_operaciones = explode(',', $pedido->operationNumber);
        $recetas = explode(',', $pedido->receta);
        $array_voucher = [];
        foreach ($images as $key => $voucher) {
            array_push($array_voucher, ['nro_operacion' => $nro_operaciones[$key], 'voucher' => $voucher]);
        }

        return view('pedidos.counter.cargar_pedido.uploadfile', compact('pedido', 'array_voucher', 'recetas'));
    }

    public function actualizarPago(Request $request, $id)
    {
        $this->pedidoImportService->actualizarPago($request, $id);

        return back()->with('success', 'Pedido modificado exitosamente');
    }

    public function cargarImagen(Request $request, $id)
    {

        // Support multiple voucher files with corresponding operation numbers.
        $request->validate([
            'voucher' => 'required',
            'voucher.*' => 'image|mimes:jpeg,png,jpg,gif|max:3048',
            'operationNumber' => 'required',
        ]);

        $pedidos = Pedidos::find($id);
        if (! $pedidos) {
            return back()->with('danger', 'Pedido no encontrado');
        }

        $files = $request->file('voucher');
        $operationNumbers = $request->input('operationNumber');

        // Normalize to arrays
        if (! is_array($files)) {
            $files = [$files];
        }
        if (! is_array($operationNumbers)) {
            $operationNumbers = [$operationNumbers];
        }

        $newPaths = [];
        $newOpNums = [];

        foreach ($files as $idx => $file) {
            if (! $file) {
                continue;
            }
            try {
                $ext = $file->extension();
            } catch (\Exception $e) {
                $ext = $file->getClientOriginalExtension() ?? 'jpg';
            }
            $imageName = $pedidos->orderId.'_'.time().'_'.$idx.'.'.$ext;
            $file->move(public_path('images/voucher_pedidos'), $imageName);
            $newPaths[] = 'images/voucher_pedidos/'.$imageName;

            // Get corresponding operation number if exists
            $op = $operationNumbers[$idx] ?? ($operationNumbers[0] ?? '');
            $newOpNums[] = $op;
        }

        // Merge with existing values, respecting empty existing values
        if ($pedidos->voucher) {
            $existing = array_filter(explode(',', $pedidos->voucher), function ($v) {
                return trim($v) !== '';
            });
            $merged = array_merge($existing, $newPaths);
            $pedidos->voucher = implode(',', $merged);
        } else {
            $pedidos->voucher = implode(',', $newPaths);
        }

        if ($pedidos->operationNumber) {
            $existingOps = array_filter(explode(',', $pedidos->operationNumber), function ($v) {
                return trim($v) !== '';
            });
            $mergedOps = array_merge($existingOps, $newOpNums);
            $pedidos->operationNumber = implode(',', $mergedOps);
        } else {
            $pedidos->operationNumber = implode(',', $newOpNums);
        }

        $pedidos->save();

        return back()->with('success', 'Imagen(es) cargada(s) exitosamente');
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
        if (! $pedidos) {
            return back()->with('danger', 'Pedido no encontrado');
        }

        $existing = [];
        if ($pedidos->receta) {
            $existing = array_filter(explode(',', $pedidos->receta), function ($v) {
                return trim($v) !== '';
            });
        }

        $newPaths = [];
        $contador = 1;
        if ($request->hasFile('receta')) {
            foreach ($request->file('receta') as $imagen) {
                $ext = '';
                try {
                    $ext = $imagen->extension();
                } catch (\Exception $e) {
                    $ext = $imagen->getClientOriginalExtension() ?? 'jpg';
                }
                $imageNameReceta = $pedidos->orderId.'_'.$contador.'_'.time().'.'.$ext;
                $contador++;
                $imagen->move(public_path('images/receta_pedidos'), $imageNameReceta);
                $newPaths[] = 'images/receta_pedidos/'.$imageNameReceta;
            }
        }

        $merged = array_merge($existing, $newPaths);
        $pedidos->receta = implode(',', $merged);
        $pedidos->save();

        return back()->with('success', 'Receta(s) cargada(s) exitosamente');
    }

    public function eliminarFotoReceta(Request $request, $id)
    {
        $pedido = Pedidos::find($id);
        if (! $pedido) {
            return back()->with('danger', 'Pedido no encontrado');
        }

        $array_recetas = explode(',', $pedido->receta ?: '');
        $urls = '';
        $text_recetas = [];

        // Remove the requested receta
        foreach ($array_recetas as $key => $receta) {
            if ($receta == $request->receta) {
                if (file_exists($receta)) {
                    @unlink($receta);
                }
                unset($array_recetas[$key]);
            }
        }

        // Rebuild cadena
        $filtered = array_values(array_filter($array_recetas, function ($v) {
            return trim($v) !== '';
        }));
        $pedido->receta = implode(',', $filtered);
        $pedido->save();

        return back()->with('success', 'Imagen de receta eliminada exitosamente');
    }


    public function eliminarFotoVoucher(Request $request, $id)
    {
        $pedido = Pedidos::find($id);
        $array_voucher = explode(',', $pedido->voucher);
        $nro_operaciones = explode(',', $pedido->operationNumber);
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
                    $urls = $urls.','.$voucher;
                    $text_nro_operacion = $text_nro_operacion.','.$nro_operaciones[$key];
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
