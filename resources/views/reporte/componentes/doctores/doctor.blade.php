<!-- Contenido migrado desde reports.doctores.partials.porDoctor -->
<div class="tab-pane fade" id="doctor" role="tabpanel">
    <div class="row">
        <div class="col-2">
            <form id="doctorSearchForm">
                <div class="form-group position-relative">
                    <label for="name_doctor">Nombre del doctor</label>
                    <input type="text" id="name_doctor" name="name_doctor" class="form-control" autocomplete="off" />
                    <div id="doctorsList" class="list-group position-absolute overflow-auto border"
                        style="z-index: 1000; max-height: 200px; width: 100%;"></div>
                    <input type="hidden" name="id_doctor" id="id_doctor" value="" />
                </div>
                <div class="form-group">
                    <label for="monthYearPicker">Mes y Año</label>
                    <div class="input-group date" id="monthYearPicker">
                        <input type="text" id="month_year" name="month_year"
                            class="form-control datetimepicker-input" value="{{ now()->format('m/Y') }}" required />
                        <div class="input-group-append" data-target="#monthYearPicker" data-toggle="datetimepicker">
                            <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-dark w-100">Buscar</button>
            </form>
        </div>
        <div class="col-10">
            <div class="card card-outline card-dark">
                <div class="card-header">
                    <h3 class="card-title" id="doctor-name-label">
                        @php $__doctorData = $doctorData ?? [
                                'doctor' => 'N/A',
                                'tipoMedico' => 'N/A',
                        ]; @endphp
                        Dr. con mayor consumo del año: {{ $__doctorData['doctor'] }} - Tipo:
                        {{ $__doctorData['tipoMedico'] }}
                    </h3>
                </div>
                <div class="card-body">
                    <div class="chart">
                        <canvas id="amountSpentByDoctorGroupedByMonthChart"
                            style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%; display: block;"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row mt-3">
        <div class="col-6">
            <div class="card card-outline card-dark">
                <div class="card-header">
                    <h3 class="card-title">Monto consumido agrupado por tipo de producto</h3>
                </div>
                <div class="card-body">
                    <div class="chart position-relative">
                        @php $__amountTipo = $doctorData['amountSpentByDoctorGroupedByTipo'] ?? []; @endphp
                        <div class="no-data-message text-center position-absolute w-100 align-content-center top-50 start-50 translate-middle"
                            style="{{ count($__amountTipo) > 0 ? 'display: none;' : '' }} height: 30%; background-color: rgba(0, 0, 0, 0.5); color: #e1e1e1ff; top: 25%;">
                            No hay datos en el mes para este gráfico.</div>
                        <canvas id="amountSpentByDoctorGroupedByTipoChart"
                            style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%; display: block;"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6">
            <div class="card card-outline card-dark">
                <div class="card-header">
                    <h3 class="card-title">Productos más comprados por el doctor</h3>
                </div>
                <div class="card-body">
                    <div class="chart position-relative">
                        @php $__topProducts = $doctorData['topMostConsumedProductsInTheMonthByDoctor'] ?? []; @endphp
                        <div class="no-data-message text-center position-absolute w-100 align-content-center top-50 start-50 translate-middle"
                            style="{{ count($__topProducts) > 0 ? 'display: none;' : '' }} height: 30%; background-color: rgba(0, 0, 0, 0.5); color: #e1e1e1ff; top: 25%;">
                            No hay datos en el mes para este gráfico.</div>
                        <canvas id="topMostConsumedProductsInTheMonthByDoctorChart"
                            style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%; display: block;"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <div class="card card-outline card-dark">
                <div class="card-body table-responsive p-0" style="height: 450px;">
                    <table class=" table table-head-fixed text-nowrap table-striped table-warning table-hover">
                        <thead>
                            <tr>
                                <th class="text-start">Producto</th>
                                <th class="text-center">Cantidad</th>
                                <th class="text-center">Sub Total</th>
                                <th class="text-center">Total</th>
                            </tr>
                        </thead>
                        <tbody id="products-data-table">
                            @php $__consumed = $doctorData['consumedProductsInTheMonthByDoctor'] ?? []; @endphp
                            @if (count($__consumed) > 0)
                                @foreach ($__consumed as $product)
                                    <tr>
                                        <td>{{ $product['articulo'] }}</td>
                                        <td class="text-center">{{ $product['total_cantidad'] }}</td>
                                        <td class="text-center">
                                            {{ number_format($product['total_subtotal'] / $product['total_cantidad'], 2) }}
                                        </td>
                                        <td class="text-center">S/ {{ $product['total_subtotal'] }}</td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-5">No hay datos en el mes para
                                        esta tabla.</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @section('css')
        <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css"
            rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css">
    @stop

    @section('js')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
        <script>
            function createChart(canvasId, labels, data, datasetLabel, type, backgroundColor = 'rgba(205, 32, 32, 1)',
                borderColor = 'rgba(121, 17, 17, 0.51)', extraDatasetProps = {}, extraOptions = {}) {
                const ctx = $(canvasId).get(0).getContext('2d');
                return new Chart(ctx, {
                    type: type,
                    data: {
                        labels: labels,
                        datasets: [{
                            label: datasetLabel,
                            backgroundColor: backgroundColor,
                            borderColor: borderColor,
                            data: data,
                            ...extraDatasetProps
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    precision: 0
                                }
                            }
                        },
                        ...extraOptions
                    }
                });
            }
        </script>
        <script>
            @php
                $__initialDoctorData = $doctorData ?? [
                    'doctor' => 'N/A',
                    'tipoMedico' => 'N/A',
                    'amountSpentByDoctorGroupedByMonth' => array_fill(1, 12, 0),
                    'amountSpentByDoctorGroupedByTipo' => [],
                    'topMostConsumedProductsInTheMonthByDoctor' => [],
                    'consumedProductsInTheMonthByDoctor' => [],
                ];
            @endphp
            const productsConsumedTable = $('#products-data-table');
            const initialValues = @json($__initialDoctorData);
            const doctorNameLabel = $('#doctor-name-label');
            let typingTimer;
            const debounceDelay = 300;
            let selectedIndex = -1;
            const doctorNameInput = $('#name_doctor');
            const doctorIdInput = $('#id_doctor');
            const monthYearInput = $('#month_year');
            const suggestionsList = $('#doctorsList');
            $('#monthYearPicker').datepicker({
                format: 'mm/yyyy',
                startView: 'months',
                minViewMode: 'months',
                autoclose: true,
                endDate: '12/31/' + new Date().getFullYear()
            });
            const monthLabels = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre',
                'Octubre', 'Noviembre', 'Diciembre'
            ];
            let amountSpentByDoctorGroupedByMonthChart = createChart('#amountSpentByDoctorGroupedByMonthChart', monthLabels,
                Object.values(initialValues.amountSpentByDoctorGroupedByMonth).map(Number), 'Monto de Inversión del Doctor',
                'line', 'rgba(212, 12, 13, 1)', 'rgba(255, 0, 0, 0.4)', {
                    pointStyle: 'circle',
                    pointRadius: 8,
                    pointHoverRadius: 12
                }, {
                    elements: {
                        line: {
                            tension: 0.1
                        }
                    }
                });
            let amountSpentByDoctorGroupedByTipoChart = createChart('#amountSpentByDoctorGroupedByTipoChart', initialValues
                .amountSpentByDoctorGroupedByTipo.map(i => i.tipo), initialValues.amountSpentByDoctorGroupedByTipo.map(i =>
                    i.total_sub_total), 'Monto de Inversión del Doctor', 'bar');
            let topMostConsumedProductsInTheMonthByDoctorChart = createChart('#topMostConsumedProductsInTheMonthByDoctorChart',
                initialValues.topMostConsumedProductsInTheMonthByDoctor.map(i => i.articulo), initialValues
                .topMostConsumedProductsInTheMonthByDoctor.map(i => i.total_cantidad), 'Cantidad comprada', 'bar');
        </script>
        <script>
            $(document).ready(() => {
                if (!initialValues.topMostConsumedProductsInTheMonthByDoctor || initialValues
                    .topMostConsumedProductsInTheMonthByDoctor.length < 1) {
                    $('#topMostConsumedProductsInTheMonthByDoctorChart').siblings('.no-data-message').show()
                }
                if (!initialValues.amountSpentByDoctorGroupedByTipo || initialValues.amountSpentByDoctorGroupedByTipo
                    .length < 1) {
                    $('#amountSpentByDoctorGroupedByTipoChart').siblings('.no-data-message').show()
                }
            })
            doctorNameInput.on('keyup', function(e) {
                if (['ArrowUp', 'ArrowDown', 'Enter'].includes(e.key)) return;
                clearTimeout(typingTimer);
                const query = doctorNameInput.val();
                if (query.length < 2) {
                    suggestionsList.fadeOut();
                    return;
                }
                typingTimer = setTimeout(function() {
                    $.ajax({
                        url: '/doctors/search',
                        type: 'GET',
                        data: {
                            _token: '{{ csrf_token() }}',
                            q: query
                        },
                        success: function(data) {
                            let html = '';
                            if (data.length > 0) {
                                data.forEach(function(doctor) {
                                    html +=
                                        `<a href="" class="list-group-item list-group-item-action doctor-item" data-id="${doctor.id}" data-name="${doctor.name}">${doctor.name}</a>`;
                                });
                                selectedIndex = -1;
                            }
                            suggestionsList.html(html).fadeIn();
                        }
                    });
                }, debounceDelay);
            });
            $(document).on('click', '.doctor-item', function(e) {
                e.preventDefault();
                doctorNameInput.val($(this).data('name'));
                doctorIdInput.val($(this).data('id'));
                suggestionsList.fadeOut();
            });
            doctorNameInput.on('keydown', function(e) {
                const items = suggestionsList.find('.doctor-item');
                if (!suggestionsList.is(':visible') || items.length === 0) return;
                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    selectedIndex = (selectedIndex + 1) % items.length;
                    highlightItem(items, selectedIndex);
                }
                if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    selectedIndex = (selectedIndex - 1 + items.length) % items.length;
                    highlightItem(items, selectedIndex);
                }
                if (e.key === 'Enter' && selectedIndex >= 0) {
                    e.preventDefault();
                    const selectedItem = $(items[selectedIndex]);
                    doctorNameInput.val(selectedItem.text());
                    doctorIdInput.val(selectedItem.data('id'));
                    suggestionsList.fadeOut();
                }
            });

            function highlightItem(items, index) {
                items.removeClass('active');
                if (index >= 0 && index < items.length) {
                    const item = $(items[index]);
                    item.addClass('active');
                    const itemTop = item.position().top;
                    const itemBottom = itemTop + item.outerHeight();
                    const containerHeight = suggestionsList.height();
                    if (itemTop < 0) {
                        suggestionsList.scrollTop(suggestionsList.scrollTop() + itemTop);
                    } else if (itemBottom > containerHeight) {
                        suggestionsList.scrollTop(suggestionsList.scrollTop() + (itemBottom - containerHeight));
                    }
                }
            }
            $(document).click(function(e) {
                if (!$(e.target).closest('#name_doctor, #doctorsList').length) {
                    suggestionsList.fadeOut();
                }
            });
            doctorNameInput.on('input', function() {
                doctorIdInput.val('');
            });

            function fetchDoctorData() {
                const id = doctorIdInput.val().trim();
                const name = doctorNameInput.val().trim();
                const monthYear = monthYearInput.val().trim();
                if (!id && !name) {
                    reRenderCharts(initialValues);
                    doctorNameLabel.text(
                        `Dr. con mayor consumo del año: ${initialValues.doctor} - Tipo: ${initialValues.tipoMedico}`);
                    return;
                }
                $.ajax({
                    url: "{{ route('reports.doctores.getDoctorReport') }}",
                    method: 'GET',
                    data: {
                        id_doctor: id,
                        name_doctor: name,
                        month_year: monthYear
                    },
                    success: function(data) {
                        reRenderCharts(data);
                    },
                    error: function() {
                        toastr.danger('No se encontraron datos para este doctor.');
                    }
                });
            }

            function reRenderTable(data) {
                productsConsumedTable.html('');
                if (data.length > 0) {
                    productsConsumedTable.html(data.map(i =>
                        `<tr><td>${i.articulo}</td><td class='text-center'>${i.total_cantidad}</td><td class='text-center'>S/ ${(i.total_subtotal/i.total_cantidad).toFixed(2)}</td><td class='text-center'>S/ ${(i.total_subtotal).toFixed(2)}</td></tr>`
                        ).join(''));
                } else {
                    productsConsumedTable.html(
                        `<tr><td colspan='4' class='text-center text-muted py-5'>No hay datos en el mes para esta tabla.</td></tr>`
                        );
                }
            }

            function reRenderCharts(doctorData) {
                doctorNameLabel.text(`Dr. ${doctorData.doctor} - Tipo: ${doctorData.tipoMedico}`);
                amountSpentByDoctorGroupedByMonthChart.data.datasets[0].data = Object.values(doctorData
                    .amountSpentByDoctorGroupedByMonth);
                amountSpentByDoctorGroupedByMonthChart.update();
                topMostConsumedProductsInTheMonthByDoctorChart.data.labels = doctorData
                    .topMostConsumedProductsInTheMonthByDoctor.map(i => i.articulo);
                topMostConsumedProductsInTheMonthByDoctorChart.data.datasets[0].data = doctorData
                    .topMostConsumedProductsInTheMonthByDoctor.map(i => i.total_cantidad);
                topMostConsumedProductsInTheMonthByDoctorChart.update();
                if (doctorData.topMostConsumedProductsInTheMonthByDoctor.length < 1) {
                    $('#topMostConsumedProductsInTheMonthByDoctorChart').siblings('.no-data-message').fadeIn();
                } else {
                    $('#topMostConsumedProductsInTheMonthByDoctorChart').siblings('.no-data-message').hide();
                }
                amountSpentByDoctorGroupedByTipoChart.data.labels = doctorData.amountSpentByDoctorGroupedByTipo.map(i => i
                .tipo);
                amountSpentByDoctorGroupedByTipoChart.data.datasets[0].data = doctorData.amountSpentByDoctorGroupedByTipo.map(
                    i => i.total_sub_total);
                if (doctorData.amountSpentByDoctorGroupedByTipo.length < 1) {
                    $('#amountSpentByDoctorGroupedByTipoChart').siblings('.no-data-message').fadeIn();
                } else {
                    $('#amountSpentByDoctorGroupedByTipoChart').siblings('.no-data-message').hide();
                }
                amountSpentByDoctorGroupedByTipoChart.update();
                reRenderTable(doctorData.consumedProductsInTheMonthByDoctor);
            }
            $('#doctorSearchForm').on('submit', function(e) {
                e.preventDefault();
                fetchDoctorData();
            });
        </script>
    </div>
@stop
