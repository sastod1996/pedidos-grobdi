@php
    $defaultRangeA = [
        'start' => now()->subMonths(2)->format('Y-m'),
        'end' => now()->subMonth()->format('Y-m'),
    ];

    $defaultRangeB = [
        'start' => now()->subMonth()->format('Y-m'),
        'end' => now()->format('Y-m'),
    ];

    $monthLabels = [
        '01' => 'Ene',
        '02' => 'Feb',
        '03' => 'Mar',
        '04' => 'Abr',
        '05' => 'May',
        '06' => 'Jun',
        '07' => 'Jul',
        '08' => 'Ago',
        '09' => 'Sep',
        '10' => 'Oct',
        '11' => 'Nov',
        '12' => 'Dic',
    ];

    $formatRangeLabel = static function (array $range) use ($monthLabels): string {
        [$startYear, $startMonth] = explode('-', $range['start']);
        [$endYear, $endMonth] = explode('-', $range['end']);

        return sprintf(
            '%s %s - %s %s',
            $monthLabels[$startMonth] ?? $startMonth,
            $startYear,
            $monthLabels[$endMonth] ?? $endMonth,
            $endYear,
        );
    };

    $defaultRangeLabel = $formatRangeLabel($defaultRangeA) . ' vs ' . $formatRangeLabel($defaultRangeB);
@endphp
<div class="grobdi-header">
    <div class="grobdi-title">
        <div>
            <h2>Reporte de Seguimiento de Doctores</h2>
            <p>Analiza variaciones en ventas por monto y cantidad de pedidos para los 10 doctores con mayor impacto.</p>
        </div>
        <button type="button" class="btn-grobdi btn-outline-primary-grobdi" id="seguimientoReset">
            <i class="fas fa-undo-alt"></i>
            Restablecer filtros
        </button>
    </div>

    <div class="grobdi-filter">
        <form id="seguimientoFilterForm" data-fetch-endpoint="{{ route('reports.doctores.seguimiento') }}">
            <div class="row">
                <div class="col-lg-6">
                    <div class="card-grobdi h-100">
                        <div class="card-header-grobdi">Periodo base</div>
                        <div class="card-body-grobdi">
                            <div class="form-group-grobdi">
                                <label for="range_a_start">Mes inicial</label>
                                <input type="month" id="range_a_start" name="range_a_start"
                                    value="{{ $defaultRangeA['start'] }}" data-default="{{ $defaultRangeA['start'] }}">
                            </div>
                            <div class="form-group-grobdi">
                                <label for="range_a_end">Mes final</label>
                                <input type="month" id="range_a_end" name="range_a_end"
                                    value="{{ $defaultRangeA['end'] }}" data-default="{{ $defaultRangeA['end'] }}">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card-grobdi h-100">
                        <div class="card-header-grobdi">Periodo comparativo</div>
                        <div class="card-body-grobdi">
                            <div class="form-group-grobdi">
                                <label for="range_b_start">Mes inicial</label>
                                <input type="month" id="range_b_start" name="range_b_start"
                                    value="{{ $defaultRangeB['start'] }}" data-default="{{ $defaultRangeB['start'] }}">
                            </div>
                            <div class="form-group-grobdi">
                                <label for="range_b_end">Mes final</label>
                                <input type="month" id="range_b_end" name="range_b_end"
                                    value="{{ $defaultRangeB['end'] }}" data-default="{{ $defaultRangeB['end'] }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-actions justify-end">
                <button type="submit" class="btn-grobdi btn-primary-grobdi" id="seguimientoApply">
                    <i class="fas fa-sliders-h"></i>
                    Comparar rangos
                </button>
            </div>
        </form>
    </div>
</div>

<div class="row">
    <div class="col-xl-4 col-md-6">
        <div class="card-grobdi">
            <div class="card-body-grobdi">
                <span class="badge-grobdi badge-red text-uppercase">Promedio negativo</span>
                <h3 class="mt-2 mb-2" id="seguimientoAvgNegativeAmount">--</h3>
                <p class="mb-2 text-muted mb-lg-3">Monto promedio que se reduce por doctor.</p>
                <p class="mb-0 font-weight-bold text-muted">Cantidad promedio de pedidos:
                    <span id="seguimientoAvgNegativeQuantity">--</span>
                </p>
            </div>
        </div>
    </div>
    <div class="col-xl-4 col-md-6">
        <div class="card-grobdi">
            <div class="card-body-grobdi">
                <span class="badge-grobdi badge-green text-uppercase">Promedio positivo</span>
                <h3 class="mt-2 mb-2" id="seguimientoAvgPositiveAmount">--</h3>
                <p class="mb-2 text-muted mb-lg-3">Monto promedio adicional por doctor.</p>
                <p class="mb-0 font-weight-bold text-muted">Cantidad promedio de pedidos:
                    <span id="seguimientoAvgPositiveQuantity">--</span>
                </p>
            </div>
        </div>
    </div>
    <div class="col-xl-4 col-md-6">
        <div class="card-grobdi">
            <div class="card-body-grobdi">
                <span class="badge-grobdi badge-yellow text-uppercase">Cobertura del analisis</span>
                <h3 class="mt-2 mb-1"><span id="seguimientoTotalDoctors">0</span> doctores</h3>
                <p class="mb-0 text-muted">Incluye top positivos y negativos para el comparativo actual.</p>
            </div>
        </div>
    </div>
