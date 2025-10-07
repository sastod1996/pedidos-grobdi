<?php

namespace App\Http\Controllers\muestras;

use App\Exports\muestras\MuestrasExport;
use App\Http\Controllers\Controller;
use App\Models\TipoMuestra;
use App\Models\Muestras;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use App\Models\Clasificacion;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class MuestrasController extends Controller
{

    /* --- CRUD --- */

    /* C - CREATE */

    public function create()
    {
        $clasificaciones = Clasificacion::with('unidadMedida', 'presentaciones')->get();
        return view('muestras.form', ['muestra' => null, 'clasificaciones' => $clasificaciones]);
    }

    public function store(Request $request)
    {
        $this->authorize('muestras.store');


        $validated = $request->validate([
            'nombre_muestra' => 'required|string|max:255',
            'clasificacion_id' => 'required|exists:clasificaciones,id',
            'cantidad_de_muestra' => 'required|numeric|min:1|max:10000',
            'observacion' => 'nullable|string',
            'tipo_frasco' => 'required|in:frasco original,frasco muestra',
            'id_doctor' => 'required|exists:doctor,id',
            'clasificacion_presentacion_id' => 'nullable|exists:clasificacion_presentaciones,id',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048', // VALIDACIÓN DE IMAGEN
        ], [
            'cantidad_de_muestra.min' => 'La cantidad de muestra debe ser al menos 1.',
            'cantidad_de_muestra.max' => 'La cantidad de muestra no puede exceder 10,000.',
            'foto.image' => 'El archivo debe ser una imagen válida.',
            'foto.mimes' => 'La imagen debe ser de tipo jpg, jpeg, png o webp.',
        ]);

        // Manejar la subida de la imagen si existeq
        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $file = $request->file('foto');
            $timestamp = Carbon::now()->format('m-d_H-i');
            $filename = Str::slug($validated['nombre_muestra']) . "_$timestamp." . $file->getClientOriginalExtension();
            $relativePath = 'images/muestras_fotos';
            $fullPath = public_path($relativePath);
            if (!file_exists($fullPath)) {
                mkdir($fullPath, 0755, true);
            } //crea directorio si no existe
            $file->move($fullPath, $filename);
            $fotoPath = $relativePath . '/' . $filename;
        }

        $muestra = Muestras::create([
            'nombre_muestra' => $validated['nombre_muestra'],
            'clasificacion_id' => $validated['clasificacion_id'],
            'cantidad_de_muestra' => $validated['cantidad_de_muestra'],
            'observacion' => $validated['observacion'],
            'tipo_frasco' => $validated['tipo_frasco'],
            'id_doctor' => $validated['id_doctor'],
            'clasificacion_presentacion_id' => $validated['clasificacion_presentacion_id'] ?? null,
            'foto' => $fotoPath,
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('muestras.index')->with('success', 'Muestra registrada exitosamente.');
    }

    /* R - READ */

    public function index(Request $request)
    {
        $user = Auth::user();

        $query = Muestras::with(['clasificacion.unidadMedida', 'tipoMuestra', 'doctor', 'clasificacionPresentacion']);

        // Filtros por rol
        if ($user->hasRole('admin') || $user->hasRole('coordinador-lineas') ||$user->hasRole('supervisor')) {
            $tiposMuestra = TipoMuestra::get();
        } else if ($user->hasRole('visitador')) {
            $query->where('created_by', $user->id);
        } else if ($user->hasRole('jefe-comercial')) {
            $query->where('aprobado_coordinadora', true);
        } else if ($user->hasRole('laboratorio')) {
            $query->where([
                'aprobado_coordinadora' => true,
                'aprobado_jefe_comercial' => true,
                'aprobado_jefe_operaciones' => true
            ]);
        } else if ($user->hasRole('jefe-operaciones')) {
            $restrictedRange = $this->getLimitMuestrasShowed();

            if ($restrictedRange) {
                [$start, $end] = $restrictedRange;
                $query->where(function ($q) use ($start, $end) {
                    $q->where('created_at', '<', $start)
                        ->orWhere('created_at', '>=', $end);
                });
            }

            $query->where([
                'aprobado_coordinadora' => true,
                'aprobado_jefe_comercial' => true
            ]);
        } else {
            $query->where([
                'aprobado_coordinadora' => true,
                'aprobado_jefe_comercial' => true
            ]);

        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('nombre_muestra', 'like', "%{$search}%")
                    ->orWhereHas('doctor', function ($q2) use ($search) {
                        $q2->where(DB::raw("CONCAT_WS(' ', name, first_lastname, second_lastname)"), 'like', "%{$search}%");
                    });
            });
        }

        $filterBy = $request->filter_by_date;
        $dateSince = $request->date_since;
        $dateTo = $request->date_to;

        if ($filterBy && $dateSince && $dateTo) {

            $column = $request->filter_by_date === 'entrega' ? 'datetime_scheduled' : 'created_at';

            $query->whereDate($column, '>=', $request->date_since);
            $query->whereDate($column, '<=', $request->date_to);
        }

        if ($request->filled('date_to')) {
        }

        if ($request->filled('lab_state')) {
            $estado = $request->lab_state === 'Elaborado' ? true : false;
            $query->where('lab_state', $estado);
        }

        if ($request->filled('order_by')) {
            switch (strtolower($request->order_by)) {
                case 'fecha_entrega':
                    $query->orderByRaw('CASE WHEN datetime_scheduled IS NULL THEN 0 ELSE 1 END ASC')
                        ->orderBy('datetime_scheduled', 'desc');
                    break;
                default:
                    $query->orderBy('created_at', 'desc');
                    break;
            }
        } else {
            $query->orderBy('created_at', 'desc');
        }


        // Paginar solo una vez al final
        $muestras = $query->paginate(10)->appends($request->except('page'));

        // Enviar datos a la vista
        $data = ['muestras' => $muestras];

        if (isset($tiposMuestra)) {
            $data['tiposMuestra'] = $tiposMuestra;
        }

        return view('muestras.index', $data);
    }

    private function getLimitMuestrasShowed()
    {
        $now = Carbon::now();

        $startRestriction = $now->copy()->startOfWeek()->addDays(2)->setTime(14, 0, 0);
        $endRestriction = $now->copy()->startOfWeek()->addDays(4)->setTime(12, 0, 0);

        if ($now->between($startRestriction, $endRestriction)) {
            return [$startRestriction, $endRestriction];
        }

        return null;
    }

    // Mostrar detalles de una muestra por su ID
    public function show($id)
    {
        $muestra = Muestras::with(['clasificacion.unidadMedida', 'tipoMuestra', 'creator', 'doctor', 'clasificacionPresentacion'])->findOrFail($id);
        return response()->json([
            'success' => true,
            'data' => $muestra
        ]);
    }

    /* U - UPDATE */

    public function edit($id)
    {
        $muestra = Muestras::findOrFail($id);
        $clasificaciones = Clasificacion::with('unidadMedida', 'presentaciones')->get();
        return view('muestras.form', ['muestra' => $muestra, 'clasificaciones' => $clasificaciones]);
    }

    public function update(Request $request, $id)
    {
        $muestra = Muestras::findOrFail($id);

        $validated = $request->validate([
            'nombre_muestra' => 'required|string|max:255',
            'clasificacion_id' => 'required|exists:clasificaciones,id',
            'cantidad_de_muestra' => 'required|numeric|min:1|max:10000',
            'observacion' => 'nullable|string',
            'tipo_frasco' => 'required|in:frasco original,frasco muestra',
            'id_doctor' => 'required|exists:doctor,id',
            'clasificacion_presentacion_id' => 'nullable|exists:clasificacion_presentaciones,id',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        if (!$muestra->state) {
            return redirect()->route('muestras.index')->with('error', "No se puede realizar esta acción una vez inhabilitada la muestra.");
        }

        if ($muestra->aprobado_coordinadora) {
            return redirect()->route('muestras.index')->with('error', "No se puede editar vez aprobada la muestra.");
        }

        if ($validated['tipo_frasco'] == 'frasco muestra') {
            $validated['clasificacion_presentacion_id'] = null;
        }

        if ($request->hasFile('foto')) {
            $file = $request->file('foto');
            $nombreMuestra = Str::slug($validated['nombre_muestra'], '_');
            $fecha = now()->format('m-d_H-i');
            $extension = $file->getClientOriginalExtension();
            $fileName = "{$nombreMuestra}-{$fecha}.{$extension}";
            $destinationPath = public_path('images/muestras_fotos');
            if (!File::exists($destinationPath)) {
                File::makeDirectory($destinationPath, 0755, true);
            }
            if (isset($muestra->foto) && $muestra->foto) {
                $oldFilePath = public_path($muestra->foto);
                if (File::exists($oldFilePath)) {
                    File::delete($oldFilePath);
                }
            }
            $file->move($destinationPath, $fileName);
            $validated['foto'] = 'images/muestras_fotos/' . $fileName;
        }

        $muestra->update($validated);

        return redirect()->route('muestras.index')->with('success', 'Muestra actualizada exitosamente.');
    }

    /* D - DELETE */

    public function destroy($id)
    {
        $muestra = Muestras::findOrFail($id);
        $muestra->delete();
        return redirect()->route('muestras.index')->with('success', 'Muestra eliminada exitosamente.');
    }

    // Soft Delete
    public function disableMuestra(Request $request, $id)
    {
        $request->validate([
            'delete_reason' => 'required|string'
        ]);

        $user = Auth::user();
        $muestra = Muestras::findOrFail($id);

        if ($user->hasRole('coordinador-lineas')) {
            if ($muestra->aprobado_jefe_comercial) {
                return response()->json(['success' => false, 'message' => "Como Coordinador de Lineas no se puede deshabilitar la muestra una vez aprobada por el Jefe Comercial."]);
            }
        }

        if ($muestra->aprobado_jefe_operaciones) {
            return response()->json(['success' => false, 'message' => "No se puede deshabilitar la muestra una vez aprobada por el Jefe de Operaciones."]);
        }

        $muestra->state = false;
        $muestra->delete_reason = $request->input('delete_reason');
        $muestra->updated_by = $user->id;
        $muestra->save();

        return response()->json(['success' => true, 'message' => "Muestra con ID: {$muestra->id} deshabilitada exitosamente"]);
    }

    /* --- FUNCIONES ADICIONALES --- */

    // REVISAR
    public function getUnidadesPorClasificacion($clasificacionId)
    {
        $clasificacion = Clasificacion::with('unidadMedida')->findOrFail($clasificacionId);
        return response()->json([
            'unidad_medida' => $clasificacion->unidadMedida
        ]);
    }

    // Exportar Excel por ROL
    public function exportExcel()
    {
        $user = Auth::user();

        $allowedRolesToSeePrices = ['admin', 'jefe-comercial', 'contabilidad', 'jefe-operaciones'];

        return Excel::download(
            new MuestrasExport($user->id, $user->role->name, $allowedRolesToSeePrices),
            "muestras_$user->name.xlsx"
        );
    }

    /* --- CONTABILIDAD --- */

    public function updatePrice(Request $request, $id)
    {
        $request->validate([
            'price' => 'required|numeric|min:0',
        ]);

        $muestra = Muestras::find($id);

        if (!$muestra) {
            return response()->json(['success' => false, 'message' => 'Muestra no encontrada.']);
        }

        if (!$muestra->state) {
            return response()->json(['success' => false, 'message' => 'No se puede realizar esta acción una vez inhabilitada la muestra.']);
        }

        if (!$muestra->aprobado_coordinadora) {
            return response()->json(['success' => false, 'message' => 'La coordinadora debe aprobar la muestra para realizar esta operación.']);
        }

        if (!$muestra->aprobado_jefe_comercial) {
            return response()->json(['success' => false, 'message' => 'El jefe comercial debe aprobar la muestra para realizar esta operación.']);
        }

        if ($muestra->precio === $request->price) {
            return response()->json(['success' => false, 'message' => 'No se puede cambiar el precio al mismo valor original.']);
        }

        if ($muestra->aprobado_jefe_operaciones) {
            return response()->json(['success' => false, 'message' => 'No se puede cambiar de precio una vez aprobado por el Jefe de Operaciones.']);
        }

        $muestra->precio = $request->price;

        $muestra->save();

        return response()->json([
            'success' => true,
            'message' => 'Precio actualizado exitosamente.',
            'precio_total' => $muestra->precio * $muestra->cantidad_de_muestra,
        ]);
    }

    /* --- COORDINADOR-LINEAS --- */

    public function updateTipoMuestra(Request $request, $id)
    {
        $request->validate([
            'id_tipo_muestra' => 'required|exists:tipo_muestras,id',
        ]);

        $muestra = Muestras::find($id);

        if (!$muestra) {
            return response()->json(['success' => false, 'message' => 'Muestra no encontrada.']);
        }

        if (!$muestra->state) {
            return response()->json(['success' => false, 'message' => 'No se puede realizar esta acción una vez inhabilitada la muestra.']);
        }

        if ($muestra->aprobado_coordinadora || $muestra->aprobado_jefe_comercial || $muestra->aprobado_jefe_operaciones) {
            return response()->json(['success' => false, 'message' => 'No se puede cambiar el tipo de muestra una vez aprobada.']);
        }

        $muestra->id_tipo_muestra = $request->id_tipo_muestra;
        $muestra->save();

        return response()->json(['success' => true, 'message' => 'Tipo de muestra actualizado exitosamente.']);
    }

    public function updateDateTimeScheduled(Request $request, $id)
    {
        $muestra = Muestras::find($id);
        if (!$muestra->state) {
            return redirect()->to(url()->previous())->with('error', 'No se puede realizar esta acción una vez inhabilitada la muestra.');
        }

        if ($muestra->aprobado_coordinadora) {
            return redirect()->to(url()->previous())->with('error', 'No se puede cambiar la fecha de entrega una vez aprobada.');
        }

        $request->validate([
            'datetime_scheduled' => 'required|date|after_or_equal:' . Carbon::now()->format('Y-m-d\TH:i'),
        ]);

        $muestra->datetime_scheduled = $request->datetime_scheduled;
        $muestra->save();

        return redirect()->to(url()->previous())->with('success', 'Fecha de entrega actualizada correctamente.');
    }

    /* --- LABORATORIO --- */

    // Actualizar el comentario de laboratorio
    public function updateComentarioLab(Request $request, $id)
    {
        $muestra = Muestras::findOrFail($id);

        if (!$muestra->state) {
            return redirect()->route('muestras.index')->with('error', 'No se puede realizar esta acción una vez inhabilitada la muestra.');
        }

        $request->validate([
            'comentario_lab' => 'required|string',
        ]);

        $muestra->comentarios = $request->comentario_lab;
        $muestra->save();

        return redirect()->route('muestras.index')->with('success', 'Comentario de laboratorio guardado correctamente.');
    }

    // Marcar muestra como elaborada por Laboratorio
    public function markAsElaborated($id)
    {
        $muestra = Muestras::findOrFail($id);

        if (!$muestra->state) {
            return response()->json(['success' => false, 'message' => 'No se puede realizar esta acción una vez inhabilitada la muestra.']);
        }

        if (!$muestra->aprobado_jefe_operaciones) {
            return response()->json(['success' => false, 'message' => "No se puede marcar como ELABORADA una muestra que aún no ha sido aprobada para realizar."]);
        }

        if ($muestra->lab_state) {
            return response()->json(['success' => false, 'message' => "Esta muestra ya esta marcada como elaborada!"]);
        }

        $muestra->lab_state = true;
        $muestra->datetime_delivered = Carbon::now();
        $muestra->save();

        return response()->json(['success' => true, 'message' => "Muestra con ID: {$muestra->id} marcada como elaborada."]);
    }

    /* --- APROBACIONES - ADMINISTRACIÓN --- */

    public function aproveMuestraByCoordinadora(Request $request)
    {
        $muestra = Muestras::find($request->id);

        if (!$muestra) {
            return response()->json(['success' => false, 'message' => `Muestra con ID {$request->id} no encontrada.`]);
        }

        if (!$muestra->state) {
            return response()->json(['success' => false, 'message' => 'No se puede realizar esta acción una vez inhabilitada la muestra.']);
        }

        if ($muestra->id_tipo_muestra < 1 || $muestra->id_tipo_muestra === null) {
            return response()->json(['success' => false, 'message' => 'Debe asignar un tipo de muestra antes de aprobar esta muestra.']);
        }
        if (!$muestra->datetime_scheduled) {
            return response()->json(['success' => false, 'message' => 'Debe asignar una fecha y hora de entrega antes de aprobar esta muestra.']);
        }

        if ($muestra->aprobado_coordinadora) {
            return response()->json(['success' => false, 'message' => 'La muestra ya ha sido aprobada por la coordinadora.']);
        }

        $muestra->aprobado_coordinadora = 1;

        $muestra->timestamps = false;

        $muestra->save();

        $muestra->timestamps = true;

        return response()->json(['success' => true, 'message' => 'Aprobación realizada exitosamente.']);
    }

    public function aproveMuestraByJefeComercial(Request $request)
    {
        $muestra = Muestras::find($request->id);

        if (!$muestra) {
            return response()->json(['success' => false, 'message' => `Muestra con ID {$request->id} no encontrada.`]);
        }

        if (!$muestra->state) {
            return response()->json(['success' => false, 'message' => 'No se puede realizar esta acción una vez inhabilitada la muestra.']);
        }

        if (!$muestra->id_tipo_muestra || $muestra->id_tipo_muestra < 1) {
            return response()->json(['success' => false, 'message' => 'La coordinadora debe asignar un tipo de muestra.']);
        }

        if (!$muestra->aprobado_coordinadora) {
            return response()->json(['success' => false, 'message' => 'La coordinadora debe aprobar la muestra para realizar esta operación.']);
        }

        if ($muestra->aprobado_jefe_comercial) {
            return response()->json(['success' => false, 'message' => 'La muestra ya ha sido aprobada por el jefe comercial.']);
        }

        $muestra->aprobado_jefe_comercial = 1;

        $muestra->timestamps = false;

        $muestra->save();

        $muestra->timestamps = true;

        return response()->json(['success' => true, 'message' => 'Aprobación realizada exitosamente.']);
    }

    public function aproveMuestraByJefeOperaciones(Request $request)
    {
        $muestra = Muestras::find($request->id);

        if (!$muestra) {
            return response()->json(['success' => false, 'message' => `Muestra con ID {$request->id} no encontrada.`]);
        }

        if (!$muestra->state) {
            return response()->json(['success' => false, 'message' => 'No se puede realizar esta acción una vez inhabilitada la muestra.']);
        }

        if (!$muestra->id_tipo_muestra || $muestra->id_tipo_muestra < 1) {
            return response()->json(['success' => false, 'message' => 'La coordinadora debe asignar un tipo de muestra.']);
        }

        if (!$muestra->aprobado_coordinadora) {
            return response()->json(['success' => false, 'message' => 'La coordinadora debe aprobar la muestra para realizar esta operación.']);
        }

        if (!$muestra->aprobado_jefe_comercial) {
            return response()->json(['success' => false, 'message' => 'El jefe comercial debe aprobar la muestra para realizar esta operación.']);
        }

        if (is_null($muestra->precio) || $muestra->precio <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Contabilidad debe asignar un precio antes de esta aprobación.'
            ]);
        }

        if ($muestra->aprobado_jefe_operaciones) {
            return response()->json(['success' => false, 'message' => 'La muestra ya ha sido aprobada por el jefe de operaciones.']);
        }

        $muestra->aprobado_jefe_operaciones = 1;

        $muestra->timestamps = false;

        $muestra->save();

        $muestra->timestamps = true;

        return response()->json(['success' => true, 'message' => 'Aprobación realizada exitosamente.']);
    }
}
