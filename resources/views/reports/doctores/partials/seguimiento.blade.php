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
        <form id="seguimientoFilterForm">
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
    <script>
        (() => {
            const doctorNameCache = new Map();

            const initSeguimientoModule = () => {
            // Estado global de la aplicación
            let appState = {
                filters: {},
                initialFilters: null,
                initialRequestFilters: null,
                lastRequestFilters: {},
                data: null,
                charts: {
                    positive: null,
                    negative: null,
                    trend: null
                }
            };

            const cssRoot = getComputedStyle(document.documentElement);
            const colors = {
                amountPrimary: cssRoot.getPropertyValue('--grobdi-navy-900').trim() || '#0f172a',
                amountSecondary: cssRoot.getPropertyValue('--grobdi-navy-700').trim() || '#1e293b',
                quantityPrimary: cssRoot.getPropertyValue('--grobdi-red-500').trim() || '#ef4444',
                quantitySecondary: cssRoot.getPropertyValue('--grobdi-red-600').trim() || '#dc2626',
                grid: cssRoot.getPropertyValue('--grobdi-slate-200').trim() || '#e5e7eb',
                text: cssRoot.getPropertyValue('--grobdi-text-base').trim() || '#1f2937',
                muted: cssRoot.getPropertyValue('--grobdi-slate-400').trim() || '#9ca3af'
            };

            const hexToRGBA = (hex, alpha = 0.2) => {
                if (!hex) {
                    return `rgba(15, 23, 42, ${alpha})`;
                }

                let sanitized = hex.trim();
                if (sanitized.startsWith('var(')) {
                    return `rgba(15, 23, 42, ${alpha})`;
                }

                if (sanitized.startsWith('#')) {
                    sanitized = sanitized.slice(1);
                }

                if (sanitized.length === 3) {
                    sanitized = sanitized.split('').map(char => char + char).join('');
                }

                if (sanitized.length !== 6) {
                    return `rgba(15, 23, 42, ${alpha})`;
                }

                const numeric = parseInt(sanitized, 16);
                const r = (numeric >> 16) & 255;
                const g = (numeric >> 8) & 255;
                const b = numeric & 255;

                return `rgba(${r}, ${g}, ${b}, ${alpha})`;
            };

            const currencyFormatter = new Intl.NumberFormat('es-PE', {
                style: 'currency',
                currency: 'PEN',
                maximumFractionDigits: 0
            });

            const quantityFormatter = new Intl.NumberFormat('es-PE', {
                maximumFractionDigits: 0
            });

            const monthFormatter = new Intl.DateTimeFormat('es-PE', {
                month: 'short',
                year: 'numeric'
            });

            const getFallbackDoctorName = idDoctor => (idDoctor ? `Doctor ${idDoctor}` : 'Sin identificar');

            const ensureDoctorNames = processedData => {
                if (!processedData) {
                    return;
                }

                const collections = [
                    processedData.doctorComparisons || [],
                    processedData.positiveByAmount || [],
                    processedData.negativeByAmount || [],
                    processedData.positiveByQuantity || [],
                    processedData.negativeByQuantity || []
                ];

                const applyResolvedName = doctor => {
                    if (!doctor || !doctor.id_doctor) {
                        return;
                    }

                    const preferredName = doctor.name || doctor.doctor || doctor.doctor_name || doctorNameCache.get(doctor.id_doctor) || getFallbackDoctorName(doctor.id_doctor);

                    doctorNameCache.set(doctor.id_doctor, preferredName);
                    doctor.name = preferredName;
                    doctor.doctor = preferredName;
                    doctor.doctor_name = preferredName;
                };

                collections.forEach(group => {
                    group.forEach(applyResolvedName);
                });
            };

                const parseDateValue = value => {
                    if (!value) {
                        return null;
                    }
                    // Soporta 'YYYY-MM' convirtiéndolo al primer día del mes
                    if (typeof value === 'string' && /^\d{4}-\d{2}$/.test(value)) {
                        value = `${value}-01`;
                    }
                    const parsed = new Date(value);
                    return Number.isNaN(parsed.getTime()) ? null : parsed;
                };

                const buildRangeLabel = (start, end) => {
                    const startDate = parseDateValue(start);
                    const endDate = parseDateValue(end);

                    if (startDate && endDate) {
                        return `${monthFormatter.format(startDate)} - ${monthFormatter.format(endDate)}`;
                    }

                    if (startDate) {
                        return monthFormatter.format(startDate);
                    }

                    if (endDate) {
                        return monthFormatter.format(endDate);
                    }

                    return '';
                };

                const formatSignedCurrency = value => {
                    const numeric = Number(value) || 0;
                    if (numeric === 0) {
                        return currencyFormatter.format(0);
                    }

                    const prefix = numeric > 0 ? '+ ' : '- ';
                    return `${prefix}${currencyFormatter.format(Math.abs(numeric))}`;
                };

                const formatSignedQuantity = value => {
                    const numeric = Number(value) || 0;
                    if (numeric === 0) {
                        return '0';
                    }

                    const prefix = numeric > 0 ? '+ ' : '- ';
                    return `${prefix}${quantityFormatter.format(Math.abs(numeric))}`;
                };

            if (typeof Chart === 'undefined') {
                console.warn('Chart.js no disponible para el comparativo de seguimiento.');
                return;
            }

            const filterForm = document.getElementById('seguimientoFilterForm');
            const rangeLabel = document.getElementById('seguimientoRangeLabel');
            const resetButton = document.getElementById('seguimientoReset');
            const metricButtons = document.querySelectorAll('[data-seguimiento-metric]');
            const metricStatus = document.getElementById('seguimientoMetricStatus');

            const defaultLabel = rangeLabel?.dataset?.defaultLabel || '';
            const metricLabels = {
                amount: 'Montos',
                quantity: 'Cantidades'
            };

            const allowedFilterKeys = ['start_date_1', 'end_date_1', 'start_date_2', 'end_date_2'];

            const extractRequestFilters = (source = {}) => allowedFilterKeys.reduce((acc, key) => {
                const value = source?.[key];
                if (value) {
                    acc[key] = value;
                }
                return acc;
            }, {});

            const metricSettings = {
                amount: {
                    key: 'amount_diff',
                    label: 'Variacion en montos (S/)',
                    tooltipLabel: 'Monto',
                    format: value => currencyFormatter.format(value)
                },
                quantity: {
                    key: 'quantity_diff',
                    label: 'Variacion en pedidos',
                    tooltipLabel: 'Pedidos',
                    format: value => `${quantityFormatter.format(value)} pedidos`
                }
            };

            const chartPalette = {
                positives: {
                    amount: colors.amountPrimary,
                    quantity: colors.quantityPrimary
                },
                negatives: {
                    amount: colors.amountSecondary,
                    quantity: colors.quantitySecondary
                }
            };

            const trendMetricSettings = {
                amount: {
                    positiveKey: 'positive_amount',
                    negativeKey: 'negative_amount',
                    positiveLabel: 'Montos positivos',
                    negativeLabel: 'Montos negativos',
                    formatter: value => currencyFormatter.format(value),
                    colors: {
                        positive: colors.amountPrimary,
                        negative: colors.amountSecondary
                    }
                },
                quantity: {
                    positiveKey: 'positive_quantity',
                    negativeKey: 'negative_quantity',
                    positiveLabel: 'Pedidos en incremento',
                    negativeLabel: 'Pedidos en descenso',
                    formatter: value => `${quantityFormatter.format(value)} pedidos`,
                    colors: {
                        positive: colors.quantityPrimary,
                        negative: colors.quantitySecondary
                    }
                }
            };

            const formatRange = (start, end) => {
                if (!start || !end) {
                    return '';
                }

                const [startYear, startMonth] = start.split('-').map(Number);
                const [endYear, endMonth] = end.split('-').map(Number);

                const startDate = new Date(startYear, startMonth - 1);
                const endDate = new Date(endYear, endMonth - 1);

                return `${monthFormatter.format(startDate)} - ${monthFormatter.format(endDate)}`;
            };

            const getFirstDayOfMonth = value => {
                if (!value) {
                    return null;
                }

                const [year, month] = value.split('-');
                if (!year || !month) {
                    return null;
                }

                return `${year}-${month}-01`;
            };

            const getLastDayOfMonth = value => {
                if (!value) {
                    return null;
                }

                const [yearRaw, monthRaw] = value.split('-').map(Number);
                if (!yearRaw || !monthRaw) {
                    return null;
                }

                const date = new Date(yearRaw, monthRaw, 0);
                const day = String(date.getDate()).padStart(2, '0');
                return `${yearRaw}-${String(monthRaw).padStart(2, '0')}-${day}`;
            };

            const applyMetricToTables = metric => {
                document.querySelectorAll('[data-metric]').forEach(element => {
                    element.hidden = element.dataset.metric !== metric;
                });
            };

            const updateMetricStatus = metric => {
                if (!metricStatus) {
                    return;
                }

                metricStatus.textContent = metricLabels[metric] || '';
            };

            const setActiveMetricButton = metric => {
                metricButtons.forEach(button => {
                    const isActive = button.dataset.seguimientoMetric === metric;
                    button.classList.toggle('active', isActive);
                    button.setAttribute('aria-pressed', isActive ? 'true' : 'false');
                });
            };
            //Correccion de la implementacion de interfaz porque no carga los pedidos positivos, soluccion hacer consulta con la base de datos
            const updateHorizontalChart = (chart, dataset, metricKey, paletteKey) => {
                if (!chart || !metricSettings[metricKey]) {
                    return;
                }

                const metric = metricSettings[metricKey];
                const values = dataset.map(item => Number(item[metric.key]) || 0);
                const labels = dataset.map(item => item.name);

                const minValue = Math.min(...values);
                const maxValue = Math.max(...values);
                const padding = (Math.abs(maxValue - minValue) || 1) * 0.15;

                chart.data.labels = labels;
                chart.data.datasets[0].data = values;
                chart.data.datasets[0].label = metric.label;
                chart.data.datasets[0].backgroundColor = chartPalette[paletteKey][metricKey];

                chart.options.scales.x.min = minValue < 0 ? minValue - padding : 0;
                chart.options.scales.x.max = maxValue > 0 ? maxValue + padding : 0;
                chart.options.scales.x.ticks.callback = value => metric.format(value);
                chart.options.plugins.tooltip.callbacks.label = context =>
                    `${metric.tooltipLabel}: ${metric.format(context.parsed.x)}`;

                chart.update();
            };

            //Verificar para que funciona este metodo
            const buildHorizontalChart = (canvasId, dataset, metricKey, paletteKey) => {
                const canvas = document.getElementById(canvasId);
                if (!canvas || !metricSettings[metricKey]) {
                    return null;
                }

                const metric = metricSettings[metricKey];



                const chart = new Chart(canvas.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: dataset.map(item => item.name),
                        datasets: [{
                            label: metric.label,
                            data: dataset.map(item => Number(item[metric.key]) || 0),
                            backgroundColor: chartPalette[paletteKey][metricKey],
                            borderRadius: 8,
                            maxBarThickness: 34
                        }]
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: {
                            mode: 'nearest',
                            axis: 'y',
                            intersect: false
                        },
                        scales: {
                            x: {
                                grid: {
                                    color: colors.grid,
                                    drawTicks: false
                                },
                                ticks: {
                                    color: colors.text,
                                    callback: value => metric.format(value)
                                }
                            },
                            y: {
                                offset: true,
                                ticks: {
                                    color: colors.text,
                                    font: {
                                        weight: '600',
                                        size: 12
                                    }
                                },
                                grid: {
                                    display: false
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                callbacks: {
                                    label: context =>
                                        `${metric.tooltipLabel}: ${metric.format(context.parsed.x)}`
                                }
                            }
                        }
                    }
                });

                updateHorizontalChart(chart, dataset, metricKey, paletteKey);
                return chart;
            };

            const updateMonthlyTrendChart = (chart, dataset, metricKey) => {
                if (!chart || !trendMetricSettings[metricKey]) {
                    return;
                }

                const config = trendMetricSettings[metricKey];
                const positiveData = dataset.map(item => Number(item[config.positiveKey]) || 0);
                const negativeData = dataset.map(item => Number(item[config.negativeKey]) || 0);

                const minValue = Math.min(...positiveData, ...negativeData);
                const maxValue = Math.max(...positiveData, ...negativeData);
                const padding = (Math.abs(maxValue - minValue) || 1) * 0.15;

                chart.data.datasets[0].label = config.positiveLabel;
                chart.data.datasets[0].data = positiveData;
                chart.data.datasets[0].borderColor = config.colors.positive;
                chart.data.datasets[0].pointBorderColor = config.colors.positive;
                chart.data.datasets[0].backgroundColor = hexToRGBA(config.colors.positive, 0.18);

                chart.data.datasets[1].label = config.negativeLabel;
                chart.data.datasets[1].data = negativeData;
                chart.data.datasets[1].borderColor = config.colors.negative;
                chart.data.datasets[1].pointBorderColor = config.colors.negative;
                chart.data.datasets[1].backgroundColor = hexToRGBA(config.colors.negative, 0.18);

                chart.options.scales.y.ticks.callback = value => config.formatter(value);
                chart.options.plugins.tooltip.callbacks.label = context =>
                    `${context.dataset.label}: ${config.formatter(context.parsed.y)}`;

                chart.options.scales.y.min = minValue - padding;
                chart.options.scales.y.max = maxValue + padding;

                chart.update();
            };

            const buildMonthlyTrendChart = (canvasId, dataset, metricKey) => {
                const canvas = document.getElementById(canvasId);
                if (!canvas || !dataset.length || !trendMetricSettings[metricKey]) {
                    return null;
                }

                const config = trendMetricSettings[metricKey];
                const ctx = canvas.getContext('2d');
                const labels = dataset.map(item => {
                    const [year, month] = item.month.split('-').map(Number);
                    return monthFormatter.format(new Date(year, month - 1));
                });

                const chart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels,
                        datasets: [{
                                label: config.positiveLabel,
                                data: dataset.map(item => Number(item[config.positiveKey]) || 0),
                                borderColor: config.colors.positive,
                                backgroundColor: hexToRGBA(config.colors.positive, 0.18),
                                borderWidth: 3,
                                pointRadius: 4,
                                pointHoverRadius: 6,
                                pointBackgroundColor: '#ffffff',
                                pointBorderColor: config.colors.positive,
                                tension: 0.35,
                                fill: true
                            },
                            {
                                label: config.negativeLabel,
                                data: dataset.map(item => Number(item[config.negativeKey]) || 0),
                                borderColor: config.colors.negative,
                                backgroundColor: hexToRGBA(config.colors.negative, 0.18),
                                borderWidth: 3,
                                pointRadius: 4,
                                pointHoverRadius: 6,
                                pointBackgroundColor: '#ffffff',
                                pointBorderColor: config.colors.negative,
                                tension: 0.35,
                                fill: true
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: {
                            mode: 'index',
                            intersect: false
                        },
                        scales: {
                            x: {
                                grid: {
                                    display: false
                                },
                                ticks: {
                                    color: colors.text
                                }
                            },
                            y: {
                                grid: {
                                    color: colors.grid
                                },
                                ticks: {
                                    color: colors.text,
                                    callback: value => config.formatter(value)
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                position: 'top',
                                labels: {
                                    usePointStyle: true,
                                    color: colors.text
                                }
                            },
                            tooltip: {
                                callbacks: {
                                    label: context =>
                                        `${context.dataset.label}: ${config.formatter(context.parsed.y)}`
                                }
                            }
                        }
                    }
                });

                updateMonthlyTrendChart(chart, dataset, metricKey);
                return chart;
            };

            let currentMetric = 'amount';

            // Función para obtener los datos del backend
            const fetchReportData = async (filters = {}) => {
                try {
                    const params = new URLSearchParams();

                    Object.entries(filters).forEach(([key, value]) => {
                        if (value) {
                            params.append(key, value);
                        }
                    });

                    const queryString = params.toString();
                    const endpoint = queryString
                        ? `{{ route('reports.doctores.seguimiento') }}?${queryString}`
                        : `{{ route('reports.doctores.seguimiento') }}`;

                    const response = await fetch(endpoint, {
                        method: 'GET',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }

                    const result = await response.json();
                    return result;
                } catch (error) {
                    console.error('Error al cargar los datos del reporte:', error);
                    return null;
                }
            };

            const normalizeDoctorEntry = (entry = {}) => {
                const idDoctor = entry.id_doctor ?? entry.id ?? null;
                const name = entry.doctor_name || entry.doctor || entry.name || getFallbackDoctorName(idDoctor);

                const prevAmount = Number(entry.prev_amount ?? entry.amount_filter_1 ?? 0);
                const currAmount = Number(entry.curr_amount ?? entry.amount_filter_2 ?? 0);
                const prevQuantity = Number(entry.prev_quantity ?? entry.quantity_filter_1 ?? 0);
                const currQuantity = Number(entry.curr_quantity ?? entry.quantity_filter_2 ?? 0);

                const amountDiff = Number(entry.amount_fluctuation ?? entry.amount_diff ?? (currAmount - prevAmount));
                const quantityDiff = Number(entry.quantity_fluctuation ?? entry.quantity_diff ?? (currQuantity - prevQuantity));

                return {
                    id_doctor: idDoctor,
                    name,
                    doctor: name,
                    doctor_name: name,
                    amount_filter_1: prevAmount,
                    amount_filter_2: currAmount,
                    amount_diff: amountDiff,
                    quantity_filter_1: prevQuantity,
                    quantity_filter_2: currQuantity,
                    quantity_diff: quantityDiff
                };
            };

            // Función para procesar los datos del backend
            const processBackendData = payload => {
                if (!payload) {
                    return null;
                }

                const {
                    top_stats: topStats = {},
                    general_Stats: generalStats = {},
                    data: rawComparisons = [],
                    filters: rawFilters = {}
                } = payload;

                // Asegurar que cualquier colección sea un array (el backend podría enviar objetos asociativos)
                const toArray = (v) => Array.isArray(v) ? v : Object.values(v || {});

                let positiveByAmount = toArray(topStats.amount_increase).map(normalizeDoctorEntry);
                let negativeByAmount = toArray(topStats.amount_decrease).map(normalizeDoctorEntry);
                let positiveByQuantity = toArray(topStats.quantity_increase).map(normalizeDoctorEntry);
                let negativeByQuantity = toArray(topStats.quantity_decrease).map(normalizeDoctorEntry);
                const doctorComparisons = toArray(rawComparisons).map(normalizeDoctorEntry);

                if (!doctorComparisons.length) {
                    const unique = new Map();
                    [...positiveByAmount, ...negativeByAmount, ...positiveByQuantity, ...negativeByQuantity].forEach(doctor => {
                        const key = doctor.id_doctor ?? doctor.name;
                        if (!unique.has(key)) {
                            unique.set(key, doctor);
                        }
                    });

                    doctorComparisons.push(...unique.values());
                }

                const buildSeriesFromComparisons = (collection, key, direction = 'positive') => {
                    return collection
                        .filter(item => {
                            const value = Number(item[key] || 0);
                            return direction === 'positive' ? value > 0 : value < 0;
                        })
                        .sort((a, b) => {
                            const valueA = Number(a[key] || 0);
                            const valueB = Number(b[key] || 0);
                            return direction === 'positive'
                                ? valueB - valueA
                                : valueA - valueB;
                        })
                        .slice(0, 10);
                };

                const fallbackPositiveAmount = buildSeriesFromComparisons(doctorComparisons, 'amount_diff', 'positive');
                const fallbackNegativeAmount = buildSeriesFromComparisons(doctorComparisons, 'amount_diff', 'negative');
                const fallbackPositiveQuantity = buildSeriesFromComparisons(doctorComparisons, 'quantity_diff', 'positive');
                const fallbackNegativeQuantity = buildSeriesFromComparisons(doctorComparisons, 'quantity_diff', 'negative');

                if (!positiveByAmount.length) {
                    positiveByAmount = fallbackPositiveAmount;
                }
                if (!negativeByAmount.length) {
                    negativeByAmount = fallbackNegativeAmount;
                }
                if (!positiveByQuantity.length) {
                    positiveByQuantity = fallbackPositiveQuantity;
                }
                if (!negativeByQuantity.length) {
                    negativeByQuantity = fallbackNegativeQuantity;
                }

                let positiveAmountDocs = doctorComparisons.filter(doc => doc.amount_diff > 0);
                let negativeAmountDocs = doctorComparisons.filter(doc => doc.amount_diff < 0);
                let positiveQuantityDocs = doctorComparisons.filter(doc => doc.quantity_diff > 0);
                let negativeQuantityDocs = doctorComparisons.filter(doc => doc.quantity_diff < 0);

                if (!positiveAmountDocs.length) {
                    positiveAmountDocs = [...fallbackPositiveAmount];
                }
                if (!negativeAmountDocs.length) {
                    negativeAmountDocs = [...fallbackNegativeAmount];
                }
                if (!positiveQuantityDocs.length) {
                    positiveQuantityDocs = [...fallbackPositiveQuantity];
                }
                if (!negativeQuantityDocs.length) {
                    negativeQuantityDocs = [...fallbackNegativeQuantity];
                }

                const computeAverage = (collection, key) => {
                    if (!collection.length) {
                        return 0;
                    }

                    const total = collection.reduce((sum, item) => sum + Number(item[key] || 0), 0);
                    return total / collection.length;
                };

                const averages = generalStats.averages || {};
                const firstAverage = averages.first || {};
                const secondAverage = averages.second || {};

                const stats = {
                    avgPositiveAmount: computeAverage(positiveAmountDocs, 'amount_diff'),
                    avgNegativeAmount: computeAverage(negativeAmountDocs, 'amount_diff'),
                    avgPositiveQuantity: computeAverage(positiveQuantityDocs, 'quantity_diff'),
                    avgNegativeQuantity: computeAverage(negativeQuantityDocs, 'quantity_diff'),
                    totalDoctors: Number(generalStats.total_doctores ?? doctorComparisons.length ?? 0),
                    totalPositiveAmountDoctors: positiveAmountDocs.length,
                    totalNegativeAmountDoctors: negativeAmountDocs.length,
                    totalPositiveQuantityDoctors: positiveQuantityDocs.length,
                    totalNegativeQuantityDoctors: negativeQuantityDocs.length,
                    firstAverageAmount: Number(firstAverage.amount ?? 0),
                    secondAverageAmount: Number(secondAverage.amount ?? 0),
                    firstAverageQuantity: Number(firstAverage.quantity ?? 0),
                    secondAverageQuantity: Number(secondAverage.quantity ?? 0)
                };

                const rangeLabels = {
                    first: buildRangeLabel(rawFilters.start_date_1, rawFilters.end_date_1),
                    second: buildRangeLabel(rawFilters.start_date_2, rawFilters.end_date_2)
                };

                const combinedLabel = rangeLabels.first && rangeLabels.second
                    ? `${rangeLabels.first} vs ${rangeLabels.second}`
                    : rangeLabels.first || rangeLabels.second || '';

                const comparisonsForTrend = doctorComparisons.length
                    ? doctorComparisons
                    : [...positiveByAmount, ...negativeByAmount, ...positiveByQuantity, ...negativeByQuantity];

                const monthlyPerformance = generateMonthlyTrend(rawFilters, comparisonsForTrend);

                return {
                    positiveByAmount,
                    negativeByAmount,
                    positiveByQuantity,
                    negativeByQuantity,
                    doctorComparisons,
                    monthlyPerformance,
                    stats,
                    filters: {
                        ...rawFilters,
                        rangeLabels,
                        combinedLabel
                    }
                };
            };

            // Función para generar tendencia mensual (usando totales acumulados)
            const generateMonthlyTrend = (filters = {}, doctors = []) => {
                const start = parseDateValue(filters.start_date_1) || parseDateValue(filters.start_date_2);
                const end = parseDateValue(filters.end_date_2) || parseDateValue(filters.end_date_1);

                if (!start || !end || start > end) {
                    return [];
                }

                const months = [];
                const cursor = new Date(start.getFullYear(), start.getMonth(), 1);
                const limit = new Date(end.getFullYear(), end.getMonth(), 1);

                while (cursor <= limit) {
                    months.push(`${cursor.getFullYear()}-${String(cursor.getMonth() + 1).padStart(2, '0')}`);
                    cursor.setMonth(cursor.getMonth() + 1);
                }

                if (!months.length) {
                    months.push(`${start.getFullYear()}-${String(start.getMonth() + 1).padStart(2, '0')}`);
                }

                const totals = doctors.reduce((acc, doctor) => {
                    const amountDiff = Number(doctor.amount_diff) || 0;
                    const quantityDiff = Number(doctor.quantity_diff) || 0;

                    if (amountDiff >= 0) {
                        acc.positiveAmount += amountDiff;
                    } else {
                        acc.negativeAmount += Math.abs(amountDiff);
                    }

                    if (quantityDiff >= 0) {
                        acc.positiveQuantity += quantityDiff;
                    } else {
                        acc.negativeQuantity += Math.abs(quantityDiff);
                    }

                    return acc;
                }, {
                    positiveAmount: 0,
                    negativeAmount: 0,
                    positiveQuantity: 0,
                    negativeQuantity: 0
                });

                return months.map((month, index) => {
                    const progress = (index + 1) / months.length;

                    return {
                        month,
                        positive_amount: Math.round(totals.positiveAmount * progress),
                        negative_amount: -Math.round(totals.negativeAmount * progress),
                        positive_quantity: Math.round(totals.positiveQuantity * progress),
                        negative_quantity: -Math.round(totals.negativeQuantity * progress)
                    };
                });
            };

            // Función para actualizar las estadísticas en el DOM
            const updateStatistics = stats => {
                const negativeAmountEl = document.getElementById('seguimientoAvgNegativeAmount');
                const negativeQuantityEl = document.getElementById('seguimientoAvgNegativeQuantity');
                const positiveAmountEl = document.getElementById('seguimientoAvgPositiveAmount');
                const positiveQuantityEl = document.getElementById('seguimientoAvgPositiveQuantity');
                const totalDoctorsEl = document.getElementById('seguimientoTotalDoctors');
                const trendSummaryEl = document.getElementById('seguimientoTrendSummary');

                if (negativeAmountEl) {
                    negativeAmountEl.textContent = formatSignedCurrency(stats.avgNegativeAmount);
                    negativeAmountEl.classList.toggle('text-success', stats.avgNegativeAmount > 0);
                    negativeAmountEl.classList.toggle('text-danger', stats.avgNegativeAmount < 0);
                }

                if (negativeQuantityEl) {
                    negativeQuantityEl.textContent = `${formatSignedQuantity(stats.avgNegativeQuantity)} pedidos`;
                }

                if (positiveAmountEl) {
                    positiveAmountEl.textContent = formatSignedCurrency(stats.avgPositiveAmount);
                    positiveAmountEl.classList.toggle('text-success', stats.avgPositiveAmount > 0);
                    positiveAmountEl.classList.toggle('text-danger', stats.avgPositiveAmount < 0);
                }

                if (positiveQuantityEl) {
                    positiveQuantityEl.textContent = `${formatSignedQuantity(stats.avgPositiveQuantity)} pedidos`;
                }

                if (totalDoctorsEl) {
                    totalDoctorsEl.textContent = quantityFormatter.format(stats.totalDoctors || 0);
                }

                if (trendSummaryEl) {
                    const amountLabel = `${currencyFormatter.format(stats.firstAverageAmount)} → ${currencyFormatter.format(stats.secondAverageAmount)}`;
                    const quantityLabel = `${quantityFormatter.format(stats.firstAverageQuantity)} → ${quantityFormatter.format(stats.secondAverageQuantity)}`;
                    trendSummaryEl.textContent = `Promedio monto: ${amountLabel} | Promedio pedidos: ${quantityLabel}`;
                }
            };

            // Función para actualizar la tabla de doctores
            const updateDoctorsTable = doctors => {
                const tbody = document.getElementById('seguimientoDoctorTableBody');
                const summary = document.getElementById('seguimientoTableSummary');

                if (!tbody) {
                    return;
                }

                if (!doctors.length) {
                    tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted py-4">No hay información disponible para los filtros seleccionados.</td></tr>';
                    if (summary) {
                        summary.textContent = 'Sin datos';
                    }
                    return;
                }

                tbody.innerHTML = doctors.map((doctor, index) => {
                    const amountDiff = Number(doctor.amount_diff) || 0;
                    const quantityDiff = Number(doctor.quantity_diff) || 0;
                    const amountTrendClass = amountDiff >= 0 ? 'badge-grobdi badge-green' : 'badge-grobdi badge-red';
                    const quantityTrendClass = quantityDiff >= 0 ? 'badge-grobdi badge-green' : 'badge-grobdi badge-red';

                    return `
                        <tr>
                            <td>${index + 1}</td>
                            <td class="text-left">${doctor.name}</td>
                            <td data-metric="quantity">
                                <span class="badge-grobdi badge-gray">${quantityFormatter.format(doctor.quantity_filter_1)} pedidos</span>
                            </td>
                            <td data-metric="quantity">
                                <span class="${quantityTrendClass}">${quantityFormatter.format(doctor.quantity_filter_2)} pedidos</span>
                            </td>
                            <td data-metric="quantity">
                                <span class="${quantityTrendClass}">${formatSignedQuantity(quantityDiff)} pedidos</span>
                            </td>
                            <td data-metric="amount">
                                <span class="badge-grobdi badge-gray">${currencyFormatter.format(doctor.amount_filter_1)}</span>
                            </td>
                            <td data-metric="amount">
                                <span class="${amountTrendClass}">${currencyFormatter.format(doctor.amount_filter_2)}</span>
                            </td>
                            <td data-metric="amount">
                                <span class="${amountTrendClass}">${formatSignedCurrency(amountDiff)}</span>
                            </td>
                        </tr>
                    `;
                }).join('');

                if (summary) {
                    const positives = doctors.filter(doc => Number(doc.amount_diff || 0) > 0).length;
                    const negatives = doctors.filter(doc => Number(doc.amount_diff || 0) < 0).length;
                    summary.textContent = `Mostrando ${doctors.length} doctores (${positives} positivos / ${negatives} negativos)`;
                }

                applyMetricToTables(currentMetric);
            };

            const syncFormWithFilters = filters => {
                if (!filterForm) {
                    return;
                }

                const toMonthValue = value => {
                    const parsed = parseDateValue(value);
                    if (!parsed) {
                        return '';
                    }
                    return `${parsed.getFullYear()}-${String(parsed.getMonth() + 1).padStart(2, '0')}`;
                };

                const setValue = (input, value) => {
                    if (!input) {
                        return;
                    }

                    if (!input.dataset.initialDefault) {
                        input.dataset.initialDefault = input.dataset.default || input.value || '';
                    }

                    const monthValue = toMonthValue(value);
                    if (monthValue) {
                        input.value = monthValue;
                    }
                };

                setValue(filterForm.range_a_start, filters?.start_date_1);
                setValue(filterForm.range_a_end, filters?.end_date_1);
                setValue(filterForm.range_b_start, filters?.start_date_2);
                setValue(filterForm.range_b_end, filters?.end_date_2);
            };

            // Función principal para renderizar el reporte
            const renderReport = async (filters = {}) => {
                const requestFilters = extractRequestFilters(filters);
                const backendData = await fetchReportData(requestFilters);
                if (!backendData) {
                    console.error('No se pudieron cargar los datos del reporte');
                    return;
                }

                const processedData = processBackendData(backendData);
                if (!processedData) {
                    console.error('Error al procesar los datos del backend');
                    return;
                }

                ensureDoctorNames(processedData);

                appState.data = processedData;
                appState.filters = processedData.filters || {};
                appState.lastRequestFilters = { ...requestFilters };

                if (!appState.initialFilters) {
                    appState.initialFilters = { ...appState.filters };
                    appState.initialRequestFilters = { ...requestFilters };
                }

                syncFormWithFilters(appState.filters);

                if (rangeLabel) {
                    rangeLabel.textContent = appState.filters.combinedLabel || defaultLabel;
                }

                // Actualizar estadísticas
                updateStatistics(processedData.stats);

                // Actualizar tabla
                updateDoctorsTable(processedData.doctorComparisons);

                // Determinar qué datasets usar según la métrica actual
                const getDatasets = () => {
                    if (currentMetric === 'amount') {
                        return {
                            positive: processedData.positiveByAmount,
                            negative: processedData.negativeByAmount
                        };
                    } else {
                        return {
                            positive: processedData.positiveByQuantity,
                            negative: processedData.negativeByQuantity
                        };
                    }
                };

                const datasets = getDatasets();

                // Actualizar o crear gráficos
                if (appState.charts.positive) {
                    updateHorizontalChart(appState.charts.positive, datasets.positive, currentMetric, 'positives');
                } else {
                    appState.charts.positive = buildHorizontalChart('chartSeguimientoMax', datasets.positive, currentMetric, 'positives');
                }

                if (appState.charts.negative) {
                    updateHorizontalChart(appState.charts.negative, datasets.negative, currentMetric, 'negatives');
                } else {
                    appState.charts.negative = buildHorizontalChart('chartSeguimientoMin', datasets.negative, currentMetric, 'negatives');
                }

                if (appState.charts.trend) {
                    updateMonthlyTrendChart(appState.charts.trend, processedData.monthlyPerformance, currentMetric);
                } else {
                    appState.charts.trend = buildMonthlyTrendChart('chartSeguimientoTrend', processedData.monthlyPerformance, currentMetric);
                }
            };
            const handleMetricChange = metric => {
                if (!metricSettings[metric]) {
                    return;
                }

                currentMetric = metric;
                setActiveMetricButton(metric);
                updateMetricStatus(metric);
                applyMetricToTables(metric);

                if (!appState.data) return;

                // Determinar qué datasets usar según la métrica
                const datasets = currentMetric === 'amount'
                    ? { positive: appState.data.positiveByAmount, negative: appState.data.negativeByAmount }
                    : { positive: appState.data.positiveByQuantity, negative: appState.data.negativeByQuantity };

                updateHorizontalChart(appState.charts.positive, datasets.positive, metric, 'positives');
                updateHorizontalChart(appState.charts.negative, datasets.negative, metric, 'negatives');
                updateMonthlyTrendChart(appState.charts.trend, appState.data.monthlyPerformance, metric);
            };

            metricButtons.forEach(button => {
                button.addEventListener('click', () => {
                    const metric = button.dataset.seguimientoMetric;
                    if (metric && metric !== currentMetric) {
                        handleMetricChange(metric);
                    }
                });
            });

            applyMetricToTables(currentMetric);
            setActiveMetricButton(currentMetric);
            updateMetricStatus(currentMetric);

            filterForm?.addEventListener('submit', event => {
                event.preventDefault();

                const rangeAStart = filterForm.range_a_start?.value || '';
                const rangeAEnd = filterForm.range_a_end?.value || '';
                const rangeBStart = filterForm.range_b_start?.value || '';
                const rangeBEnd = filterForm.range_b_end?.value || '';

                const rangeA = formatRange(rangeAStart, rangeAEnd);
                const rangeB = formatRange(rangeBStart, rangeBEnd);

                if (rangeLabel) {
                    rangeLabel.textContent = rangeA && rangeB ? `${rangeA} vs ${rangeB}` : defaultLabel;
                }

                // Llamar al backend con los nuevos filtros
                const filters = {
                    start_date_1: getFirstDayOfMonth(rangeAStart),
                    end_date_1: getLastDayOfMonth(rangeAEnd),
                    start_date_2: getFirstDayOfMonth(rangeBStart),
                    end_date_2: getLastDayOfMonth(rangeBEnd)
                };

                renderReport(filters);
            });

            resetButton?.addEventListener('click', () => {
                if (!filterForm) {
                    return;
                }

                const baselineFilters = appState.initialFilters || {};
                const baselineRequest = appState.initialRequestFilters || {};

                if (Object.keys(baselineFilters).length) {
                    syncFormWithFilters(baselineFilters);
                } else {
                    ['range_a_start', 'range_a_end', 'range_b_start', 'range_b_end'].forEach(key => {
                        const input = filterForm[key];
                        if (input) {
                            const initial = input.dataset.initialDefault || input.dataset.default || '';
                            input.value = initial;
                        }
                    });
                }

                if (rangeLabel) {
                    rangeLabel.textContent = baselineFilters.combinedLabel || defaultLabel;
                }

                handleMetricChange('amount');
                renderReport(baselineRequest); // Recargar con filtros por defecto
            });

            // Inicializar el reporte al cargar la página
            applyMetricToTables(currentMetric);
            setActiveMetricButton(currentMetric);
            updateMetricStatus(currentMetric);
            renderReport();
        };

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initSeguimientoModule);
        } else {
            initSeguimientoModule();
        }
        })();
    </script>
@endpush
