@extends('adminlte::page')

@section('title', 'Muestras')

@section('content_header')
@stop

@php
$role = auth()->check() ? auth()->user()->role->name : null;
$currentParams = request()->except(['page']);
@endphp

@section('content')
<div class="container">
    @include('messages')
    <div class="d-flex flex-column flex-md-row justify-content-between">
        <h1>Estado de las Muestras</h1>
        <div class="btn-group mt-2 mt-md-0" role="group">
            @can('muestras.create')
            <a href="{{ route('muestras.create') }}" class="btn btn-s btn-success my-1">
                <i class="fas fa-plus-circle mr-1"></i> Agregar Muestra
            </a>
            @endcan
            <a class="btn btn-s btn-outline-success my-1" href="{{ route('muestras.exportExcel') }}">
                <i class="fas fa-file-excel mr-1"></i>Exportar Excel
            </a>
        </div>
    </div>
    <hr>
    <div class="row my-3">
        <div class="col-12">
            <form method="GET" action="{{ route('muestras.index') }}" class="d-flex flex-column flex-md-row">
                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Buscar por nombre..."
                    class="form-control">
                <select class="custom-select mx-md-2 my-2 my-md-0" name="filter_by_date">
                    <option value="registro" {{ request('filter_by_date') == 'registro' ? 'selected' : '' }}>
                        Por fecha de registro
                    </option>
                    <option value="entrega" {{ request('filter_by_date') == 'entrega' ? 'selected' : '' }}>
                        Por fecha de entrega
                    </option>
                </select>
                <input
                    type="date"
                    name="date_since"
                    value="{{ request('date_since') }}"
                    class="form-control mb-2 mb-md-0 mr-md-2"
                    placeholder="Desde">
                <input
                    type="date"
                    name="date_to"
                    value="{{ request('date_to') }}"
                    class="form-control mb-2 mb-md-0"
                    placeholder="Hasta">
                <button type="submit" class="btn btn-primary ml-md-2">Buscar</button>
            </form>
        </div>
    </div>
    <div class="row mb-3">
        <div class="col-12 col-xl-6 mb-3">
            <form id="orderForm" class="row" action="{{ route('muestras.index') }}" method="GET">
                <div class="col-xl-2 col-3 align-content-center">
                    <h5 class="m-0">
                        <strong>
                            Ordenar por
                        </strong>
                    </h5>
                </div>
                <div class="col-9 col-xl-10">
                    <select class="custom-select rounded-0" id="orderSelect"
                        onchange="window.location = this.value;">
                        <option value="{{ route('muestras.index', array_merge($currentParams, ['order_by' => 'fecha_registro'])) }}"
                            {{ request('order_by') == 'fecha_registro' ? 'selected' : '' }}>
                            Fecha de Registro
                        </option>
                        <option value="{{ route('muestras.index', array_merge($currentParams, ['order_by' => 'fecha_entrega'])) }}"
                            {{ request('order_by') == 'fecha_entrega' ? 'selected' : '' }}>
                            Fecha de Entrega
                        </option>
                    </select>
                </div>
            </form>
        </div>
        <div class="col-12 col-xl-6">
            <form id="filterForm" class="d-flex gap-2" action="{{ route('muestras.index') }}" method="GET">
                <div class="col-2 col-md-3 col-lg-4 align-content-center">
                    <h5 class="m-0 text-truncate">
                        <strong>
                            Estado de Laboratorio
                        </strong>
                    </h5>
                </div>
                <div class="col-10 col-md-9 col-lg-8">
                    <div class="btn-group btn-group-toggle w-100" data-toggle="buttons">
                        {{-- Pendientes --}}
                        <label class="btn {{ request('lab_state') == 'Pendiente' ? 'btn-info active' : 'btn-outline-secondary' }}">
                            <input type="radio" name="lab_state" value="Pendiente"
                                onchange="document.getElementById('filterForm').submit();"
                                {{ request('lab_state') == 'Pendiente' ? 'checked' : '' }}>
                            Pendientes
                        </label>
                        {{-- Todas (sin lab_state) --}}
                        <label class="btn {{ request()->has('lab_state') ? 'btn-outline-secondary' : 'btn-info active' }}">
                            <input type="radio" name="lab_state" value=""
                                onchange="window.location='{{ route('muestras.index') }}';"
                                {{ !request()->has('lab_state') ? 'checked' : '' }}>
                            Todas
                        </label>
                        {{-- Elaboradas --}}
                        <label class=" btn {{ request('lab_state') == 'Elaborado' ? 'btn-info active' : 'btn-outline-secondary' }}">
                            <input type="radio" name="lab_state" value="Elaborado"
                                onchange="document.getElementById('filterForm').submit();"
                                {{ request('lab_state') == 'Elaborado' ? 'checked' : '' }}>
                            Elaboradas
                        </label>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-hover" id="table_muestras">
            <thead>
                <tr>
                    <th class="px-3">ID</th>
                    <th>Nombre de la Muestra</th>
                    <th>Clasificación</th>
                    <th>Tipo de Frasco</th>
                    <th>Tipo de Muestra</th>
                    <th>Cantidad</th>
                    @if(in_array($role, ['admin', 'jefe-comercial' , 'contabilidad', 'jefe-operaciones']))
                    <th>Precio Por Unidad</th>
                    <th>Precio Total</th>
                    @endif
                    @if(in_array($role, ['admin','coordinador-lineas','jefe-comercial','jefe-operaciones','supervisor']))
                    <th>Aprobar Muestra</th>
                    @endif
                    @if(in_array($role, ['admin','laboratorio']))
                    <th>Estado de Laboratorio</th>
                    @endif
                    <th>Creado por</th>
                    <th>Doctor</th>
                    <th>Fecha y Hora acordada para la Entrega</th>
                    <th>Acciones</th>
                </tr>
            </thead>

            <tbody>
                @foreach($muestras as $index => $muestra)
                <tr id="muestra_{{ $muestra->id }}">
                    <td>{{ $muestra->id }}</td>
                    <td>{{ $muestra->nombre_muestra }}</td>
                    <td>
                        @php
                        $isSetClasificacion = $muestra->clasificacion;
                        @endphp
                        @if($isSetClasificacion)
                        {{ $muestra->clasificacion->nombre_clasificacion}}
                        <hr>
                        <strong>Unidad de Medida:</strong> {{ $muestra->clasificacion->unidadMedida->nombre_unidad_de_medida }}
                        @endif
                    </td>
                    <td>{{ $muestra->tipo_frasco ?? 'No asignado' }}</td>
                    <td>
                        @if(in_array($role, ['admin', 'coordinador-lineas','supervisor']) && !$muestra->aprobado_coordinadora && $muestra->state)
                        <div class="d-flex">
                            <select class="custom-select rounded-0 mr-2" name="tipo_muestra" data-id="{{ $muestra->id }}" data-original="{{ $muestra->tipoMuestra ? $muestra->tipoMuestra->id : '' }}" {{ $muestra->aprobado_coordinadora ? 'disabled' : '' }}>
                                <option disabled {{ !$muestra->tipoMuestra ? 'selected' : '' }} value="0">Seleccione un tipo de muestra</option>
                                @foreach($tiposMuestra as $tipo)
                                <option value="{{ $tipo->id }}" {{ $muestra->tipoMuestra && $muestra->tipoMuestra->id === $tipo->id ? 'selected disabled' : '' }}>{{ $tipo->name }}</option>
                                @endforeach
                            </select>
                            <button type="button" class="btn btn-primary save-tipo-muestra-btn" data-id="{{ $muestra->id }}" disabled>
                                <i class="fas fa-save"></i>
                            </button>
                        </div>
                        @else
                        <span class="{{ $muestra->tipoMuestra ? '' : 'text-danger' }}">{{ $muestra->tipoMuestra->name ?? 'No se ha asignado un tipo de muestra' }}</span>
                        @endif
                    </td>
                    <td>{{ $muestra->cantidad_de_muestra }}</td>
                    @if(in_array($role, ['admin', 'jefe-comercial' , 'contabilidad', 'jefe-operaciones']))
                    @php
                    $isPrecioNotSetted = empty($muestra->precio);
                    @endphp
                    <td>
                        @php $puedeEditarPrecio = !$muestra->aprobado_jefe_operaciones && in_array($role, ['admin', 'contabilidad']); @endphp
                        @if ($puedeEditarPrecio && $muestra->state)
                        <div class="d-flex align-items-center">
                            <input type="number"
                                name="price-input"
                                class="form-control precio-input mr-2"
                                data-id="{{ $muestra->id }}"
                                value="{{ $muestra->precio }}"
                                data-original="{{ $muestra->precio }}"
                                required>
                            <button type="button"
                                class="btn btn-primary save-precio-btn"
                                data-id="{{ $muestra->id }}">
                                <i class="fas fa-save"></i>
                            </button>
                        </div>
                        @else
                        <span class="{{ $isPrecioNotSetted ? 'text-danger' : '' }}">{{ $isPrecioNotSetted ? "No asignado" :  "S/ " . number_format($muestra->precio, 2) }}</span>
                        @endif
                    </td>
                    <td class="precio_total {{ $isPrecioNotSetted ? 'text-danger' : '' }}" data-id="{{ $muestra->id }}">
                        {{ $muestra->precio && $muestra->cantidad_de_muestra ? 'S/ ' . number_format($muestra->precio * $muestra->cantidad_de_muestra, 2) : 'No asignado' }}
                    </td>
                    @endif
                    @if(in_array($role, ['admin','coordinador-lineas','jefe-comercial','jefe-operaciones','supervisor']))
                    <td>
                        @php
                        $rolesCheckbox = [
                            'coordinador-lineas' => [
                                'class' => 'coordinadora-checkbox',
                                'checked' => $muestra->aprobado_coordinadora,
                                'canApprove' => in_array($role, ['admin', 'coordinador-lineas','supervisor']) && $muestra->state,
                            ],
                            'supervisor' => [
                                'class' => 'coordinadora-checkbox',
                                'checked' => $muestra->aprobado_coordinadora,
                                'canApprove' => in_array($role, ['admin','supervisor']) && $muestra->state,
                            ],
                            'jefe-comercial' => [
                                'class' => 'jcomercial-checkbox',
                                'checked' => $muestra->aprobado_jefe_comercial,
                                'canApprove' => in_array($role, ['admin', 'jefe-comercial']) && $muestra->state,
                            ],
                            'jefe-operaciones' => [
                            'class' => 'joperaciones-checkbox',
                            'checked' => $muestra->aprobado_jefe_operaciones,
                            'canApprove' => in_array($role, ['admin', 'jefe-operaciones']) && $muestra->state,
                            ],
                        ];

                        $config = $rolesCheckbox[$role] ?? null;
                        @endphp
                        @foreach($rolesCheckbox as $rolKey => $config)
                        @if($role === $rolKey || $role === 'admin')
                        <input
                            type="checkbox"
                            style="width: 1.3em; height: 1.3em;"
                            class="{{ $config['class'] }}"
                            data-id="{{ $muestra->id }}"
                            {{ $muestra->tipoMuestra === null ? 'disabled' : '' }}
                            {{ $config['checked'] ? 'checked disabled' : '' }}
                            {{ $config['canApprove'] ? '' : 'disabled' }}>
                        @endif
                        @endforeach
                    </td>
                    @endif
                    @can('muestras.markAsElaborated')
                    <td>
                        <select class="custom-select rounded-0 mr-2" name="lab_state" data-id="{{ $muestra->id }}" {{ $muestra->lab_state || !$muestra->state ? 'disabled' : '' }}>
                            <option value="0" {{ $muestra->lab_state ? 'selected' : '' }}>Pendiente</option>
                            <option value="1" {{ $muestra->lab_state ? 'selected disabled' : '' }}>Elaborada</option>
                        </select>
                    </td>
                    @endcan
                    <td>{{ $muestra->creator ? $muestra->creator->name : 'Desconocido' }}</td>
                    <td>{{ optional($muestra->doctor)->name ?? $muestra->name_doctor ?? 'No asignado' }}</td>
                    <td>
                        @if(!$muestra->aprobado_coordinadora)
                        @can('muestras.updateDateTimeScheduled')
                        <form action="{{ route('muestras.updateDateTimeScheduled', $muestra->id) }}" method="POST" class="d-flex flex-column" id="fecha_form_{{ $muestra->id }}">
                            @csrf
                            @method('PUT')
                            <input
                                type="datetime-local"
                                name="datetime_scheduled"
                                class="form-control mb-2"
                                value="{{ old('datetime_scheduled', $muestra->datetime_scheduled ? \Carbon\Carbon::parse($muestra->datetime_scheduled)->format('Y-m-d\TH:i') : '') }}"
                                id="fecha_{{ $muestra->id }}"
                                min="{{ \Carbon\Carbon::now()->format('Y-m-d\TH:i') }}">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Guardar
                            </button>
                        </form>
                        @endcan
                        @else
                        <span class="{{ $muestra->datetime_scheduled ? '': 'text-danger' }}">{{ $muestra->datetime_scheduled ? \Carbon\Carbon::parse($muestra->datetime_scheduled)->format('d/m/Y H:i') : 'No asignada' }}</span>
                        @endif
                    </td>
                    <td>
                        <div>
                            <button
                                class="btn btn-success btn-sm btn-show-details"
                                data-id="{{ $muestra->id }}"
                                data-toggle="modal"
                                data-target="#muestraDetailsModal">
                                <i class="fas fa-eye"></i>
                            </button>
                            @can('muestras.edit')
                            <a href="{{ route('muestras.edit', $muestra->id) }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-edit"></i>
                            </a>
                            @endcan
                            @can('muestras.disable')
                            <button
                                class="btn btn-danger btn-sm btn-show-delete"
                                data-id="{{ $muestra->id }}"
                                data-toggle="modal"
                                data-target="#deleteModal">
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
    {!! $muestras->appends(request()->except('page'))->links() !!}
