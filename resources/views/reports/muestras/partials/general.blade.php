@php
    $generalReport = $data['generalReport'];
@endphp
<div class="row">
    <div class="col-12">
        <div class="card bg-dark card-outline card-danger">
            <div class="card-header py-1">
                <div class="row">
                    <div class="col-6 align-content-center">
                        <h6 class="card-title">
                            <i class="fas fa-filter"></i> Filtros
                        </h6>
                    </div>
                    <div class="col-6">
                        <small class="badge bg-light text-dark p-2 float-sm-right">
                            <i class="fas fa-calendar-alt"></i>
                            <span class="d-none d-md-inline">Data de: </span>
                            <i
                                id="resumen-start-date-indicator">{{ ucfirst(now()->startOfMonth()->format('d/m/Y')) }}</i>
                            - <i id="resumen-end-date-indicator">{{ ucfirst(now()->format('d/m/Y')) }}</i>
                        </small>
                    </div>
                </div>
            </div>
            <div class="card-body py-1">
                <div class="row">
                    <div class="col-12 col-md-9">
                        <form id="resume-filter">
                            <div class="row">
                                <div class="col-12 col-sm-6 col-md-4">
                                    <div class="form-group">
                                        <label class="form-label">
                                            Fecha Inicio
                                        </label>
                                        <input type="date" class="form-control"
                                            name="start_date"value="{{ now()->startOfMonth()->toDateString() }}">
                                    </div>
                                </div>
                                <div class="col-12 col-sm-6 col-md-4">
                                    <div class="form-group">
                                        <label class="form-label">
                                            Fecha Fin
                                        </label>
                                        <input type="date" class="form-control" name="end_date"
                                            value="{{ now()->toDateString() }}">
                                    </div>
                                </div>
                                <div class="col-12 col-md-4 align-content-end align-content-md-end mb-md-3">
                                    <button class="btn btn-danger w-100" type="submit">
                                        <i class="fas fa-filter"></i> Filtrar
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="col-12 col-md-3 mt-3 mt-md-0 align-content-md-end mb-md-3">
                        <button class="btn btn-outline-light w-100" id="resume-clean-filter">
                            <i class="fas fa-eraser"></i> Limpiar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-6">
        <div class="card card-danger">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h5 class="mb-0">
                            <i class="fas fa-chart-pie"></i> Comparativa por Tipos de Muestras
                        </h5>
                        <small>
                            <i>Mostrando: <span id="resume-tipo-muestra-dataset-indicator">Cantidad de
                                    muestras</span></i>
                        </small>
                    </div>
                    <div class="col-auto">
                        <select class="badge bg-light border-0" style="padding-top: .35rem; padding-bottom: .35rem;"
                            id="resume-tipo-muestra-dataset-selector">
                            <option value="0">Cantidad de muestras</option>
                            <option value="1">Inversión en muestras</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <div class="position-relative">
                    <canvas id="resume-tipo-muestras-chart" height="300px">
                    </canvas>
                    @include('empty-chart', [
                        'dataLength' => array_sum(
                            array_column($generalReport['general_stats']['by_tipo_muestra'], 'count')),
                    ])
                </div>
            </div>
        </div>
    </div>
    <div class="col-6">
        <div class="card card-danger">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h5 class="mb-0">
                            <i class="far fa-chart-bar"></i> Comparativa por Tipos de Frasco
                        </h5>
                        <small>
                            <i>Mostrando: <span id="resume-tipo-frasco-dataset-indicator">Cantidad de
                                    muestras</span></i>
                        </small>
                    </div>
                    <div class="col-auto">
                        <select class="badge bg-light border-0" style="padding-top: .35rem; padding-bottom: .35rem;"
                            id="resume-tipo-frasco-dataset-selector">
                            <option value="0">Cantidad de muestras</option>
                            <option value="1">Inversión en muestras</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <div class="position-relative">
                    <canvas id="resume-tipo-frasco-chart" height="300px">
                    </canvas>
                    @include('empty-chart', [
                        'dataLength' => $generalReport['general_stats']['total_muestras'],
                    ])
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-12">
        <div class="card card-danger card-tabs">
            <div class="card-header p-0 pt-1">
                <ul class="nav nav-tabs" id="resume-detail-table-tabs" role="tablist">
                    <li class="pt-2 px-3">
                        <i class="fas fa-pump-soap"></i> Tabla detallada
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" id="resume-table-frasco-original-tab" data-toggle="pill"
                            href="#resume-table-frasco-original" role="tab"
                            aria-controls="resume-table-frasco-original" aria-selected="true">Frasco Original</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="resume-table-frasco-muestra-tab" data-toggle="pill"
                            href="#resume-table-frasco-muestra" role="tab"
                            aria-controls="resume-table-frasco-muestra" aria-selected="false">Frasco Muestra</a>
                    </li>
                </ul>
            </div>
            <div class="card-body p-0">
                <div class="tab-content" id="resume-detail-table-tabContent">
                    <div class="tab-pane fade show active" id="resume-table-frasco-original" role="tabpanel"
                        aria-labelledby="resume-table-frasco-original-tab">
                        <div class="table-responsive" style="max-height: 60dvh;">
                            <table class="table table-dark table-head-fixed text-nowrap m-0">
                                <thead>
                                    <tr>
                                        <th>Nombre</th>
                                        <th class="text-center">Cantidad</th>
                                        <th class="text-center">Precio Unitario</th>
                                        <th class="text-center">Precio Total</th>
                                        <th class="text-right">Presentación</th>
                                    </tr>
                                </thead>
                                <tbody id="resume-frasco-original-tbody">
                                    @include('empty-table', [
                                        'dataLength' => count($generalReport['data']['frasco_original']),
                                        'colspan' => 5,
                                    ])
                                    @if (isset($generalReport['data']['frasco_original']) && count($generalReport['data']['frasco_original']) > 0)
                                        @foreach ($generalReport['data']['frasco_original'] as $muestra)
                                            <tr>
                                                <td>{{ $muestra['nombre_muestra'] }}</td>
                                                <td class="text-center">{{ $muestra['cantidad_de_muestra'] }}</td>
                                                <td class="text-center">S/ {{ $muestra['precio'] ?? 0.0 }}</td>
                                                <td class="text-center">S/
                                                    {{ number_format(($muestra['precio'] ?? 0) * $muestra['cantidad_de_muestra'], 2) }}
                                                </td>
                                                <td class="text-right">
                                                    {{ $muestra->clasificacion->nombre_clasificacion }} -
                                                    {{ $muestra->clasificacionPresentacion->quantity }}
                                                    {{ $muestra->clasificacion->unidadMedida->nombre_unidad_de_medida }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endif
                                </tbody>
                                <tfoot class="table-foot-fixed table-dark">
                                    <tr>
                                        <th><i class="fas fa-calculator"></i> TOTAL</th>
                                        <th class="text-center" id="tfoot-frasco-original-cantidad">
                                            {{ $generalReport['general_stats']['by_tipo_frasco']['Frasco Original']['count'] }}
                                        </th>
                                        <th></th>
                                        <th class="text-center" id="tfoot-frasco-original-amount">S/
                                            {{ $generalReport['general_stats']['by_tipo_frasco']['Frasco Original']['amount'] }}
                                        </th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="resume-table-frasco-muestra" role="tabpanel"
                        aria-labelledby="resume-table-frasco-muestra-tab">
                        <div class="table-responsive" style="max-height: 60dvh;">
                            <table class="table table-dark table-head-fixed text-nowrap m-0">
                                <thead>
                                    <tr>
                                        <th>Nombre</th>
                                        <th class="text-center">Cantidad</th>
                                        <th class="text-center">Precio Unitario</th>
                                        <th class="text-center">Precio Total</th>
                                        <th class="text-right">Clasificacion</th>
                                    </tr>
                                </thead>
                                <tbody id="resume-frasco-muestra-tbody">
                                    @include('empty-table', [
                                        'dataLength' => count($generalReport['data']['frasco_muestra']),
                                        'colspan' => 5,
                                    ])
                                    @if (isset($generalReport['data']['frasco_muestra']) && count($generalReport['data']['frasco_muestra']) > 0)
                                        @foreach ($generalReport['data']['frasco_muestra'] as $muestra)
                                            <tr>
                                                <td>{{ $muestra['nombre_muestra'] }}</td>
                                                <td class="text-center">{{ $muestra['cantidad_de_muestra'] }}</td>
                                                <td class="text-center">S/ {{ $muestra['precio'] ?? 0.0 }}</td>
                                                <td class="text-center">S/
                                                    {{ number_format(($muestra['precio'] ?? 0.0) * $muestra['cantidad_de_muestra'], 2) }}
                                                </td>
                                                <td class="text-right">
                                                    {{ $muestra->clasificacion->nombre_clasificacion }}</td>
                                            </tr>
                                        @endforeach
                                    @endif
                                </tbody>
                                <tfoot class="table-foot-fixed table-dark">
                                    <tr>
                                        <th><i class="fas fa-calculator"></i> TOTAL</th>
                                        <th class="text-center" id="tfoot-frasco-muestra-cantidad">
                                            {{ $generalReport['general_stats']['by_tipo_frasco']['Frasco Muestra']['quantity'] }}
                                        </th>
                                        <th></th>
                                        <th class="text-center" id="tfoot-frasco-muestra-amount">S/
                                            {{ $generalReport['general_stats']['by_tipo_frasco']['Frasco Muestra']['amount'] }}
                                        </th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('partial-js')
    <script>
        flatpickr('#resume-filter input[name="start_date"]', {
            dateFormat: 'Y-m-d',
            locale: 'es',
            maxDate: "today"
        });
        flatpickr('#resume-filter input[name="end_date"]', {
            dateFormat: 'Y-m-d',
            locale: 'es',
            maxDate: "today"
        });
        const data = @json($generalReport);

        const resumeTableForiginalBody = $('#resume-frasco-original-tbody');
        const resumeTableFmuestraBody = $('#resume-frasco-muestra-tbody');

        const resumeGeneralTipoMuestra = data.general_stats.by_tipo_muestra;
        const resumeGeneralTipoFrasco = data.general_stats.by_tipo_frasco;
        const resumeTipoMuestraLabels = Object.keys(resumeGeneralTipoMuestra);
        const resumeTipoFrascoLabels = Object.keys(resumeGeneralTipoFrasco);
        const resumeTipoMuestraChartDataset = [{
            label: 'Cantidad de muestras',
            data: Object.values(resumeGeneralTipoMuestra).map(i => i.count),
            backgroundColor: generateHslColors(resumeTipoMuestraLabels, 0.5),
            hoverOffset: 4,
            hidden: false
        }, {
            label: 'Inversión en muestras',
            data: Object.values(resumeGeneralTipoMuestra).map(i => i.amount),
            backgroundColor: generateHslColors(resumeTipoMuestraLabels, 0.5),
            hoverOffset: 4,
            hidden: true
        }];
        resumeTipoFrascoChartOptions = {
            scales: {
                'y': {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return value; // sin S/
                        }
                    }
                },
                'y1': {
                    type: 'linear',
                    display: false,
                    position: 'left',
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            if (value >= 1000000) return 'S/ ' + (value / 1000000).toFixed(1) + 'M';
                            if (value >= 1000) return 'S/ ' + (value / 1000).toFixed(1) + 'K';
                            return 'S/ ' + value.toLocaleString('es-PE');
                        }
                    }
                }
            },
            plugins: {
                tooltip: {
                    displayColors: false,
                    callbacks: {
                        label: function(context) {
                            const datasetIndex = context.datasetIndex;
                            const value = context.parsed.y;
                            return `${context.dataset.label}: ${datasetIndex === 0 ? value : 'S/ ' + getFormattedMoneyValue(value)}`;
                        }
                    }
                }
            }
        };
        resumeTipoMuestraChartOptions = {
            plugins: {
                tooltip: {
                    displayColors: false,
                    callbacks: {
                        label: function(context) {
                            const datasetIndex = context.datasetIndex;
                            const value = context.parsed;
                            return `${context.dataset.label}: ${datasetIndex === 0 ? value : 'S/ ' + getFormattedMoneyValue(value)}`;
                        }
                    }
                }
            }
        };
        const resumeTipoFrascoChartDataset = [{
            label: 'Cantidad de muestras',
            data: Object.values(resumeGeneralTipoFrasco).map(i => i.count),
            borderColor: generateHslColors(resumeTipoFrascoLabels),
            borderWidth: 1.5,
            backgroundColor: generateHslColors(resumeTipoFrascoLabels, 0.5),
            hidden: false,
            yAxisID: 'y',
        }, {
            label: 'Inversión en muestras',
            data: Object.values(resumeGeneralTipoFrasco).map(i => i.amount),
            borderColor: generateHslColors(resumeTipoFrascoLabels),
            borderWidth: 1.5,
            backgroundColor: generateHslColors(resumeTipoFrascoLabels, 0.5),
            hoverOffset: 4,
            hidden: true,
            yAxisID: 'y1',
        }];

        const resumeTipoMuestraChart = createChart('#resume-tipo-muestras-chart', resumeTipoMuestraLabels,
            resumeTipoMuestraChartDataset, 'pie', resumeTipoMuestraChartOptions);

        const resumeTipoFrascoChart = createChart('#resume-tipo-frasco-chart', resumeTipoFrascoLabels,
            resumeTipoFrascoChartDataset, 'bar', withToggleableLegend(resumeTipoFrascoChartOptions));

        $('#resume-tipo-muestra-dataset-selector').on('change', function(e) {
            const selectedIndex = parseInt($(this).val());
            $('#resume-tipo-muestra-dataset-indicator').text($(this).find('option:selected').text());
            updateActiveDataset(resumeTipoMuestraChart, selectedIndex);
        })
        $('#resume-tipo-frasco-dataset-selector').on('change', function(e) {
            const selectedIndex = parseInt($(this).val());
            $('#resume-tipo-frasco-dataset-indicator').text($(this).find('option:selected').text());
            const scales = resumeTipoFrascoChart.options.scales;
            if (selectedIndex === 0) {
                scales.y.display = true;
                scales.y1.display = false;
            } else {
                scales.y.display = false
                scales.y1.display = true;
            }
            updateActiveDataset(resumeTipoFrascoChart, selectedIndex);
        })

        function resumeTipoMuestraUpdateChart(data) {
            const labels = Object.keys(data);
            const dataValues = Object.values(data);

            resumeTipoMuestraChart.data.labels = labels;
            resumeTipoMuestraChart.data.datasets[0].data = dataValues.map(i => i.count);
            resumeTipoMuestraChart.data.datasets[1].data = dataValues.map(i => i.amount);
            resumeTipoMuestraChart.data.datasets.forEach(ds => {
                ds.backgroundColor = generateHslColors(labels, 0.5);
            });
            resumeTipoMuestraChart.update();
            detectChartDataLength(resumeTipoMuestraChart);
        }

        function resumeTipoFrascoUpdateChart(data) {
            const labels = Object.keys(data);
            const dataValues = Object.values(data);

            resumeTipoFrascoChart.data.labels = labels;
            resumeTipoFrascoChart.data.datasets[0].data = dataValues.map(i => i.count);
            resumeTipoFrascoChart.data.datasets[1].data = dataValues.map(i => i.amount);
            resumeTipoFrascoChart.data.datasets.forEach(ds => {
                ds.backgroundColor = generateHslColors(labels, 0.5);
                ds.borderColor = generateHslColors(labels);
            });
            resumeTipoFrascoChart.update();
            detectChartDataLength(resumeTipoFrascoChart);
        }

        function resumeUpdateFrascoOriginalTable(data, generalStat) {
            tableRenderRows(resumeTableForiginalBody, data, (i) => `
                <tr>
                    <td>${i.nombre_muestra}</td>
                    <td class="text-center">${i.cantidad_de_muestra}</td>
                    <td class="text-center">S/ ${(i.precio ?? 0.0)}</td>
                    <td class="text-center">S/ ${(i.precio ?? 0.0) * i.cantidad_de_muestra}</td>
                    <td class="text-right">${i.clasificacion.nombre_clasificacion} - ${i.clasificacion_presentacion.quantity} ${i.clasificacion.unidad_medida.nombre_unidad_de_medida}</td>
                </tr>`)
            $('#tfoot-frasco-original-cantidad').text(generalStat.quantity);
            $('#tfoot-frasco-original-amount').text('S/ ' + generalStat.amount);
        }

        function resumeUpdateFrascoMuestraTable(data, generalStat) {
            tableRenderRows(resumeTableFmuestraBody, data, (i) => `
                <tr>
                    <td>${i.nombre_muestra}</td>
                    <td class="text-center">${i.cantidad_de_muestra}</td>
                    <td class="text-center">S/ ${(i.precio ?? 0.0)}</td>
                    <td class="text-center">S/ ${(i.precio ?? 0.0) * i.cantidad_de_muestra}</td>
                    <td class="text-right">${i.clasificacion.nombre_clasificacion}</td>
                </tr>`);
            $('#tfoot-frasco-muestra-cantidad').text(generalStat.quantity);
            $('#tfoot-frasco-muestra-amount').text('S/ ' + generalStat.amount);
        }

        function resumeUpdateGraphics(response) {
            $("#resumen-start-date-indicator").text(new Date(response.filters.start_date).toLocaleDateString(
                'es-PE'));
            $("#resumen-end-date-indicator").text(new Date(response.filters.end_date).toLocaleDateString(
                'es-PE'));

            resumeTipoMuestraUpdateChart(response.general_stats.by_tipo_muestra);
            resumeTipoFrascoUpdateChart(response.general_stats.by_tipo_frasco);
            resumeUpdateFrascoOriginalTable(response.data.frasco_original, response.general_stats.by_tipo_frasco[
                'Frasco Original']);
            resumeUpdateFrascoMuestraTable(response.data.frasco_muestra, response.general_stats.by_tipo_frasco[
                'Frasco Muestra']);
        }

        $('#resume-filter').on('submit', function(e) {
            e.preventDefault();
            const formData = $(this).serializeArray();
            const start_date = formData.find(i => i.name === 'start_date').value;
            const end_date = formData.find(i => i.name === 'end_date').value;

            $.ajax({
                url: "{{ route('reports.muestras.general') }}",
                method: "GET",
                data: {
                    start_date,
                    end_date
                },
                success: function(response) {
                    resumeUpdateGraphics(response);
                    const startDate = new Date(response.filters.start_date).toLocaleDateString(
                        'es-PE');
                    const endDate = new Date(response.filters.end_date).toLocaleDateString(
                        'es-PE');

                    toast(`Mostrando reporte del ${startDate} al ${endDate}`, ToastIcon.SUCCESS);
                },
                error: function(xhr) {
                    $('#productos-filter button[type="submit"]').prop('disabled', false)
                        .html('<i class="fas fa-filter"></i> Filtrar');
                    const message = xhr.responseJSON?.message || xhr.statusText ||
                        "Error desconocido";
                    toast(message, ToastIcon.ERROR);
                }
            });
        });

        const resumeCleanFilter = $('#resume-clean-filter');
        resumeCleanFilter.on('click', function(e) {
            e.preventDefault();

            // Desactivar botón mientras carga
            resumeCleanFilter.prop('disabled', true)
                .html('<i class="fas fa-spinner fa-spin"></i> Cargando...');
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            const startOfMonth = new Date(today.getFullYear(), today.getMonth(), 1);

            // Resetear valores en los flatpickr
            const startPicker = $('#resume-filter input[name="start_date"]')[0]._flatpickr;
            const endPicker = $('#resume-filter input[name="end_date"]')[0]._flatpickr;

            if (startPicker) startPicker.setDate(startOfMonth, true);
            if (endPicker) endPicker.setDate(today, true);

            // Enviar formulario
            $('#resume-filter').trigger('submit');

            // Restaurar botón
            resumeCleanFilter.prop('disabled', false)
                .html('<i class="fas fa-eraser"></i> Limpiar');
        });
    </script>
@endpush('partial-js')
