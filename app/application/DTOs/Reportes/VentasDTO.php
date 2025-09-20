<?php

namespace App\Application\DTOs\Reportes;

use App\Models\Pedidos;
use Illuminate\Support\Facades\DB;

/**
 * DTO específico para reportes de ventas
 *
 * Esta clase contiene toda la estructura de datos necesaria para los reportes de ventas.
 * Incluye datos de visitadoras, productos, provincias y estadísticas generales.
 *
 * Propiedades que debe contener:
 * - visitadoras: Datos por visitadora (nombres, ventas, visitas)
 * - productos: Datos por producto (nombres, ventas, unidades)
 * - provincias: Datos por provincia (nombres, ventas, porcentaje)
 * - general: Datos generales del reporte (tendencias, metas vs realizado)
 */
class VentasDTO extends ReporteDTO
{
    // Propiedades específicas para reportes de ventas
    public array $visitadoras;  // Datos agrupados por visitadora
    public array $productos;    // Datos agrupados por producto
    public array $provincias;   // Datos agrupados por provincia
    public array $general;      // Datos generales del reporte

    /**
     * Constructor que inicializa datos de ventas
     *
     * @param array $filtros Filtros aplicados al reporte
     */
    public function __construct(array $filtros = [])
    {
        // Llamar al constructor padre con datos básicos
        parent::__construct(
            'Reporte de Ventas',
            'ventas',
            $filtros,
            [], // datos se inicializan después
            []  // estadísticas se calculan después
        );

        // Inicializar propiedades específicas
        $this->visitadoras = $this->getDatosVisitadoras($filtros);
        $this->productos = $this->getDatosProductos($filtros);
        $this->provincias = $this->getDatosProvincias($filtros);
        $this->general = $this->getDatosGeneral($filtros);
    }

    /**
     * Obtiene datos agrupados por visitadora desde pedidos
     *
     * @param array $filtros
     * @return array Datos de visitadoras con ventas y visitas
     */
    private function getDatosVisitadoras(array $filtros = []): array
    {
        // Consulta real a la tabla pedidos con join a users
        $query = Pedidos::selectRaw('users.name as visitadora, SUM(pedidos.prize) as ventas, COUNT(pedidos.id) as visitas')
            ->join('users', 'pedidos.visitadora_id', '=', 'users.id')
            ->groupBy('users.id', 'users.name')
            ->orderByRaw('SUM(pedidos.prize) DESC');

        // Aplicar filtros si existen
        if (isset($filtros['anio_general'])) {
            $query->whereYear('pedidos.created_at', $filtros['anio_general']);
        }
        if (isset($filtros['mes_general'])) {
            $query->whereMonth('pedidos.created_at', $filtros['mes_general']);
        }

        $resultados = $query->get();

        return [
            'labels' => $resultados->pluck('visitadora')->toArray(),
            'ventas' => $resultados->pluck('ventas')->map(function ($venta) {
                return (float) $venta;
            })->toArray(),
            'visitas' => $resultados->pluck('visitas')->map(function ($visita) {
                return (int) $visita;
            })->toArray(),
            'visitadoraData' => $this->getVentasPerVisitadoraData($filtros),
        ];
    }

    public function getVentasPerVisitadoraData(array $filtros = [])
    {
        $query = DB::table('pedidos as p')
            ->join('users as u', 'u.id', '=', 'p.visitadora_id')
            ->select(
                'u.id as visitadora_id',
                'u.name as visitadora',
                DB::raw('SUM(p.prize) as total_monto'),
                DB::raw('COUNT(p.id) as total_pedidos')
            )
            ->where('u.role_id', 6);

        if (isset($filtros['start_date']) && isset($filtros['end_date'])) {
            $query->whereBetween('p.created_at', [$filtros['start_date'], $filtros['end_date']]);
        }

        $res = $query->groupBy('u.id', 'u.name')->get();

        $totalPedidos = $res->sum('total_pedidos');

        return $res->map(function ($item) use ($totalPedidos) {
            return [
                'visitadora' => $item->visitadora,
                'total_monto' => (float) $item->total_monto,
                'total_pedidos' => (int) $item->total_pedidos,
                'porcentaje_pedidos' => $totalPedidos > 0 ? round(($item->total_pedidos / $totalPedidos) * 100, 1) : 0,
            ];
        })->toArray();
    }



