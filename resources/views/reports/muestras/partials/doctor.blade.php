@php
    $doctorReport = $data['doctorReport'];
@endphp
<div class="row g-4">
    <div class="col-12 col-lg-3">
        <div class="card-grobdi h-100">
            <div class="card-header-grobdi">
                <h4 class="mb-1">Filtros del doctor</h4>
                <p class="text-muted mb-0">Selecciona un doctor y rango de fechas</p>
            </div>
            <div class="card-body-grobdi">
                <form id="doctor-filter" class="grobdi-form">
                    <div class="form-group-grobdi position-relative">
                        <label for="doctor-name-query" class="form-label">Nombre del doctor</label>
                        <input type="text" id="doctor-name-query" name="doctor-name-query"
                            class="form-control-grobdi" autocomplete="off" placeholder="Ej. Dr. Ruiz" />
                        <div id="doctor-suggestions-list" class="autocomplete-dropdown"></div>
                        <input type="hidden" name="doctor-id-doctor" id="doctor-id-doctor"
                            value="{{ $doctorReport['filters']['id_doctor'] }}" />
                    </div>
                    <div class="form-grid">
                        <div>
                            <label for="doctors-start-date-input" class="form-label">Fecha inicio</label>
                            <div class="input-with-icon">
                                <input class="form-control-grobdi" type="date" name="fecha"
                                    id="doctors-start-date-input" value="{{ now()->startOfMonth()->format('Y-m-d') }}"
                                    required>
                                <span><i class="fas fa-calendar-alt"></i></span>
                            </div>
                        </div>
                        <div>
                            <label for="doctors-end-date-input" class="form-label">Fecha fin</label>
                            <div class="input-with-icon">
                                <input class="form-control-grobdi" type="date" name="doctors-end-date-input"
                                    id="doctors-end-date-input" value="{{ now()->format('Y-m-d') }}" required>
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
            <x-slot name="actions">
                <select class="form-control-grobdi form-control-sm" id="doctor-anual-dataset-selector">
                    <option value="amount">Inversión en muestras</option>
                    <option value="count">Cantidad de muestras</option>
                </select>
            </x-slot>
            <p class="text-muted mb-2">Mostrando datos por: <span id="doctor-anual-dataset-indicator">Inversión en
                    muestras</span></p>
            @include('empty-chart', ['dataLength' => count($doctorReport['data']['muestras'])])
            <div class="chart-wrapper" style="height: 320px;">
                <canvas id="doctor-amount-spent-anually-chart"></canvas>
            </div>
        </x-grobdi.report.chart-card>
    </div>
</div>

<div class="row g-4 mt-1">
    <div class="col-12 col-lg-4">
        <x-grobdi.report.chart-card
            title="Comparación de Tipos de Frasco"
            subtitle="Alterna entre montos y cantidades"
        >
            <x-slot name="actions">
                <select class="form-control-grobdi form-control-sm" id="doctor-tipo-frasco-dataset-selector">
                    <option value="0">Montos</option>
                    <option value="1">Cantidades</option>
                </select>
            </x-slot>
            @include('empty-chart', ['dataLength' => count($doctorReport['data']['muestras'])])
            <div class="chart-wrapper" style="height: 320px;">
                <canvas id="doctor-tipo-frasco-chart"></canvas>
            </div>
        </x-grobdi.report.chart-card>
    </div>
    <div class="col-12 col-lg-8">
        <x-grobdi.layout.table-card
            title="Detalle de muestras"
            subtitle="Cantidades e inversión por presentación"
            table-class="table-striped table-hover align-middle"
        >
            <thead>
                <tr>
                    <th class="text-start">Muestra</th>
                    <th class="text-center">Cantidad</th>
                    <th class="text-center">Inversión</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody id="doctor-muestras-details-table">
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
        </x-grobdi.layout.table-card>
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
