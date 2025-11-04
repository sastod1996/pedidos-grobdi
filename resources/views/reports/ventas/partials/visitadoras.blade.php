@php
    $visitadorasReport = $data['visitadorasReport'];
@endphp
<div class="row mb-2">
    <div class="card card-outline card-dark col-6">
        <div class="card-body">
            <form id="visitadoras-filter">
                <div class="row">
                    <div class="col-12 col-lg-6">
                        <div class="form-group">
                            <label for="visitadoras-start-date-input">
                                Fecha Inicio</label>
                            <div class="input-group date" data-target-input="nearest">
                                <input class="form-control datetimepicker-input" type="date" name="fecha"
                                    id="visitadoras-start-date-input"
                                    value="{{ now()->startOfMonth()->format('Y-m-d') }}" required>
                                <div class="input-group-append" data-target="#visitadoras-start-date-input">
                                    <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-lg-6">
                        <div class="form-group">
                            <label for="visitadoras-end-date-input">
                                Fecha Fin</label>
                            <div class="input-group date" data-target-input="nearest">
                                <input class="form-control datetimepicker-input" type="date"
                                    name="visitadoras-end-date-input" id="visitadoras-end-date-input"
                                    value="{{ now()->format('Y-m-d') }}" required>
                                <div class="input-group-append" data-target="#visitadoras-end-date-input">
                                    <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row mb-2">
                    <div class="col">
                        <button class="btn btn-danger w-100" type="submit">
                            <i class="fas fa-filter"></i> Filtrar
                        </button>
                    </div>
                </div>
            </form>
            <div class="row">
                <div class="col">
                    <button class="btn btn-outline-dark w-100" id="visitadoras-clean-filter">
                        <i class="fas fa-eraser"></i> Limpiar
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6">
        <div class="row">
            <div class="col-12">
                <div class="card bg-danger">
                    <div class="card-body text-center">
                        <h3 id="visitadoras-total-amount-card-label">
                            S/ {{ number_format($visitadorasReport['general_stats']['total_amount'], 2) }}
                        </h3>
                        <p class="mb-0">Monto Total</p>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="card bg-dark">
                    <div class="card-body text-center">
                        <h3 id="visitadoras-total-pedidos-card-label">
                            {{ $visitadorasReport['general_stats']['total_pedidos'] }}
                        </h3>
                        <p class="mb-0">Total de Pedidos</p>
                    </div>
                </div>
            </div>
            <div class="col- col-md-6">
                <div class="card bg-dark">
                    <div class="card-body text-center">
                        <h3 id="visitadoras-top-visitadora">
                            {{ $visitadorasReport['general_stats']['top_visitadora'] }}
                        </h3>
                        <p class="mb-0">Mejor Visitadora</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row mb-4">
    <div class="col-6">
        <div class="card card-outline card-dark">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-chart-bar text-danger"></i> Ingresos por Visitadora
                </h5>
            </div>
            <div class="card-body">
                <div class="position-relative">
                    <canvas id="visitadoras-total-amount-chart" style="height: 400px;"></canvas>
                    @include('empty-chart', ['dataLength' => count($visitadorasReport['data'])])
                </div>
            </div>
        </div>
    </div>
    <div class="col-6">
        <div class="card card-outline card-dark">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-chart-pie text-danger"></i> Porcentaje de pedidos por visitadora
                </h5>
            </div>
            <div class="card-body">
                <div class="position-relative">
                    <canvas id="visitadoras-percentages-chart" style="height: 400px;"></canvas>
                    @include('empty-chart', ['dataLength' => count($visitadorasReport['data'])])
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-danger">
                <h5 class="mb-0">
                    <i class="fas fa-table"></i> Estadísticas Detalladas
                </h5>
            </div>
            <div class="card-body p-0">
                <table class="table-dark table-hover table table-striped mb-0">
                    <thead>
                        <tr>
                            <th>Visitadora</th>
                            <th colspan="2" class="text-center">Monto total</th>
                            <th class="text-center">Cantidad de Pedidos</th>
                        </tr>
                    </thead>
                    <tbody id="visitadoras-table-body">
                        @include('empty-table', [
                            'dataLength' => count($visitadorasReport['data']),
                            'colspan' => 4,
                        ])
                        @if ($visitadorasReport['data'] && count($visitadorasReport['data']) > 0)
                            @foreach ($visitadorasReport['data'] as $visitadora)
                                <tr>
                                    <td>{{ $visitadora['visitadora'] }}</td>
                                    <td class="text-center">S/ {{ $visitadora['total_amount'] }}</td>
                                    <td class="text-center"><span
                                            class="badge bg-danger">{{ $visitadora['pedidos_percentage'] }}%</span>
                                    </td>
                                    <td class="text-center">{{ $visitadora['total_pedidos'] }}</td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Botón Descargar Excel -->
<div class="text-center mt-4">
    <button class="btn btn-success" id="descargar-excel-visitadora">
        <i class="fas fa-download"></i> Descargar Detallado Excel
    </button>
</div>

