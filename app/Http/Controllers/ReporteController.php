<?php

namespace App\Http\Controllers;

use App\Application\Services\Reportes\VentasReporteService;
use App\Application\Services\Reportes\DoctoresReporteService;
use App\Application\Services\Reportes\VisitadorasReporteService;
use App\Models\VisitaDoctor;
use App\Models\Zone;
use App\Models\EstadoVisita;
use App\Models\Distrito;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Domain\Reports\ReportsService; // Para funcionalidades legacy de doctores

class ReporteController extends Controller
{
    public function __construct(
        protected VentasReporteService $ventasService,
        protected DoctoresReporteService $doctoresService,
        protected VisitadorasReporteService $visitadorasService,
        protected ReportsService $reportsService // inyección del antiguo servicio
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

            // Usar el nuevo método que procesa todo en el backend
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
                'tabla_html' => '<tr><td colspan="6" class="text-center py-5"><div class="text-muted"><i class="fas fa-exclamation-triangle fa-3x mb-3 text-warning"></i><h5>Error al cargar datos</h5><p>Ocurrió un error interno. Intente nuevamente.</p></div></td></tr>',
                'configuracion_grafico' => [],
                'datos_pareto' => ['labels' => [], 'porcentajes_acumulados' => [], 'punto_80' => 0],
                'indicador_rango' => '<small class="badge bg-danger text-white px-3 py-1"><i class="fas fa-exclamation-triangle me-1"></i>Error en la consulta</small>',
                'mensaje' => 'Error interno del servidor'
            ], 500);
        }
    }

    public function doctores(Request $request)
    {
        // Aceptar nuevos filtros de rango de fechas y mantener compatibilidad con antiguos
        $filtros = $request->only([
            'fecha_inicio_tipo_doctor',
            'fecha_fin_tipo_doctor',
            'anio_tipo_doctor', //! legacy
            'mes',              //! legacy
            'tipo_medico'
        ]);

        $data = $this->doctoresService->getData($filtros);
        // También necesitamos datos iniciales para la sección migrada de consumo por doctor ($doctorData)
        // Usa mes/año actuales si no hay selección; esto replica el comportamiento legacy.
        $currentYear = now()->year;
        $currentMonth = now()->month;
        try {
            $doctorData = $this->reportsService->doctor()->getDoctorReport($currentYear, $currentMonth);
        } catch (\Throwable $e) {
            // Fallback seguro si el servicio falla para no romper la vista
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
            'anio_tipo_doctor', //! legacy
            'mes',              //! legacy
            'tipo_medico'
        ]);
        $data = $this->doctoresService->getData($filtros);
        return response()->json($data->toArray());
    }

    public function visitadoras(Request $request)
    {
        // Replicamos la lógica del antiguo ReportsController@indexVisitadoras
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

        // Reutilizamos la vista existente de legacy si se requiere, pero según requerimiento
        // se integrará el contenido en la vista nueva doctor.blade (component). Para mantener compatibilidad
        // devolvemos la misma estructura esperada por esa vista partial.
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
}
