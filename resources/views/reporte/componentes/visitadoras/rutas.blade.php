<!-- Tab Rutas Dinámico -->
<div class="tab-pane fade show active" id="rutas" role="tabpanel">
    <div class="row mb-4">
        <div class="col-2">
            <div class="card card-outline card-danger">
                <div class="card-body">
                    <div class="col-12">
                        <div class="form-group">
                            <label for="month">Mes</label>
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
                            <label for="zone">Zona</label>
                            <select name="zone" id="zone" class="form-control">
                                @isset($zones)
                                @foreach ($zones as $zone)
                                <option value="{{ $zone->id }}">{{ $zone->name }}</option>
                                @endforeach
                                @endisset
                            </select>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-group">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="select-all-distritos">
                                <label class="form-check-label" for="select-all-distritos">Seleccionar todos los distritos de la zona</label>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-group">
                            <label>Distritos</label>
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
                            <canvas id="pieChart" style="min-height: 250px; height: 250px; max-height: 350px; max-width: 100%;"></canvas>
                        </div>
                        <div class="col-md-4">
                            <div class="card card-outline card-dark">
                                <div class="card-header d-flex justify-content-center">
                                    <h3 class="card-title mb-0">Leyenda</h3>
                                </div>
                                <div class="card-body" id="chart-legend"></div>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-5">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body table-responsive p-0">
                                    <table class="table table-head-fixed text-nowrap">
                                        <thead>
                                            <tr class="text-center">
                                                <th>Distrito</th>
                                                @isset($estadosVisitas)
                                                @foreach ($estadosVisitas as $estado)
                                                <th>{{ $estado->name }}</th>
                                                @endforeach
                                                @endisset
                                            </tr>
                                        </thead>
                                        <tbody id="distritos-data-table">
                                            <tr>
                                                <td colspan="{{ isset($estadosVisitas) ? count($estadosVisitas) + 1 : 1 }}" class="text-center text-muted">
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
</div>

@section('css')
@parent
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css">
@endsection

@section('js')
@parent
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
<script>
    const initialValues = JSON.parse(`@json($initialValues ?? [])`);
    const estados = JSON.parse(`@json($estadosVisitas ?? [])`);
    var pieChartCanvas = document.getElementById('pieChart') ? document.getElementById('pieChart').getContext('2d') : null;
    const distritosContainer = $('#distritos-container');
    const legendContainer = $('#chart-legend');
    const monthSelect = $('#month');
    const zone = $('#zone');

    var pieData = {
        labels: [],
        datasets: [{
            data: [],
            backgroundColor: []
        }]
    };
    var pieOptions = {
        maintainAspectRatio: false,
        responsive: true,
        plugins: {
            legend: {
                display: false
            }
        }
    };
    var pieChart = pieChartCanvas ? new Chart(pieChartCanvas, {
        type: 'pie',
        data: pieData,
        options: pieOptions
    }) : null;

    function setInitialValues() {
        estados.map(e => {
            pieData.labels.push(e.name);
            pieData.datasets[0].backgroundColor.push(e.color);
            pieData.datasets[0].data.push(initialValues[e.id] ?? 0);
        });
    }

    function renderRows(data) {
        const tableBody = $('#distritos-data-table');
        tableBody.html('');
        const distritos = Object.entries(data).filter(([id]) => id !== 'Total');
        if (distritos.length === 0) {
            tableBody.html(`<tr><td colspan="${estados.length + 1}" class="text-center text-muted">No hay distritos seleccionados o los distritos seleccionados no tienen visitas para mostrar</td></tr>`);
            return;
        }
        distritos.forEach(([distritoId, values]) => {
            let row = `<tr class="text-center"><td>${values.distrito}</td>${estados.map(e => `<td>${values.estados[e.id] ?? 0}</td>`).join('')}</tr>`;
            tableBody.append(row);
        });
    }

    function renderLegend() {
        legendContainer.html(pieData.labels.map((label, i) => `<div class="d-flex align-items-center mb-2" style="gap: 8px;"><span class="badge me-2" style="cursor:default; background-color:${pieData.datasets[0].backgroundColor[i]}; width:18px; height:18px;">&nbsp;</span><span>${label} <small class="text-muted">(${pieData.datasets[0].data[i]})</small></span></div>`).join(''));
    }

    function updateChart() {
        const selectedCheckboxes = $('input[name="distritos[]"]:checked').map(function() {
            return parseInt($(this).val());
        }).get();
        fetch(`{{ route('reports.visitas.filter') }}?month=${monthSelect.val()}&distritos=${JSON.stringify(selectedCheckboxes)}`)
            .then(r => r.json())
            .then(data => {
                const total = data.Total ?? {};
                if (Object.keys(total).length === 0) {
                    toastr.info('Este distrito no tiene visitas programadas en el mes. Se mostrarán las visitas totales del mes.');
                    pieChart && pieChart.update();
                    renderLegend();
                    return;
                }
                pieData.datasets[0].data = estados.map(e => total[e.id] ?? 0);
                pieChart && pieChart.update();
                renderLegend();
                renderRows(data);
            }).catch(err => console.error('Error fetching filtered data:', err));
    }

    $(document).ready(function() {
        if (!pieChartCanvas) return;
        setInitialValues();
        renderLegend();
        zone.trigger('change');
        monthSelect.on('change', updateChart);
    });

    zone.on('change', function() {
        let zoneId = $(this).val();
        distritosContainer.html('<span>Cargando distritos...</span>');
        fetch(`/reports/visitadoras/distritos/${zoneId}`)
            .then(r => r.json())
            .then(data => {
                distritosContainer.empty();
                if (data.length === 0) {
                    distritosContainer.html('<span>No hay distritos programados para esta zona.</span>');
                    return;
                }
                data.forEach(d => {
                    let checkbox = `<div class="form-check me-3 mb-2"><input class="form-check-input" type="checkbox" name="distritos[]" value="${d.id}" id="distrito_${d.id}"><label class="form-check-label" for="distrito_${d.id}">${d.name}</label></div>`;
                    distritosContainer.append(checkbox);
                });
            });
    });

    $(document).on('change', '#select-all-distritos', function() {
        const checked = $(this).is(':checked');
        distritosContainer.find('input[name="distritos[]"]').prop('checked', checked);
        updateChart();
    });

    distritosContainer.on('change', 'input[name="distritos[]"]', function() {
        const allChecked = distritosContainer.find('input[name="distritos[]"]').length === distritosContainer.find('input[name="distritos[]"]:checked').length;
        $('#select-all-distritos').prop('checked', allChecked);
        updateChart();
    });
</script>
@endsection