    /**
     * Obtiene datos agrupados por producto desde detail_pedidos
     *
     * @param array $filtros
     * @return array Datos de productos con ventas y unidades
     */
    private function getDatosProductos(array $filtros = []): array
    {
        // Consulta real a detail_pedidos con join a pedidos - TODOS los productos
        $query = DB::table('detail_pedidos')
            ->selectRaw('detail_pedidos.articulo as producto, SUM(detail_pedidos.sub_total) as ventas, SUM(detail_pedidos.cantidad) as unidades')
            ->join('pedidos', 'detail_pedidos.pedidos_id', '=', 'pedidos.id')
            ->whereNotNull('detail_pedidos.articulo')
            ->where('detail_pedidos.articulo', '!=', '')
            ->whereRaw('LOWER(detail_pedidos.articulo) NOT LIKE ?', ['%delivery%'])
            ->whereRaw('LOWER(detail_pedidos.articulo) NOT LIKE ?', ['bolsa%'])
            ->groupBy('detail_pedidos.articulo')
            ->orderByRaw('SUM(detail_pedidos.sub_total) DESC');

        // Aplicar filtros si existen
        if (isset($filtros['anio_general'])) {
            $query->whereYear('pedidos.created_at', $filtros['anio_general']);
        }
        if (isset($filtros['mes_general'])) {
            $query->whereMonth('pedidos.created_at', $filtros['mes_general']);
        }

        // Filtros específicos para productos por fechas
        if (isset($filtros['fecha_inicio_producto'])) {
            $query->whereDate('pedidos.created_at', '>=', $filtros['fecha_inicio_producto']);
        }
        if (isset($filtros['fecha_fin_producto'])) {
            $query->whereDate('pedidos.created_at', '<=', $filtros['fecha_fin_producto']);
        }

        // Filtro por defecto: si NO se especificó año, mes ni rangos de fecha para productos,
        // limitar desde el primer día del mes actual hasta hoy.
        if (
            !isset($filtros['anio_general']) && !isset($filtros['mes_general'])
            && !isset($filtros['fecha_inicio_producto']) && !isset($filtros['fecha_fin_producto'])
        ) {
            $primerDiaMes = date('Y-m-01');
            $hoy = date('Y-m-d');
            $query->whereDate('pedidos.created_at', '>=', $primerDiaMes)
                ->whereDate('pedidos.created_at', '<=', $hoy);
        }

        $resultados = $query->get();

        return [
            'labels' => $resultados->pluck('producto')->toArray(),
            'ventas' => $resultados->pluck('ventas')->map(function ($venta) {
                return (float) $venta;
            })->toArray(),
            'unidades' => $resultados->pluck('unidades')->map(function ($unidad) {
                return (int) $unidad;
            })->toArray()
        ];
    }

    /**
     * Obtiene datos agrupados por provincia desde pedidos
     *
     * @param array $filtros
     * @return array Datos de provincias con ventas y porcentajes
     */
    private function getDatosProvincias(array $filtros = []): array
    {
        // Consulta real a la tabla pedidos agrupada por district
        $query = Pedidos::selectRaw('district as provincia, SUM(prize) as ventas, COUNT(*) as pedidos')
            ->groupBy('district')
            ->orderByRaw('SUM(prize) DESC')
            ->limit(10); // Top 10 provincias/distritos

        // Aplicar filtros si existen
        if (isset($filtros['anio_general'])) {
            $query->whereYear('created_at', $filtros['anio_general']);
        }
        if (isset($filtros['mes_general'])) {
            $query->whereMonth('created_at', $filtros['mes_general']);
        }

        $resultados = $query->get();

        $totalVentas = $resultados->sum('ventas');
        $porcentajes = $resultados->map(function ($item) use ($totalVentas) {
            return $totalVentas > 0 ? round(($item->ventas / $totalVentas) * 100, 1) : 0;
        })->toArray();

        return [
            'labels' => $resultados->pluck('provincia')->toArray(),
            'ventas' => $resultados->pluck('ventas')->map(function ($venta) {
                return (float) $venta;
            })->toArray(),
            'porcentaje' => $porcentajes
        ];
    }

