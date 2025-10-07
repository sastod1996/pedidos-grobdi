<?php

namespace App\Http\Controllers;

use App\Application\Services\Reportes\VentasReporteService;
use App\Application\Services\Reportes\GeoVentasService;
use App\Application\Services\Reportes\DoctoresReporteService;
use App\Application\Services\Reportes\VisitadorasReporteService;
use App\Models\VisitaDoctor;
use App\Models\Zone;
use App\Models\EstadoVisita;
use App\Models\Distrito;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Domain\Reports\ReportsService;

class ReporteController extends Controller
{
    public function __construct(
        protected VentasReporteService $ventasService,
        protected DoctoresReporteService $doctoresService,
        protected VisitadorasReporteService $visitadorasService,
        protected ReportsService $reportsService,
        protected GeoVentasService $geoVentasService
    ) {}

    public function ventas(Request $request)
    {
        $filtros = $request->only(['mes_general', 'anio_general']);
        $data = $this->ventasService->getData($filtros);

        return view('reporte.ventas', ['data' => $data->toArray()]);
    }

    public function apiVentasVisitadora(Request $request)
    {
        return $this->ventasService->getVisitadoraData($request['start_date'], $request['end_date']);
    }

    public function apiVentas(Request $request)
    {
        try {
            $filtros = $request->only([
                'mes_general',
                'anio_general',
                'fecha_inicio_producto',
                'fecha_fin_producto'
            ]);

            $data = $this->ventasService->getData($filtros);

            $datosCompletos = $data->getDatosProductosCompletos($filtros);

            return response()->json($datosCompletos);
        } catch (\Exception $e) {
            Log::error('Error en apiVentas: ' . $e->getMessage());

            return response()->json([
                'error' => true,
                'message' => 'Error al procesar los datos de ventas',
                'productos' => ['labels' => [], 'ventas' => [], 'unidades' => []],
                'estadisticas' => [
                    'total_productos' => 0,
                    'total_ventas' => 0,
                    'total_unidades' => 0,
                    'precio_promedio' => 0,
                    'total_ventas_formateado' => 'S/ 0.00',
                    'total_unidades_formateado' => '0',
                    'precio_promedio_formateado' => 'S/ 0.00'
                ],
                'configuracion_grafico' => [],
                'datos_pareto' => ['labels' => [], 'porcentajes_acumulados' => [], 'punto_80' => 0],
                'indicador' => null,
                'mensaje' => 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * API: Datos generales (tab General) - ingresos, visitas y series por año/mes
     */
    public function apiVentasGeneral(Request $request)
    {
        try {
            $filtros = $request->only(['mes_general', 'anio_general']);

            // Validación mínima: año requerido
            if (empty($filtros['anio_general'])) {
                return response()->json([
                    'error' => true,
                    'message' => 'El parámetro anio_general es requerido',
                    'general' => []
                ], 422);
            }

            $data = $this->ventasService->getData($filtros);

            // El DTO expone la propiedad pública $general con la forma esperada por el frontend
            $general = $data->general ?? ($data->toArray()['general'] ?? []);

            return response()->json([
                'general' => $general
            ]);
        } catch (\Throwable $e) {
            Log::error('Error en apiVentasGeneral: ' . $e->getMessage());
            return response()->json([
                'error' => true,
                'message' => 'Error al procesar los datos generales de ventas',
                'general' => []
            ], 500);
        }
    }

    /**
     * API: Ventas por Provincia/Departamento (normaliza campo district contra catálogos)
     */
    public function apiVentasProvincias(Request $request)
    {
        try {
            $filtros = $request->only([
                'fecha_inicio_provincia',
                'fecha_fin_provincia',
                'anio_general',
                'mes_general',
                'agrupacion'
            ]);

            Log::info('apiVentasProvincias filtros recibidos', $filtros);

            $geo = $this->geoVentasService->getGeoVentas($filtros);

            return response()->json($geo);
        } catch (\Throwable $e) {
            Log::error('Error en apiVentasProvincias: ' . $e->getMessage());
            return response()->json([
                'agrupacion' => $request->input('agrupacion', 'provincia'),
                'labels' => [],
                'ventas' => [],
                'porcentaje' => [],
                'pedidos' => [],
                'total_ventas' => 0,
                'total_pedidos' => 0,
                'message' => 'Error al cargar datos',
                'titulo' => 'Ventas por Ubigeo'
            ], 500);
        }
    }

    public function doctores(Request $request)
    {
        $filtros = $request->only([
            'fecha_inicio_tipo_doctor',
            'fecha_fin_tipo_doctor',
            'anio_tipo_doctor',
            'mes',
            'tipo_medico'
        ]);

        $data = $this->doctoresService->getData($filtros);
        $currentYear = now()->year;
        $currentMonth = now()->month;
        try {
            $doctorData = $this->reportsService->doctor()->getDoctorReport($currentYear, $currentMonth);
        } catch (\Throwable $e) {
            $doctorData = [
                'doctor' => 'N/A',
                'tipoMedico' => 'N/A',
                'amountSpentByDoctorGroupedByMonth' => array_fill(1, 12, 0),
                'amountSpentByDoctorGroupedByTipo' => [],
                'topMostConsumedProductsInTheMonthByDoctor' => [],
                'consumedProductsInTheMonthByDoctor' => []
            ];
        }

        return view('reporte.doctores', [
            'data' => $data->toArray(),
            'doctorData' => $doctorData
        ]);
    }

    public function apiDoctores(Request $request)
    {
        $filtros = $request->only([
            'fecha_inicio_tipo_doctor',
            'fecha_fin_tipo_doctor',
            'anio_tipo_doctor',
            'mes',
            'tipo_medico'
        ]);
        $data = $this->doctoresService->getData($filtros);
        return response()->json($data->toArray());
    }

    public function visitadoras(Request $request)
    {
        $month = $request->input('month', now()->month);

        $initialValues = VisitaDoctor::select('estado_visita_id', DB::raw('COUNT(*) as total'))
            ->whereMonth('fecha', $month)
            ->whereYear('fecha', now()->year)
            ->groupBy('estado_visita_id')
            ->pluck('total', 'estado_visita_id');

        $zones = Zone::select('id', 'name')->get();
        $estadosVisitas = EstadoVisita::all();

        return view('reporte.visitadoras', compact('initialValues', 'zones', 'estadosVisitas'));
    }

    /**
     * Devuelve distritos programados por zona (basado en listas de doctores)
     */
    public function getDistritosByZone($zoneId)
    {
        $distritosByZone = Distrito::whereHas('listas', function ($q) use ($zoneId) {
            $q->where('zone_id', $zoneId);
        })->get();

        return response()->json($distritosByZone);
    }

    /**
     * Filtro dinámico de visitas por distrito similar a ReportsController@filterVisitasDoctor
     */
    public function filterVisitasDoctor(Request $request)
    {
        $month = $request->input('month', now()->month);
        $distritos = $request->input('distritos', []);

        if (is_string($distritos)) {
            $distritos = trim($distritos) === '[]' || trim($distritos) === '' ? [] : explode(',', trim($distritos, '[]'));
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

    /**
     * NUEVO: Migración de ReportsController@indexDoctores
     * Muestra la vista legacy de reporte por doctor (consumo / productos) sin tabs.
     */
    public function doctoresLegacy(Request $request)
    {
        $year = now()->year;
        $month = now()->month;

        $doctorData = $this->reportsService->doctor()->getDoctorReport($year, $month);
        return view('reports.doctores.index', compact('doctorData'));
    }

    /**
     * NUEVO: Migración de ReportsController@getDoctorReport (endpoint AJAX)
     */
    public function getDoctorReportLegacy(Request $request)
    {
        $month = now()->month;
        $year = now()->year;

        $monthYear = $request->input('month_year');
        if ($monthYear) {
            $parts = explode('/', $monthYear);
            if (count($parts) === 2) {
                $month = (int) $parts[0];
                $year = (int) $parts[1];
            }
        }

        $doctorId = $request->input('id_doctor');

        $doctorData = $this->reportsService->doctor()->getDoctorReport($year, $month, $doctorId);

        return response()->json($doctorData);
    }

    public function filtrosDoctores(Request $request)
    {
        return response()->json([
            'anios' => $this->doctoresService->getAniosDisponibles(),
            'tipos_medico' => $this->doctoresService->getTiposMedicoDisponibles(),
            'meses' => [
                ['value' => 1, 'label' => 'Enero'],
                ['value' => 2, 'label' => 'Febrero'],
                ['value' => 3, 'label' => 'Marzo'],
                ['value' => 4, 'label' => 'Abril'],
                ['value' => 5, 'label' => 'Mayo'],
                ['value' => 6, 'label' => 'Junio'],
                ['value' => 7, 'label' => 'Julio'],
                ['value' => 8, 'label' => 'Agosto'],
                ['value' => 9, 'label' => 'Septiembre'],
                ['value' => 10, 'label' => 'Octubre'],
                ['value' => 11, 'label' => 'Noviembre'],
                ['value' => 12, 'label' => 'Diciembre'],
            ]
        ]);
    }

    /**
     * API: Obtiene pedidos detallados por departamento
     */
    public function apiPedidosPorDepartamento(Request $request)
    {
        try {
            $departamento = $request->input('departamento');
            $filtros = $request->only([
                'fecha_inicio_provincia',
                'fecha_fin_provincia',
                'anio_general',
                'mes_general',
                'agrupacion'
            ]);

            if (!$departamento) {
                return response()->json([
                    'error' => true,
                    'message' => 'El parámetro departamento es requerido'
                ], 400);
            }

            Log::info('apiPedidosPorDepartamento', [
                'departamento' => $departamento,
                'filtros' => $filtros
            ]);

            $data = $this->geoVentasService->getPedidosDetallados($departamento, $filtros);

            return response()->json($data);
        } catch (\Throwable $e) {
            Log::error('Error en apiPedidosPorDepartamento: ' . $e->getMessage());
            return response()->json([
                'error' => true,
                'message' => 'Error al obtener los pedidos detallados',
                'departamento' => $request->input('departamento', ''),
                'total_pedidos' => 0,
                'total_ventas' => 0,
                'pedidos' => []
            ], 500);
        }
    }
}
