@php
    $doctorReport = $data['doctorReport'];
@endphp
<div class="row">
    <div class="col-2">
        <form id="doctor-filter">
            <div class="form-group position-relative">
                <label for="doctor-name-query">Nombre del doctor</label>
                <input type="text" id="doctor-name-query" name="doctor-name-query" class="form-control"
                    autocomplete="off" />
                <div id="doctor-suggestions-list" class="list-group position-absolute overflow-auto border"
                    style="z-index: 1000; max-height: 200px; width: 100%;"></div>
                <input type="hidden" name="doctor-id-doctor" id="doctor-id-doctor"
                    value="{{ $doctorReport['filters']['id_doctor'] }}" />
            </div>
            <div class="form-group">
                <div class="form-group">
                    <label for="visitadoras-start-date-input">
                        Fecha Inicio</label>
                    <div class="input-group date" data-target-input="nearest">
                        <input class="form-control datetimepicker-input" type="date" name="fecha"
                            id="visitadoras-start-date-input" value="{{ now()->startOfMonth()->format('Y-m-d') }}"
                            required>
                        <div class="input-group-append" data-target="#visitadoras-start-date-input">
                            <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                        </div>
                    </div>
                </div>
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
            <button type="submit" class="btn btn-dark w-100">
                Buscar
            </button>
        </form>
    </div>
    <div class="col-10">
        <div class="card card-outline card-dark">
            <div class="card-header">
                <h3 class="card-title">
                    <font dir="auto" style="vertical-align: inherit;">
                        <font dir="auto" style="vertical-align: inherit;" id="doctor-name-label">
                            {{ $doctorReport['doctor_info']['is_top_doctor'] ? 'Top Doctor:' : ' ' }}
                            {{ $doctorReport['doctor_info']['doctor'] }} - Tipo:
                            {{ $doctorReport['doctor_info']['tipo_doctor'] }}
                        </font>
                    </font>
                </h3>
            </div>
            <div class="card-body">
                <font dir="auto" style="vertical-align: inherit;">
                    <font dir="auto" style="vertical-align: inherit;">
                        <div class="chart">
                            <div class="chartjs-size-monitor">
                                <div class="chartjs-size-monitor-expand">
                                    <div class=""></div>
                                </div>
                                <div class="chartjs-size-monitor-shrink">
                                    <div class=""></div>
                                </div>
                            </div>
                            <canvas id="doctor-amount-spent-anually-chart"
                                style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%; display: block;"
                                width="496" height="250" class="chartjs-render-monitor"></canvas>
                        </div>
                    </font>
                </font>
            </div>
        </div>
    </div>
