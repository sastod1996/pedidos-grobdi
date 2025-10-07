<?php

namespace App\Application\DTOs\Reportes;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

/**
 * DTO específico para reportes de doctores
 *
 * Esta clase contiene toda la estructura de datos necesaria para los reportes de doctores.
 * Incluye datos de tipos de doctores y datos individuales de cada doctor.
 *
 * Propiedades que debe contener:
 * - tipos: Datos agrupados por tipo de doctor (prescriptor, comprador, etc.)
 * - doctores: Datos individuales de doctores con especialidades y estadísticas
 */
class DoctoresDTO extends ReporteDTO
{
    // Propiedades específicas para reportes de doctores
    public array $tipos;             // Datos agrupados por tipo de doctor
    public array $doctores;          // Datos individuales de doctores
    public array $ventasPorMes;      // Ventas por tipo de doctor por mes
    public array $estadisticasTabla; // Estadísticas para la tabla resumen

    /**
     * Fabrica los datos ejecutando las consultas directamente (se reemplaza el uso del Repository)
     *
     * Reglas de filtrado solicitadas:
     * - El rango de fechas (fecha_inicio, fecha_fin) se aplica sobre la fecha de creación del doctor (doctor.created_at)
     * - Además, para las ventas y estadísticas con pedidos, el mismo rango se aplica también a pedidos.created_at
     * - Si solo llega fecha_inicio o solo fecha_fin se filtra con >= o <= respectivamente
     * - tipo_medico (opcional) restringe a un tipo específico
     */
    public static function buildFromFiltros(array $filtros = []): self
    {
        // Normalizar fechas (aceptamos 'YYYY-MM-DD HH:MM:SS' ya pre-procesado por el service)
        $fechaInicio = $filtros['fecha_inicio'] ?? null;
        $fechaFin    = $filtros['fecha_fin'] ?? null;
        $tipoMedico  = $filtros['tipo_medico'] ?? null;

        // Datos agrupados por tipo de doctor (si hay rango, contar doctores con pedidos en ese rango)
        if ($fechaInicio || $fechaFin) {
            $queryTipos = DB::table('doctor')
                ->join('pedidos', 'doctor.id', '=', 'pedidos.id_doctor')
                ->select('doctor.tipo_medico', DB::raw('COUNT(DISTINCT doctor.id) as total_doctores'))
                ->whereNotNull('doctor.tipo_medico');

            if ($tipoMedico) {
                $queryTipos->where('doctor.tipo_medico', $tipoMedico);
            }
            if ($fechaInicio) {
                $queryTipos->where('pedidos.created_at', '>=', $fechaInicio);
            }
            if ($fechaFin) {
                $queryTipos->where('pedidos.created_at', '<=', $fechaFin);
            }
            $resultadosTipos = $queryTipos->groupBy('doctor.tipo_medico')->get();
        } else {
            $queryTipos = DB::table('doctor')
                ->select('tipo_medico', DB::raw('COUNT(*) as total_doctores'))
                ->whereNotNull('tipo_medico');
            if ($tipoMedico) {
                $queryTipos->where('tipo_medico', $tipoMedico);
            }
            $resultadosTipos = $queryTipos->groupBy('tipo_medico')->get();
        }

        $tipos = [
            'labels'  => $resultadosTipos->pluck('tipo_medico')->toArray(),
            'datos'   => $resultadosTipos->pluck('total_doctores')->toArray(),
            'colores' => self::getColoresPorTipo()
        ];

        // Ventas por tipo y mes (solo filtrar por pedidos.created_at para incluir todos los tipos activos en rango de ventas)
        $queryVentas = DB::table('doctor')
            ->join('pedidos', 'doctor.id', '=', 'pedidos.id_doctor')
            ->select(
                'doctor.tipo_medico',
                DB::raw('MONTH(pedidos.created_at) as mes'),
                DB::raw('SUM(CAST(pedidos.prize as DECIMAL(10,2))) as total_ventas'),
                DB::raw('COUNT(pedidos.id) as total_pedidos')
            )
            ->whereNotNull('doctor.tipo_medico');

        if ($tipoMedico) {
            $queryVentas->where('doctor.tipo_medico', $tipoMedico);
        }
        if ($fechaInicio) {
            $queryVentas->where('pedidos.created_at', '>=', $fechaInicio);
        }
        if ($fechaFin) {
            $queryVentas->where('pedidos.created_at', '<=', $fechaFin);
        }

        $resultadosVentas = $queryVentas->groupBy('doctor.tipo_medico', 'mes')->get();
        // Pasar también lista de tipos detectados para asegurar que aparezcan aunque ventas = 0
        $ventasPorMes = self::procesarVentasPorMes($resultadosVentas, $tipos['labels']);

        // 3. Estadísticas por tipo (totales + promedios)
        $queryEstadisticas = DB::table('doctor')
            ->leftJoin('pedidos', function($join) use ($fechaInicio, $fechaFin) {
                $join->on('doctor.id', '=', 'pedidos.id_doctor');
                if ($fechaInicio) {
                    $join->where('pedidos.created_at', '>=', $fechaInicio);
                }
                if ($fechaFin) {
                    $join->where('pedidos.created_at', '<=', $fechaFin);
                }
            })
            ->select(
                'doctor.tipo_medico',
                DB::raw('COUNT(DISTINCT doctor.id) as total_doctores'),
                DB::raw('COALESCE(SUM(CAST(pedidos.prize as DECIMAL(10,2))), 0) as total_ventas'),
                DB::raw('COALESCE(AVG(CAST(pedidos.prize as DECIMAL(10,2))), 0) as promedio_ventas'),
                DB::raw('COUNT(pedidos.id) as total_pedidos')
            )
            ->whereNotNull('doctor.tipo_medico');

        if ($tipoMedico) {
            $queryEstadisticas->where('doctor.tipo_medico', $tipoMedico);
        }
        if ($fechaInicio) {
            $queryEstadisticas->where('doctor.created_at', '>=', $fechaInicio);
        }
        if ($fechaFin) {
            $queryEstadisticas->where('doctor.created_at', '<=', $fechaFin);
        }

        $resultadosEstadisticas = $queryEstadisticas->groupBy('doctor.tipo_medico')->get();
        $estadisticasTabla = self::procesarEstadisticas($resultadosEstadisticas);

        $instancia = new self(
            $filtros,
            $tipos,
            $ventasPorMes,
            $estadisticasTabla
        );
        // Guardar un criterio de interpretación de fechas
        $instancia->datos['criterio_fecha'] = ($fechaInicio || $fechaFin)
            ? 'Rango aplicado sobre pedidos.created_at (y ventas)'
            : 'Sin filtro de fechas';
        return $instancia;
    }

