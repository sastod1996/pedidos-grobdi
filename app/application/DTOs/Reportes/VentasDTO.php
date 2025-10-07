<?php

namespace App\Application\DTOs\Reportes;

use App\Models\Pedidos;
use App\Application\Services\Reportes\GeoVentasService;
use Carbon\Carbon;
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
     * Constructor que inicializa datos de ventas de forma lazy
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

        // Inicializar solo las propiedades livianas primero
        $this->visitadoras = $this->getVentasByVisitadoraData($filtros);
        $this->general = $this->getDatosGeneral($filtros);

        // Las propiedades pesadas (productos, provincias) se inicializan solo cuando se necesiten
        // Esto evita timeouts al crear la instancia
    }

    /**
     * Obtiene datos de productos de forma lazy
     */
    private function getProductosLazy(): array
    {
        if (!isset($this->productos)) {
            $this->productos = $this->getDatosProductos($this->filtros);
        }
        return $this->productos;
    }

    /**
     * Obtiene datos de provincias de forma lazy
     */
    private function getProvinciasLazy(): array
    {
        if (!isset($this->provincias)) {
            $this->provincias = $this->getDatosProvincias($this->filtros);
        }
        return $this->provincias;
    }

    /**
     * Obtiene datos agrupados por visitadora desde pedidos
     *
     * @param array $filtros
     * @return array Datos de visitadoras con ventas y visitas
     */
    public function getVentasByVisitadoraData(array $filtros = [])
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
            $startDate = Carbon::parse($filtros['start_date'])->startOfDay();
            $endDate = Carbon::parse($filtros['end_date'])->endOfDay();
            $query->whereBetween('p.created_at', [$startDate, $endDate]);
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
        // Optimización: Limitar resultados para evitar timeouts
        $limiteProductos = 100; // Máximo 100 productos para evitar consultas muy pesadas

        // Consulta optimizada con límite
        $query = DB::table('detail_pedidos')
            ->selectRaw('detail_pedidos.articulo as producto, SUM(detail_pedidos.sub_total) as ventas, SUM(detail_pedidos.cantidad) as unidades')
            ->join('pedidos', 'detail_pedidos.pedidos_id', '=', 'pedidos.id')
            ->whereNotNull('detail_pedidos.articulo')
            ->where('detail_pedidos.articulo', '!=', '')
            ->whereRaw('LOWER(detail_pedidos.articulo) NOT LIKE ?', ['%delivery%'])
            ->whereRaw('LOWER(detail_pedidos.articulo) NOT LIKE ?', ['bolsa%'])
            ->groupBy('detail_pedidos.articulo')
            ->orderByRaw('SUM(detail_pedidos.sub_total) DESC')
            ->limit(100);

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
        // Usar el servicio especializado con los datos del DTO
        $service = new GeoVentasService();
        $filtros = array_merge(['agrupacion' => 'provincia'], $filtros);
        $datos = $service->getGeoVentas($filtros);
        return [
            'labels' => $datos['labels'],
            'ventas' => $datos['ventas'],
            'porcentaje' => $datos['porcentaje'],
            'pedidos' => $datos['pedidos']
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
        $productos = $this->getProductosLazy();

        if (empty($productos['labels'])) {
            return [
                'productos' => $productos,
                'estadisticas' => $this->getEstadisticasVacias(),
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
            'configuracion_grafico' => $this->getConfiguracionGrafico(count($productos['labels'])),
            'datos_pareto' => $this->calcularDatosPareto($productos),
            // Enviar información estructurada del rango (sin HTML) para que el frontend lo renderice
            'indicador' => $this->getIndicadorRango($filtros),
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

    // Eliminado: generación de HTML de tabla. El frontend debe renderizar con los datos estructurados.

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
     * Devuelve un indicador estructurado del rango de fechas seleccionado (sin HTML)
     */
    private function getIndicadorRango(array $filtros): array
    {
        $fechaInicio = $filtros['fecha_inicio_producto'] ?? null;
        $fechaFin = $filtros['fecha_fin_producto'] ?? null;
        $today = date('Y-m-d');
        $primerDiaMes = date('Y-m-01');

        if ($fechaInicio === $primerDiaMes && $fechaFin === $today) {
            return [
                'tipo' => 'por_defecto',
                'desde' => $primerDiaMes,
                'hasta' => $today
            ];
        } elseif ($fechaInicio && $fechaFin) {
            return [
                'tipo' => 'personalizado',
                'desde' => $fechaInicio,
                'hasta' => $fechaFin
            ];
        } elseif ($fechaInicio) {
            return [
                'tipo' => 'desde',
                'desde' => $fechaInicio,
                'hasta' => null
            ];
        } elseif ($fechaFin) {
            return [
                'tipo' => 'hasta',
                'desde' => null,
                'hasta' => $fechaFin
            ];
        }

        return [
            'tipo' => 'todos',
            'desde' => null,
            'hasta' => null
        ];
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

    // Eliminado: HTML de tabla vacía. El frontend gestiona mensajes vacíos.

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
     * Obtiene pedidos detallados por departamento usando GeoVentasService
     *
     * @param string $departamento Nombre del departamento
     * @param array $filtros Filtros aplicados
     * @return array Pedidos detallados del departamento
     */
    public function getPedidosDetallados(string $departamento, array $filtros = []): array
    {
        $service = new GeoVentasService();
        return $service->getPedidosDetallados($departamento, $filtros);
    }

    /**
     * Obtiene datos crudos de ventas por distrito para GeoVentasService
     * Este método contiene la consulta a BD que antes estaba en GeoVentasService
     *
     * @param array $filtros Filtros aplicados
     * @return \Illuminate\Support\Collection Datos crudos de la consulta
     */
    public static function getDatosCrudosGeoVentas(array $filtros = []): \Illuminate\Support\Collection
    {
        $query = Pedidos::query()
            ->selectRaw('district, SUM(prize) as ventas, COUNT(*) as pedidos')
            ->whereNotNull('district')
            ->where('district', '!=', '')
            ->whereRaw('LOWER(district) NOT LIKE ?', ['%retiro de tienda%'])
            ->whereRaw('LOWER(district) NOT LIKE ?', ['%recojo en tienda%'])
            ->where('zone_id', 1);

        // Aplicar filtros de fecha
        if (!empty($filtros['fecha_inicio_provincia'])) {
            $query->whereDate('created_at', '>=', $filtros['fecha_inicio_provincia']);
        }
        if (!empty($filtros['fecha_fin_provincia'])) {
            $query->whereDate('created_at', '<=', $filtros['fecha_fin_provincia']);
        }
        if (!empty($filtros['anio_general'])) {
            $query->whereYear('created_at', $filtros['anio_general']);
        }
        if (!empty($filtros['mes_general'])) {
            $query->whereMonth('created_at', $filtros['mes_general']);
        }

        return $query->groupBy('district')->get();
    }

    /**
     * Obtiene datos crudos de pedidos detallados para GeoVentasService
     * Este método contiene la consulta a BD que antes estaba en GeoVentasService
     *
     * @param array $filtros Filtros aplicados
     * @return \Illuminate\Support\Collection Datos crudos de la consulta
     */
    public static function getDatosCrudosPedidosDetallados(array $filtros = []): \Illuminate\Support\Collection
    {
        $query = Pedidos::query()
            ->select([
                'pedidos.id',
                'pedidos.created_at as fecha_pedido',
                'pedidos.prize as total',
                'pedidos.district as distrito_original',
                'users.name as visitadora',
                'users.email as email_visitadora'
            ])
            ->leftJoin('users', 'pedidos.user_id', '=', 'users.id')
            ->whereNotNull('pedidos.district')
            ->where('pedidos.district', '!=', '')
            // Excluir registros de retiro/recogida en tienda
            ->whereRaw('LOWER(pedidos.district) NOT LIKE ?', ['%retiro de tienda%'])
            ->whereRaw('LOWER(pedidos.district) NOT LIKE ?', ['%recojo en tienda%'])
            ->where('pedidos.zone_id', 1);

        // Aplicar filtros de fecha
        if (!empty($filtros['fecha_inicio_provincia'])) {
            $query->whereDate('pedidos.created_at', '>=', $filtros['fecha_inicio_provincia']);
        }
        if (!empty($filtros['fecha_fin_provincia'])) {
            $query->whereDate('pedidos.created_at', '<=', $filtros['fecha_fin_provincia']);
        }
        if (!empty($filtros['anio_general'])) {
            $query->whereYear('pedidos.created_at', $filtros['anio_general']);
        }
        if (!empty($filtros['mes_general'])) {
            $query->whereMonth('pedidos.created_at', $filtros['mes_general']);
        }

        return $query->orderBy('pedidos.created_at', 'desc')->get();
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
            'productos' => $this->getProductosLazy(),
            'provincias' => $this->getProvinciasLazy(),
            'general' => $this->general,
        ]);
    }
}
