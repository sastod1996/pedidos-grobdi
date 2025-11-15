@php
    $doctorReport = $data['doctorReport'];
@endphp
<div class="row g-4 align-items-stretch">
    <div class="col-12 col-lg-3">
        <div class="card-grobdi h-100">
            <div class="card-header-grobdi">
                <h4 class="mb-0">Filtros del doctor</h4>
                <p class="text-muted mb-0">Refina la búsqueda por nombre y rango de fechas</p>
            </div>
            <div class="card-body-grobdi">
                <form id="doctor-filter" class="grobdi-form">
                    <div class="form-group-grobdi position-relative">
                        <label for="doctor-name-query" class="form-label">Nombre del doctor</label>
                        <input type="text" id="doctor-name-query" name="doctor-name-query"
                            class="form-control-grobdi" autocomplete="off" placeholder="Ej. Dr. Condori" />
                        <div id="doctor-suggestions-list" class="autocomplete-dropdown"></div>
                        <input type="hidden" name="doctor-id-doctor" id="doctor-id-doctor"
                            value="{{ $doctorReport['filters']['id_doctor'] }}" />
                    </div>
                    <div class="form-grid">
                        <div>
                            <label for="visitadoras-start-date-input" class="form-label">Fecha inicio</label>
                            <div class="input-with-icon">
                                <input class="form-control-grobdi" type="date" name="fecha"
                                    id="visitadoras-start-date-input" value="{{ now()->startOfMonth()->format('Y-m-d') }}"
                                    required>
                                <span><i class="fas fa-calendar-alt"></i></span>
                            </div>
                        </div>
                        <div>
                            <label for="visitadoras-end-date-input" class="form-label">Fecha fin</label>
                            <div class="input-with-icon">
                                <input class="form-control-grobdi" type="date" name="visitadoras-end-date-input"
                                    id="visitadoras-end-date-input" value="{{ now()->format('Y-m-d') }}" required>
                                <span><i class="fas fa-calendar-alt"></i></span>
                            </div>
                        </div>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn-grobdi btn-primary-grobdi w-100">
                            <i class="fas fa-search mr-2"></i>Buscar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-9">
        <x-grobdi.report.chart-card
            title="{{ $doctorReport['doctor_info']['is_top_doctor'] ? 'Top Doctor' : 'Doctor' }}"
            :subtitle="sprintf('%s • %s • %s • %s',
                $doctorReport['doctor_info']['doctor'],
                $doctorReport['doctor_info']['tipo_doctor'] ?? 'Tipo sin registrar',
                $doctorReport['doctor_info']['especialidad'] ?? 'Especialidad sin registrar',
                $doctorReport['doctor_info']['distrito'] ?? 'Distrito sin registrar')"
        >
            <div class="chart-wrapper" style="height: 320px;">
                <canvas id="doctor-amount-spent-anually-chart"></canvas>
            </div>
        </x-grobdi.report.chart-card>
    </div>
</div>

<div class="row g-4 mt-1">
    <div class="col-12 col-lg-6">
        <x-grobdi.report.chart-card
            title="Monto consumido por tipo"
            subtitle="Distribución por categorías de producto"
        >
            @include('empty-chart', [
                'dataLength' => count($doctorReport['data']['amount_spent_monthly_grouped_by_tipo']),
            ])
            <div class="chart-wrapper" style="height: 300px;">
                <canvas id="amount-spent-monthly-grouped-by-tipo-chart"></canvas>
            </div>
        </x-grobdi.report.chart-card>
    </div>
    <div class="col-12 col-lg-6">
        <x-grobdi.report.chart-card
            title="Productos más consumidos"
            subtitle="Ranking configurable de pedidos"
        >
            <x-slot name="actions">
                <select id="doctor-top-consumed-products-select" class="form-control-grobdi form-control-sm">
                    <option value="0">Top 3</option>
                    <option value="1">Top 5</option>
                    <option value="2">Top 10</option>
                </select>
            </x-slot>
            @include('empty-chart', [
                'dataLength' => count($doctorReport['data']['most_consumed_products_monthly']),
            ])
            <div class="chart-wrapper" style="height: 300px;">
                <canvas id="most-consumed-products-monthly-chart"></canvas>
            </div>
        </x-grobdi.report.chart-card>
    </div>
</div>

<div class="row g-4 mt-1">
    <div class="col-12">
        <x-grobdi.layout.table-card
            title="Detalle de productos consumidos"
            subtitle="Listado ordenado por volumen y monto"
            table-id="doctor-products-details-table"
            table-class="table-striped table-hover align-middle"
        >
            <thead>
                <tr>
                    <th class="text-start">Producto</th>
                    <th class="text-center">Cantidad</th>
                    <th class="text-center">Sub Total</th>
                    <th class="text-center">Total</th>
                </tr>
            </thead>
            <tbody>
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
                            S/ {{ number_format($product['total_sub_total'], 2) }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </x-grobdi.layout.table-card>
    </div>
</div>

@push('partial-js')
    <script>
        const initialDoctorReport = @json($doctorReport);
        let fullMostConsumedProducts = initialDoctorReport.data.most_consumed_products_monthly;
        const doctorProductsDetailsTable = $('#doctor-products-details-table');
    const doctorNameLabel = $('#doctor-name-label');
    const doctorDetailsLabel = $('#doctor-details-label');
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
            fullMostConsumedProducts.slice(0, 3).map(i => i.articulo), [{
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
            const prefix = doctorInfo.is_top_doctor ? 'Top Doctor:' : 'Dr.';
            doctorNameLabel.text(`${prefix} ${doctorInfo.doctor}`);
            const normalize = (value) => value ? value : 'Sin registrar';
            const detailsText = [
                `Tipo: ${normalize(doctorInfo.tipo_doctor)}`,
                `Especialidad: ${normalize(doctorInfo.especialidad)}`,
                `Distrito: ${normalize(doctorInfo.distrito)}`,
                `Centro de Salud: ${normalize(doctorInfo.centro_salud)}`,
            ].join(' • ');
            doctorDetailsLabel.text(detailsText);
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