    /**
     * Constructor que inicializa datos de doctores
     *
     * @param array $filtros Filtros aplicados al reporte
     * @param array $tipos Datos de tipos de doctores
     * @param array $ventasPorMes Datos de ventas por mes
     * @param array $estadisticasTabla Estadísticas de tabla
     */
    public function __construct(
        array $filtros = [],
        array $tipos = [],
        array $ventasPorMes = [],
        array $estadisticasTabla = []
    ) {
        // Inicializar propiedades específicas primero
        $this->tipos = $tipos;
        $this->ventasPorMes = $ventasPorMes;
        $this->estadisticasTabla = $estadisticasTabla;
        $this->doctores = []; // Para futuras expansiones

        // Llamar al constructor padre con datos básicos
        parent::__construct(
            'Reporte de Tipos de Doctores',
            'doctores',
            $filtros,
            $this->getDatosIniciales(),
            $this->getEstadisticasIniciales($estadisticasTabla)
        );


    }

    /**
     * Procesa resultados de ventas mensuales para Chart.js
     */
    private static function procesarVentasPorMes(Collection $resultados, array $tiposLabels = []): array
    {
        $tiposEncontrados = $resultados->pluck('tipo_medico')->unique()->toArray();
        // Unir con los tipos provenientes de la lista principal para que todos aparezcan
        foreach ($tiposLabels as $lbl) {
            if ($lbl !== null && $lbl !== '' && !in_array($lbl, $tiposEncontrados, true)) {
                $tiposEncontrados[] = $lbl;
            }
        }
        $datosPorTipo = [];
        $datasets = [];

        foreach ($tiposEncontrados as $tipo) {
            $datosPorTipo[$tipo] = array_fill(0, 12, 0);
        }

        foreach ($resultados as $row) {
            $tipo = $row->tipo_medico;
            $mesIndex = ((int)$row->mes) - 1; // 0..11
            if (isset($datosPorTipo[$tipo]) && $mesIndex >= 0 && $mesIndex < 12) {
                $datosPorTipo[$tipo][$mesIndex] = (float)$row->total_ventas;
            }
        }

        $colores = self::getColoresPorTipo();
        foreach ($tiposEncontrados as $tipo) {
            $color = $colores[$tipo] ?? '#6c757d';
            $datasets[] = [
                'label' => $tipo,
                'data'  => $datosPorTipo[$tipo],
                'backgroundColor' => $color,
                'borderColor' => $color,
            ];
        }

        return [
            'meses' => self::getMesesLabels(),
            'datasets' => $datasets
        ];
    }

