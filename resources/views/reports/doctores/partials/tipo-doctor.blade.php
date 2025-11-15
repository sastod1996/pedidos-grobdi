@php
    $tipoDoctorReport = $data['tipoDoctorReport'];
@endphp
<div class="card-grobdi mb-4">
    <div class="card-body-grobdi">
        <div class="row g-3 align-items-end">
            <div class="col-12 col-md-8">
                <form id="tipo-doctor-filter" class="grobdi-form">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <div class="form-group-grobdi">
                                <label for="tipo-doctor-year" class="form-label"><i class="fas fa-calendar-check mr-1"></i>
                                    Año</label>
                                <input type="text" id="tipo-doctor-year" class="form-control-grobdi"
                                    placeholder="Seleccione un año" value="{{ date('Y') }}" readonly>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <button class="btn-grobdi btn-primary-grobdi w-100" type="submit">
                                <i class="fas fa-filter mr-2"></i>Aplicar filtros
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            <div class="col-12 col-md-4 mt-2 mt-md-0">
                <button class="btn-grobdi btn-outline-primary-grobdi w-100" id="tipo-doctor-clean-filter">
                    <i class="fas fa-eraser mr-2"></i>Limpiar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Gráfica Principal por Mes -->
<div class="row ">
    <div class="col-12">
        <x-grobdi.report.chart-card
            title="Ventas por Tipo de Doctor - Mensual"
            subtitle="Evolución mensual de ventas"
        >
            <x-slot name="actions">
                <span class="badge-grobdi badge-gray">
                    Año en vista: <span id="tipo-doctor-table-year-indicator">{{ now()->year }}</span>
                </span>
            </x-slot>
            @include('empty-chart', ['dataLength' => $tipoDoctorReport['resume']['total_amount']])
            <div class="chart-wrapper" style="height: 430px;">
                <canvas id="tipo-doctor-bar-chart"></canvas>
            </div>
        </x-grobdi.report.chart-card>
    </div>
</div>

