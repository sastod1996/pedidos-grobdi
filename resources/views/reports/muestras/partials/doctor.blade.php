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
                    <label for="doctors-start-date-input">
                        Fecha Inicio</label>
                    <div class="input-group date" data-target-input="nearest">
                        <input class="form-control datetimepicker-input" type="date" name="fecha"
                            id="doctors-start-date-input" value="{{ now()->startOfMonth()->format('Y-m-d') }}" required>
                        <div class="input-group-append" data-target="#doctors-start-date-input">
                            <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="doctors-end-date-input">
                        Fecha Fin</label>
                    <div class="input-group date" data-target-input="nearest">
                        <input class="form-control datetimepicker-input" type="date" name="doctors-end-date-input"
                            id="doctors-end-date-input" value="{{ now()->format('Y-m-d') }}" required>
                        <div class="input-group-append" data-target="#doctors-end-date-input">
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
                <div class="row align-items-center">
                    <div class="row">
                        <div class="mb-1 font-weight-bold" id="doctor-name-label" style="font-size: 1.1rem;">
                            {{ $doctorReport['doctor_info']['is_top_doctor'] ? 'Top Doctor:' : 'Dr.' }}
                            {{ $doctorReport['doctor_info']['doctor'] }}
                        </div>
                        <div class="text-muted mb-1" id="doctor-details-label" style="font-size: 0.95rem;">
                            Tipo: {{ $doctorReport['doctor_info']['tipo_doctor'] ?? 'Sin registrar' }}
                            <span class="mx-1">•</span>
                            Especialidad: {{ $doctorReport['doctor_info']['especialidad'] ?? 'Sin registrar' }}
                            <span class="mx-1">•</span>
                            Distrito: {{ $doctorReport['doctor_info']['distrito'] ?? 'Sin registrar' }}
                            <span class="mx-1">•</span>
                            Centro de Salud: {{ $doctorReport['doctor_info']['centro_salud'] ?? 'Sin registrar' }}
                        </div>
                        <small>
                            <i>Mostrando datos por: <span id="doctor-anual-dataset-indicator">Inversión en
                                    muestras</span></i>
                        </small>
                    </div>
                    <div class="col-auto">
                        <select class="badge bg-danger border-0" style="padding-top: .35rem; padding-bottom: .35rem;"
                            id="doctor-anual-dataset-selector">
                            <option value="amount">Inversión en muestras</option>
                            <option value="count">Cantidad de muestras</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="position-relative">
                    @include('empty-chart', ['dataLength' => count($doctorReport['data']['muestras'])])
                    <canvas id="doctor-amount-spent-anually-chart"
                        style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%; display: block;"
                        width="496" height="250" class="chartjs-render-monitor"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-12 col-lg-4">
        <div class="card card-outline card-danger h-100">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h5 class="mb-0">
                            Comparación de Tipos de Frasco
                        </h5>
                    </div>
                    <div class="col-auto">
                        <select class="badge bg-danger border-0" style="padding-top: .35rem; padding-bottom: .35rem;"
                            id="doctor-tipo-frasco-dataset-selector">
                            <option value="0">Montos</option>
                            <option value="1">Cantidades</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="card-body h-100">
                <div class="position-relative">
                    <canvas id="doctor-tipo-frasco-chart" style="min-height: 100%;"></canvas>
                    @include('empty-chart', [
                        'dataLength' => count($doctorReport['data']['muestras']),
                    ])
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-8">
        <div class="card card-outline card-danger">
            <div class="card-body table-responsive p-0" style="height: 380px;">
                <table class=" table table-head-fixed text-nowrap table-striped">
                    <thead>
                        <tr>
                            <th class="text-start">Muestra</th>
                            <th class="text-center">Cantidad</th>
                            <th class="text-center">Inversión</th>
                            <th class="text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody id="doctor-muestras-details-table" class="table-dark">
                        @include('empty-table', [
                            'dataLength' => count($doctorReport['data']['muestras']),
                            'colspan' => 4,
                        ])
                        @foreach ($doctorReport['data']['muestras'] as $muestra)
                            <tr>
                                <td>
                                    {{ $muestra['name'] }}
                                </td>
                                <td class="text-center">
                                    {{ $muestra['quantity'] }}
                                </td>
                                <td class="text-center">
                                    S/ {{ $muestra['price'] }}
                                </td>
                                <td class="text-right">
                                    S/ {{ $muestra['total_price'] }}
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
        let doctorReport = @json($doctorReport);
        const doctorMuestrasDetailsTable = $('#doctor-muestras-details-table');
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

        const doctorsStartDateInput = $('#doctors-start-date-input')
        const doctorsEndDateInput = $('#doctors-end-date-input')

        const doctorAnualDatasetSelector = $('#doctor-anual-dataset-selector');
        const doctorTipoFrascoDatasetSelector = $('#doctor-tipo-frasco-dataset-selector');

        flatpickr('#doctors-start-date-input', {
            altInput: true,
            dateFormat: "Y-m-d",
            altFormat: "d/m/Y",
            locale: 'es',
            maxDate: "today"
        });
        flatpickr('#doctors-end-date-input', {
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

        const yearlyData = doctorReport.data.anual;
        const firstYearObject = Object.values(yearlyData)[0];
        const firstYearValues = Array.from({
            length: 12
        }, (_, i) => {
            const month = i + 1;
            return Number(firstYearObject[month].amount ?? 0);
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

        let doctorTipoFrascoChart = createChart('#doctor-tipo-frasco-chart', Object.keys(doctorReport.data.by_tipo_frasco),
            [{
                label: 'Precios',
                data: Object.values(doctorReport.data.by_tipo_frasco).map(i => i.amount),
                backgroundColor: 'rgba(212, 12, 13, 0.5)',
                borderColor: 'rgba(212, 12, 13, 1)',
                borderWidth: 2,
                hidden: false
            }, {
                label: 'Cantidades',
                data: Object.values(doctorReport.data.by_tipo_frasco).map(i => i.count),
                backgroundColor: 'rgba(212, 12, 13, 0.5)',
                borderColor: 'rgba(212, 12, 13, 1)',
                borderWidth: 2,
                hidden: true
            }],
            'bar', withToggleableLegend()
        );

        function doctorFetchReportData() {
            const id = doctorIdInput.val().trim();
            const startDate = doctorsStartDateInput.val();
            const endDate = doctorsEndDateInput.val();

            $.ajax({
                url: "{{ route('reports.muestras.doctores') }}",
                method: 'GET',
                data: {
                    id_doctor: id,
                    start_date: startDate,
                    end_date: endDate
                },
                success: function(response) {
                    toast(`Mostrando datos del doctor: ${response.doctor_info.doctor} - Periodo: ${response.filters.month}-${response.filters.year}`,
                        ToastIcon.SUCCESS);
                    doctorReport = response;
                    doctorUpdateGraphics(response);
                    console.log(response);
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
            doctorUpdateAmountSpentAnuallyChart(response.data.anual);

            doctorUpdateTipoFrascoChart(response.data.by_tipo_frasco);

            tableRenderRows(doctorMuestrasDetailsTable, data.muestras, (i) => `
                <tr>
                    <td>${i.name}</td>
                    <td class="text-center">${i.quantity}</td>
                    <td class="text-center">S/ ${i.price}</td>
                    <td class="text-right">S/ ${i.total_price}</td>
                </tr>`);
        }

        $('#doctor-tipo-frasco-dataset-selector').on('change', function(e) {
            const optionSelected = parseInt($(this).val());

            if (optionSelected === 1) {
                doctorTipoFrascoChart.options.plugins.tooltip.callbacks.label = function(context) {
                    return context.dataset.label + ': ' + context.parsed.y.toLocaleString();
                }
            } else {
                doctorTipoFrascoChart.options.plugins.tooltip.callbacks.label = function(context) {
                    return context.dataset.label + ': S/ ' + context.parsed.y.toLocaleString();
                }
            }

            updateActiveDataset(doctorTipoFrascoChart, optionSelected)
        })

        function doctorUpdateTipoFrascoChart(data) {
            doctorTipoFrascoChart.data.datasets[0].data = Object.values(data).map(i => Number(i.amount));
            doctorTipoFrascoChart.data.datasets[1].data = Object.values(data).map(i => i.count);

            doctorTipoFrascoChart.update();
            detectChartDataLength(doctorTipoFrascoChart);
        }


        doctorAnualDatasetSelector.on('change', function(e) {
            const $select = $(this);
            const optionSelected = $select.val();
            if (optionSelected === 'count') {
                doctorAmountSpentAnuallyChart.options.plugins.tooltip.callbacks.label = function(context) {
                    return context.dataset.label + ': ' + context.parsed.y.toLocaleString();
                }
            } else {
                doctorAmountSpentAnuallyChart.options.plugins.tooltip.callbacks.label = function(context) {
                    return context.dataset.label + ': S/ ' + context.parsed.y.toLocaleString();
                }
            }

            doctorUpdateAmountSpentAnuallyChart(doctorReport.data.anual);
            $('#doctor-anual-dataset-indicator').text($select.find('option:selected').text());
        })

        function doctorUpdateAmountSpentAnuallyChart(data) {
            const field = doctorAnualDatasetSelector.val();

            const years = Object.keys(data);
            const colors = generateHslColors(years);

            const datasets = Object.entries(data).map(([year, monthsObj], index) => {
                const data = Array.from({
                    length: 12
                }, (_, i) => {
                    const month = i + 1;
                    return Number(monthsObj[month][field] ?? 0);
                });

                const borderColor = colors[index];
                const backgroundColor = colors[index].replace(/\/\s*1\s*\)/, '/ 0.5)');

                return {
                    label: `Año ${year}`,
                    data,
                    borderWidth: 2,
                    backgroundColor: backgroundColor,
                    borderColor: borderColor,
                    pointRadius: 6,
                    fill: false,
                    tension: 0.1,
                    pointStyle: 'circle',
                    pointRadius: 8,
                    pointHoverRadius: 12
                };

            });

            doctorAmountSpentAnuallyChart.data.datasets = datasets;
            doctorAmountSpentAnuallyChart.update();
            detectChartDataLength(doctorAmountSpentAnuallyChart);
        }

        $('#doctor-filter').on('submit', function(e) {
            e.preventDefault();
            doctorFetchReportData();
        })
    </script>
@endpush('partial-js')
