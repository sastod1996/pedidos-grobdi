<?php

namespace App\Http\Controllers;

use App\Models\Distrito;
use App\Models\EstadoVisita;
use App\Models\VisitaDoctor;
use App\Models\Zone;
use App\Domain\Reports\ReportsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportsController extends Controller
{

    protected ReportsService $reportsService;

    public function __construct(ReportsService $reportsService)
    {
        $this->reportsService = $reportsService;
    }

    public function motorizadosView(){
        return view('reports.motorizados.index');
    }

    public function indexVisitadoras(Request $request)
    {
        // DEPRECATED: Lógica movida a ReporteController::visitadoras
        $month = $request->input('month', now()->month);

        $initialValues = VisitaDoctor::select('estado_visita_id', DB::raw('COUNT(*) as total'))
            ->whereMonth('fecha', $month)
            ->whereYear('fecha', now()->year)
            ->groupBy('estado_visita_id')
            ->pluck('total', 'estado_visita_id');

        $zones = Zone::select('id', 'name')->get();

        $estadosVisitas = EstadoVisita::all();
        return view('reports.visitadoras.index', compact('estadosVisitas', 'initialValues', 'zones'));
    }

    public function indexVentas(Request $request)
    {
        return view('reports.ventas.index');
    }

    public function indexDoctores()
    {
        // DEPRECATED: lógica migrada a ReporteController::doctoresLegacy
        abort(410, 'Endpoint deprecated, use /reporte/doctores');
    }

    public function getDoctorReport(Request $request)
    {
        // DEPRECATED: lógica migrada a ReporteController::getDoctorReportLegacy
        return response()->json(['message' => 'Deprecated endpoint. Use nueva ruta en ReporteController'], 410);
    }

    public function getDistritosByZone($zoneId)
    {
        // DEPRECATED: usar ReporteController::getDistritosByZone
        $distritosByZone = Distrito::whereHas('listas', function ($q) use ($zoneId) {
            $q->where('zone_id', $zoneId);
        })->get();

        return response()->json($distritosByZone);
    }

    public function filterVisitasDoctor(Request $request)
    {
        // DEPRECATED: usar ReporteController::filterVisitasDoctor
        $month = $request->input('month', now()->month);
        $distritos = $request->input('distritos', []);

        if (is_string($distritos)) {
            $distritos = trim($distritos) === '[]' || trim($distritos) === '' ?
                [] : explode(',', trim($distritos, '[]'));
        }

        $distritos = array_filter(array_map('intval', $distritos));

        if (empty($distritos)) {
            $resumenVisitas = VisitaDoctor::select('estado_visita_id', DB::raw('COUNT(*) as total'))
                ->whereMonth('fecha', $month)
                ->whereYear('fecha', now()->year)
                ->groupBy('estado_visita_id')
                ->pluck('total', 'estado_visita_id');

            return response()->json([
                'Total' => $resumenVisitas
            ]);
        }

        $resultados = VisitaDoctor::query()
            ->select([
                'distritos.id as distrito_id',
                'distritos.name as distrito_name',
                'visita_doctor.estado_visita_id',
                DB::raw('COUNT(*) as total')
            ])
            ->join('doctor', 'doctor.id', '=', 'visita_doctor.doctor_id')
            ->join('distritos', 'distritos.id', '=', 'doctor.distrito_id')
            ->whereMonth('visita_doctor.fecha', $month)
            ->whereYear('visita_doctor.fecha', now()->year)
            ->whereIn('doctor.distrito_id', $distritos)
            ->groupBy('distritos.id', 'distritos.name', 'visita_doctor.estado_visita_id')
            ->get();

        $resumen = $resultados->groupBy('distrito_id')->map(function ($rows) {
            return [
                'distrito' => $rows->first()->distrito_name,
                'estados'  => $rows->pluck('total', 'estado_visita_id'),
            ];
        });

        $resumen['Total'] = $resultados->groupBy('estado_visita_id')->map->sum('total');

        return response()->json($resumen);
    }
}
