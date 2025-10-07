@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
<div class="container-fluid">
    <div class="row mb-2">
        <div class="col-sm-6">
            <h1 class="m-0">Rutas</h1>
        </div>
        <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Inicio</a></li>
                <li class="breadcrumb-item active">Rutas</li>
            </ol>
        </div>
    </div>
</div>
@stop

@section('content')
<div class="row mb-4">
    <div class="col-2">
        <div class="card card-outline card-danger">
            <div class="card-body">
                <div class="col-12">
                    <div class="form-group">
                        <label for="month">
                            Mes
                        </label>
                        <select name="month" id="month" class="form-control">
                            <option value="1" {{ request('month', now()->month) == 1 ? 'selected' : '' }}>Enero</option>
                            <option value="2" {{ request('month', now()->month) == 2 ? 'selected' : '' }}>Febrero</option>
                            <option value="3" {{ request('month', now()->month) == 3 ? 'selected' : '' }}>Marzo</option>
                            <option value="4" {{ request('month', now()->month) == 4 ? 'selected' : '' }}>Abril</option>
                            <option value="5" {{ request('month', now()->month) == 5 ? 'selected' : '' }}>Mayo</option>
                            <option value="6" {{ request('month', now()->month) == 6 ? 'selected' : '' }}>Junio</option>
                            <option value="7" {{ request('month', now()->month) == 7 ? 'selected' : '' }}>Julio</option>
                            <option value="8" {{ request('month', now()->month) == 8 ? 'selected' : '' }}>Agosto</option>
                            <option value="9" {{ request('month', now()->month) == 9 ? 'selected' : '' }}>Septiembre</option>
                            <option value="10" {{ request('month', now()->month) == 10 ? 'selected' : '' }}>Octubre</option>
                            <option value="11" {{ request('month', now()->month) == 11 ? 'selected' : '' }}>Noviembre</option>
                            <option value="12" {{ request('month', now()->month) == 12 ? 'selected' : '' }}>Diciembre</option>
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
                    <div class="col-md-8 d-flex justify-content-center">
                        <canvas id="reportByZoneChart" style="min-height: 250px; height: 250px; max-height: 350px; max-width: 100%;"></canvas>
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
                <div class="row mt-5">
                    <div class="col-12">
                        <div class="card card-outline card-danger">
                            <div class="card-body table-responsive p-0">
                                <table class="table table-light mb-0 text-nowrap">
                                    <thead>
                                        <tr class="text-center">
                                            <th>Distrito</th>
                                            @foreach ($estadosVisitas as $estado)
                                            <th>{{ $estado->name }}</th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody id="distritos-data-table" class="table-light">
                                        <tr>
                                            <td colspan="{{ count($estadosVisitas) + 1 }}" class="text-center text-muted">
                                                No hay distritos seleccionados o los distritos seleccionados no tienen visitas para mostrar
                                            </td>
                                        </tr>
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
@stop

@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css">
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
<script src="{{ asset('js/chart-factory.js') }}"></script>

<script>
    const initialValues = JSON.parse(`@json($initialValues)`);
    const estados = JSON.parse(`@json($estadosVisitas)`);
    const distritosDataTable = $('#distritos-data-table');
    const distritosContainer = $('#distritos-container');
    const legendContainer = $('#chart-legend');
    const monthSelect = $('#month');
    const zone = $('#zone');

    var options = {
        maintainAspectRatio: false,
        responsive: true,
        plugins: {
            legend: {
                display: false,
            },
        }
    };

    reportByZoneChart = createChart('#reportByZoneChart', [], [{
        data: [],
        backgroundColor: [],
    }], 'pie', options);