    /**
     * Obtiene datos generales del reporte desde pedidos
     *
     * @param array $filtros
     * @return array Datos de tendencias y estadísticas generales
     */
    private function getDatosGeneral(array $filtros = []): array
    {
        $anio = $filtros['anio_general'] ?? date('Y');
        $mes = $filtros['mes_general'] ?? null;

        if ($mes) {
            // Si hay mes específico, mostrar datos diarios del mes
            return $this->getDatosMesEspecifico($anio, $mes);
        } else {
            // Si solo hay año, mostrar datos mensuales del año
            return $this->getDatosAnioCompleto($anio);
        }
    }

    /**
     * Obtiene datos para un mes específico desde pedidos
     *
     * @param int $anio
     * @param int $mes
     * @return array
     */
    private function getDatosMesEspecifico(int $anio, int $mes): array
    {
        // Consulta a la tabla de pedidos de la base de datos 
        $ventasPorDia = Pedidos::selectRaw('DAY(created_at) as dia, SUM(prize) as ventas, COUNT(*) as visitas')
            ->whereYear('created_at', $anio)
            ->whereMonth('created_at', $mes)
            ->groupByRaw('DAY(created_at)')
            ->orderByRaw('DAY(created_at)')
            ->get()
            ->keyBy('dia');

        $dias = range(1, cal_days_in_month(CAL_GREGORIAN, $mes, $anio));
        $ventas = [];
        $visitas = [];

        foreach ($dias as $dia) {
            $data = $ventasPorDia->get($dia);
            $ventas[] = $data ? (float) $data->ventas : 0;
            $visitas[] = $data ? (int) $data->visitas : 0;
        }

        return [
            'tipo' => 'diario',
            'periodo' => "Mes $mes del $anio",
            'labels' => array_map(function ($dia) {
                return str_pad($dia, 2, '0', STR_PAD_LEFT);
            }, $dias),
            'ventas' => $ventas,
            'visitas' => $visitas,
            'total_ventas' => array_sum($ventas),
            'total_visitas' => array_sum($visitas),
            'promedio_venta' => count($dias) > 0 ? round(array_sum($ventas) / count($dias), 2) : 0
        ];
    }

    /**
     * Obtiene datos para un año completo (gráfico de barras por mes) desde pedidos
     *
     * @param int $anio
     * @return array
     */
    private function getDatosAnioCompleto(int $anio): array
    {
        // Consulta real a la tabla pedidos
        $ventasPorMes = Pedidos::selectRaw('MONTH(created_at) as mes, SUM(prize) as ventas, COUNT(*) as visitas')
            ->whereYear('created_at', $anio)
            ->groupByRaw('MONTH(created_at)')
            ->orderByRaw('MONTH(created_at)')
            ->get()
            ->keyBy('mes');

        $meses = [
            'Enero',
            'Febrero',
            'Marzo',
            'Abril',
            'Mayo',
            'Junio',
            'Julio',
            'Agosto',
            'Septiembre',
            'Octubre',
            'Noviembre',
            'Diciembre'
        ];
        $ventas = [];
        $visitas = [];

        for ($i = 1; $i <= 12; $i++) {
            $data = $ventasPorMes->get($i);
            $ventas[] = $data ? (float) $data->ventas : 0;
            $visitas[] = $data ? (int) $data->visitas : 0;
        }

        return [
            'tipo' => 'mensual',
            'periodo' => "Año $anio",
            'labels' => $meses,
            'ventas' => $ventas,
            'visitas' => $visitas,
            'total_ventas' => array_sum($ventas),
            'total_visitas' => array_sum($visitas),
            'promedio_venta' => count($meses) > 0 ? round(array_sum($ventas) / count($meses), 2) : 0
        ];
    }

