<?php

namespace App\Http\Controllers\muestras;

use App\Application\Services\Muestras\MuestrasService;
use App\Exports\muestras\MuestrasExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\muestras\DisableMuestraRequest;
use App\Http\Requests\muestras\FilterMuestrasRequest;
use App\Http\Requests\muestras\StoreOrUpdateMuestraRequest;
use App\Models\TipoMuestra;
use App\Models\Muestras;
use Illuminate\Http\Request;
use App\Models\Clasificacion;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class MuestrasController extends Controller
{
    public function __construct(private readonly MuestrasService $service)
    {
    }

    /* --- CRUD --- */

    /* C - CREATE */

    public function create()
    {
        $clasificaciones = Clasificacion::with('unidadMedida', 'presentaciones')->get();
        return view('muestras.form', ['muestra' => null, 'clasificaciones' => $clasificaciones]);
    }

    public function store(StoreOrUpdateMuestraRequest $request)
    {
        $this->authorize('muestras.store');

        $this->service->create($request->validated(), $request->user()->id, $request->file('foto'));

        return redirect()->route('muestras.index')->with('success', 'Muestra registrada correctamente.');
    }

    /* R - READ */

    public function index(FilterMuestrasRequest $request)
    {
        $user = $request->user();
        $filters = $request->getFilters();

        $data = $this->service->getFilteredMuestras($filters, $user);

        return view('muestras.index', $data);
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
    public function update(StoreOrUpdateMuestraRequest $request, Muestras $muestra)
    {
        try {
            $this->service->update($muestra, $request->validated());
            return redirect()->route('muestras.index')->with('success', 'Muestra actualizada correctamente.');
        } catch (\LogicException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /* D - DELETE */

    public function destroy($id)
    {
        $muestra = Muestras::findOrFail($id);
        $muestra->delete();
        return redirect()->route('muestras.index')->with('success', 'Muestra eliminada correctamente.');
    }

    // Soft Delete
    public function disableMuestra(DisableMuestraRequest $request, Muestras $muestra)
    {
        try {
            $this->service->disable($muestra, $request->validated('delete_reason'), $request->user()->id);
            return response()->json(['success' => true, 'message' => "Muestra con ID: {$muestra->id} deshabilitada correctamente"]);
        } catch (\LogicException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
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

    public function updatePrice(Request $request, Muestras $muestra)
    {
        $this->authorize('muestras.updatePrice');
        $validated = $request->validate([
            'price' => 'required|numeric|min:0'
        ]);
        try {
            $result = $this->service->updatePrice($muestra, $validated['price']);
            return response()->json([
                'success' => true,
                'message' => 'Precio actualizado correctamente.',
                'precio_total' => $result->precio * $result->cantidad_de_muestra,
            ]);
        } catch (\LogicException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /* --- COORDINADOR-LINEAS --- */

    public function updateTipoMuestra(Request $request, Muestras $muestra)
    {
        $this->authorize('muestras.updateTipoMuestra');
        $validated = $request->validate([
            'id_tipo_muestra' => 'required|exists:tipo_muestras,id'
        ]);
        try {
            $this->service->updateTipoMuestra($muestra, $validated['id_tipo_muestra']);
            return response()->json(['success' => true, 'message' => 'Tipo de muestra asignado correctamente.']);
        } catch (\LogicException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function updateDateTimeScheduled(Request $request, Muestras $muestra)
    {
        $this->authorize('muestras.updateDateTimeScheduled');
        $validated = $request->validate([
            'datetime_scheduled' => 'required|date|after_or_equal:' . Carbon::now()->format('Y-m-d\TH:i')
        ]);
        try {
            $this->service->updateDateTimeScheduled($muestra, $validated['datetime_scheduled']);
            return redirect()->to(url()->previous())->with('success', 'Fecha de entrega actualizada correctamente.');
        } catch (\LogicException $e) {
            return redirect()->to(url()->previous())->with('error', $e->getMessage());
        }
    }

    /* --- LABORATORIO --- */
    // Actualizar el comentario de laboratorio
    public function updateComentarioLab(Request $request, Muestras $muestra)
    {
        $this->authorize('muestras.updateComentarioLab');
        $validated = $request->validate([
            'comentario_lab' => 'required|string'
        ]);
        try {
            $this->service->updateComentarioLab($muestra, $validated['comentario_lab']);
            return redirect()->route('muestras.index')->with('success', 'Comentario de laboratorio actualizado correctamente.');
        } catch (\LogicException $e) {
            return redirect()->route('muestras.index')->with('error', $e->getMessage());
        }
    }

    // Marcar muestra como elaborada por Laboratorio
    public function markAsElaborated(Muestras $muestra)
    {
        $this->authorize('muestras.markAsElaborated');
        try {
            $this->service->markAsElaborated($muestra);
            return response()->json(['success' => true, 'message' => "Muestra con ID: {$muestra->id} marcada como elaborada."]);
        } catch (\LogicException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /* --- APROBACIONES - ADMINISTRACIÓN --- */
    public function aproveMuestraByCoordinadora(Muestras $muestra)
    {
        $this->authorize('muestras.aproveCoordinadora');
        try {
            $this->service->aproveByCoordinadora($muestra);
            return response()->json(['success' => true, 'message' => 'Aprobación realizada correctamente.']);
        } catch (\LogicException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function aproveMuestraByJefeComercial(Muestras $muestra)
    {
        $this->authorize('muestras.aproveJefeComercial');
        try {
            $this->service->aproveByJefeComercial($muestra);
            return response()->json(['success' => true, 'message' => 'Aprobación realizada correctamente.']);
        } catch (\LogicException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function aproveMuestraByJefeOperaciones(Muestras $muestra)
    {
        $this->authorize('muestras.aproveJefeOperaciones');
        try {
            $this->service->aproveByJefeOperaciones($muestra);
            return response()->json(['success' => true, 'message' => 'Aprobación realizada correctamente.']);
        } catch (\LogicException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