    /**
     * Procesa estadísticas para tabla
     */
    private static function procesarEstadisticas(Collection $resultados): array
    {
        $estadisticas = [];
        $totalDoctores = 0;
        $totalVentas = 0;

        foreach ($resultados as $r) {
            $totalDoctores += $r->total_doctores;
            $totalVentas   += $r->total_ventas;
        }

        foreach ($resultados as $r) {
            $porcentaje = $totalDoctores > 0 ? ($r->total_doctores / $totalDoctores * 100) : 0;
            $estadisticas[] = [
                'tipo'            => $r->tipo_medico,
                'total_doctores'  => (int)$r->total_doctores,
                'porcentaje'      => round($porcentaje, 1),
                'promedio_ventas' => round((float)$r->promedio_ventas, 2),
                'total_ventas'    => round((float)$r->total_ventas, 2)
            ];
        }

        $estadisticas[] = [
            'tipo'            => 'Total',
            'total_doctores'  => (int)$totalDoctores,
            'porcentaje'      => 100,
            'promedio_ventas' => $totalDoctores > 0 ? round($totalVentas / $totalDoctores, 2) : 0,
            'total_ventas'    => round((float)$totalVentas, 2)
        ];

        return $estadisticas;
    }

    private static function getColoresPorTipo(): array
    {
        return [
            'Prescriptor' => '#28a745',
            'En Proceso'  => '#ffc107',
            'Comprador'   => '#dc3545'
        ];
    }

    private static function getMesesLabels(): array
    {
        return [
            'Enero','Febrero','Marzo','Abril','Mayo','Junio',
            'Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'
        ];
    }

    /**
     * Obtiene datos iniciales del reporte (meses, etc.)
     *
     * @return array Datos básicos de configuración
     */
    private function getDatosIniciales(): array
    {
        return [
            'meses' => ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
                        'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
            'anio_actual' => date('Y'),
            'tipos_disponibles' => ['Comprador', 'Prescriptor', 'En Proceso']
        ];
    }

    /**
     * Obtiene estadísticas iniciales del reporte
     *
     * @param array $estadisticasTabla Estadísticas de la tabla
     * @return array Estadísticas calculadas
     */
    private function getEstadisticasIniciales(array $estadisticasTabla = []): array
    {
        if (empty($estadisticasTabla)) {
            return [
                'total_doctores' => 0,
                'tipos_doctores' => 3,
                'promedio_ventas' => 0,
                'mejor_tipo' => 'N/A'
            ];
        }

        $totalDoctores = 0;
        $totalVentas = 0;
        $mejorTipo = '';
        $maxVentas = 0;

        foreach ($estadisticasTabla as $estadistica) {
            if ($estadistica['tipo'] !== 'Total') {
                $totalDoctores += $estadistica['total_doctores'];
                $totalVentas += $estadistica['total_ventas'];
                
                if ($estadistica['total_ventas'] > $maxVentas) {
                    $maxVentas = $estadistica['total_ventas'];
                    $mejorTipo = $estadistica['tipo'];
                }
            }
        }

        return [
            'total_doctores' => $totalDoctores,
            'tipos_doctores' => count($estadisticasTabla) - 1, // -1 por el total
            'promedio_ventas' => $totalDoctores > 0 ? round($totalVentas / $totalDoctores, 2) : 0,
            'mejor_tipo' => $mejorTipo ?: 'N/A'
        ];
    }

    /**
     * Convierte el DTO a array incluyendo propiedades específicas
     *
     * @return array Array completo con todos los datos de doctores
     */
    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'tipos' => $this->tipos,
            'doctores' => $this->doctores,
            'ventasPorMes' => $this->ventasPorMes,
            'estadisticasTabla' => $this->estadisticasTabla,
            'filtros_aplicados' => [
                'fecha_inicio' => $this->filtros['fecha_inicio'] ?? null,
                'fecha_fin' => $this->filtros['fecha_fin'] ?? null,
                'tipo_medico' => $this->filtros['tipo_medico'] ?? null,
            ],
            'criterio_fecha' => $this->datos['criterio_fecha'] ?? null
        ]);
    }
}