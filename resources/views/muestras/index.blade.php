@extends('adminlte::page')

@section('title', 'Muestras')

@section('content_header')
@stop

@php
    $user = auth()->user();
    $role = $user?->role->name;
    $currentParams = request()->except(['page']);
    $canViewPricing =
        $user &&
        ($user->can('muestras.updatePrice') ||
            $user->can('muestras.aproveJefeOperaciones') ||
            $user->can('muestras.aproveJefeComercial'));
    $canApproveCoordinadora = $user && $user->can('muestras.aproveCoordinadora');
    $canApproveJefeComercial = $user && $user->can('muestras.aproveJefeComercial');
    $canApproveJefeOperaciones = $user && $user->can('muestras.aproveJefeOperaciones');
    $canApproveAny = $canApproveCoordinadora || $canApproveJefeComercial || $canApproveJefeOperaciones;
    $canManageTipoMuestra = $user && $user->can('muestras.updateTipoMuestra');
    $canEditPrice = $user && $user->can('muestras.updatePrice');
@endphp

@section('content')
    @can('muestras.index')
        <div class="container-fluid">
            @include('messages')

            {{-- Header Grobdi --}}
            <div class="grobdi-header">
                <div class="grobdi-title">
                    <h1>Estado de las Muestras</h1>
                    <div class="d-flex gap-2">
                        @can('muestras.create')
                            <a href="{{ route('muestras.create') }}" class="btn">
                                <i class="fas fa-plus-circle mr-1"></i> Agregar Muestra
                            </a>
                        @endcan
                        @can('muestras.exportExcel')
                            <a href="{{ route('muestras.exportExcel') }}" class="btn">
                                <i class="fas fa-file-excel mr-1"></i>Exportar Excel
                            </a>
                        @endcan
                    </div>
                </div>

                <div class="grobdi-filter">
                    <form method="GET" action="{{ route('muestras.index') }}">
                        <div class="row">
                            <div class="col-md-3">
                                <label for="search">Buscar por nombre</label>
                                <input type="text" id="search" name="search" value="{{ request('search') }}"
                                    placeholder="Buscar por nombre...">
                            </div>
                            <div class="col-md-3">
                                <label for="filter_by_date">Filtrar por fecha</label>
                                <select id="filter_by_date" name="filter_by_date">
                                    <option value="registro" {{ request('filter_by_date') == 'registro' ? 'selected' : '' }}>
                                        Por fecha de registro
                                    </option>
                                    <option value="entrega" {{ request('filter_by_date') == 'entrega' ? 'selected' : '' }}>
                                        Por fecha de entrega
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="date_since">Desde</label>
                                <input type="date" id="date_since" name="date_since" value="{{ request('date_since') }}">
                            </div>
                            <div class="col-md-2">
                                <label for="date_to">Hasta</label>
                                <input type="date" id="date_to" name="date_to" value="{{ request('date_to') }}">
                            </div>
                            <div class="col-md-2">
                                <label>&nbsp;</label>
                                <button type="submit">Buscar</button>
                            </div>
                        </div>
                    </form>

                    <hr style="border-color: var(--grobdi-slate-300);" class="my-4">

                    <form id="orderForm" action="{{ route('muestras.index') }}" method="GET">
                        <div class="row">
                            <div class="col-md-6">
                                <label for="orderSelect">Ordenar por</label>
                                <select id="orderSelect" onchange="window.location = this.value;">
                                    <option
                                        value="{{ route('muestras.index', array_merge($currentParams, ['order_by' => 'fecha_registro'])) }}"
                                        {{ request('order_by') == 'fecha_registro' ? 'selected' : '' }}>
                                        Fecha de Registro
                                    </option>
                                    <option
                                        value="{{ route('muestras.index', array_merge($currentParams, ['order_by' => 'fecha_entrega'])) }}"
                                        {{ request('order_by') == 'fecha_entrega' ? 'selected' : '' }}>
                                        Fecha de Entrega
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label>Estado de Laboratorio</label>
                                <div class="grobdi-radio-group d-flex flex-row flex-wrap align-items-center">
                                    <label class="grobdi-radio mb-0">
                                        <input type="radio" name="lab_state" value="Pendiente"
                                            onchange="document.getElementById('filterForm').submit();"
                                            {{ request('lab_state') == 'Pendiente' ? 'checked' : '' }}>
                                        <span class="radio-custom"></span>
                                        <span class="radio-label">Pendientes</span>
                                    </label>

                                    <label class="grobdi-radio mb-0">
                                        <input type="radio" name="lab_state" value=""
                                            onchange="window.location='{{ route('muestras.index') }}';"
                                            {{ !request()->has('lab_state') ? 'checked' : '' }}>
                                        <span class="radio-custom"></span>
                                        <span class="radio-label">Todas</span>
                                    </label>

                                    <label class="grobdi-radio mb-0">
                                        <input type="radio" name="lab_state" value="Elaborado"
                                            onchange="document.getElementById('filterForm').submit();"
                                            {{ request('lab_state') == 'Elaborado' ? 'checked' : '' }}>
                                        <span class="radio-custom"></span>
                                        <span class="radio-label">Elaboradas</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-hover table-grobdi" id="table_muestras">
                    <thead>
                        <tr>
                            <th class="px-3">ID</th>
                            <th>Nombre de la Muestra</th>
                            <th>Clasificación</th>
                            <th>Tipo de Frasco</th>
                            <th>Tipo de Muestra</th>
                            <th>Cantidad</th>
                            @if ($canViewPricing)
                                <th>Precio Por Unidad</th>
                                <th>Precio Total</th>
                            @endif
                            @if ($canApproveAny)
                                <th>Aprobar Muestra</th>
                            @endif
                            @can('muestras.markAsElaborated')
                                <th>Estado de Laboratorio</th>
                            @endcan
                            <th>Creado por</th>
                            <th>Doctor</th>
                            <th>Fecha y Hora acordada para la Entrega</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($muestras as $index => $muestra)
                            <tr id="muestra_{{ $muestra->id }}">
                                <td>{{ $muestra->id }}</td>
                                <td>{{ $muestra->nombre_muestra }}</td>
                                <td>
                                    @php
                                        $isSetClasificacion = $muestra->clasificacion;
                                    @endphp
                                    @if ($isSetClasificacion)
                                        {{ $muestra->clasificacion->nombre_clasificacion }}
                                        <hr>
                                        <strong>Unidad de Medida:</strong>
                                        {{ $muestra->clasificacion->unidadMedida->nombre_unidad_de_medida }}
                                    @endif
                                </td>
                                <td>{{ $muestra->tipo_frasco ?? 'No asignado' }}</td>
                                <td>
                                    @if (in_array($role, ['admin', 'coordinador-lineas', 'supervisor']) &&
                                            !$muestra->isAprovedByCoordinadora() &&
                                            $muestra->isActive())
                                        <div class="d-flex">
                                            <select class="custom-select rounded-0 mr-2" name="tipo_muestra"
                                                data-id="{{ $muestra->id }}"
                                                data-original="{{ $muestra->tipoMuestra ? $muestra->tipoMuestra->id : '' }}"
                                                {{ $muestra->isAprovedByCoordinadora() ? 'disabled' : '' }}>
                                                <option disabled {{ !$muestra->tipoMuestra ? 'selected' : '' }} value="0">
                                                    Seleccione un tipo de muestra</option>
                                                @foreach ($tiposMuestra as $tipo)
                                                    <option value="{{ $tipo->id }}"
                                                        {{ $muestra->tipoMuestra && $muestra->tipoMuestra->id === $tipo->id ? 'selected disabled' : '' }}>
                                                        {{ $tipo->name }}</option>
                                                @endforeach
                                            </select>
                                            <button type="button" class="btn btn-primary save-tipo-muestra-btn"
                                                data-id="{{ $muestra->id }}" disabled>
                                                <i class="fas fa-save"></i>
                                            </button>
                                        </div>
                                    @else
                                        <span
                                            class="{{ $muestra->tipoMuestra ? '' : 'text-danger' }}">{{ $muestra->tipoMuestra->name ?? 'No se ha asignado un tipo de muestra' }}</span>
                                    @endif
                                </td>
                                <td>{{ $muestra->cantidad_de_muestra }}</td>
                                @if (in_array($role, ['admin', 'jefe-comercial', 'contabilidad', 'jefe-operaciones']))
                                    @php
                                        $isPrecioNotSetted = empty($muestra->precio);
                                    @endphp
                                    <td>
                                        @php $puedeEditarPrecio = !$muestra->isAprovedByJefeOperaciones() && in_array($role, ['admin', 'contabilidad']); @endphp
                                        @if ($puedeEditarPrecio && $muestra->isActive())
                                            <div class="d-flex align-items-center">
                                                <input type="number" name="price-input" class="form-control precio-input mr-2"
                                                    data-id="{{ $muestra->id }}" value="{{ $muestra->precio }}"
                                                    data-original="{{ $muestra->precio }}" required>
                                                <button type="button" class="btn btn-primary save-precio-btn"
                                                    data-id="{{ $muestra->id }}">
                                                    <i class="fas fa-save"></i>
                                                </button>
                                            </div>
                                        @else
                                            <span
                                                class="{{ $isPrecioNotSetted ? 'text-danger' : '' }}">{{ $isPrecioNotSetted ? 'No asignado' : 'S/ ' . number_format($muestra->precio, 2) }}</span>
                                        @endif
                                    </td>
                                    <td class="precio_total {{ $isPrecioNotSetted ? 'text-danger' : '' }}"
                                        data-id="{{ $muestra->id }}">
                                        {{ $muestra->precio && $muestra->cantidad_de_muestra ? 'S/ ' . number_format($muestra->precio * $muestra->cantidad_de_muestra, 2) : 'No asignado' }}
                                    </td>
                                @endif
                                @if (in_array($role, ['admin', 'coordinador-lineas', 'jefe-comercial', 'jefe-operaciones', 'supervisor']))
                                    <td>
                                        @php
                                            $approvalConfigs = [
                                                'supervisor' => [
                                                    'method' => 'isAprovedByCoordinadora',
                                                    'url' => route('muestras.aproveCoordinadora', [
                                                        'muestra' => $muestra->id,
                                                    ]),
                                                    'canApprove' =>
                                                        in_array($role, ['admin', 'supervisor']) &&
                                                        $muestra->isActive(),
                                                ],
                                                'jefe-comercial' => [
                                                    'method' => 'isAprovedByJefeComercial',
                                                    'url' => route('muestras.aproveJefeComercial', [
                                                        'muestra' => $muestra->id,
                                                    ]),
                                                    'canApprove' =>
                                                        in_array($role, ['admin', 'jefe-comercial']) &&
                                                        $muestra->isActive(),
                                                ],
                                                'jefe-operaciones' => [
                                                    'method' => 'isAprovedByJefeOperaciones',
                                                    'url' => route('muestras.aproveJefeOperaciones', [
                                                        'muestra' => $muestra->id,
                                                    ]),
                                                    'canApprove' =>
                                                        in_array($role, ['admin', 'jefe-operaciones']) &&
                                                        $muestra->isActive(),
                                                ],
                                            ];
                                        @endphp

                                        @foreach ($approvalConfigs as $rolKey => $config)
                                            @if ($role === $rolKey || $role === 'admin')
                                                <input type="checkbox" class="approval-checkbox"
                                                    style="width: 1.3em; height: 1.3em;" data-id="{{ $muestra->id }}"
                                                    data-url="{{ $config['url'] }}"
                                                    {{ $muestra->tipoMuestra === null ? 'disabled' : '' }}
                                                    {{ $muestra->{$config['method']}() ? 'checked disabled' : '' }}
                                                    {{ $config['canApprove'] ? '' : 'disabled' }}>
                                            @endif
                                        @endforeach
                                    </td>
                                @endif
                                @can('muestras.markAsElaborated')
                                    <td>
                                        <select class="custom-select rounded-0 mr-2" name="lab_state"
                                            data-id="{{ $muestra->id }}"
                                            {{ $muestra->isProduced() || !$muestra->isActive() ? 'disabled' : '' }}>
                                            <option value="0" {{ $muestra->isProduced() ? 'selected' : '' }}>
                                                Pendiente</option>
                                            <option value="1" {{ $muestra->isProduced() ? 'selected disabled' : '' }}>
                                                Elaborada
                                            </option>
                                        </select>
                                    </td>
                                @endcan
                                <td>{{ $muestra->creator ? $muestra->creator->name : 'Desconocido' }}</td>
                                <td>{{ optional($muestra->doctor)->name ?? ($muestra->name_doctor ?? 'No asignado') }}</td>
                                <td>
                                    @if (!$muestra->isAprovedByCoordinadora())
                                        @can('muestras.updateDateTimeScheduled')
                                            <form action="{{ route('muestras.updateDateTimeScheduled', $muestra->id) }}"
                                                method="POST" class="d-flex flex-column" id="fecha_form_{{ $muestra->id }}">
                                                @csrf
                                                @method('PUT')
                                                <input type="datetime-local" name="datetime_scheduled"
                                                    class="form-control mb-2 flatpickr-datetime"
                                                    value="{{ old('datetime_scheduled', $muestra->datetime_scheduled ? \Carbon\Carbon::parse($muestra->datetime_scheduled)->format('Y-m-d\TH:i') : '') }}"
                                                    id="fecha_{{ $muestra->id }}"
                                                    min="{{ \Carbon\Carbon::now()->format('Y-m-d\TH:i') }}">
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="fas fa-save"></i> Guardar
                                                </button>
                                            </form>
                                        @endcan
                                    @else
                                        <span
                                            class="{{ $muestra->datetime_scheduled ? '' : 'text-danger' }}">{{ $muestra->datetime_scheduled ? \Carbon\Carbon::parse($muestra->datetime_scheduled)->format('d/m/Y H:i') : 'No asignada' }}</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="row">
                                        <button class="col-12 btn btn-success btn-sm btn-show-details"
                                            data-id="{{ $muestra->id }}" data-toggle="modal"
                                            data-target="#muestraDetailsModal">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        @can('muestras.edit')
                                            <a href="{{ route('muestras.edit', $muestra->id) }}"
                                                class="btn btn-primary btn-sm col-12 mt-1">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        @endcan
                                        @can('muestras.disable')
                                            <button class="btn btn-danger btn-sm btn-show-delete col-12 mt-1"
                                                data-id="{{ $muestra->id }}" data-toggle="modal" data-target="#deleteModal">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div>
            @include('deleteModal')
            @include('muestras.details')
        </div>
        <div class="d-flex justify-content-end mt-3">
            {{ $muestras->appends(request()->query())->links() }}
        </div>
    @endcan