</div>
</div>
@stop

@section('css')
<link rel="stylesheet" href="{{ asset('css/muestras/labora.css') }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css">
@stop

@section('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
<script>
    $(document).ready(() => {
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
                    const muestra = response.data;
                    if (!muestra.state) {
                        $('#modal_title').text('Detalles de la Muestra - DESHABILITADA');
                    } else {
                        $('#modal_title').text('Detalles de la Muestra');
                    }
                    $('#nombre_muestra').text(muestra.nombre_muestra);
                    $('#clasificacion_muestra').text(`${muestra.clasificacion ? muestra.clasificacion.nombre_clasificacion : 'No disponible'}`);
                    $('#unidad_medida').text(muestra.clasificacion.unidad_medida.nombre_unidad_de_medida ?? 'No disponible');
                    $('#tipo_frasco').text(muestra.tipo_frasco);
                    if (muestra.tipo_frasco == 'Frasco Muestra') {
                        $('#presentacion_frasco').hide();
                    } else {
                        $('#presentacion_frasco').show();
                        $('#presentacion_frasco_original').text(`${muestra.clasificacion_presentacion?.quantity} ${muestra.clasificacion.unidad_medida.nombre_unidad_de_medida}`)
                    }
                    $('#tipo_muestra').text(muestra.tipo_muestra ? muestra.tipo_muestra.name : 'No disponible');
                    $('#cantidad_muestra').text(muestra.cantidad_de_muestra);
                    $('#precio_unitario').text(muestra.precio ? `S/ ${muestra.precio}` : 'No asignado');
                    $('#precio_total').text(muestra.precio && muestra.cantidad_de_muestra ? `S/ ${muestra.precio * muestra.cantidad_de_muestra}` : 'No asignado');
                    $('#observaciones').text(muestra.observacion ?? 'Ninguna');
                    $('#doctor').text(muestra.doctor ? muestra.doctor.name : (muestra.name_doctor ?? 'No asignado'));
                    $('#creado_por').text(muestra.creator?.name ?? 'Desconocido');
                    const coordinadoraBadge = $('#coordinadora-badge')
                    if (muestra.aprobado_coordinadora) {
                        coordinadoraBadge.removeClass('bg-warning');
                        coordinadoraBadge.addClass('bg-success').text('Aprobada');
                    } else {
                        coordinadoraBadge.removeClass('bg-success');
                        coordinadoraBadge.addClass('bg-warning').text('Pendiente');
                    }
                    const jComercialBadge = $('#jComercial-badge')
                    if (muestra.aprobado_jefe_comercial) {
                        jComercialBadge.removeClass('bg-warning');
                        jComercialBadge.addClass('bg-success').text('Aprobada');
                    } else {
                        jComercialBadge.removeClass('bg-success');
                        jComercialBadge.addClass('bg-warning').text('Pendiente');
                    }
                    const jOperacionesBadge = $('#jOperaciones-badge')
                    if (muestra.aprobado_jefe_operaciones) {
                        jOperacionesBadge.removeClass('bg-warning');
                        jOperacionesBadge.addClass('bg-success').text('Aprobada');
                    } else {
                        jOperacionesBadge.removeClass('bg-success');
                        jOperacionesBadge.addClass('bg-warning').text('Pendiente');
                    }
                    const labState = $('#lab-state-badge')
                    if (muestra.lab_state) {
                        labState.removeClass('bg-warning');
                        labState.addClass('bg-success').text('Elaborada');
                    } else {
                        labState.removeClass('bg-success');
                        labState.addClass('bg-warning').text('Pendiente');
                    }
                    const dateTimeRegistered = formatDateTime(muestra.created_at);
                    $('#date-registered').text(`${dateTimeRegistered.date},`);
                    $('#time-registered').text(`a las ${dateTimeRegistered.time}.`);
                    const dateTimeScheduled = formatDateTime(muestra.datetime_scheduled);
                    $('#date-scheduled').text(muestra.datetime_scheduled ? `${dateTimeScheduled.date},` : 'Aún no se ha programado una fecha de entrega.');
                    $('#time-scheduled').text(muestra.datetime_scheduled ? `a las ${dateTimeScheduled.time}.` : '');
                    const dateTimeDelivered = formatDateTime(muestra.datetime_delivered);
                    $('#date-delivered').text(muestra.datetime_delivered ? `${dateTimeDelivered.date},` : 'Esta muestra aún no ha sido elaborada.');
                    $('#time-delivered').text(muestra.datetime_delivered ? `a las ${dateTimeDelivered.time}.` : '');
                    $('#comentarioForm').attr('action', `/muestras/laboratorio/${muestra.id}/comentario`);
                    $('#comentarioForm textarea[name="comentario_lab"]').val(muestra.comentarios || '');
                    $('#comentario-laboratorio').text(muestra.comentarios ? `"${muestra.comentarios}"` : '"No hay comentario del laboratorio disponible."')
                    const deleteReasonDiv = $('#delete-reason-div');
                    if (muestra.delete_reason) {
                        deleteReasonDiv.show();
                        $('#delete-reason').text(muestra.delete_reason);
                    } else {
                        deleteReasonDiv.hide();
                    }
                },
                error: function() {
                    toastr.error('No se pudieron cargar los detalles de la muestra.');
                }
            });
        });

        $('.btn-show-delete').on('click', function(btn) {
            const muestraId = $(this).data('id');
            $('#deleteModal').data('id', muestraId);
            $('#delete_reason').val('');
        })

        $('#deleteForm').on('submit', (e) => {
            e.preventDefault();

            const delete_reason = $('#delete_reason').val();

            const muestraId = $('#deleteModal').data('id'); // Obtenemos el ID guardado

            if (!muestraId) {
                toastr.error('ID de muestra no encontrado.');
                return;
            }

            $.ajax({
                url: `{{ url('muestras') }}/disable/${muestraId}`,
                type: 'POST',
                data: {
                    _method: 'DELETE',
                    _token: '{{ csrf_token() }}',
                    delete_reason: delete_reason
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message || 'Item eliminado correcamente.');
                        $(`#muestra_${muestraId}`).fadeOut();
                        $('#close-modal').click()
                    } else {
                        toastr.error(response.message || 'No fue posible eliminar el item.');
                    }
                },
                error: function(xhr) {
                    toastr.error('Error al eliminar el item.');
                    console.error(xhr.responseJSON);
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
                        toastr.success(response.message || 'Muestra marcada como elaborada correctamente.');
                        selectElement.prop('disabled', true);
                    } else {
                        toastr.error(response.message || 'No fue posible marcar la muestra como elaborada.');
                        selectElement.prop('selectedIndex', 0);
                        selectElement.prop('disabled', false);
                    }
                },
                error: function(xhr) {
                    toastr.error('Error al actualizar el estado de la muestra.');
                    selectElement.prop('selectedIndex', 0);
                    selectElement.prop('disabled', false);
                    console.error(xhr.responseJSON);
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
                    toastr.success(response.message || 'Tipo de muestra actualizado correctamente.');
                    checkbox.prop('disabled', false);
                } else {
                    toastr.error(response.message || 'Ocurrió un error en la actualización del tipo de muestra.');
                    checkbox.prop('checked', false).prop('disabled', true);
                    console.error(response);
                }
            },
            error: function(xhr) {
                toastr.error('Error al actualizar el tipo de muestra.');
                console.error(xhr.responseJSON);
                btn.prop('disabled', true);
                checkbox.prop('disabled', true);
            }
        });
    });

    $(".coordinadora-checkbox").on('click', function() {
        const checkbox = $(this);
        const muestraId = checkbox.data('id');

        if (!confirm('Seguro que desea aprobar esta muestra?')) {
            checkbox.prop('checked', false);
            e.preventDefault();
            return;
        }

        $.ajax({
            url: `{{ url(path: 'muestras') }}/aprove-coordinador`,
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                _method: 'PUT',
                id: muestraId,
            },
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message || 'Aprobación realizada correctamente.');
                    $('select[name="tipo_muestra"]')
                        .filter(`[data-id="${muestraId}"]`)
                        .prop('disabled', true);
                    checkbox.prop('checked', true).prop('disabled', true);
                } else {
                    toastr.error(response.message || 'Ocurrió un error en la aprobación.');
                    checkbox.prop('checked', false).prop('disabled', false);
                }
            },
            error: function(xhr) {
                toastr.error('Error al actualizar la aprobación por Coordinadora.');
                console.error(xhr.responseJSON);
                checkbox.prop('checked', false).prop('disabled', false);
            }
        });
    });

    $(".jcomercial-checkbox").on('click', function() {
        const checkbox = $(this);
        const muestraId = checkbox.data('id');

        if (!confirm('Seguro que desea aprobar esta muestra?')) {
            checkbox.prop('checked', false);
            e.preventDefault();
            return;
        }

        checkbox.prop('disabled', true);

        if (checkbox.is(':checked')) {
            $.ajax({
                url: `{{ url(path: 'muestras') }}/aprove-jcomercial`,
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    _method: 'PUT',
                    id: muestraId,
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message || 'Aprobación realizada correctamente.');
                    } else {
                        toastr.error(response.message || 'Ocurrió un error en la aprobación.');
                        checkbox.prop('checked', false).prop('disabled', false);
                    }
                },
                error: function(xhr) {
                    toastr.error('Error al actualizar la aprobación por Jefe Comercial.');
                    console.error(xhr.responseJSON);
                    checkbox.prop('checked', false).prop('disabled', false);
                }
            });
        } else {
            checkbox.prop('checked', true).prop('disabled', false);
        }
    });

    $(".joperaciones-checkbox").on('click', function() {
        const checkbox = $(this);
        const muestraId = checkbox.data('id');

        if (!confirm('Seguro que desea aprobar esta muestra?')) {
            checkbox.prop('checked', false);
            e.preventDefault();
            return;
        }

        checkbox.prop('disabled', true);

        if (checkbox.is(':checked')) {
            $.ajax({
                url: `{{ url(path: 'muestras') }}/aprove-joperaciones`,
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    _method: 'PUT',
                    id: muestraId,
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message || 'Aprobación realizada correctamente.');
                    } else {
                        toastr.error(response.message || 'Ocurrió un error en la aprobación.');
                        checkbox.prop('checked', false).prop('disabled', false);
                    }
                },
                error: function(xhr) {
                    toastr.error('Error al actualizar la aprobación por Jefe de Operaciones.');
                    console.error(xhr.responseJSON);
                    checkbox.prop('checked', false).prop('disabled', false);
                }
            });
        } else {
            checkbox.prop('checked', true).prop('disabled', false);
        }
    });

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
                    toastr.success(response.message || 'Precio actualizado correctamente.');
                    precioTxt.data('original', precio);
                    precioTotalCol.removeClass('text-danger');
                    precioTotalCol.text('S/ ' + (parseFloat(response.precio_total)).toFixed(2));
                } else {
                    toastr.error(response.message || 'Ocurrió un error a la hora de colocar el precio.');
                    btn.prop('disabled', false);
                }
            },
            error: function(xhr) {
                toastr.error('Error al colocar precio.');
                console.error(xhr.responseJSON);
                btn.prop('disabled', false);
            }
        });
    });

    function handleDelete(btn) {
        if (!confirm('¿Desea eliminar esta muestra?')) return;

        const muestraId = btn.getAttribute('data-id');

        $.ajax({
            url: `{{ url('muestras') }}/${muestraId}`,
            type: 'DELETE',
            data: {
                _token: '{{ csrf_token() }}',
            },
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message || 'Muestra deshabilitada correctamente.');
                    $(`#muestra_${muestraId}`).fadeOut();
                } else {
                    toastr.error(response.message || 'Ocurrió un error al deshabilitar la muestra.');
                }
            },
            error: function(xhr) {
                toastr.error('Error al deshabilitar la muestra.');
                console.error(xhr.responseJSON);
            }
        });
    }

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