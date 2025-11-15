@php
    $zonesReport = $data['zonesReport'];
@endphp

@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content')
    @can('reports.rutas')
    <x-grobdi.report.chart-card
        title="Reporte por Zonas"
        subtitle="Controla visitas por estado, mes y distritos desde un solo tablero"
    >
        <div class="row">
                <div class="col-2">
                    <div class="card card-outline card-danger">
                        <div class="card-header">
                            <h5 class="card-title">Filtros</h5>
                        </div>
                        <div class="card-body">
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="month">
                                        Mes
                                    </label>
                                    <select name="month" id="month" class="form-control">
                                        <option value="1" {{ request('month', now()->month) == 1 ? 'selected' : '' }}>
                                            Enero
                                        </option>
                                        <option value="2" {{ request('month', now()->month) == 2 ? 'selected' : '' }}>
                                            Febrero
                                        </option>
                                        <option value="3" {{ request('month', now()->month) == 3 ? 'selected' : '' }}>
                                            Marzo
                                        </option>
                                        <option value="4" {{ request('month', now()->month) == 4 ? 'selected' : '' }}>
                                            Abril
                                        </option>
                                        <option value="5" {{ request('month', now()->month) == 5 ? 'selected' : '' }}>
                                            Mayo
                                        </option>
                                        <option value="6" {{ request('month', now()->month) == 6 ? 'selected' : '' }}>
                                            Junio
                                        </option>
                                        <option value="7" {{ request('month', now()->month) == 7 ? 'selected' : '' }}>
                                            Julio
                                        </option>
                                        <option value="8" {{ request('month', now()->month) == 8 ? 'selected' : '' }}>
                                            Agosto
                                        </option>
                                        <option value="9" {{ request('month', now()->month) == 9 ? 'selected' : '' }}>
                                            Septiembre</option>
                                        <option value="10"
                                            {{ request('month', now()->month) == 10 ? 'selected' : '' }}>Octubre
                                        </option>
                                        <option value="11"
                                            {{ request('month', now()->month) == 11 ? 'selected' : '' }}>
                                            Noviembre</option>
                                        <option value="12"
                                            {{ request('month', now()->month) == 12 ? 'selected' : '' }}>
                                            Diciembre</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="zone">
                                        Zona
                                    </label>
                                    <select name="zone" id="zone" class="form-control">
                                        @foreach ($zones as $zone)
                                            <option value="{{ $zone->id }}">{{ $zone->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="select-all-distritos">
                                        <label class="form-check-label" for="select-all-distritos">
                                            Seleccionar todos los distritos de la zona
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="">Distritos</label>
                                    <div id="distritos-container"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-10">
                    <div class="card card-outline card-danger">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-8 d-flex justify-content-center position-relative">
                                    @include('empty-chart', [
                                        'dataLength' => array_sum(
                                            $zonesReport['general_stats']['total_per_estado']),
                                    ])
                                    <canvas id="zone-chart"
                                        style="min-height: 250px; height: 250px; max-height: 350px; max-width: 100%;"></canvas>
                                </div>
                                <div class="col-md-4">
                                    <div class="card">
                                        <div class="card-header d-flex justify-content-center bg-dark">
                                            <h3 class="card-title mb-0">Leyenda</h3>
                                        </div>
                                        <div class="card-body" id="chart-legend">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-12">
                                    <div class="card card-dark">
                                        <div class="card-header">
                                            <h5 class="card-title">
                                                <i class="fas fa-road mr-1"></i> Detalle de estados por distrito
                                            </h5>
                                        </div>
                                        <div class="card-body table-responsive p-0">
                                            <table class="table mb-0 text-nowrap">
                                                <thead class="bg-danger">
                                                    <tr class="text-center">
                                                        <th>Distrito</th>
                                                        @foreach ($estadosVisitas as $estado)
                                                            <th>{{ $estado->name }}</th>
                                                        @endforeach
                                                    </tr>
                                                </thead>
                                                <tbody id="distritos-data-table" class="table-light">
                                                    @include('empty-table', [
                                                        'colspan' => count($estadosVisitas) + 1,
                                                        'dataLength' => count($zonesReport['data']),
                                                    ])
                                                    @if (isset($zonesReport['data']) && count($zonesReport['data']) > 0)
                                                        @foreach ($zonesReport['data'] as $distrito)
                                                            <tr class="text-center">
                                                                <td>{{ $distrito['distrito'] }}</td>
                                                                @foreach ($distrito['estados'] as $estado)
                                                                    <td>{{ $estado }}</td>
                                                                @endforeach
                                                            </tr>
                                                        @endforeach
                                                    @endif
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
        </div>
    </x-grobdi.report.chart-card>

    @endcan
@stop

@section('plugins.Chartjs', true)
@section('plugins.Sweetalert2', true)

@section('js')
    <script src="{{ asset('js/sweetalert2-factory.js') }}"></script>
    <script src="{{ asset('js/chart-helpers.js') }}"></script>
    <script src="{{ asset('js/table-helpers.js') }}"></script>

    <script>
        let selectedDistritos = [];
        const zonesReport = @json($zonesReport);
        const estados = @json($estadosVisitas);
        const distritosDataTable = $('#distritos-data-table');
        const distritosContainer = $('#distritos-container');
        const legendContainer = $('#chart-legend');
        const monthSelect = $('#month');
        const zone = $('#zone');

        var zoneChartOptions = {
            maintainAspectRatio: false,
            responsive: true,
            plugins: {
                legend: {
                    display: false,
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return ' ' + context.raw + ' Visitas';
                        }
                    }
                },
            }
        };
        zoneChartDatasets = [{
            data: estados.map(e => zonesReport.general_stats.total_per_estado[e.id]),
            backgroundColor: estados.map(e => e.color),
            borderColor: '#ffffff3a',
            hoverBorderColor: '#fff'
        }]

        zoneChart = createChart('#zone-chart', estados.map(e => e.name), zoneChartDatasets, 'pie',
            zoneChartOptions);

        function fetchReportRutasByZone() {
            const month = monthSelect.val();
            let distritos = selectedDistritos.map(d => d.id);

            $.ajax({
                url: `{{ route('reports.rutas.zones') }}`,
                type: 'GET',
                data: {
                    month,
                    distritos
                },
                success: function(response) {
                    zonesUpdateGraphics(response);
                    if (response.data.length === 0) {
                        toast(`No se encontraron datos con los filtros usados`, ToastIcon.INFO);
                    } else {
                        toast(`Trayendo datos de: Mes: ${response.filters.month}, Año: ${response.filters.year}`,
                            ToastIcon.SUCCESS);
                    }
                },
                error: function(xhr) {
                    const message = xhr.responseJSON?.message || xhr.statusText || "Error desconocido";
                    toast(message, ToastIcon.ERROR);
                }
            });
        }

        function zonesUpdateGraphics(response) {
            zonesUpdateChart(response.general_stats.total_per_estado);
            tableRenderRows(distritosDataTable, response.data,
                (i) => {
                    const estadosCols = estados.map(e => `<td>${i.estados[e.id] ?? 0}</td>`).join('');
                    return `
                            <tr class="text-center">
                                <td>${i.distrito}</td>
                                ${estadosCols}
                            </tr>`
                });

        }

        function zonesUpdateChart(total_per_estado) {
            zoneChart.data.datasets[0].data = estados.map(e => total_per_estado[e.id]);
            zoneChart.update();
            detectChartDataLength(zoneChart);
            renderLegend();
        }

        function renderLegend() {
            const labels = zoneChart.data.labels;
            const backgroundColor = zoneChart.data.datasets[0].backgroundColor;
            const dataValues = zoneChart.data.datasets[0].data;

            legendContainer.html(labels.map((label, i) => `
                <div class="d-flex align-items-center mb-2" style="gap: 8px;">
                    <span class="badge me-2" style="cursor:default; background-color:${backgroundColor[i]}; width:18px; height:18px;">&nbsp;</span>
                    <span>${label} <small class="text-muted">(${dataValues[i]})</small></span>
                </div>`).join(''));
        }

        function renderDistritos(data) {
            distritosContainer.empty();

            if (data.length === 0) {
                distritosContainer.html(
                    '<span>No hay distritos programados para esta zona.</span>');
                return;
            }

            const mergedDistritos = [...selectedDistritos, ...data.map(d => ({
                id: d.id,
                name: d.name
            }))].reduce((acc, curr) => {
                if (!acc.find(x => x.id === curr.id)) acc.push(curr);
                return acc;
            }, []);

            mergedDistritos.forEach(d => {
                const isChecked = selectedDistritos.some(sel => sel.id === d.id);
                distritosContainer.append(
                    `<div class="form-check me-3 mb-2">
                        <input class="form-check-input" type="checkbox" name="distritos[]" value="${d.id}" id="distrito_${d.id}" ${isChecked ? 'checked': ''} />
                        <label class="form-check-label" for="distrito_${d.id}">
                            ${d.name}
                        </label>
                    </div>`);
            });

            const allChecked = mergedDistritos.length > 0 & mergedDistritos.every(d => selectedDistritos.some(sel => sel
                .id === d.id));
            $('#select-all-distritos').prop('checked', allChecked);
        }

        $(function() {
            const zonesDistritosBaseUrl = `{{ route('rutas.zones.distritos', ['zoneId' => 'ZONE_ID']) }}`;
            renderLegend();

            zone.on('change', function() {
                let zoneId = $(this).val();
                const zonesDistritosUrl = zonesDistritosBaseUrl.replace('ZONE_ID', zoneId);

                distritosContainer.html(
                    `<div class="w-100 d-flex flex-column justify-content-center align-items-center">
                        <div class="spinner-border text-danger" role="status"></div>
                        <p class="text-muted mt-3 mb-0">Cargando distritos...</p>
                    </div>`);

                fetch(zonesDistritosUrl)
                    .then(response => response.json())
                    .then(data => renderDistritos(data));
            });

            $(document).on('change', '#select-all-distritos', function() {
                const checked = $(this).is(':checked');
                const checkboxes = distritosContainer.find('input[name="distritos[]"]');

                checkboxes.prop('checked', checked);

                if (checked) {
                    checkboxes.each(function() {
                        const id = parseInt($(this).val());
                        const name = $(this).next('label').text().trim();
                        if (!selectedDistritos.some(d => d.id === id)) {
                            selectedDistritos.push({
                                id,
                                name
                            });
                        }
                    });
                } else {
                    const idsVisibles = checkboxes.map(function() {
                        return parseInt($(this).val());
                    }).get();
                    selectedDistritos = selectedDistritos.filter(d => !idsVisibles.includes(d.id));
                }

                fetchReportRutasByZone();
            });

            distritosContainer.on('change', 'input[name="distritos[]"]', function() {
                const id = parseInt($(this).val());
                const name = $(this).next('label').text().trim();

                if ($(this).is(':checked')) {
                    if (!selectedDistritos.some(d => d.id === id)) {
                        selectedDistritos.push({
                            id,
                            name
                        });
                    }
                } else {
                    selectedDistritos = selectedDistritos.filter(d => d.id !== id);
                }

                const allChecked = distritosContainer.find('input[name="distritos[]"]').length ===
                    distritosContainer.find('input[name="distritos[]"]:checked').length;
                $('#select-all-distritos').prop('checked', allChecked);

                fetchReportRutasByZone();
            });
            monthSelect.on('change', fetchReportRutasByZone)
        })
    </script>
@stop