    /**
     * Obtiene datos procesados para productos con estadísticas completas
     *
     * @param array $filtros
     * @return array Datos completos listos para el frontend
     */
    public function getDatosProductosCompletos(array $filtros = []): array
    {
        $productos = $this->getDatosProductos($filtros);

        if (empty($productos['labels'])) {
            return [
                'productos' => $productos,
                'estadisticas' => $this->getEstadisticasVacias(),
                'tabla_html' => $this->getTablaVaciaHtml(),
                'configuracion_grafico' => $this->getConfiguracionGraficoVacio(),
                'mensaje' => 'No hay datos disponibles para los filtros seleccionados'
            ];
        }

        // Procesar datos para gráficos
        $productorProcesados = $this->procesarProductosParaGraficos($productos);

        return [
            'productos' => $productos,
            'productos_procesados' => $productorProcesados,
            'estadisticas' => $this->calcularEstadisticasProductos($productos),
            'tabla_html' => $this->generarTablaProductosHtml($productos),
            'configuracion_grafico' => $this->getConfiguracionGrafico(count($productos['labels'])),
            'datos_pareto' => $this->calcularDatosPareto($productos),
            'indicador_rango' => $this->generarIndicadorRango($filtros),
            'mensaje' => null
        ];
    }

    /**
     * Procesa productos para gráficos (ordena y agrega metadatos)
     */
    private function procesarProductosParaGraficos(array $productos): array
    {
        if (empty($productos['labels'])) {
            return ['labels' => [], 'ventas' => [], 'unidades' => [], 'colores' => [], 'rankings' => []];
        }

        // Crear array combinado para ordenar
        $productosArray = [];
        for ($i = 0; $i < count($productos['labels']); $i++) {
            $productosArray[] = [
                'nombre' => $productos['labels'][$i],
                'ventas' => $productos['ventas'][$i] ?? 0,
                'unidades' => $productos['unidades'][$i] ?? 0,
                'indice_original' => $i
            ];
        }

        // Ordenar por ventas descendente
        usort($productosArray, function ($a, $b) {
            return $b['ventas'] <=> $a['ventas'];
        });

        // Extraer datos ordenados y generar colores
        $labelsOrdenados = [];
        $ventasOrdenadas = [];
        $unidadesOrdenadas = [];
        $colores = [];
        $rankings = [];

        foreach ($productosArray as $index => $producto) {
            $labelsOrdenados[] = $producto['nombre'];
            $ventasOrdenadas[] = $producto['ventas'];
            $unidadesOrdenadas[] = $producto['unidades'];
            $rankings[] = $index + 1;

            // Generar colores según el ranking
            if ($index === 0) {
                $colores[] = 'rgba(255, 193, 7, 0.8)'; // Oro para #1
            } elseif ($index === 1) {
                $colores[] = 'rgba(108, 117, 125, 0.8)'; // Plata para #2
            } elseif ($index === 2) {
                $colores[] = 'rgba(205, 164, 90, 0.8)'; // Bronce para #3
            } elseif ($index < 10) {
                $opacity = 0.9 - ($index * 0.08);
                $colores[] = "rgba(40, 167, 69, {$opacity})"; // Verde degradado top 10
            } elseif ($index < 50) {
                $opacity = 0.7 - (($index - 10) * 0.01);
                $colores[] = "rgba(23, 162, 184, {$opacity})"; // Azul para siguientes
            } else {
                $opacity = max(0.3, 0.6 - (($index - 50) * 0.005));
                $colores[] = "rgba(108, 117, 125, {$opacity})"; // Gris para resto
            }
        }

        return [
            'labels' => $labelsOrdenados,
            'ventas' => $ventasOrdenadas,
            'unidades' => $unidadesOrdenadas,
            'colores' => $colores,
            'rankings' => $rankings
        ];
    }

