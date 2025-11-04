@php
    $generalReport = $data['generalReport'];
@endphp
<!-- Filters -->
<div class="card card-outline card-dark mb-3">
    <div class="card-body py-2">
        <div class="row">
            <div class="col-12 col-md-9">
                <form id="general-filter">
                    <div class="row">
                        <div class="col-12 col-sm-6 col-md-4">
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
                        <div class="col-12 col-sm-6 col-md-4">
                            <div class="form-group">
                                <label class="form-label">Año</label>
                                <select class="form-control" name="year">
                                    @for ($year = date('Y'); $year >= 2020; $year--)
                                        <option value="{{ $year }}">{{ $year }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                        <div class="col-12 col-md-4 align-content-end align-content-md-end mb-md-3">
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
    <div class="col-12 col-sm-6 col-lg-4 order-2 order-lg-1  mb-3 mb-lg-0">
        <div class="card card-outline card-danger bg-dark h-100">
            <div class="card-body text-center align-content-center">
                <h3 id="general-total-pedidos">{{ $generalReport['general_stats']['total_pedidos'] }}</h3>
                <p class="mb-0">Total de Pedidos</p>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-4 order-1 order-lg-2  mb-3 mb-lg-0">
        <div class="card card-outline card-danger bg-dark h-100">
            <div class="card-body text-center align-content-center">
                <h3 id="general-total-amount">S/
                    {{ $generalReport['general_stats']['total_amount'] }}
                </h3>
                <p class="mb-0">Ingresos Totales</p>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-lg-4  order-3 order-lg-3 mb-3 mb-lg-0">
        <div class="card card-outline card-danger bg-dark h-100">
            <div class="card-body text-center align-content-center">
                <h3 id="general-average-amount">S/
                    {{ $generalReport['general_stats']['average_amount'] }}</h3>
                <p class="mb-0">Promedio de Ingresos/Pedidos</p>
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
                <canvas id="amount-chart" style="height: 300px; max-height: 60vh;"></canvas>
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
@push('partial-js')
    <script>
        const generalReport = @json($data['generalReport']);
        const generalAmountChartDataset = [{
            label: 'Ingresos anuales',
            data: generalReport.data.map(i => i.total_amount),
            backgroundColor: 'rgba(212, 12, 13, 0.4)',
            borderColor: 'rgba(255, 0, 0, 1)',
            borderWidth: 1,
            fill: true
        }];
        const generalAmountChartOptions = {
            responsive: true,
            onResize: function(chart, size) {
                const isMobile = size.width < 768;

                chart.options.plugins.title.font.size = isMobile ? 13 : 16;
                chart.options.plugins.legend.labels.font.size = isMobile ? 12 : 14;

                if (chart.options.plugins.title.text.length < 5) {
                    chart.data.labels = generalReport.data.map(i => monthLabel(i.label));
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
        let generalAmountChart = createChart('#amount-chart', generalReport.data.map(i => monthLabel(i.label)),
            generalAmountChartDataset, 'line', generalAmountChartOptions);

        $('#general-filter').on('submit', function(e) {
            e.preventDefault();
            const month = $(this).find('[name="month"]').val();
            const year = $(this).find('[name="year"]').val();

            const submitBtn = $(e.currentTarget).find('button[type="submit"]');
            submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Cargando...');

            $.ajax({
                url: "{{ route('reports.ventas.general') }}",
                method: "GET",
                data: {
                    month,
                    year
                },
                success: function(response) {
                    toast('Datos obtenidos correctamente', ToastIcon.SUCCESS)
                    submitBtn.prop('disabled', false).html('<i class="fas fa-filter"></i> Filtrar');
                    updateGeneralGraphics(response);
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

        function updateGeneralGraphics(res) {
            $('#general-total-amount').text('S/ ' + getFormattedMoneyValue(res.general_stats.total_amount));
            $('#general-total-pedidos').text(res.general_stats.total_pedidos);
            $('#general-average-amount').text('S/ ' + getFormattedMoneyValue(res.general_stats.average_amount));

            if (res.chart_info.type === 'mensual') {
                generalSetChartLabels('Ingresos anuales', res.chart_info.period, res.data.map(i => monthLabel(i.label)));
                $('#general-card-title').text('Reportes por Mes')
            } else {
                generalSetChartLabels('Ingresos mensuales', res.chart_info.period, res.data.map(i => i.label));
                $('#general-card-title').text('Reportes por Día')
            }
            generalAmountChart.data.datasets[0].data = res.data.map(i => i.total_amount);

            generalAmountChart.update();
        }

        function generalSetChartLabels(label, period, labels) {
            generalAmountChart.data.datasets[0].label = label;
            generalAmountChart.options.plugins.title.text = period;
            generalAmountChart.data.labels = labels;
        }

        const generalCleanFiltertBtn = $('#general-clean-filter');
        generalCleanFiltertBtn.on('click', (e) => {
            generalCleanFiltertBtn.prop('disabled', true)
                .html('<i class="fas fa-spinner fa-spin"></i> Cargando...');
            $('#general-month').prop('selectedIndex', 0);
            $('#general-year').val(new Date().getFullYear());
            $('#general-filter').trigger('submit');
        });

        $('#general-download-excel').on('click', function(e) {
            e.preventDefault();
            e.stopImmediatePropagation();
            toast('Descargando reporte general (próximamente)', 'info');
        });
    </script>
@endpush('partial-js')
