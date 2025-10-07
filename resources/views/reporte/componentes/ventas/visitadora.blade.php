<!-- Tab Visitadora -->
<div class="tab-pane fade show active" id="visitadora" role="tabpanel">
    <div class="row mb-2">
        <form class="card card-outline card-danger col-8 col-sm-6 col-lg-4" id="dateRangeForm">
            <div class="card-body">
                <div class="form-group">
                    <label>Rango de fecha:</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">
                                <i class="far fa-calendar-alt"></i>
                            </span>
                        </div>
                        <input type="text" class="form-control float-right" id="dateRangeFilter">
                    </div>
                </div>
                <div class="row mb-2">
                    <div class="col">
                        <button class="btn btn-danger w-100" type="submit">
                            <i class="fas fa-filter"></i> Filtrar
                        </button>
                    </div>
                </div>
                <div class="row">
                    <div class="col">
                        <button class="btn btn-outline-dark w-100">
                            <i class="fas fa-times"></i> Limpiar
                        </button>
                    </div>
                </div>
            </div>
        </form>
        <div class="col-4 col-sm-6 col-lg-8">
            <div class="row">
                <div class="col-12">
                    <div class="card bg-danger">
                        <div class="card-body text-center">
                            <h3 id="total-monto">
                                S/ {{ array_sum(array_column($data['visitadoras'], 'total_monto')) }}
                            </h3>
                            <p class="mb-0">Monto Total</p>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-6">
                    <div class="card bg-dark">
                        <div class="card-body text-center">
                            <h3 id="total-pedidos">{{ array_sum(array_column($data['visitadoras'], 'total_pedidos')) }}</h3>
                            <p class="mb-0">Total de Pedidos</p>
                        </div>
                    </div>
                </div>
                <div class="col- col-md-6">
                    <div class="card bg-dark">
                        <div class="card-body text-center">
                            <h3 id="top-visitadora">{{collect($data['visitadoras'])->max('visitadora')}}</h3>
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
                        <i class="fas fa-chart-bar text-danger"></i> Monto por Visitadora
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="montoGroupedByVisitadoraChart" style="height: 400px;"></canvas>
                </div>
            </div>
        </div>
        <div class="col-6">
            <div class="card card-outline card-dark">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-bar text-danger"></i> Cantidad de pedidos por Visitadora
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="porcentajesGroupedByVisitadoraChart" style="height: 400px;"></canvas>
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
                        <tbody id="tableVentasVisitadoraBody">
                            @foreach ($data['visitadoras'] as $visitadora)
                            <tr>
                                <td>{{ $visitadora['visitadora'] }}</td>
                                <td class="text-center">S/ {{ $visitadora['total_monto'] }}</td>
                                <td class="text-center"><span class="badge bg-danger">{{ $visitadora['porcentaje_pedidos'] }}%</span></td>
                                <td class="text-center">{{ $visitadora['total_pedidos'] }}</td>
                            </tr>
                            @endforeach
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

</div>

@push('js')
<script src="{{ asset('js/chart-factory.js') }}"></script>
<script src="{{ asset('js/red-tone-by-string-generator.js') }}"></script>

<script>
    const tableVentasVisitadoraBody = $('#tableVentasVisitadoraBody')
    $('#dateRangeFilter').daterangepicker({
        locale: {
            format: 'DD/MM/YYYY',
            applyLabel: 'Aplicar',
            cancelLabel: 'Cancelar',
            daysOfWeek: ['Do', 'Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sa'],
            monthNames: [
                'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
                'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
            ],
            firstDay: 1
        }
    });

    const initialValues = JSON.parse(`@json($data)`)
    const labels = initialValues.visitadoras.map(i => i.visitadora)
    const montoDataset = [{
        label: 'Monto Total',
        data: initialValues.visitadoras.map(i => i.total_monto),
        backgroundColor: 'rgba(212, 12, 13, 0.4)',
        borderColor: 'rgba(255, 0, 0, 1)',
        borderWidth: 0.9
    }];

    const porcentajesDataset = [{
        label: 'Pedidos por visitadora (%)',
        data: initialValues.visitadoras.map(i => i.porcentaje_pedidos),
        backgroundColor: initialValues.visitadoras.map(v => getColorByVisitadoraName(v.visitadora)),
        borderColor: 'rgba(255, 255, 255, 1)',
        borderWidth: 0.5
    }]

    const montoOptions = {
        scales: {
            y: {
                beginAtZero: true,
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

    const porcentajesOptions = {
        plugins: {
            tooltip: {
                callbacks: {
                    label: function(context) {
                        let value = context.parsed;
                        return context.label + ': ' + value.toLocaleString() + '%';
                    }
                }
            }
        }
    };

    let montoGroupedByVisitadoraChart = createChart('#montoGroupedByVisitadoraChart', labels, montoDataset, 'bar',
        montoOptions);
    let porcentajesGroupedByVisitadoraChart = createChart('#porcentajesGroupedByVisitadoraChart', labels, porcentajesDataset, 'pie', porcentajesOptions);

    $('#dateRangeForm').on('submit', (e) => {
        e.preventDefault();
        const rango = $('#dateRangeFilter').val().split(' - ');
        const start_date = rango[0].split('/').reverse().join('-');
        const end_date = rango[1].split('/').reverse().join('-');

        $.ajax({
            url: "{{ route('reporte.api.ventas.visitadora') }}",
            method: "GET",
            data: {
                start_date,
                end_date
            },
            success: function(response) {
                console.log(response);
                updateGraphics(response)
            },
            error: function(xhr) {
                console.error("Error cargando datos:", xhr);
            }
        });
    })

    function updateGraphics(response) {
        montoGroupedByVisitadoraChart.data.labels = response.map((v) => v.visitadora);
        montoGroupedByVisitadoraChart.data.datasets[0].data = response.map((v) => v.total_monto);
        montoGroupedByVisitadoraChart.update();

        porcentajesGroupedByVisitadoraChart.data.labels = response.map((v) => v.visitadora);
        porcentajesGroupedByVisitadoraChart.data.datasets[0].data = response.map((v) => v.porcentaje_pedidos);
        porcentajesGroupedByVisitadoraChart.update();

        tableVentasVisitadoraBody.html('');

        tableVentasVisitadoraBody.html(
            response.map(i => `
        <tr>
            <td>${i.visitadora}</td>
            <td class="text-center">${i.total_monto}</td>
            <td class="text-center"><span class="badge bg-primary">${i.porcentaje_pedidos}%</span></td>
            <td class="text-center">${i.total_pedidos}</td>
        </tr>`).join('')
        );
    }
</script>
@endpush('js')