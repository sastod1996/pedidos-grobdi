@php
    $generalReport = $data['generalReport'];
    $currentYear = now()->year;
@endphp
<!-- Filters -->
<div class="card card-outline card-dark mb-3">
    <div class="card-body py-2">
        <div class="row">
            <div class="col-12 col-md-9">
                <form id="general-filter">
                    <div class="row">
                        <div class="col-12 col-md-3">
                            <div class="form-group">
                                <label class="form-label">Mes</label>
                                <select class="form-control" name="month">
                                    <option value="0">Todos los meses</option>
                                    @for ($month = 1; $month <= 12; $month++)
                                        <option value="{{ $month }}">
                                            {{ ucfirst(Carbon\Carbon::createFromDate(null, $month, 1)->locale('es')->monthName) }}
                                        </option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-md-3">
                            <div class="form-group">
                                <label class="form-label">Desde</label>
                                <select class="form-control" name="start-year">
                                    @for ($year = date('Y'); $year >= 2020; $year--)
                                        <option value="{{ $year }}">{{ $year }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-md-3">
                            <div class="form-group">
                                <label class="form-label">Hasta</label>
                                <select class="form-control" name="end-year">
                                    @for ($year = date('Y'); $year >= 2020; $year--)
                                        <option value="{{ $year }}">{{ $year }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                        <div class="col-12 col-md-3 align-content-end align-content-md-end mb-md-3">
                            <button class="btn btn-danger btn-block w-100" type="submit">
                                <i class="fas fa-filter"></i> Filtrar
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            <div class="col-12 col-md-3 mt-3 mt-md-0 align-content-md-end mb-md-3">
                <button class="btn btn-outline-dark btn-block w-100" id="general-clean-filter">
                    <i class="fas fa-eraser"></i> Limpiar
                </button>
            </div>
        </div>
    </div>
</div>
<!-- General Stats -->
<div class="row mb-3">
    <div class="col-12">
        <div class="card card-dark">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h5 class="mb-0">
                            <i class="fas fa-chart-bar"></i> <span id="general-stats-period-label">Estadisticas por
                                Mes</span>
                        </h5>
                    </div>
                    <div class="col-auto">
                        <div class="row">
                            <div class="col-12 col-md-auto" style="text-align: end;">
                                <div class="btn-group btn-group-sm" role="group" aria-label="Navegación de año">
                                    <button type="button" class="btn btn-light btn-sm btn-year-selector"
                                        data-action="prev" disabled>
                                        <i class="fas fa-chevron-left"></i>
                                    </button>
                                    <button type="button" class="btn btn-light btn-sm" id="year-display"
                                        style="font-weight: bold; min-width: 50px;">
                                        {{ $currentYear }}
                                    </button>
                                    <button type="button" class="btn btn-light btn-sm btn-year-selector"
                                        data-action="next" disabled>
                                        <i class="fas fa-chevron-right"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-12 col-sm-6 mb-3 mb-lg-0">
                        <div class="card card-outline card-danger bg-dark h-100">
                            <div class="card-body text-center align-content-center" style="overflow: hidden;">
                                <div id="total-pedidos-container-label" class="year-slider-container">
                                    <div class="year-slide" data-index="2025">
                                        <h3>
                                            {{ $generalReport['general_stats'][2025]['total_pedidos'] }}
                                        </h3>
                                        <p class="mb-0">Total de Pedidos</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 mb-3 mb-lg-0">
                        <div class="card card-outline card-danger bg-dark h-100">
                            <div class="card-body text-center align-content-center" style="overflow: hidden;">
                                <div id="total-amount-container-label" class="year-slider-container">
                                    <div class="year-slide" data-year="2025">
                                        <h3>
                                            S/ {{ $generalReport['general_stats'][2025]['total_amount'] }}
                                        </h3>
                                        <p class="mb-0">Ingresos Totales</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Graphics -->
<div class="row mb-4">
    <div class="col-12 mb-4">
        <div class="card card-danger">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-calendar-alt"></i> <span id="general-card-title">Reportes por Mes</span>
                </h5>
            </div>
            <div class="card-body">
                <canvas id="amount-chart" style="height: 40vh;"></canvas>
            </div>
        </div>
    </div>
</div>
<!-- Botón Descargar Excel -->
<div class="text-center">
    <button class="btn btn-success btn-lg" id="general-download-excel">
        <i class="fas fa-download"></i> <span class="d-none d-sm-inline">Descargar Detallado Excel</span>
        <span class="d-sm-none">Descargar Excel</span>
    </button>
</div>

@section('css')
    <style>
        .year-slider-container {
            display: flex;
            flex-direction: row-reverse;
            height: 65px;
            transition: transform 0.4s cubic-bezier(0.22, 0.61, 0.36, 1);
        }

        .year-slide {
            min-width: 100%;
            text-align: center;
        }
    </style>
@stop

@push('partial-js')
    <script>
        const yearDisplay = $('#year-display');
        const pedidosContainer = $('#total-pedidos-container-label');
        const amountsContainer = $('#total-amount-container-label');

        function goToCard(currentYear) {
            if (!availableYears.includes(currentYear)) {
                return;
            }
            const index = availableYears.indexOf(currentYear);
            const translate = (index * 100) + '%';
            pedidosContainer.css('transform', `translateX(${translate})`);
            amountsContainer.css('transform', `translateX(${translate})`);
        }

        $('.btn-year-selector').on('click', function() {
            const action = $(this).data('action');
            let currentYear = parseInt(yearDisplay.text());

            if (action === 'prev') {
                currentYear--;
            } else if (action === 'next') {
                currentYear++;
            } else {
                return
            }

            goToCard(currentYear);
            yearDisplay.text(currentYear);
            generalUpdateNavYearButtons();
        });

        function generalUpdateNavYearButtons() {
            const prevBtn = $('.btn-year-selector[data-action="prev"]');
            const nextBtn = $('.btn-year-selector[data-action="next"]');
            let currentYear = parseInt(yearDisplay.text());

            if (availableYears.includes(currentYear - 1)) {
                prevBtn.prop('disabled', false);
            } else {
                prevBtn.prop('disabled', true);
            }

            if (availableYears.includes(currentYear + 1)) {
                nextBtn.prop('disabled', false);
            } else {
                nextBtn.prop('disabled', true);
            }
        }

        const generalReport = @json($data['generalReport']);
        const generalAmountChartDataset = [{
            label: 'Ingresos anuales',
            data: generalReport.data[0].data.map(i => i.total_amount),
            backgroundColor: 'rgba(212, 12, 13, 0.4)',
            borderColor: 'rgba(255, 0, 0, 1)',
            borderWidth: 1,
            pointRadius: 5,
            pointHoverRadius: 8,
            fill: true
        }];
        const generalAmountChartOptions = {
            responsive: true,
            onResize: function(chart, size) {
                const isMobile = size.width < 768;

                chart.options.plugins.title.font.size = isMobile ? 13 : 16;
                chart.options.plugins.legend.labels.font.size = isMobile ? 12 : 14;

                if (chart.options.plugins.title.text.length < 10) {
                    chart.data.labels = generalReport.data[0].data.map(i => monthLabel(i.label));
                    if (isMobile) {
                        chart.data.labels = chart.data.labels.map(label => label.substring(0, 3));
                    }
                }
                chart.update();
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'S/ ' + value.toLocaleString();
                        }
                    }
                }
            },
            plugins: {
                title: {
                    display: true,
                    text: generalReport.chart_info.period,
                    font: {
                        size: window.innerWidth < 768 ? 13 : 16
                    }
                },
                legend: {
                    labels: {
                        font: {
                            size: window.innerWidth < 768 ? 12 : 14
                        }
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'Ingresos: S/ ' + context.parsed.y.toLocaleString();
                        }
                    }
                }
            }
        };
        let generalAmountChart = createChart('#amount-chart', generalReport.data[0].data.map(i => i.label),
            generalAmountChartDataset, 'line', generalAmountChartOptions);

        $('#general-filter').on('submit', function(e) {
            e.preventDefault();
            const month = $(this).find('[name="month"]').val();
            const startYear = $(this).find('[name="start-year"]').val();
            const endYear = $(this).find('[name="end-year"]').val();

            const submitBtn = $(e.currentTarget).find('button[type="submit"]');
            submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Cargando...');

            $.ajax({
                url: "{{ route('reports.ventas.general') }}",
                method: "GET",
                data: {
                    month: month,
                    start_year: startYear,
                    end_year: endYear,
                },
                success: function(response) {
                    toast('Datos obtenidos correctamente', ToastIcon.SUCCESS)
                    submitBtn.prop('disabled', false).html('<i class="fas fa-filter"></i> Filtrar');
                    updateGeneralGraphics(response);
                    updateData(response);
                    generalCleanFiltertBtn.prop('disabled', false)
                        .html('<i class="fas fa-eraser"></i> Limpiar');
                },
                error: function(xhr) {
                    const message = xhr.responseJSON?.message || xhr.statusText || "Error desconocido";
                    toast(message, ToastIcon.ERROR);
                    submitBtn.prop('disabled', false).html('<i class="fas fa-filter"></i> Filtrar');
                    generalCleanFiltertBtn.prop('disabled', false)
                        .html('<i class="fas fa-eraser"></i> Limpiar');
                }
            });
        })

        function monthLabel(monthNumber) {
            return new Date(2025, parseInt(monthNumber) - 1, 1)
                .toLocaleString("es-ES", {
                    month: "long"
                });
        }

        availableYears = []

        function updateData(res) {
            availableYears = Object.keys(res.general_stats)
                .map(Number)
                .sort((a, b) => b - a);
            const currentYear = availableYears[0];
            yearDisplay.text(currentYear);
            goToCard(currentYear);
            generalUpdateNavYearButtons();
        }

        function updateGeneralGraphics(res) {
            updateGeneralCards(res.general_stats);
            const longestYearData = res.data.reduce((max, current) => {
                return current.data.length > max.data.length ? current : max;
            });
            if (res.chart_info.type === 'mensual') {
                $('#general-card-title').text('Reportes por Mes')
                $('#general-stats-period-label').text('Estadisticas por Mes')
                generalAmountChart.data.labels = longestYearData.data.map(d => monthLabel(d.label));
            } else {
                $('#general-card-title').text('Reportes por Día')
                $('#general-stats-period-label').text('Estadisticas por Día')
                generalAmountChart.data.labels = longestYearData.data.map(d => d.label);
            }

            generalAmountChart.options.plugins.title.text = res.chart_info.period;

            generalAmountChart.data.datasets = res.data.map((i, index) => {
                const colors = generateHslColors(i.data);
                const borderColor = colors[index];
                const backgroundColor = colors[index].replace(/\/\s*1\s*\)/, '/ 0.4)');
                return {
                    label: i.year,
                    data: i.data.map(d => d.total_amount),
                    backgroundColor: backgroundColor,
                    borderColor: borderColor,
                    borderWidth: 1,
                    pointRadius: 8,
                    pointHoverRadius: 12,
                    fill: true
                }
            });

            generalAmountChart.update();
        }

        function updateGeneralCards(generalStats) {
            const sortedEntries = Object.entries(generalStats)
                .sort(([yearA], [yearB]) => parseInt(yearB) - parseInt(yearA));

            pedidosContainer.html(
                sortedEntries.map(([year, data]) =>
                    `<div class="year-slide" data-index="${year}">
                        <h3>${data.total_pedidos}</h3>
                        <p class="mb-0">Total de Pedidos - ${year}</p>
                    </div>`).join('')
            );

            amountsContainer.html(
                sortedEntries.map(([year, data]) =>
                    `<div class="year-slide" data-index="${year}">
                        <h3>S/ ${data.total_amount}</h3>
                        <p class="mb-0">Ingresos Totales - ${year}</p>
                    </div>`).join('')
            );
        }

        window.CURRENT_YEAR = {{ $currentYear }};
        const generalCleanFiltertBtn = $('#general-clean-filter');
        generalCleanFiltertBtn.on('click', (e) => {
            generalCleanFiltertBtn.prop('disabled', true)
                .html('<i class="fas fa-spinner fa-spin"></i> Cargando...');
            $('#general-filter [name="month"]').prop('selectedIndex', 0);
            $('#general-filter [name="start-year"]').val(window.CURRENT_YEAR);
            $('#general-filter [name="end-year"]').val(window.CURRENT_YEAR);
            $('#general-filter').trigger('submit');
        });

        $('#general-download-excel').on('click', function(e) {
            e.preventDefault();
            e.stopImmediatePropagation();
            toast('Descargando reporte general (próximamente)', 'info');
        });
    </script>
@endpush('partial-js')