<!-- Gráficas Complementarias -->
<div class="row mb-4">
    <div class="col-12 col-lg-6">
        <x-grobdi.report.chart-card
            title="Distribución por Tipo de Doctor"
            subtitle="Compara ingresos, pedidos y cantidad de doctores"
        >
            <x-slot name="actions">
                <select class="form-control-grobdi form-control-sm" id="tipo-doctor-pie-chart-select">
                    <option value="0">Ingresos Totales</option>
                    <option value="1">Cantidad de Pedidos</option>
                    <option value="2">Total de Doctores</option>
                </select>
            </x-slot>
            <p class="text-muted mb-2">Mostrando: <span id="tipo-doctor-pie-chart-showing-label">Ingresos Totales</span></p>
            @include('empty-chart', ['dataLength' => $tipoDoctorReport['resume']['total_amount']])
            <div class="chart-wrapper" style="height: 360px;">
                <canvas id="tipo-doctor-pie-chart"></canvas>
            </div>
        </x-grobdi.report.chart-card>
    </div>
    <div class="col-12 col-lg-6">
        <x-grobdi.layout.table-card
            title="Tabla de detalles por Tipo de Doctor"
            subtitle="Estadísticas generales"
            table-class="table-striped align-middle"
        >
            <thead>
                <tr>
                    <th><i class="fas fa-user-md mr-1"></i> Tipo</th>
                    <th class="text-center"><i class="fas fa-users mr-1"></i> Cantidad</th>
                    <th class="text-center"><i class="fas fa-boxes mr-1"></i> Pedidos</th>
                    <th class="text-center"><i class="fas fa-dollar-sign mr-1"></i> Ingresos</th>
                </tr>
            </thead>
            <tbody id="tipo-doctor-table-body">
                @include('empty-table', [
                    'colspan' => 4,
                    'dataLength' => count($tipoDoctorReport['resume']['tipos_resume']),
                ])
                @if (isset($tipoDoctorReport) && count($tipoDoctorReport['resume']['tipos_resume']) > 0)
                    @foreach ($tipoDoctorReport['resume']['tipos_resume'] as $tipoDoctor)
                        <tr>
                            <td>
                                <strong>{{ $tipoDoctor['tipo_medico'] }}</strong>
                            </td>
                            <td class="text-center">
                                {{ $tipoDoctor['total_doctores'] }}
                            </td>
                            <td class="text-center">{{ $tipoDoctor['total_pedidos'] }}</td>
                            <td class="text-center">
                                <span class="badge-grobdi badge-green">
                                    S/ {{ number_format($tipoDoctor['total_amount'], 2) }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                @endif
            </tbody>
            <tfoot>
                <tr>
                    <th><i class="fas fa-calculator mr-1"></i> TOTAL</th>
                    <th class="text-center" id="tipo-doctor-tfoot-total-doctores">
                        {{ $tipoDoctorReport['resume']['total_doctores'] }} Drs.
                    </th>
                    <th class="text-center" id="tipo-doctor-tfoot-total-pedidos">
                        {{ $tipoDoctorReport['resume']['total_pedidos'] }}
                    </th>
                    <th class="text-center" id="tipo-doctor-tfoot-total-amount">
                        S/ {{ number_format($tipoDoctorReport['resume']['total_amount'], 2) }}
                    </th>
                </tr>
            </tfoot>
        </x-grobdi.layout.table-card>
    </div>
</div>

<!-- Acciones adicionales -->
<div class="row">
    <div class="col-12">
        <div class="card-grobdi text-center">
            <div class="card-body-grobdi py-3">
                <button class="btn-grobdi btn-success-grobdi btn-lg" id="descargar-excel-tipo-doctor">
                    <i class="fas fa-file-excel mr-2"></i>Descargar Excel
                </button>
            </div>
        </div>
    </div>
</div>

@push('partial-js')
    <script>
        let tipoDoctorReport = @json($tipoDoctorReport);
        const tipoDoctorTableBody = $('#tipo-doctor-table-body');
        const tipoDoctorYearPicker = $('#tipo-doctor-year');
        tipoDoctorYearPicker.datepicker({
            format: "yyyy",
            viewMode: "years",
            minViewMode: "years",
            startDate: new Date(2020, 0, 1),
            endDate: new Date(new Date().getFullYear(), 11, 31),
            autoclose: true,
            language: "es"
        });

        const tipoDoctorBarChartOptions = {
            responsive: true,
            onResize: function(chart, size) {
                const isMobile = size.width < 768;

                chart.options.plugins.title.font.size = isMobile ? 13 : 16;
                chart.options.plugins.legend.labels.font.size = isMobile ? 12 : 14;

                if (isMobile) {
                    chart.data.labels = chart.data.labels.map(label => label.substring(0, 3));
                } else {
                    chart.data.labels = tipoDoctorReport.data.map(i => monthLabel(i.month));
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
                },
                x: {
                    ticks: {
                        maxRotation: 45,
                        minRotation: 0
                    }
                }
            },
            plugins: {
                title: {
                    display: true,
                    text: 'Ventas Mensuales por Tipo de Doctor',
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
                            return context.dataset.label + ': S/ ' + context.parsed.y.toLocaleString();
                        }
                    }
                }
            }
        };

        const tipoDoctorTipos = tipoDoctorReport.resume.tipos_resume.map(t => t.tipo_medico);
        const tipoDoctorColorsByTipo = generateHslColors(tipoDoctorTipos);

        const tipoDoctorBarChartDatasets = tipoDoctorTipos.map((tipo, idx) => ({
            label: tipo,
            backgroundColor: tipoDoctorColorsByTipo[idx],
            data: tipoDoctorReport.data.map(mes =>
                mes.tipos_resume.find(t => t.tipo_medico === tipo)?.total_amount || 0
            )
        }));

        function monthLabel(monthNumber) {
            return new Date(2025, parseInt(monthNumber) - 1, 1)
                .toLocaleString("es-ES", {
                    month: "long"
                });
        }

        tipoDoctorBarChart = createChart('#tipo-doctor-bar-chart', tipoDoctorReport.data.map(i => monthLabel(i.month)),
            tipoDoctorBarChartDatasets, 'bar', tipoDoctorBarChartOptions);

        const tipoDoctorPieChartDataset = [{
            label: 'Ingresos',
            data: tipoDoctorReport.resume.tipos_resume.map(i => i.total_amount),
            backgroundColor: tipoDoctorColorsByTipo,
            hidden: false
        }, {
            label: 'Cantidad de pedidos',
            data: tipoDoctorReport.resume.tipos_resume.map(i => i.total_pedidos),
            backgroundColor: tipoDoctorColorsByTipo,
            hidden: true
        }, {
            label: 'Cantidad de doctores',
            data: tipoDoctorReport.resume.tipos_resume.map(i => i.total_doctores),
            backgroundColor: tipoDoctorColorsByTipo,
            hidden: true
        }]

        const tipoDoctorPieChartOptions = {
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        font: {
                            size: window.innerWidth < 768 ? 12 : 14
                        }
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const datasetIndex = context.datasetIndex;
                            let value = context.parsed;
                            return `${context.dataset.label}: ${datasetIndex === 0 ? 'S/ '+ getFormattedMoneyValue(value) : value}`;
                        }
                    }
                }
            }
        };

        tipoDoctorPieChart = createChart('#tipo-doctor-pie-chart', tipoDoctorTipos, tipoDoctorPieChartDataset,
            'pie', tipoDoctorPieChartOptions);

        function tipoDoctorUpdatePieChart(tipos_resume) {
            const bgColors = generateHslColors(tipos_resume);
            tipoDoctorPieChart.data.labels = tipos_resume.map(i => i.tipo_medico);
            tipoDoctorPieChart.data.datasets[0].data = tipos_resume.map(i => i.total_amount);
            tipoDoctorPieChart.data.datasets[1].data = tipos_resume.map(i => i.total_pedidos);
            tipoDoctorPieChart.data.datasets[2].data = tipos_resume.map(i => i.total_doctores);

            tipoDoctorPieChart.data.datasets.forEach(e => {
                e.backgroundColor = bgColors;
            });

            tipoDoctorPieChart.update();
            detectChartDataLength(tipoDoctorPieChart);
        }

        $('#tipo-doctor-pie-chart-select').on('change', function(e) {
            const selectedIndex = parseInt($(this).val());
            $('#tipo-doctor-pie-chart-showing-label').text($(this).find('option:selected').text());

            updateActiveDataset(tipoDoctorPieChart, selectedIndex);
        });

        $('#tipo-doctor-filter').on('submit', function(e) {
            e.preventDefault()
            const btnPressed = e.originalEvent?.submitter;
            const year = $('#tipo-doctor-year').val();

            if (btnPressed) {
                $(btnPressed).prop('disabled', true)
            }

            $.ajax({
                url: "{{ route('reports.doctores.tipo-doctor') }}",
                method: 'GET',
                data: {
                    year: year
                },
                success: function(response) {
                    toast(`Mostrando datos del año: ${response.filters.year}`, ToastIcon.SUCCESS);
                    tipoDoctorReport = response;
                    updateGraphics(response);
                },
                error: function(xhr) {
                    toast('Hubo un error al traer datos del doctor solicitado', ToastIcon.ERROR);
                },
                complete: function() {
                    if (btnPressed) {
                        $(btnPressed).prop('disabled', false).html('Buscar');
                    }
                }
            });
        });

        function updateGraphics(response) {
            $('#tipo-doctor-table-year-indicator').text(response.filters.year);
            tipoDoctorUpdateBarChart(tipoDoctorReport.resume.tipos_resume.map(t => t.tipo_medico), response.data);
            tipoDoctorUpdatePieChart(response.resume.tipos_resume);
            tipoDoctorUpdateTable(response.resume);
        }

        function tipoDoctorUpdateTable(resume) {
            tableRenderRows(tipoDoctorTableBody, resume.tipos_resume, (i) => `
                <tr>
                    <td>
                        <strong>${i.tipo_medico}</strong>
                    </td>
                    <td class="text-center">${i.total_doctores}</td>
                    <td class="text-center">${i.total_pedidos}</td>
                    <td class="text-center">
                        <span class="badge bg-success">
                            S/ ${getFormattedMoneyValue(i.total_amount)}
                        </span>
                    </td>
                </tr>`);
            $('#tipo-doctor-tfoot-total-doctores').text(`${resume.total_doctores} Drs.`);
            $('#tipo-doctor-tfoot-total-pedidos').text(resume.total_pedidos);
            $('#tipo-doctor-tfoot-total-amount').text(`S/ ${resume.total_amount}`);
        }

        function tipoDoctorUpdateBarChart(tipoDoctorTipos, data) {
            const tipoDoctorColorsByTipo = generateHslColors(tipoDoctorTipos);

            tipoDoctorBarChart.data.datasets = tipoDoctorTipos.map((tipo, idx) => ({
                label: tipo,
                backgroundColor: tipoDoctorColorsByTipo[idx],
                data: data.map(mes => mes.tipos_resume.find(t => t.tipo_medico === tipo)?.total_amount || 0)
            }));

            tipoDoctorBarChart.update();
            detectChartDataLength(tipoDoctorBarChart);
        }

        $('#tipo-doctor-clean-filter').on('click', function(e) {
            e.preventDefault();

            const defaultYear = new Date().getFullYear();
            tipoDoctorYearPicker.datepicker('update', new Date(defaultYear, 0, 1));
            tipoDoctorYearPicker.trigger('changeDate');
            $('#tipo-doctor-filter').trigger('submit');
        })
    </script>
@endpush('partial-js')