</script>
<script>
    function setInitialValues() {
        estados.map(e => {
            reportByZoneChart.data.labels.push(e.name);
            reportByZoneChart.data.datasets[0].backgroundColor.push(e.color);
            reportByZoneChart.data.datasets[0].data.push(initialValues[e.id] ?? 0);
        });
    }

    function renderRows(data) {
        const distritosDataTable = $('#distritos-data-table');
        distritosDataTable.html('');

        const distritos = Object.entries(data).filter(([id]) => id !== "Total");

        if (distritos.length === 0) {
            distritosDataTable.html(`
            <tr>
                <td colspan="${estados.length + 1}" class="text-center text-muted">
                    No hay distritos seleccionados o los distritos seleccionados no tienen visitas para mostrar
                </td>
            </tr>
        `);
            return;
        }

        distritos.forEach(([distritoId, values]) => {
            let row = `<tr class="text-center">
            <td>${values.distrito}</td>
            ${estados.map(e => `<td>${values.estados[e.id] ?? 0}</td>`).join('')}
        </tr>`;
            distritosDataTable.append(row);
        });
    }

    function updateChart() {
        const selectedCheckboxes = $('input[name="distritos[]"]:checked').map(function() {
            return parseInt($(this).val());
        }).get();

        fetch(`{{ route('reports.visitas.filter') }}?month=${monthSelect.val()}&distritos=${JSON.stringify(selectedCheckboxes)}`)
            .then(response => response.json())
            .then(data => {
                const total = data.Total ?? {};

                if (total.length === 0) {
                    toastr.info('Este distrito no tiene visitas programadas en el mes. Se mostrarán las visitas totales del mes.');
                    reportByZoneChart.update();
                    renderLegend();
                    return;
                }

                reportByZoneChart.data.datasets[0].data = estados.map(e => total[e.id] ?? 0);

                reportByZoneChart.update();
                renderLegend();
                renderRows(data);
            }).catch(error => {
                console.error('Error fetching filtered data:', error);
            });
    }

    function renderLegend() {
        const labels = reportByZoneChart.data.labels;
        const backgroundColor = reportByZoneChart.data.datasets[0].backgroundColor;
        const dataValues = reportByZoneChart.data.datasets[0].data;

        legendContainer.html(labels.map((label, i) => `
        <div class="d-flex align-items-center mb-2" style="gap: 8px;">
            <span class="badge me-2" style="cursor:default; background-color:${backgroundColor[i]}; width:18px; height:18px;">&nbsp;</span>
            <span>${label} <small class="text-muted">(${dataValues[i]})</small></span>
        </div>`).join(''));
    }
</script>
<script>
    setInitialValues();
    renderLegend();

    $(document).ready(function() {
        zone.trigger('change');
        monthSelect.on('change', updateChart);
    });

    zone.on('change', function() {
        let zoneId = $(this).val();

        distritosContainer.html('<span>Cargando distritos...</span>');

        fetch(`/reports/visitadoras/distritos/${zoneId}`)
            .then(response => response.json())
            .then(data => {
                distritosContainer.empty();

                if (data.length === 0) {
                    distritosContainer.html('<span>No hay distritos programados para esta zona.</span>');
                    return;
                }

                data.forEach(d => {
                    let checkbox = `<div class="form-check me-3 mb-2">
                                                <input class="form-check-input" type="checkbox" name="distritos[]" 
                                                value="${d.id}" id="distrito_${d.id}">
                                                    <label class="form-check-label" for="distrito_${d.id}">
                                                        ${d.name}
                                                    </label>
                                            </div>`;
                    distritosContainer.html(distritosContainer.html() + checkbox);
                });
                $('#select-all-distritos').prop('checked', false);
            });
    });

    $(document).on('change', '#select-all-distritos', function() {
        const checked = $(this).is(':checked');
        distritosContainer.find('input[name="distritos[]"]').prop('checked', checked);
        updateChart();
    });

    distritosContainer.on('change', 'input[name="distritos[]"]', function() {
        const allChecked = distritosContainer.find('input[name="distritos[]"]').length ===
            distritosContainer.find('input[name="distritos[]"]:checked').length;
        $('#select-all-distritos').prop('checked', allChecked);
        updateChart();
    });

    distritosContainer.on('change', 'input[name="distritos[]"]', updateChart);
</script>
@stop