    /**
     * Calcula estadísticas completas de productos
     */
    private function calcularEstadisticasProductos(array $productos): array
    {
        if (empty($productos['labels'])) {
            return $this->getEstadisticasVacias();
        }

        $totalProductos = count($productos['labels']);
        $totalVentas = array_sum($productos['ventas']);
        $totalUnidades = array_sum($productos['unidades']);
        $precioPromedio = $totalUnidades > 0 ? $totalVentas / $totalUnidades : 0;

        return [
            'total_productos' => $totalProductos,
            'total_ventas' => $totalVentas,
            'total_unidades' => $totalUnidades,
            'precio_promedio' => $precioPromedio,
            'total_ventas_formateado' => 'S/ ' . number_format($totalVentas, 2, '.', ','),
            'total_unidades_formateado' => number_format($totalUnidades, 0, '.', ','),
            'precio_promedio_formateado' => 'S/ ' . number_format($precioPromedio, 2, '.', ',')
        ];
    }

    /**
     * Genera HTML de la tabla de productos
     */
    private function generarTablaProductosHtml(array $productos): string
    {
        if (empty($productos['labels'])) {
            return '<tr><td colspan="6" class="text-center py-5"><div class="text-muted"><i class="fas fa-inbox fa-3x mb-3 opacity-25"></i><h5>No hay datos disponibles</h5><p>Los filtros aplicados no devolvieron resultados.</p></div></td></tr>';
        }

        $totalVentas = array_sum($productos['ventas']);
        $html = '';

        // Crear array combinado y ordenar
        $productosArray = [];
        for ($i = 0; $i < count($productos['labels']); $i++) {
            $productosArray[] = [
                'nombre' => $productos['labels'][$i],
                'ventas' => $productos['ventas'][$i] ?? 0,
                'unidades' => $productos['unidades'][$i] ?? 0
            ];
        }

        // Ordenar por ventas descendente
        usort($productosArray, function ($a, $b) {
            return $b['ventas'] <=> $a['ventas'];
        });

        // Generar filas HTML
        foreach ($productosArray as $index => $producto) {
            $precioPromedio = $producto['unidades'] > 0 ? ($producto['ventas'] / $producto['unidades']) : 0;
            $porcentaje = $totalVentas > 0 ? ($producto['ventas'] / $totalVentas) * 100 : 0;
            $ranking = $index + 1;

            // Determinar badge de ranking
            $rankingBadge = '';
            if ($ranking === 1) {
                $rankingBadge = '<span class="badge bg-warning text-dark">🏆 #1</span>';
            } elseif ($ranking === 2) {
                $rankingBadge = '<span class="badge bg-secondary">🥈 #2</span>';
            } elseif ($ranking === 3) {
                $rankingBadge = '<span class="badge bg-info">🥉 #3</span>';
            } elseif ($ranking <= 10) {
                $rankingBadge = '<span class="badge bg-success">#' . $ranking . '</span>';
            } else {
                $rankingBadge = '<span class="badge bg-light text-dark">#' . $ranking . '</span>';
            }

            $html .= '<tr>';
            $html .= '<td class="text-center">' . $rankingBadge . '</td>';
            $html .= '<td><strong>' . htmlspecialchars($producto['nombre']) . '</strong></td>';
            $html .= '<td class="text-center"><span class="badge bg-primary">' . number_format($producto['unidades']) . '</span></td>';
            $html .= '<td class="text-end"><strong class="text-success">S/ ' . number_format($producto['ventas'], 2) . '</strong></td>';
            $html .= '<td class="text-end">S/ ' . number_format($precioPromedio, 2) . '</td>';
            $html .= '<td class="text-center">';
            $html .= '<div class="progress" style="height: 20px;">';
            $html .= '<div class="progress-bar bg-success" role="progressbar" style="width: ' . $porcentaje . '%" aria-valuenow="' . $porcentaje . '" aria-valuemin="0" aria-valuemax="100">';
            $html .= number_format($porcentaje, 1) . '%';
            $html .= '</div></div></td>';
            $html .= '</tr>';
        }

        return $html;
    }