@push('partial-js')
    <script>
        const visitadorasTableBody = $('#visitadoras-table-body')
        const visitadorasStartDateInput = $('#visitadoras-start-date-input')
        const visitadorasEndDateInput = $('#visitadoras-end-date-input')

        flatpickr('#visitadoras-start-date-input', {
            altInput: true,
            dateFormat: "Y-m-d",
            altFormat: "d/m/Y",
            locale: 'es',
            maxDate: "today"
        });
        flatpickr('#visitadoras-end-date-input', {
            altInput: true,
            dateFormat: "Y-m-d",
            altFormat: "d/m/Y",
            locale: 'es',
            maxDate: "today"
        });

        const visitadorasReport = @json($visitadorasReport);
        const visitadorasLabels = visitadorasReport.data.map(i => i.visitadora)
        const visitadorasTotalAmountChartDataset = [{
            label: 'Ingresos',
            data: visitadorasReport.data.map(i => i.total_amount),
            backgroundColor: 'rgba(212, 12, 13, 0.4)',
            borderColor: 'rgba(255, 0, 0, 1)',
            borderWidth: 0.9
        }];

        const visitadorasPercentagesChartDataset = [{
            label: 'Porcentaje de pedidos',
            data: visitadorasReport.data.map(i => i.pedidos_percentage),
            backgroundColor: generateHslColors(visitadorasReport.data.map(i => i.visitadora)),
            borderColor: '#fff',
            borderWidth: 2
        }]

        const visitadorasTotalAmountChartOptions = {
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
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': S/ ' + context.parsed.y.toLocaleString();
                        }
                    }
                }
            }
        };

        const visitadorasPercentagesChartOptions = {
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let value = context.parsed;
                            return context.dataset.label + ': ' + value.toLocaleString() + '%';
                        }
                    }
                }
            }
        };

        let visitadorasTotalAmountChart = createChart('#visitadoras-total-amount-chart', visitadorasLabels,
            visitadorasTotalAmountChartDataset,
            'bar', visitadorasTotalAmountChartOptions);
        let visitadorasPercentagesChart = createChart('#visitadoras-percentages-chart', visitadorasLabels,
            visitadorasPercentagesChartDataset, 'pie', visitadorasPercentagesChartOptions);

        $('#visitadoras-filter').on('submit', function(e) {
            e.preventDefault();
            const start_date = visitadorasStartDateInput.val();
            const end_date = visitadorasEndDateInput.val();

            $.ajax({
                url: "{{ route('reports.ventas.visitadoras') }}",
                method: "GET",
                data: {
                    start_date,
                    end_date
                },
                success: function(response) {
                    visitadorasUpdateGraphics(response);
                },
                error: function(xhr) {
                    const message = xhr.responseJSON?.message || xhr.statusText || "Error desconocido";
                    toast(message, ToastIcon.ERROR);
                }
            });
        })

        function visitadorasUpdateCharts(chart, labels, dataset, arrayForColors) {
            chart.data.labels = labels;
            chart.data.datasets[0].data = dataset;
            if (arrayForColors) {
                chart.data.datasets[0].backgroundColor = generateHslColors(arrayForColors);
            }
            chart.update();
            detectChartDataLength(chart);
        }

        function visitadorasUpdateGraphics(res) {
            $('#visitadoras-total-amount-card-label').text('S/ ' + getFormattedMoneyValue(res.general_stats.total_amount));
            $('#visitadoras-total-pedidos-card-label').text(res.general_stats.total_pedidos);
            $('#visitadoras-top-visitadora').text(res.general_stats.top_visitadora);

            const labels = res.data.map(i => i.visitadora);

            visitadorasUpdateCharts(visitadorasTotalAmountChart, labels, res.data.map(i => i.total_amount));

            visitadorasUpdateCharts(visitadorasPercentagesChart, labels, res.data.map(i => i.pedidos_percentage), labels);

            tableRenderRows(visitadorasTableBody, res.data,
                (i) => `
                <tr>
                    <td>${i.visitadora}</td>
                    <td class="text-center">${i.total_amount}</td>
                    <td class="text-center"><span class="badge bg-primary">${i.pedidos_percentage}%</span></td>
                    <td class="text-center">${i.total_pedidos}</td>
                </tr>`);
        }

        $('#visitadoras-clean-filter').on('click', function(e) {
            e.preventDefault();

            // Desactivar botón mientras carga
            $('#visitadoras-clean-filter').prop('disabled', true)
                .html('<i class="fas fa-spinner fa-spin"></i> Cargando...');

            const picker = visitadorasDateRangeInput.data('daterangepicker');
            picker.setStartDate(moment().startOf('month'));
            picker.setEndDate(moment());
            visitadorasDateRangeInput.val(
                moment().startOf('month').format('YYYY-MM-DD') + ' - ' + moment().format('YYYY-MM-DD')
            );

            $('#visitadoras-filter').trigger('submit');

            $('#visitadoras-clean-filter').prop('disabled', false)
                .html('<i class="fas fa-eraser"></i> Limpiar');
        });
    </script>
@endpush('partial-js')