@stop

@section('plugins.Chartjs', true)
@section('plugins.Sweetalert2', true)
@section('js')
    <script src="{{ asset('js/chart-helpers.js') }}"></script>
    <script src="{{ asset('js/sweetalert2-factory.js') }}"></script>
    <script>
        $(document).ready(() => {
            const deleteModal = $('#deleteModal');
            const deleteReason = $('#delete_reason');
            let currentTimeLineChart;

            const dateSince = flatpickr('#date_since', {
                altInput: true,
                dateFormat: "Y-m-d",
                altFormat: "d/m/Y",
                locale: 'es',
                maxDate: "today"
            });
            const dateTo = flatpickr('#date_to', {
                altInput: true,
                dateFormat: "Y-m-d",
                altFormat: "d/m/Y",
                locale: 'es',
                maxDate: "today"
            });

            flatpickr('.flatpickr-datetime', {
                enableTime: true,
                altInput: true,
                altFormat: "d/m/Y H:i",
                dateFormat: "Y-m-d H:i",
                time_24hr: true,
                minDate: "today",
                locale: "es",
            });

            $(".save-precio-btn").prop('disabled', true);

            $('input[name="price-input"]').on('input', function() {
                const input = $(this);
                let value = input.val();

                // Validar y cortar a dos decimales si es necesario
                if (value.includes('.')) {
                    const [intPart, decimalPart] = value.split('.');
                    if (decimalPart.length > 2) {
                        value = intPart + '.' + decimalPart.slice(0, 2);
                        input.val(value);
                    }
                }
                const currentValue = parseFloat(value);
                const originalValue = parseFloat(input.data('original')) || 0;
                const muestraId = input.data('id');
                const saveBtn = $(`.save-precio-btn[data-id="${muestraId}"]`);

                if (!currentValue || currentValue < 0 || currentValue === originalValue) {
                    saveBtn.prop('disabled', true);
                } else {
                    saveBtn.prop('disabled', false);
                }
            });

            $('.btn-show-details').on('click', function() {
                const muestraId = $(this).data('id');
                $.ajax({
                    url: `{{ url(path: 'muestras') }}/${muestraId}`,
                    type: 'GET',
                    success: function(response) {
                        if (!response.success) {
                            toast(response.message ||
                                'No se pudieron cargar los detalles de la muestra',
                                ToastIcon.ERROR);
                            return;
                        }

                        const muestra = response.data;

                        createTimeLineChart(muestra.status, muestra.datetime_scheduled,
                            muestra.created_at, muestra.creator);

                        if (!muestra.state) {
                            $('#modal_title').text('Detalles de la Muestra - DESHABILITADA');
                        } else {
                            $('#modal_title').text('Detalles de la Muestra');
                        }
                        $('#nombre_muestra').text(muestra.nombre_muestra);
                        $('#clasificacion_muestra').text(
                            `${muestra.clasificacion ? muestra.clasificacion.nombre_clasificacion : 'No disponible'}`
                        );
                        $('#unidad_medida').text(muestra.clasificacion.unidad_medida
                            .nombre_unidad_de_medida ?? 'No disponible');
                        $('#tipo_frasco').text(muestra.tipo_frasco);
                        if (muestra.tipo_frasco == 'Frasco Muestra') {
                            $('#presentacion_frasco').hide();
                        } else {
                            $('#presentacion_frasco').show();
                            $('#presentacion_frasco_original').text(
                                `${muestra.clasificacion_presentacion?.quantity} ${muestra.clasificacion.unidad_medida.nombre_unidad_de_medida}`
                            )
                        }
                        $('#tipo_muestra').text(muestra.tipo_muestra ? muestra.tipo_muestra
                            .name : 'No disponible');
                        $('#cantidad_muestra').text(muestra.cantidad_de_muestra);
                        $('#precio_unitario').text(muestra.precio ? `S/ ${muestra.precio}` :
                            'No asignado');
                        $('#precio_total').text(muestra.precio && muestra.cantidad_de_muestra ?
                            `S/ ${muestra.precio * muestra.cantidad_de_muestra}` :
                            'No asignado');
                        $('#observaciones').text(muestra.observacion ?? 'Ninguna');
                        $('#doctor').text(muestra.doctor ? muestra.doctor.name : (muestra
                            .name_doctor ?? 'No asignado'));
                        $('#creado_por').text(muestra.creator?.name ?? 'Desconocido');

                        $('#comentarioForm').attr('action',
                            `/muestras/laboratorio/${muestra.id}/comentario`);
                        $('#comentarioForm textarea[name="comentario_lab"]').val(muestra
                            .comentarios || '');
                        $('#comentario-laboratorio').text(muestra.comentarios ?
                            `"${muestra.comentarios}"` :
                            '"No hay comentario del laboratorio disponible."')
                        const deleteReasonDiv = $('#delete-reason-div');
                        if (muestra.delete_reason) {
                            deleteReasonDiv.show();
                            $('#delete-reason').text(muestra.delete_reason);
                        } else {
                            deleteReasonDiv.hide();
                        }
                    },
                    error: function(xhr) {
                        const message = xhr.responseJSON?.message || xhr.statusText ||
                            "Error desconocido";
                        toast(message, ToastIcon.ERROR);
                    }
                });
            });

            const transformToCapitalizedArray = (text) => {
                if (typeof text !== 'string' || text.length === 0) {
                    return [];
                }

                const words = text.replace(/_/g, ' ').split(' ');

                const capitalizedWords = words.map(word => {
                    if (word.length === 0) {
                        return ''; // Maneja el caso de múltiples guiones bajos consecutivos
                    }
                    return word.charAt(0).toUpperCase() + word.slice(1).toLowerCase();
                });

                return capitalizedWords;
            };

            function createTimeLineChart(statusEvents, datetime_scheduled, created_at, creator) {
                if (currentTimeLineChart) {
                    currentTimeLineChart.destroy();
                    currentTimeLineChart = null;
                }

                const events = [];

                events.push({
                    label: 'Creada',
                    timestamp: new Date(created_at),
                    comment: `Creada por: ${creator.name}`
                })

                if (Array.isArray(statusEvents)) {
                    statusEvents.forEach(ev => {
                        let label = ev.type || 'Evento';
                        events.push({
                            label,
                            timestamp: new Date(ev.created_at),
                            comment: ev.comment || ''
                        });
                    });
                }

                if (datetime_scheduled) {
                    events.push({
                        label: 'Fecha de Entrega',
                        timestamp: new Date(datetime_scheduled),
                        comment: 'Fecha de Entrega'
                    });
                }

                events.sort((a, b) => a.timestamp - b.timestamp);

                const eventNames = events.map(e => transformToCapitalizedArray(e.label));
                const timeLabels = events.map(e => {
                    const d = new Date(e.timestamp);
                    const datePart = d.toLocaleDateString('es-PE');
                    const timePart = d.toLocaleTimeString('es-PE', {
                        hour: '2-digit',
                        minute: '2-digit',
                        hour12: false
                    });
                    return [datePart, timePart];
                });
                const comments = events.map(e => e.comment);
                const mainData = events.map((_, i) => ({
                    x: i,
                    y: 0
                }));


                const mainDataset = [{
                    label: 'Línea de Tiempo',
                    data: mainData,
                    borderColor: events.map((event, i) =>
                        event.label === 'Fecha de Entrega' ?
                        'rgba(0, 105, 217, 1)' :
                        'rgba(33, 136, 56, 1)'
                    ),
                    backgroundColor: events.map((event, i) =>
                        event.label === 'Fecha de Entrega' ?
                        'rgba(0, 105, 217, 0.6)' :
                        'rgba(25, 135, 84, 0.6)'
                    ),
                    borderWidth: 2,
                    pointRadius: 6,
                    pointHoverRadius: 8,
                    fill: false,
                    showLine: true,
                    spanGaps: true,
                }];

                const options = {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            type: 'category',
                            labels: timeLabels,
                            position: 'bottom'
                        },
                        x2: {
                            type: 'category',
                            labels: eventNames,
                            position: 'top',
                        },
                        y: {
                            display: false,
                            min: -0.5,
                            max: 0.5,
                        }
                    },
                    plugins: {
                        tooltip: {
                            displayColors: false,
                            callbacks: {
                                label: function(context) {
                                    const dataIndex = context.dataIndex;
                                    return comments[dataIndex] || eventNames[dataIndex];
                                }
                            }
                        },
                        legend: {
                            display: false
                        }
                    }
                };
                currentTimeLineChart = createChart('#muestra-status-timeline-chart', [], mainDataset, 'line',
                    options);

                if (currentTimeLineChart) {
                    detectChartDataLength(currentTimeLineChart);
                }
            }

            $('.btn-show-delete').on('click', function(btn) {
                const muestraId = $(this).data('id');
                deleteModal.data('id', muestraId);
                deleteReason.val('');
            })

            $('#deleteForm').on('submit', function(e) {
                e.preventDefault();
                const form = $(this);
                const muestraId = deleteModal.data('id');
                const delete_reason = deleteReason.val().trim();

                if (!muestraId) {
                    toast(`ID de muestra no encontrado.`, ToastIcon.ERROR);
                    return;
                }
                if (!delete_reason) {
                    toast(`Por favor ingrese el motivo de eliminación.`, ToastIcon.WARNING);
                    return;
                }

                const baseUrl = "{{ route('muestras.disable', ':id') }}";
                const url = baseUrl.replace(':id', muestraId);

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: {
                        _method: 'DELETE',
                        _token: '{{ csrf_token() }}',
                        delete_reason: delete_reason
                    },
                    success: function(response) {
                        if (!response.success) {
                            toast(response.message ||
                                'No fue posible eliminar el item.', ToastIcon.WARNING);
                            return;
                        }

                        toast(response.message || 'Item eliminado correcamente.',
                            ToastIcon.INFO);
                        $(`#muestra_${muestraId}`).fadeOut();
                        $('#close-modal').click();
                    },
                    error: function(xhr) {
                        const message = xhr.responseJSON?.message || xhr.statusText ||
                            "Error al eliminar el item";
                        toast(message, ToastIcon.ERROR);
                    }
                });
            })
        });

        $('select[name="lab_state"]').on('change', function(select) {
            const selectElement = $(this);
            const selectedValue = parseInt($(this).val());
            const muestraId = $(this).data('id');

            selectElement.prop('disabled', true);
            if (selectedValue === 1) {
                if (!confirm('Seguro que desea marcar como ELABORADA esta muestra?')) {
                    $(this).prop('selectedIndex', 0);
                    selectElement.prop('disabled', false);
                    e.preventDefault();
                    return;
                }

                $.ajax({
                    url: `{{ url('muestras') }}/laboratorio/${muestraId}/state`,
                    type: 'POST',
                    data: {
                        _method: 'PUT',
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            toast(response.message ||
                                'Muestra marcada como elaborada correctamente.', ToastIcon.SUCCESS);
                            selectElement.prop('disabled', true);
                        } else {
                            toast(response.message ||
                                'No fue posible marcar la muestra como elaborada.', ToastIcon
                                .WARNING);
                            selectElement.prop('selectedIndex', 0);
                            selectElement.prop('disabled', false);
                        }
                    },
                    error: function(xhr) {
                        const message = xhr.responseJSON?.message || xhr.statusText ||
                            "Error al actualizar el estado de la muestra.";
                        toast(message, ToastIcon.ERROR);
                        selectElement.prop('selectedIndex', 0);
                        selectElement.prop('disabled', false);
                    }
                });
            }
        });

        $('select[name="tipo_muestra"]').on('change', function(select) {
            const selectedValue = $(this).val();
            const muestraId = $(this).data('id');
            const saveBtn = $(`.save-tipo-muestra-btn[data-id="${muestraId}"]`)

            if (selectedValue > 0) {
                saveBtn.prop('disabled', false);
            } else {
                saveBtn.prop('disabled', false);
            }
        });

        $('.save-tipo-muestra-btn').on('click', function() {
            const btn = $(this)
            if (btn.disabled) return;
            btn.prop('disabled', true);
            const muestraId = btn.data('id');
            const checkbox = $(`.coordinadora-checkbox[data-id='${muestraId}']`);
            const select = $(`select[name="tipo_muestra"][data-id="${muestraId}"]`);
            const tipoMuestra = select.val();

            $.ajax({
                url: `{{ url('muestras') }}/edit/${muestraId}/update-tipo-muestra`,
                type: 'POST',
                data: {
                    _method: 'PUT',
                    _token: '{{ csrf_token() }}',
                    id_tipo_muestra: tipoMuestra
                },
                success: function(response) {
                    if (response.success) {
                        const optionSelected = select.find('option:selected');
                        const previousId = select.data('original') ?? null;
                        if (previousId) {
                            select.find(`option[value="${previousId}"]`).prop('disabled', false);
                        }
                        optionSelected.prop('disabled', true);
                        select.data('original', tipoMuestra);
                        toast(response.message ||
                            'Tipo de muestra actualizado correctamente.', ToastIcon.SUCCESS);
                        checkbox.prop('disabled', false);
                    } else {
                        toast(response.message ||
                            'Ocurrió un error en la actualización del tipo de muestra.', ToastIcon
                            .WARNING);
                        checkbox.prop('checked', false).prop('disabled', true);
                    }
                },
                error: function(xhr) {
                    const message = xhr.responseJSON?.message || xhr.statusText ||
                        "Error al actualizar el tipo de muestra.";
                    toast(message, ToastIcon.ERROR);
                    btn.prop('disabled', true);
                    checkbox.prop('disabled', true);
                }
            });
        });

        $('.approval-checkbox').on('click', function(e) {
            const checkbox = $(this);

            if (checkbox.prop('disabled')) return;

            const muestraId = checkbox.data('id');
            let url = checkbox.data('url');

            if (!confirm('Seguro que desea aprobar esta muestra?')) {
                checkbox.prop('checked', false);
                return;
            }

            checkbox.prop('disabled', false);

            $.ajax({
                url: url,
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    _method: 'PUT',
                },
                success: function(response) {
                    if (!response.success) {
                        toast(response.message || 'Error al realizar la aprobación.',
                            ToastIcon.WARNING);
                        checkbox.prop('checked', false).prop('disabled', false);
                        return;
                    }
                    toast(response.message || 'Aprobación realizada correctamente.',
                        ToastIcon.SUCCESS);

                    $(`select[name="tipo_muestra"][data-id="${muestraId}"]`).prop('disabled', true);

                    checkbox.prop('checked', true).prop('disabled', true);
                },
                error: function(xhr) {
                    const message = xhr.responseJSON?.message || xhr.statusText ||
                        'Error al procesar la aprobación.';
                    toast(message, ToastIcon.ERROR);
                    checkbox.prop('checked', false).prop('disabled', false);
                }
            });
        })

        $(".save-precio-btn").on('click', function() {
            const btn = $(this);
            const muestraId = btn.data('id');
            const precioTxt = $(`.precio-input[data-id="${muestraId}"]`);
            const precio = precioTxt.val()
            const precioTotalCol = $(`.precio_total[data-id="${muestraId}"]`);
            if (btn.disabled || !precio) return;

            btn.prop('disabled', true);

            $.ajax({
                url: `{{ url('muestras') }}/${muestraId}/update-price`,
                type: 'POST',
                data: {
                    _method: 'PUT',
                    _token: '{{ csrf_token() }}',
                    price: precio
                },
                success: function(response) {
                    if (response.success) {
                        toast(response.message || 'Precio actualizado correctamente.',
                            ToastIcon.SUCCESS);
                        precioTxt.data('original', precio);
                        precioTotalCol.removeClass('text-danger');
                        precioTotalCol.text('S/ ' + (parseFloat(response.precio_total)).toFixed(2));
                    } else {
                        toast(response.message ||
                            'Ocurrió un error a la hora de colocar el precio.', ToastIcon.WARNING);
                        btn.prop('disabled', false);
                    }
                },
                error: function(xhr) {
                    const message = xhr.responseJSON?.message || xhr.statusText ||
                        'Error al colocar precio.';
                    toast(message, ToastIcon.ERROR);
                    btn.prop('disabled', false);
                }
            });
        });

        function formatDateTime(fechaISO) {
            const fecha = new Date(fechaISO);
            const opcionesFecha = {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            };
            const opcionesHora = {
                hour: '2-digit',
                minute: '2-digit',
                hour12: false
            };
            const fechaFormateada = fecha.toLocaleDateString('es-PE', opcionesFecha);
            const horaFormateada = fecha.toLocaleTimeString('es-PE', opcionesHora);

            return {
                date: fechaFormateada,
                time: horaFormateada
            };
        }
    </script>
@stop