    /**
     * Calcula datos para análisis Pareto
     */
    private function calcularDatosPareto(array $productos): array
    {
        if (empty($productos['labels'])) {
            return ['labels' => [], 'porcentajes_acumulados' => [], 'punto_80' => 0];
        }

        // Crear array combinado y ordenar
        $productosArray = [];
        for ($i = 0; $i < count($productos['labels']); $i++) {
            $productosArray[] = [
                'nombre' => $productos['labels'][$i],
                'ventas' => $productos['ventas'][$i] ?? 0
            ];
        }

        usort($productosArray, function ($a, $b) {
            return $b['ventas'] <=> $a['ventas'];
        });

        $totalVentas = array_sum($productos['ventas']);
        $acumulado = 0;
        $porcentajesAcumulados = [];
        $labels = [];

        foreach ($productosArray as $index => $producto) {
            $acumulado += $producto['ventas'];
            $porcentaje = $totalVentas > 0 ? ($acumulado / $totalVentas) * 100 : 0;
            $porcentajesAcumulados[] = $porcentaje;
            $labels[] = 'Top ' . ($index + 1);
        }

        // Encontrar punto donde se alcanza el 80%
        $punto80 = 0;
        foreach ($porcentajesAcumulados as $index => $porcentaje) {
            if ($porcentaje >= 80) {
                $punto80 = $index + 1;
                break;
            }
        }

        return [
            'labels' => $labels,
            'porcentajes_acumulados' => $porcentajesAcumulados,
            'punto_80' => $punto80,
            'total_productos' => count($productosArray)
        ];
    }

    /**
     * Obtiene configuración optimizada para gráficos según cantidad de productos
     */
    private function getConfiguracionGrafico(int $numProductos): array
    {
        if ($numProductos <= 10) {
            return [
                'altura' => 500,
                'barThickness' => 'flex',
                'maxBarThickness' => 50,
                'categoryPercentage' => 0.8,
                'barPercentage' => 0.7,
                'fontSizeY' => 14,
                'fontSizeX' => 12,
                'maxChars' => 50,
                'paddingY' => 15,
                'paddingLeft' => 200,
                'borderWidth' => 2,
                'borderRadius' => 8
            ];
        } elseif ($numProductos <= 30) {
            return [
                'altura' => 800,
                'barThickness' => 'flex',
                'maxBarThickness' => 35,
                'categoryPercentage' => 0.9,
                'barPercentage' => 0.8,
                'fontSizeY' => 12,
                'fontSizeX' => 11,
                'maxChars' => 45,
                'paddingY' => 10,
                'paddingLeft' => 180,
                'borderWidth' => 2,
                'borderRadius' => 6
            ];
        } elseif ($numProductos <= 100) {
            return [
                'altura' => max(1200, $numProductos * 25),
                'barThickness' => 'flex',
                'maxBarThickness' => 25,
                'categoryPercentage' => 0.95,
                'barPercentage' => 0.85,
                'fontSizeY' => 11,
                'fontSizeX' => 10,
                'maxChars' => 40,
                'paddingY' => 8,
                'paddingLeft' => 160,
                'borderWidth' => 1,
                'borderRadius' => 4
            ];
        } else {
            return [
                'altura' => max(1500, $numProductos * 20),
                'barThickness' => 'flex',
                'maxBarThickness' => 20,
                'categoryPercentage' => 0.98,
                'barPercentage' => 0.9,
                'fontSizeY' => 10,
                'fontSizeX' => 9,
                'maxChars' => 35,
                'paddingY' => 6,
                'paddingLeft' => 140,
                'borderWidth' => 1,
                'borderRadius' => 3
            ];
        }
    }