</div>

<div class="card-grobdi">
    <div class="card-header-grobdi">
        Detalle del comparativo
    </div>
    <div class="card-body-grobdi">
        <div
            class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-3">
            <div>
                <h5 class="mb-1">Rangos analizados</h5>
                <p class="mb-0 text-muted" id="seguimientoRangeLabel" data-default-label="{{ $defaultRangeLabel }}">
                    {{ $defaultRangeLabel }}
                </p>
            </div>
            <div class="d-flex flex-column align-items-md-end gap-2 w-100 w-md-auto">
                <div class="d-flex align-items-center flex-wrap gap-2">
                    <span class="text-muted small mb-0">Ver por:</span>
                    <div class="btn-group btn-group-sm" role="group" aria-label="Filtro por metricas">
                        <button type="button" class="btn-grobdi btn-outline-primary-grobdi active"
                            data-seguimiento-metric="amount" aria-pressed="true">
                            Montos
                        </button>
                        <button type="button" class="btn-grobdi btn-outline-primary-grobdi"
                            data-seguimiento-metric="quantity" aria-pressed="false">
                            Cantidades
                        </button>
                    </div>
                </div>
                <div class="text-muted small" id="seguimientoMetricStatusWrapper">
                    Mostrando por: <span class="font-weight-bold text-body" id="seguimientoMetricStatus">Montos</span>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-6">
                <div class="card-grobdi h-100">
                    <div class="card-header-grobdi d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-arrow-trend-up mr-2"></i>Top incrementos por doctor</span>
                        <span class="badge-grobdi badge-green">Valores positivos</span>
                    </div>
                    <div class="card-body-grobdi">
                        <div class="chart-wrapper" style="height: 420px;">
                            <canvas id="chartSeguimientoMax"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card-grobdi h-100">
                    <div class="card-header-grobdi d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-arrow-trend-down mr-2"></i>Top caidas por doctor</span>
                        <span class="badge-grobdi badge-red">Valores negativos</span>
                    </div>
                    <div class="card-body-grobdi">
                        <div class="chart-wrapper" style="height: 420px;">
                            <canvas id="chartSeguimientoMin"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card-grobdi">
    <div
        class="card-header-grobdi d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center">
        <div>
            <span class="badge-grobdi badge-blue text-uppercase">Tabla de doctores</span>
            <h4 class="mt-2 mb-0">Tabla comparativa de doctores</h4>
        </div>
        <span class="badge-grobdi badge-gray" id="seguimientoTableSummary">En espera de datos</span>
        <div>
            <label for="">Ordenar por: </label>
            <button class="btn-grobdi btn-outline-primary-grobdi btn-sm" id="order_table">
                Negativos
            </button>
        </div>
    </div>
    <div class="card-body-grobdi responsive-table-wrapper">
        <table class="table table-grobdi table-striped table-hover mt-4 mb-0">
            <thead>
                <tr>
                    <th scope="col">#</th>
                    <th scope="col" class="text-left">Doctor</th>
                    <th scope="col" data-metric="quantity">Pedidos del filtro 1</th>
                    <th scope="col" data-metric="quantity">Pedidos del filtro 2</th>
                    <th scope="col" data-metric="quantity">Margen de diferencia</th>
                    <th scope="col" data-metric="amount">Monto 1</th>
                    <th scope="col" data-metric="amount">Monto 2</th>
                    <th scope="col" data-metric="amount">Diferencia de monto</th>
                </tr>
            </thead>
            <tbody id="seguimientoDoctorTableBody">
                <tr>
                    <td colspan="8" class="text-center text-muted py-4">Cargando información...</td>
                </tr>
            </tbody>
        </table>

    </div>
</div>

<div class="card-grobdi mt-4">
    <div
        class="card-header-grobdi d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center">
        <div>
            <span class="badge-grobdi badge-blue text-uppercase">Evolucion mensual</span>
            <h4 class="mt-2 mb-0">Tendencia acumulada por mes</h4>
        </div>
        <span class="badge-grobdi badge-gray" id="seguimientoTrendSummary">En espera de datos</span>
    </div>
    <div class="card-body-grobdi">
        <p class="text-muted mb-4">Visualiza como se comportan los incrementos y descensos mes a mes segun la metrica
            seleccionada. Los valores negativos se muestran por debajo de la linea base.</p>
        <div class="chart-wrapper" style="height: 360px;">
            <canvas id="chartSeguimientoTrend"></canvas>
        </div>
    </div>
</div>

@push('partial-js')
    <script src="{{ asset('js/reports/doctores/seguimiento.js') }}" defer></script>
@endpush
