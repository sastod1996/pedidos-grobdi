<!-- Tab Producto Mejorado -->
<div class="tab-pane fade" id="producto" role="tabpanel">

    <!-- Header con título y estadísticas generales -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm bg-success">
                <div class="card-body text-white">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h3 class="mb-1">
                                <i class="fas fa-chart-bar me-2"></i> Reporte Detallado por Producto
                            </h3>
                            <p class="mb-0 opacity-75">Análisis completo de ventas y rendimiento por producto</p>
                            <div class="mt-2">
                                <small class="badge bg-light text-dark px-3 py-1">
                                    <i class="fas fa-calendar-alt me-1"></i>
                                    Datos por defecto: <strong>{{ date('d/m/Y', strtotime(date('Y-m-01'))) }} - {{ date('d/m/Y') }}</strong>
                                    <span class="text-muted">(Mes actual)</span>
                                </small>
                            </div>
                        </div>
                        <div class="col-md-4 text-end">
                            <div class="row g-2">
                                <div class="col-6">
                                    <div class="text-center">
                                        <h5 class="mb-0" id="header_total_productos">
                                            {{ count($data['productos']['labels'] ?? []) }}
                                        </h5>
                                        <small class="opacity-75">Productos</small>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="text-center">
                                        <h5 class="mb-0" id="header_total_ventas">S/
                                            {{ number_format(array_sum($data['productos']['ventas'] ?? []), 0) }}
                                        </h5>
                                        <small class="opacity-75">Ventas Totales</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros con mejor diseño -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light border-bottom-0">
                    <h6 class="mb-0 text-muted">
                        <i class="fas fa-filter me-2"></i> Filtros de Búsqueda
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label for="fecha_inicio_producto" class="form-label fw-medium">
                                <i class="fas fa-calendar-alt text-primary me-1"></i> Fecha Inicio
                            </label>
                            <input type="date" class="form-control border-2" id="fecha_inicio_producto">
                        </div>
                        <div class="col-md-3">
                            <label for="fecha_fin_producto" class="form-label fw-medium">
                                <i class="fas fa-calendar-check text-primary me-1"></i> Fecha Fin
                            </label>
                            <input type="date" class="form-control border-2" id="fecha_fin_producto">
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-primary w-100 fw-medium" id="filtrar_producto">
                                <i class="fas fa-search me-2"></i> Buscar Datos
                            </button>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-outline-secondary w-100 fw-medium" id="limpiar_producto">
                                <i class="fas fa-refresh me-2"></i> Limpiar Filtros
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Cards de estadísticas rápidas -->
    <div class="row mb-4 g-3">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 bg-primary">
                <div class="card-body text-white text-center">
                    <div class="mb-2">
                        <i class="fas fa-boxes fa-2x opacity-75"></i>
                    </div>
                    <h4 class="mb-1" id="stat_total_productos">{{ count($data['productos']['labels'] ?? []) }}</h4>
                    <p class="mb-0 small opacity-75">Total Productos</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 bg-success">
                <div class="card-body text-white text-center">
                    <div class="mb-2">
                        <i class="fas fa-shopping-cart fa-2x opacity-75"></i>
                    </div>
                    <h4 class="mb-1" id="stat_total_unidades">
                        {{ number_format(array_sum($data['productos']['unidades'] ?? [])) }}
                    </h4>
                    <p class="mb-0 small opacity-75">Unidades Vendidas</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 bg-info">
                <div class="card-body text-white text-center">
                    <div class="mb-2">
                        <i class="fas fa-dollar-sign fa-2x opacity-75"></i>
                    </div>
                    <h4 class="mb-1" id="stat_total_ingresos">S/
                        {{ number_format(array_sum($data['productos']['ventas'] ?? []), 0) }}
                    </h4>
                    <p class="mb-0 small opacity-75">Ingresos Totales</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 bg-warning">
                <div class="card-body text-white text-center">
                    <div class="mb-2">
                        <i class="fas fa-chart-line fa-2x opacity-75"></i>
                    </div>
                    <h4 class="mb-1" id="stat_precio_promedio">
                        S/
                        {{ array_sum($data['productos']['unidades'] ?? []) > 0 ? number_format(array_sum($data['productos']['ventas'] ?? []) / array_sum($data['productos']['unidades'] ?? []), 2) : '0.00' }}
                    </h4>
                    <p class="mb-0 small opacity-75">Precio Promedio</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráfica principal mejorada -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header border-bottom-0 bg-success">
                    <div class="row align-items-center">
                        <div class="col">
                            <h5 class="mb-0 text-white">
                                <i class="fas fa-chart-bar me-2"></i> Ranking de Ventas por Producto
                            </h5>
                            <small class="text-white opacity-75">Todos los productos ordenados por ingresos</small>
                        </div>
                        <div class="col-auto">
                            <div class="d-flex gap-2 align-items-center">

                                <span class="badge bg-light text-dark px-3 py-2" id="productos_count">
                                    {{ count($data['productos']['labels'] ?? []) }} productos
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">

                    <!-- Contenedor mejorado con scroll -->
                    <div class="chart-container-enhanced" id="chartContainer">
                        <canvas id="productosVentasChart"></canvas>
                    </div>

                    <!-- Información de navegación en la parte inferior -->
                    <div class="chart-footer p-2 bg-light border-top">
                        <div class="row text-center">
                            <div class="col-4">
                                <small class="text-muted">
                                    <i class="fas fa-chart-bar text-success"></i>
                                    <span id="productosVisibles">0</span> productos mostrados
                                </small>
                            </div>
                            <div class="col-4">
                                <small class="text-muted">
                                    <i class="fas fa-dollar-sign text-info"></i>
                                    Total: S/ <span id="totalVisible">0</span>
                                </small>
                            </div>
                            <div class="col-4">
                                <small class="text-muted">
                                    <i class="fas fa-mouse-pointer text-warning"></i>
                                    Hover para detalles
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Tabla detallada mejorada -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header border-bottom-0 bg-primary">
                    <div class="row align-items-center">
                        <div class="col">
                            <h5 class="mb-0 text-white">
                                <i class="fas fa-table me-2"></i> Detalle Completo de Productos
                            </h5>
                            <small class="text-white opacity-75">Información detallada con métricas de
                                rendimiento</small>
                        </div>
                        <div class="col-auto">
                            <button class="btn btn-light btn-sm" onclick="exportarTabla()">
                                <i class="fas fa-download me-1"></i> Exportar
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <!-- Tabla con scroll vertical y mejor diseño -->
                    <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                        <table class="table table-hover mb-0">
                            <thead class="sticky-top bg-dark">
                                <tr class="text-white">
                                    <th class="text-center border-0 py-3">
                                        <i class="fas fa-hashtag me-1"></i> #
                                    </th>
                                    <th class="border-0 py-3">
                                        <i class="fas fa-box me-1"></i> Producto
                                    </th>
                                    <th class="text-center border-0 py-3">
                                        <i class="fas fa-cubes me-1"></i> Unidades
                                    </th>
                                    <th class="text-end border-0 py-3">
                                        <i class="fas fa-money-bill-wave me-1"></i> Ingresos (S/)
                                    </th>
                                    <th class="text-end border-0 py-3">
                                        <i class="fas fa-tag me-1"></i> Precio Prom. (S/)
                                    </th>
                                    <th class="text-center border-0 py-3">
                                        <i class="fas fa-chart-pie me-1"></i> % Total
                                    </th>
                                </tr>
                            </thead>
                            <tbody id="tabla_productos">
                                @if (isset($data['productos']['labels']) && count($data['productos']['labels']) > 0)
                                @php
                                $totalVentas = array_sum($data['productos']['ventas'] ?? []);

                                // Crear array combinado para ordenar por ventas
                                $productos = [];
                                foreach ($data['productos']['labels'] as $index => $producto) {
                                $productos[] = [
                                'nombre' => $producto,
                                'unidades' => $data['productos']['unidades'][$index] ?? 0,
                                'ventas' => $data['productos']['ventas'][$index] ?? 0,
                                ];
                                }

                                // Ordenar por ventas descendente
                                usort($productos, function ($a, $b) {
                                return $b['ventas'] <=> $a['ventas'];
                                    });
                                    @endphp

                                    @foreach ($productos as $index => $producto)
                                    @php
                                    $porcentaje =
                                    $totalVentas > 0 ? ($producto['ventas'] / $totalVentas) * 100 : 0;
                                    $precioPromedio =
                                    $producto['unidades'] > 0
                                    ? $producto['ventas'] / $producto['unidades']
                                    : 0;

                                    // Colores para el ranking
                                    $badgeClass = 'bg-secondary';
                                    if ($index == 0) {
                                    $badgeClass = 'bg-warning';
                                    } elseif ($index == 1) {
                                    $badgeClass = 'bg-secondary';
                                    } elseif ($index == 2) {
                                    $badgeClass = 'bg-info';
                                    } elseif ($index < 10) {
                                        $badgeClass='bg-success' ;
                                        }
                                        @endphp
                                        <tr class="border-bottom">
                                        <td class="text-center py-3">
                                            <span class="badge {{ $badgeClass }} px-3 py-2 fs-6">
                                                @if ($index == 0)
                                                🥇
                                                @elseif($index == 1)
                                                🥈
                                                @elseif($index == 2)
                                                🥉
                                                @endif
                                                {{ $index + 1 }}
                                            </span>
                                        </td>
                                        <td class="py-3">
                                            <div class="d-flex align-items-center">
                                                <div class="me-3">
                                                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center"
                                                        style="width: 40px; height: 40px;">
                                                        <i class="fas fa-box"></i>
                                                    </div>
                                                </div>
                                                <div>
                                                    <h6 class="mb-0 fw-bold">{{ $producto['nombre'] }}</h6>
                                                    <small class="text-muted">Ranking:
                                                        #{{ $index + 1 }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center py-3">
                                            <span
                                                class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 fs-6">
                                                {{ number_format($producto['unidades']) }}
                                            </span>
                                        </td>
                                        <td class="text-end py-3">
                                            <h6 class="mb-0 text-success fw-bold">
                                                S/ {{ number_format($producto['ventas'], 2) }}
                                            </h6>
                                        </td>
                                        <td class="text-end py-3">
                                            <span class="fw-medium">
                                                S/ {{ number_format($precioPromedio, 2) }}
                                            </span>
                                        </td>
                                        <td class="text-center py-3">
                                            <div class="progress"
                                                style="height: 25px; background-color: #e9ecef;">
                                                <div class="progress-bar bg-success progress-bar-striped"
                                                    role="progressbar" style="width: {{ $porcentaje }}%;"
                                                    aria-valuenow="{{ $porcentaje }}" aria-valuemin="0"
                                                    aria-valuemax="100">
                                                    <span
                                                        class="fw-bold">{{ number_format($porcentaje, 1) }}%</span>
                                                </div>
                                            </div>
                                        </td>
                                        </tr>
                                        @endforeach
                                        @else
                                        <tr>
                                            <td colspan="6" class="text-center py-5">
                                                <div class="text-muted">
                                                    <i class="fas fa-inbox fa-3x mb-3 opacity-25"></i>
                                                    <h5>No hay datos disponibles</h5>
                                                    <p>Ajusta los filtros para mostrar información</p>
                                                </div>
                                            </td>
                                        </tr>
                                        @endif
                            </tbody>
                            <tfoot class="sticky-bottom bg-light border-top">
                                <tr class="fw-bold">
                                    <th colspan="2" class="text-end py-3 bg-dark text-white">
                                        <i class="fas fa-calculator me-2"></i> RESUMEN TOTAL
                                    </th>
                                    <th class="text-center py-3 bg-dark text-white" id="total_unidades">
                                        {{ number_format(array_sum($data['productos']['unidades'] ?? [])) }}
                                    </th>
                                    <th class="text-end py-3 bg-dark text-white" id="total_ventas">
                                        S/ {{ number_format(array_sum($data['productos']['ventas'] ?? []), 2) }}
                                    </th>
                                    <th class="text-end py-3 bg-dark text-white" id="promedio_precio">
                                        S/
                                        {{ array_sum($data['productos']['unidades'] ?? []) > 0 ? number_format(array_sum($data['productos']['ventas'] ?? []) / array_sum($data['productos']['unidades'] ?? []), 2) : '0.00' }}
                                    </th>
                                    <th class="text-center py-3 bg-dark text-white">100%</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Botones de acción mejorados -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-4">
                    <h6 class="mb-3 text-muted">
                        <i class="fas fa-download me-2"></i> Exportar Datos
                    </h6>
                    <div class="d-flex gap-3 justify-content-center flex-wrap">
                        <button class="btn btn-success btn-lg px-4" id="descargar-excel-producto">
                            <i class="fas fa-file-excel me-2"></i> Descargar Excel Completo
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .chart-container-enhanced {
        position: relative;
        height: 70vh;
        max-height: 800px;
        overflow-y: auto;
        overflow-x: hidden;
        background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
        border: 1px solid #e3e6f0;
    }

    /* Scroll personalizado más elegante */
    .chart-container-enhanced::-webkit-scrollbar {
        width: 14px;
    }

    .chart-container-enhanced::-webkit-scrollbar-track {
        background: linear-gradient(180deg, #f1f3f5 0%, #e9ecef 100%);
        border-radius: 10px;
        box-shadow: inset 0 0 5px rgba(0, 0, 0, 0.1);
    }

    .chart-container-enhanced::-webkit-scrollbar-thumb {
        background: linear-gradient(180deg, #28a745 0%, #20c997 100%);
        border-radius: 10px;
        border: 2px solid #f1f3f5;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    }

    .chart-container-enhanced::-webkit-scrollbar-thumb:hover {
        background: linear-gradient(180deg, #218838 0%, #1ea87e 100%);
        transform: scale(1.1);
    }

    /* Indicador de posición de scroll */
    .chart-container-enhanced::before {
        content: '';
        position: absolute;
        top: 0;
        right: 20px;
        width: 4px;
        height: 100%;
        background: rgba(40, 167, 69, 0.1);
        z-index: 1000;
        pointer-events: none;
    }

    /* Efecto de fade en los bordes para indicar scroll */
    .chart-container-enhanced::after {
        content: '';
        position: sticky;
        top: 0;
        left: 0;
        right: 0;
        height: 20px;
        background: linear-gradient(180deg, rgba(248, 249, 250, 0.9) 0%, rgba(248, 249, 250, 0) 100%);
        z-index: 999;
        pointer-events: none;
    }

    /* Mejoras para la tabla */
    .table-enhanced {
        font-size: 0.9rem;
    }

    .table-enhanced th {
        background: linear-gradient(135deg, #343a40 0%, #495057 100%);
        color: white;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
        border: none;
        position: sticky;
        top: 0;
        z-index: 10;
    }

    .table-enhanced tbody tr:hover {
        background: linear-gradient(90deg, rgba(40, 167, 69, 0.05) 0%, rgba(40, 167, 69, 0.1) 50%, rgba(40, 167, 69, 0.05) 100%);
        transform: translateX(2px);
        transition: all 0.2s ease;
    }

    .ranking-badge {
        position: relative;
        overflow: hidden;
    }

    .ranking-badge::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
        transition: left 0.5s;
    }

    .ranking-badge:hover::before {
        left: 100%;
    }

    /* Animación para barras de progreso */
    .progress-animated .progress-bar {
        animation: progressAnimation 2s ease-in-out;
    }

    @keyframes progressAnimation {
        0% {
            width: 0%;
        }

        100% {
            width: var(--progress-width);
        }
    }

    /* Responsive mejoras */
    @media (max-width: 768px) {
        .chart-container-enhanced {
            height: 60vh;
        }

        .table-enhanced {
            font-size: 0.8rem;
        }

        .chart-footer .col-4 {
            font-size: 0.7rem;
        }
    }

    /* Loading overlay */
    .chart-loading {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255, 255, 255, 0.9);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1000;
    }

    .loading-spinner {
        width: 3rem;
        height: 3rem;
        border: 0.3rem solid rgba(40, 167, 69, 0.3);
        border-top: 0.3rem solid #28a745;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% {
            transform: rotate(0deg);
        }

        100% {
            transform: rotate(360deg);
        }
    }

    .chart-scroll-container {
        border: 2px solid #e9ecef;
        border-radius: 8px;
        background: #f8f9fa;
    }

    .chart-scroll-container::-webkit-scrollbar {
        height: 8px;
    }

    .chart-scroll-container::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 4px;
    }

    .chart-scroll-container::-webkit-scrollbar-thumb {
        background: #28a745;
        border-radius: 4px;
    }

    .chart-scroll-container::-webkit-scrollbar-thumb:hover {
        background: #20c997;
    }

    /* Estilos para el contenedor del gráfico con scroll optimizado */
    .chart-container-scroll {
        max-height: 800px;
        /* Altura controlada para activar scroll cuando sea necesario */
        overflow-y: auto;
        overflow-x: hidden;
        scroll-behavior: smooth;
        border-radius: 8px;
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .chart-container-scroll::-webkit-scrollbar {
        width: 12px;
    }

    .chart-container-scroll::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 6px;
    }

    .chart-container-scroll::-webkit-scrollbar-thumb {
        background: #28a745;
        border-radius: 6px;
    }

    .chart-container-scroll::-webkit-scrollbar-thumb:hover {
        background: #20c997;
    }

    .table-responsive::-webkit-scrollbar {
        width: 8px;
    }

    .table-responsive::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 4px;
    }

    .table-responsive::-webkit-scrollbar-thumb {
        background: #667eea;
        border-radius: 4px;
    }

    .chart-relation-indicator {
        position: absolute;
        top: 15px;
        right: 15px;
        background: rgba(255, 255, 255, 0.95);
        padding: 10px 15px;
        border-radius: 8px;
        font-size: 13px;
        color: #495057;
        border: 2px solid #28a745;
        z-index: 10;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        backdrop-filter: blur(5px);
        transition: all 0.3s ease;
    }

    .chart-relation-indicator:hover {
        background: rgba(255, 255, 255, 1);
        transform: translateY(-2px) scale(1.02);
        box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
        border-color: #20c997;
    }

    .separation-info {
        position: absolute;
        bottom: 10px;
        left: 15px;
        background: rgba(40, 167, 69, 0.1);
        padding: 8px 12px;
        border-radius: 6px;
        font-size: 11px;
        color: #28a745;
        border: 1px solid rgba(40, 167, 69, 0.3);
        z-index: 10;
        backdrop-filter: blur(3px);
        transition: all 0.3s ease;
    }

    .separation-info:hover {
        background: rgba(40, 167, 69, 0.15);
        transform: translateY(-1px);
    }

    @media print {
        .card {
            box-shadow: none !important;
            border: 1px solid #dee2e6 !important;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Verificar que jQuery esté cargado
        if (typeof $ === 'undefined') {
            console.error('jQuery no está cargado');
            return;
        }

        let productosVentasChart = null;
        let productosLineChart = null;

        // Inicializar fechas por defecto
        initializeDates();

        // Event listeners para los botones
        $('#filtrar_producto').on('click', function(e) {
            e.preventDefault();
            aplicarFiltros();
        });

        $('#limpiar_producto').on('click', function(e) {
            e.preventDefault();
            limpiarFiltros();
        });

        // Inicializar fechas por defecto
        function initializeDates() {
            const today = new Date();
            const primerDiaMes = new Date(today.getFullYear(), today.getMonth(), 1);
            const formatDate = (d) => d.toISOString().slice(0, 10);

            const $fechaInicio = $('#fecha_inicio_producto');
            const $fechaFin = $('#fecha_fin_producto');

            if ($fechaInicio.length && !$fechaInicio.val()) {
                $fechaInicio.val(formatDate(primerDiaMes));
            }
            if ($fechaFin.length && !$fechaFin.val()) {
                $fechaFin.val(formatDate(today));
            }
        }

        // Función simplificada para aplicar filtros
        function aplicarFiltros() {
            const fechaInicio = $('#fecha_inicio_producto').val();
            const fechaFin = $('#fecha_fin_producto').val();

            // Mostrar loading
            $('#filtrar_producto').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Cargando...');

            // Petición AJAX optimizada
            $.ajax({
                url: '{{ route('api.reportes.ventas')}}',
                method: 'GET',
                data: {
                    fecha_inicio_producto: fechaInicio || '',
                    fecha_fin_producto: fechaFin || ''
                },
                dataType: 'json',
                success: function(response) {
                    if (response && !response.error) {
                        actualizarInterfaz(response);
                    } else {
                        mostrarError(response.mensaje || 'Error desconocido');
                    }
                },
                error: function(xhr) {
                    const mensaje = xhr.status === 500 ? 'Error interno del servidor' : 'Error de conexión';
                    mostrarError(mensaje);
                },
                complete: function() {
                    $('#filtrar_producto').prop('disabled', false).html('<i class="fas fa-search me-2"></i> Buscar Datos');
                }
            });
        }

        // Función para limpiar filtros
        function limpiarFiltros() {
            const today = new Date();
            const primerDiaMes = new Date(today.getFullYear(), today.getMonth(), 1);
            const formatDate = (d) => d.toISOString().slice(0, 10);

            $('#fecha_inicio_producto').val(formatDate(primerDiaMes));
            $('#fecha_fin_producto').val(formatDate(today));
            aplicarFiltros();
        }

        // Función para actualizar toda la interfaz con datos del backend
        function actualizarInterfaz(response) {
            // Actualizar estadísticas del header (viene procesado del backend)
            if (response.estadisticas) {
                $('#header_total_productos').text(response.estadisticas.total_productos);
                $('#header_total_ventas').text(response.estadisticas.total_ventas_formateado);
                $('#stat_total_productos').text(response.estadisticas.total_productos);
                $('#stat_total_unidades').text(response.estadisticas.total_unidades_formateado);
                $('#stat_total_ingresos').text(response.estadisticas.total_ventas_formateado);
                $('#stat_precio_promedio').text(response.estadisticas.precio_promedio_formateado);
            }

            // Renderizar indicador de rango en el frontend con datos estructurados
            if (response.indicador) {
                const ind = response.indicador;
                let html = '';
                const fmt = (d) => {
                    if (!d) return '';
                    const dd = new Date(d);
                    return dd.toLocaleDateString('es-PE');
                };
                if (ind.tipo === 'por_defecto') {
                    html = `<small class="badge bg-light text-dark px-3 py-1"><i class="fas fa-calendar-alt me-1"></i>Datos por defecto: <strong>${fmt(ind.desde)} - ${fmt(ind.hasta)}</strong> <span class="text-muted">(Mes actual)</span></small>`;
                } else if (ind.tipo === 'personalizado') {
                    html = `<small class="badge bg-info text-white px-3 py-1"><i class="fas fa-calendar-alt me-1"></i>Rango personalizado: <strong>${fmt(ind.desde)} - ${fmt(ind.hasta)}</strong></small>`;
                } else if (ind.tipo === 'desde') {
                    html = `<small class="badge bg-info text-white px-3 py-1"><i class="fas fa-calendar-alt me-1"></i>Desde: <strong>${fmt(ind.desde)}</strong></small>`;
                } else if (ind.tipo === 'hasta') {
                    html = `<small class="badge bg-info text-white px-3 py-1"><i class="fas fa-calendar-alt me-1"></i>Hasta: <strong>${fmt(ind.hasta)}</strong></small>`;
                } else {
                    html = `<small class="badge bg-warning text-dark px-3 py-1"><i class="fas fa-calendar-alt me-1"></i><strong>Todos los datos históricos</strong></small>`;
                }
                $('.mt-2').html(html);
            }

            // Renderizar tabla en el frontend
            if (response.productos && response.productos.labels && response.productos.labels.length) {
                const productos = [];
                const labels = response.productos.labels;
                const ventas = response.productos.ventas || [];
                const unidades = response.productos.unidades || [];
                for (let i = 0; i < labels.length; i++) {
                    productos.push({
                        nombre: labels[i],
                        ventas: ventas[i] || 0,
                        unidades: unidades[i] || 0
                    });
                }
                productos.sort((a, b) => b.ventas - a.ventas);

                const totalVentas = ventas.reduce((a, b) => a + (b || 0), 0);
                let html = '';
                productos.forEach((p, idx) => {
                    const porcentaje = totalVentas > 0 ? (p.ventas / totalVentas) * 100 : 0;
                    const precioProm = p.unidades > 0 ? (p.ventas / p.unidades) : 0;
                    let badgeClass = 'bg-secondary';
                    if (idx === 0) badgeClass = 'bg-warning';
                    else if (idx === 1) badgeClass = 'bg-secondary';
                    else if (idx === 2) badgeClass = 'bg-info';
                    else if (idx < 10) badgeClass = 'bg-success';
                    html += `
                        <tr class="border-bottom">
                            <td class="text-center py-3">
                                <span class="badge ${badgeClass} px-3 py-2 fs-6">${idx===0?'🥇':idx===1?'🥈':idx===2?'🥉':''} ${idx+1}</span>
                            </td>
                            <td class="py-3">
                                <div class="d-flex align-items-center">
                                    <div class="me-3">
                                        <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                            <i class="fas fa-box"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 fw-bold">${$('<div>').text(p.nombre).html()}</h6>
                                        <small class="text-muted">Ranking: #${idx+1}</small>
                                    </div>
                                </div>
                            </td>
                            <td class="text-center py-3">
                                <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 fs-6">${(p.unidades||0).toLocaleString()}</span>
                            </td>
                            <td class="text-end py-3">
                                <h6 class="mb-0 text-success fw-bold">S/ ${(p.ventas||0).toLocaleString('es-PE', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</h6>
                            </td>
                            <td class="text-end py-3"><span class="fw-medium">S/ ${precioProm.toLocaleString('es-PE', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</span></td>
                            <td class="text-center py-3">
                                <div class="progress" style="height: 25px; background-color: #e9ecef;">
                                    <div class="progress-bar bg-success progress-bar-striped" role="progressbar" style="width: ${porcentaje}%;" aria-valuenow="${porcentaje}" aria-valuemin="0" aria-valuemax="100">
                                        <span class="fw-bold">${porcentaje.toFixed(1)}%</span>
                                    </div>
                                </div>
                            </td>
                        </tr>`;
                });
                $('#tabla_productos').html(html);
            } else {
                $('#tabla_productos').html(`
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <div class="text-muted">
                                <i class="fas fa-inbox fa-3x mb-3 opacity-25"></i>
                                <h5>No hay datos disponibles</h5>
                                <p>Ajusta los filtros para mostrar información</p>
                            </div>
                        </td>
                    </tr>`);
            }

            // Actualizar totales de tabla
            if (response.productos && response.productos.ventas && response.productos.unidades) {
                const totalVentas = response.productos.ventas.reduce((a, b) => a + b, 0);
                const totalUnidades = response.productos.unidades.reduce((a, b) => a + b, 0);
                const promedio = totalUnidades > 0 ? totalVentas / totalUnidades : 0;

                $('#total_unidades').text(totalUnidades.toLocaleString());
                $('#total_ventas').text('S/ ' + totalVentas.toLocaleString('es-PE', {
                    minimumFractionDigits: 2
                }));
                $('#promedio_precio').text('S/ ' + promedio.toLocaleString('es-PE', {
                    minimumFractionDigits: 2
                }));
            }

            // Actualizar gráficos con datos procesados del backend
            actualizarGraficos(response);
        }

        // Función simplificada para actualizar gráficos
        function actualizarGraficos(response) {
            // Destruir gráficos existentes
            if (productosVentasChart) productosVentasChart.destroy();
            if (productosLineChart) productosLineChart.destroy();

            // Si no hay datos, crear gráficos vacíos
            if (!response.productos_procesados || !response.productos_procesados.labels.length) {
                crearGraficosVacios();
                return;
            }

            // Crear gráfico de barras con datos procesados del backend
            crearGraficoVentas(response);

            // Crear gráfico Pareto con datos procesados del backend  
            crearGraficoPareto(response);
        }

        // Crear gráfico de ventas simplificado
        function crearGraficoVentas(response) {
            const ctx = document.getElementById('productosVentasChart');
            if (!ctx) return;

            const data = response.productos_procesados;
            const config = response.configuracion_grafico;

            // Ajustar altura del canvas
            ctx.style.height = config.altura + 'px';

            productosVentasChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: data.labels,
                    datasets: [{
                        label: 'Ventas por Producto (S/)',
                        data: data.ventas,
                        backgroundColor: data.colores,
                        borderWidth: config.borderWidth,
                        borderRadius: config.borderRadius
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    indexAxis: 'y',
                    scales: {
                        x: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return formatearMoneda(value);
                                }
                            }
                        },
                        y: {
                            ticks: {
                                font: {
                                    size: config.fontSizeY
                                },
                                callback: function(value, index) {
                                    const label = this.getLabelForValue(value);
                                    const ranking = `#${index + 1}`;
                                    const displayLabel = label.length > config.maxChars ?
                                        label.substring(0, config.maxChars - 3) + '...' : label;
                                    return `${ranking} ${displayLabel}`;
                                }
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                title: function(context) {
                                    const ranking = context[0].dataIndex + 1;
                                    const icons = ['🏆', '🥈', '🥉'];
                                    const icon = icons[ranking - 1] || '📊';
                                    return `${icon} #${ranking} - ${context[0].label.replace(/^#\d+\s/, '')}`;
                                },
                                label: function(context) {
                                    const value = context.parsed.x;
                                    return `💰 Ventas: ${formatearMoneda(value)}`;
                                }
                            }
                        }
                    }
                }
            });
        }

        // Crear gráfico Pareto simplificado
        function crearGraficoPareto(response) {
            const ctx = document.getElementById('productosLineChart');
            if (!ctx || !response.datos_pareto) return;

            const paretoData = response.datos_pareto;

            productosLineChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: paretoData.labels,
                    datasets: [{
                        label: 'Porcentaje Acumulado de Ventas',
                        data: paretoData.porcentajes_acumulados,
                        borderColor: 'rgba(23, 162, 184, 1)',
                        backgroundColor: 'rgba(23, 162, 184, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        pointRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100,
                            ticks: {
                                callback: function(value) {
                                    return value + '%';
                                }
                            }
                        }
                    },
                    plugins: {
                        title: {
                            display: true,
                            text: `Análisis Pareto - Distribución de Ventas (${paretoData.total_productos} productos)`
                        }
                    }
                }
            });
        }

        // Crear gráficos vacíos
        function crearGraficosVacios() {
            const ctxVentas = document.getElementById('productosVentasChart');
            const ctxPareto = document.getElementById('productosLineChart');

            if (ctxVentas) {
                productosVentasChart = new Chart(ctxVentas, {
                    type: 'bar',
                    data: {
                        labels: ['Sin datos'],
                        datasets: [{
                            label: 'Sin datos',
                            data: [0],
                            backgroundColor: 'rgba(108, 117, 125, 0.3)'
                        }]
                    },
                    options: {
                        responsive: true,
                        indexAxis: 'y',
                        plugins: {
                            title: {
                                display: true,
                                text: 'No hay datos disponibles'
                            }
                        }
                    }
                });
            }

            if (ctxPareto) {
                productosLineChart = new Chart(ctxPareto, {
                    type: 'line',
                    data: {
                        labels: ['Sin datos'],
                        datasets: [{
                            label: 'Sin datos',
                            data: [0],
                            borderColor: 'rgba(23, 162, 184, 1)'
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            title: {
                                display: true,
                                text: 'No hay datos disponibles'
                            }
                        }
                    }
                });
            }
        }

        // Mostrar mensaje de error
        function mostrarError(mensaje) {
            $('#header_total_productos, #stat_total_productos').text('0');
            $('#header_total_ventas, #stat_total_ingresos').text('S/ 0');
            $('#stat_total_unidades').text('0');
            $('#stat_precio_promedio').text('S/ 0.00');

            $('#tabla_productos').html(`
                <tr><td colspan="6" class="text-center py-5">
                    <div class="text-muted">
                        <i class="fas fa-exclamation-triangle fa-3x mb-3 text-warning"></i>
                        <h5>Error al cargar datos</h5>
                        <p>${mensaje}</p>
                        <button class="btn btn-outline-primary mt-3" onclick="location.reload()">
                            <i class="fas fa-refresh me-2"></i>Recargar Página
                        </button>
                    </div>
                </td></tr>
            `);

            crearGraficosVacios();
        }

        // Función auxiliar para formatear moneda
        function formatearMoneda(value) {
            if (value >= 1000000) return 'S/ ' + (value / 1000000).toFixed(1) + 'M';
            if (value >= 1000) return 'S/ ' + (value / 1000).toFixed(1) + 'K';
            return 'S/ ' + value.toLocaleString('es-PE');
        }

        // Funciones de exportación simplificadas
        window.exportarTabla = () => alert('Función de exportación lista para implementar');
        window.compartirReporte = () => {
            if (navigator.share) {
                navigator.share({
                    title: 'Reporte de Productos',
                    url: window.location.href
                });
            } else {
                navigator.clipboard.writeText(window.location.href);
                alert('Link copiado al portapapeles');
            }
        };

        // Inicializar al cargar la página si hay datos
        @if(isset($data['productos']) && !empty($data['productos']['labels']))
        const datosIniciales = @json($data['productos']);
        if (datosIniciales && datosIniciales.labels && datosIniciales.labels.length > 0) {
            // Simular respuesta del backend para inicialización
            const respuestaSimulada = {
                productos: datosIniciales,
                productos_procesados: datosIniciales,
                estadisticas: {
                    total_productos: datosIniciales.labels.length,
                    total_ventas: datosIniciales.ventas.reduce((a, b) => a + b, 0),
                    total_unidades: datosIniciales.unidades.reduce((a, b) => a + b, 0),
                    total_ventas_formateado: 'S/ ' + datosIniciales.ventas.reduce((a, b) => a + b, 0).toLocaleString('es-PE'),
                    total_unidades_formateado: datosIniciales.unidades.reduce((a, b) => a + b, 0).toLocaleString(),
                    precio_promedio: datosIniciales.unidades.reduce((a, b) => a + b, 0) > 0 ?
                        datosIniciales.ventas.reduce((a, b) => a + b, 0) / datosIniciales.unidades.reduce((a, b) => a + b, 0) : 0,
                    precio_promedio_formateado: 'S/ ' + (datosIniciales.unidades.reduce((a, b) => a + b, 0) > 0 ?
                        (datosIniciales.ventas.reduce((a, b) => a + b, 0) / datosIniciales.unidades.reduce((a, b) => a + b, 0)).toFixed(2) : '0.00')
                },
                configuracion_grafico: {
                    altura: Math.max(500, datosIniciales.labels.length * 25),
                    fontSizeY: datosIniciales.labels.length > 50 ? 10 : 12,
                    maxChars: datosIniciales.labels.length > 50 ? 35 : 45,
                    borderWidth: 2,
                    borderRadius: 4
                },
                datos_pareto: null, // Se calculará después
                indicador: null
            };
            actualizarInterfaz(respuestaSimulada);
        }
        @endif
    });
</script>


</script>