    /**
     * Genera indicador de rango de fechas
     */
    private function generarIndicadorRango(array $filtros): string
    {
        $fechaInicio = $filtros['fecha_inicio_producto'] ?? null;
        $fechaFin = $filtros['fecha_fin_producto'] ?? null;

        $today = date('Y-m-d');
        $primerDiaMes = date('Y-m-01');

        if ($fechaInicio === $primerDiaMes && $fechaFin === $today) {
            return '<small class="badge bg-light text-dark px-3 py-1"><i class="fas fa-calendar-alt me-1"></i>Datos por defecto: <strong>' . date('d/m/Y', strtotime($primerDiaMes)) . ' - ' . date('d/m/Y', strtotime($today)) . '</strong> <span class="text-muted">(Mes actual)</span></small>';
        } elseif ($fechaInicio && $fechaFin) {
            return '<small class="badge bg-info text-white px-3 py-1"><i class="fas fa-calendar-alt me-1"></i>Rango personalizado: <strong>' . date('d/m/Y', strtotime($fechaInicio)) . ' - ' . date('d/m/Y', strtotime($fechaFin)) . '</strong></small>';
        } elseif ($fechaInicio) {
            return '<small class="badge bg-info text-white px-3 py-1"><i class="fas fa-calendar-alt me-1"></i>Desde: <strong>' . date('d/m/Y', strtotime($fechaInicio)) . '</strong></small>';
        } elseif ($fechaFin) {
            return '<small class="badge bg-info text-white px-3 py-1"><i class="fas fa-calendar-alt me-1"></i>Hasta: <strong>' . date('d/m/Y', strtotime($fechaFin)) . '</strong></small>';
        } else {
            return '<small class="badge bg-warning text-dark px-3 py-1"><i class="fas fa-calendar-alt me-1"></i><strong>Todos los datos históricos</strong></small>';
        }
    }

    /**
     * Retorna estadísticas vacías
     */
    private function getEstadisticasVacias(): array
    {
        return [
            'total_productos' => 0,
            'total_ventas' => 0,
            'total_unidades' => 0,
            'precio_promedio' => 0,
            'total_ventas_formateado' => 'S/ 0.00',
            'total_unidades_formateado' => '0',
            'precio_promedio_formateado' => 'S/ 0.00'
        ];
    }

    /**
     * Retorna HTML de tabla vacía
     */
    private function getTablaVaciaHtml(): string
    {
        return '<tr><td colspan="6" class="text-center py-5"><div class="text-muted"><i class="fas fa-inbox fa-3x mb-3 opacity-25"></i><h5>No hay datos disponibles</h5><p>Los filtros aplicados no devolvieron resultados. Intente con diferentes fechas.</p></div></td></tr>';
    }

    /**
     * Retorna configuración de gráfico vacío
     */
    private function getConfiguracionGraficoVacio(): array
    {
        return [
            'altura' => 400,
            'barThickness' => 'flex',
            'maxBarThickness' => 50,
            'categoryPercentage' => 0.8,
            'barPercentage' => 0.7,
            'fontSizeY' => 12,
            'fontSizeX' => 10,
            'maxChars' => 30,
            'paddingY' => 10,
            'paddingLeft' => 100,
            'borderWidth' => 1,
            'borderRadius' => 4
        ];
    }

    /**
     * Convierte el DTO a array incluyendo propiedades específicas
     *
     * @return array Array completo con todos los datos de ventas
     */
    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'visitadoras' => $this->visitadoras,
            'productos' => $this->productos,
            'provincias' => $this->provincias,
            'general' => $this->general,
        ]);
    }
}