</div>
<div class="row mt-3">
    <div class="col-6">
        <div class="card card-outline card-dark">
            <div class="card-header">
                <h3 class="card-title">
                    <font dir="auto" style="vertical-align: inherit;">
                        <font dir="auto" style="vertical-align: inherit;">Monto consumido agrupado por tipo de
                            producto</font>
                    </font>
                </h3>
            </div>
            <div class="card-body">
                <font dir="auto" style="vertical-align: inherit;">
                    <font dir="auto" style="vertical-align: inherit;">
                        <div class="chart position-relative">
                            <div class="chartjs-size-monitor">
                                <div class="chartjs-size-monitor-expand">
                                    <div class=""></div>
                                </div>
                                <div class="chartjs-size-monitor-shrink">
                                    <div class=""></div>
                                </div>
                            </div>
                            @include('empty-chart', [
                                'dataLength' => count(
                                    $doctorReport['data']['amount_spent_monthly_grouped_by_tipo']),
                            ])
                            <canvas id="amount-spent-monthly-grouped-by-tipo-chart"
                                style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%; display: block;"
                                width="496" height="250" class="chartjs-render-monitor"></canvas>
                        </div>
                    </font>
                </font>
            </div>
        </div>
    </div>
    <div class="col-6">
        <div class="card card-outline card-dark">
            <div class="card-header">
                <h3 class="card-title">
                    <font dir="auto" style="vertical-align: inherit;">
                        <font dir="auto" style="vertical-align: inherit;">Productos más comprados por el doctor
                        </font>
                    </font>
                </h3>
                <div class="col-auto" style="text-align: end;">
                    <select class="badge bg-danger border-0 p-0" id="doctor-top-consumed-products-select">
                        <option value="0">Top 3</option>
                        <option value="1">Top 5</option>
                        <option value="2">Top 10</option>
                    </select>
                </div>
            </div>
            <div class="card-body">
                <font dir="auto" style="vertical-align: inherit;">
                    <font dir="auto" style="vertical-align: inherit;">
                        <div class="chart position-relative">
                            <div class="chartjs-size-monitor">
                                <div class="chartjs-size-monitor-expand">
                                    <div class=""></div>
                                </div>
                                <div class="chartjs-size-monitor-shrink">
                                    <div class=""></div>
                                </div>
                            </div>
                            @include('empty-chart', [
                                'dataLength' => count($doctorReport['data']['most_consumed_products_monthly']),
                            ])
                            <canvas id="most-consumed-products-monthly-chart"
                                style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%; display: block;"
                                width="496" height="250" class="chartjs-render-monitor"></canvas>
                        </div>
                    </font>
                </font>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-12">
        <div class="card card-outline card-danger">
            <div class="card-body table-responsive p-0" style="height: 450px;">
                <table class=" table table-head-fixed text-nowrap table-striped">
                    <thead>
                        <tr>
                            <th class="text-start">Producto</th>
                            <th class="text-center">Cantidad</th>
                            <th class="text-center">Sub Total</th>
                            <th class="text-center">Total</th>
                        </tr>
                    </thead>
                    <tbody id="doctor-products-details-table" class="table-dark">
                        @include('empty-table', [
                            'dataLength' => count($doctorReport['data']['most_consumed_products_monthly']),
                            'colspan' => 4,
                        ])
                        @foreach ($doctorReport['data']['most_consumed_products_monthly'] as $product)
                            <tr>
                                <td>
                                    {{ $product['articulo'] }}
                                </td>
                                <td class="text-center">
                                    {{ $product['total_cantidad'] }}
                                </td>
                                <td class="text-center">
                                    S/
                                    {{ number_format($product['total_sub_total'] / $product['total_cantidad'], 2) }}
                                </td>
                                <td class="text-center">
                                    S/ {{ $product['total_sub_total'] }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('partial-js')
    <script>
        const initialDoctorReport = @json($doctorReport);
        let fullMostConsumedProducts = initialDoctorReport.data.most_consumed_products_monthly;
        const doctorProductsDetailsTable = $('#doctor-products-details-table');
        const doctorNameLabel = $('#doctor-name-label');
        const monthYearInput = $('#doctor-month-year');
        const doctorIdInput = $('#doctor-id-doctor');
        initAutocompleteInput({
            apiUrl: `{{ route('doctors.search') }}`,
            inputSelector: '#doctor-name-query',
            listSelector: '#doctor-suggestions-list',
            hiddenIdSelector: doctorIdInput,
        });

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

        const monthLabels = [
            'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
            'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
        ];

        const yearlyData = initialDoctorReport.data.amount_spent_anually;
        const firstYearObject = Object.values(yearlyData)[0];
        const firstYearValues = Array.from({
            length: 12
        }, (_, i) => {
            const month = i + 1;
            return Number(firstYearObject[month] ?? 0);
        });


        let doctorAmountSpentAnuallyChart = createChart('#doctor-amount-spent-anually-chart', monthLabels,
            [{
                label: 'Monto de Inversión del Doctor',
                data: firstYearValues.map(Number),
                backgroundColor: 'rgba(212, 12, 13, 0.5)',
                borderColor: 'rgba(212, 12, 13, 1)',
                borderWidth: 2,
                pointStyle: 'circle',
                pointRadius: 8,
                pointHoverRadius: 12
            }],
            'line', {
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                },
                elements: {
                    line: {
                        tension: 0.1
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
            }
        );
        let doctorAmountSpentMonthlyGroupedByTipoChart = createChart(
            '#amount-spent-monthly-grouped-by-tipo-chart',
            initialDoctorReport.data.amount_spent_monthly_grouped_by_tipo.map(i => i.tipo), [{
                label: 'Monto invertido por el doctor',
                data: initialDoctorReport.data.amount_spent_monthly_grouped_by_tipo.map(i => i.total_sub_total),
                backgroundColor: 'rgba(212, 12, 13, 0.5)',
                borderColor: 'rgba(212, 12, 13, 1)',
                borderWidth: 2,
            }],
            'bar', {
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': S/ ' + context.parsed.y.toLocaleString();
                            }
                        }
                    }
                }
            });

        let doctorMostConsumedProductsChart = createChart('#most-consumed-products-monthly-chart',
            initialDoctorReport.data.most_consumed_products_monthly.map(i => i.articulo), [{
                label: 'Cantidad comprada',
                data: fullMostConsumedProducts.slice(0, 3).map(i => i.total_cantidad),
                backgroundColor: 'rgba(212, 12, 13, 0.5)',
                borderColor: 'rgba(212, 12, 13, 1)',
                borderWidth: 2,
            }],
            'bar', {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'y',
                scales: {
                    x: {
                        beginAtZero: true,
                    },
                    y: {
                        ticks: {
                            font: {
                                size: 12
                            },
                            callback: function(value, index) {
                                const label = this.getLabelForValue(value);
                                const displayLabel = label.length > 20 ? label.substring(0, 15) + '...' : label;
                                return `#${index+1} - ${displayLabel}`;
                            }
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                }
            });

        function doctorFetchReportData() {
            const id = doctorIdInput.val().trim();
            const start_date = visitadorasStartDateInput.val();
            const end_date = visitadorasEndDateInput.val();

            $.ajax({
                url: "{{ route('reports.doctores.doctores') }}",
                method: 'GET',
                data: {
                    id_doctor: id,
                    start_date: start_date,
                    end_date: end_date
                },
                success: function(response) {
                    toast(`Mostrando datos del doctor: ${response.doctor_info.doctor} - Periodo: ${response.filters.month}-${response.filters.year}`,
                        ToastIcon.SUCCESS);
                    fullMostConsumedProducts = response.data.most_consumed_products_monthly;
                    doctorUpdateGraphics(response);
                },
                error: function(xhr) {
                    toast('Hubo un error al traer datos del doctor solicitado', ToastIcon.ERROR);
                }
            });
        }

        function doctorUpdateGraphics(response) {
            const doctorInfo = response.doctor_info;
            const data = response.data;
            doctorNameLabel
                .text(
                    `${doctorInfo.is_top_doctor ? 'Top Doctor:': 'Dr.'} ${doctorInfo.doctor} - Tipo: ${doctorInfo.tipo_doctor}`
                );
            doctorUpdateAmountSpentAnuallyChart(response.data.amount_spent_anually);
            doctorUpdateBarCharts(
                doctorMostConsumedProductsChart,
                data.most_consumed_products_monthly.slice(0, 3).map(i => i.articulo),
                data.most_consumed_products_monthly.slice(0, 3).map(i => i.total_cantidad));
            doctorUpdateBarCharts(
                doctorAmountSpentMonthlyGroupedByTipoChart,
                data.amount_spent_monthly_grouped_by_tipo.map(i => i.tipo),
                data.amount_spent_monthly_grouped_by_tipo.map(i => i.total_sub_total));

            tableRenderRows(doctorProductsDetailsTable, data.most_consumed_products_monthly, (i) => `
                <tr>
                    <td>${i.articulo}</td>
                    <td class="text-center">${i.total_cantidad}</td>
                    <td class="text-center">S/ ${(i.total_sub_total / i.total_cantidad).toFixed(2)}</td>
                    <td class="text-center">S/ ${i.total_sub_total.toFixed(2)}</td>
                </tr>`);
        }

        function doctorUpdateAmountSpentAnuallyChart(amountSpentAnuallyData) {
            const years = Object.keys(amountSpentAnuallyData);
            const colors = generateHslColors(years); // colores para cada año

            const datasets = Object.entries(amountSpentAnuallyData).map(([year, monthsObj], index) => {
                const data = Array.from({
                    length: 12
                }, (_, i) => {
                    const month = i + 1;
                    return Number(monthsObj[month] ?? 0);
                });

                const borderColor = colors[index];
                const backgroundColor = colors[index].replace(/\/\s*1\s*\)/, '/ 0.2)'); // ajusta alpha a 0.2

                return {
                    label: `Año ${year}`,
                    data,
                    backgroundColor: backgroundColor,
                    borderColor: borderColor,
                    borderWidth: 2,
                    pointRadius: 6,
                    fill: false,
                    tension: 0.1
                };
            });

            doctorAmountSpentAnuallyChart.data.datasets = datasets;
            doctorAmountSpentAnuallyChart.update();
        }

        function doctorUpdateBarCharts(chart, labels, data) {
            chart.data.labels = labels;
            chart.data.datasets[0].data = data;
            chart.update();
            detectChartDataLength(chart);
        }

        $('#doctor-top-consumed-products-select').on('change', function(e) {
            const selectedOption = Number.parseInt($(this).val());
            if (fullMostConsumedProducts.length < 1) {
                return;
            } else {
                switch (selectedOption) {
                    case 1:
                        doctorUpdateBarCharts(doctorMostConsumedProductsChart,
                            fullMostConsumedProducts.slice(0, 5).map(i => i.articulo),
                            fullMostConsumedProducts.slice(0, 5).map(i => i.total_cantidad));
                        break;
                    case 2:
                        doctorUpdateBarCharts(
                            doctorMostConsumedProductsChart,
                            fullMostConsumedProducts.slice(0, 10).map(i => i.articulo),
                            fullMostConsumedProducts.slice(0, 10).map(i => i.total_cantidad));
                        break;
                    default:
                        doctorUpdateBarCharts(
                            doctorMostConsumedProductsChart,
                            fullMostConsumedProducts.slice(0, 3).map(i => i.articulo),
                            fullMostConsumedProducts.slice(0, 3).map(i => i.total_cantidad));
                        break;
                }
            }
        })

        $('#doctor-filter').on('submit', function(e) {
            e.preventDefault();
            doctorFetchReportData();
        })
    </script>
@endpush('partial-js')
