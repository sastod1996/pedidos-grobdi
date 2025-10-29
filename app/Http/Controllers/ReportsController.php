<?php

namespace App\Http\Controllers;

use App\Models\Distrito;
use App\Models\EstadoVisita;
use App\Models\Zone;
use App\Domain\Reports\ReportsService;
use Illuminate\Http\Request;

class ReportsController extends Controller
{
    public function __construct(protected readonly ReportsService $reportsService)
    {
    }

    /* Ventas */
    public function ventasView()
    {
        $data = $this->reportsService->ventas()->createInitialReport();
        return view('reports.ventas.index', compact('data'));
    }
    public function getVentasGeneralReport(Request $request)
    {
        $filters = [
            'month' => $request->input('month'),
            'year' => $request->input('year'),
        ];
        return response()->json($this->reportsService->ventas()->getGeneralReport($filters)->toArray(), 200);
    }
    public function getVisitadorasReport(Request $request)
    {
        $filters = [
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
        ];
        return response()->json($this->reportsService->ventas()->getVisitadorasReport($filters)->toArray(), 200);
    }
    public function getProductosReport(Request $request)
    {
        $filters = [
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
        ];

        return response()->json($this->reportsService->ventas()->getProductosReport($filters)->toArray(), 200);
    }
    public function getProvinciasReport(Request $request)
    {
        $filters = [
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
        ];

        return response()->json($this->reportsService->ventas()->getProvinciasReport($filters)->toArray(), 200);
    }
    public function getPedidosDetailsByProvincia(Request $request)
    {
        $filters = [
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
            'departamento' => $request->input('departamento')
        ];

        if (!isset($filters['departamento'])) {
            return response()->json([
                'success' => false,
                'message' => 'El parámetro { departamento } es requerido'
            ], 400);
        }

        return response()->json($this->reportsService->ventas()->getDetailsPedidosByDepartamento($filters)->toArray(), 200);
    }

    /* Rutas */
    public function rutasView()
    {
        $zones = Zone::select('id', 'name')->get();
        $estadosVisitas = EstadoVisita::all();
        $data = $this->reportsService->rutas()->createInitialReport();

        return view('reports.rutas.index', compact('data', 'zones', 'estadosVisitas'));
    }
    public function getZonesReport(Request $request)
    {
        $filters = [
            'month' => $request->input('month'),
            'year' => $request->input('year'),
            'distritos' => $request->input('distritos')
        ];

        return response()->json($this->reportsService->rutas()->getZonesReport($filters)->toArray(), 200);
    }
    public function getDistritosByZone($zoneId)
    {
        if (!isset($zoneId)) {
            return response()->json([
                'success' => false,
                'message' => 'El parámetro { zoneId } es requerido'
            ], 400);
        }

        $distritosByZone = Distrito::whereHas('listas', function ($q) use ($zoneId) {
            $q->where('zone_id', $zoneId);
        })->get();

        return response()->json($distritosByZone);
    }

    /* Doctores */
    public function doctorsView()
    {
        $data = $this->reportsService->doctors()->createInitialReport();
        return view('reports.doctores.index', compact('data'));
    }
    public function getDoctorReport(Request $request)
    {
        $filters = [
            'id_doctor' => $request->input('id_doctor'),
            'month' => $request->input('month'),
            'year' => $request->input('year'),
        ];
        return response()->json($this->reportsService->doctors()->getDoctorReport($filters)->toArray(), 200);
    }
    public function getTipoDoctorReport(Request $request)
    {
        $filters = [
            'year' => $request->input('year'),
        ];
        return response()->json($this->reportsService->doctors()->getTipoDoctorReport($filters)->toArray(), 200);
    }

    /* Muestras */
    public function muestrasView()
    {
        $arrayTabs = [
            ['name' => 'general', 'icon' => 'fas fa-tablets'],
            ['name' => 'doctor', 'icon' => 'fas fa-user-md'],
        ];

        $data = $this->reportsService->muestras()->createInitialReport();

        return view('reports.muestras.index', compact('arrayTabs', 'data'));
    }
    public function getMuestrasGeneralReport(Request $request)
    {
        $filters = [
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
        ];
        return response()->json($this->reportsService->muestras()->getGeneralReport($filters)->toArray(), 200);
    }
    /* public function getMuestrasDoctorReport(Request $request)
    {
        $filters = [
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
        ];
        return response()->json($this->reportsService->muestras()->getDoctorReport($filters), 200);
    } */
}
