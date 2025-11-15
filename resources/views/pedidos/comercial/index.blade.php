@extends('adminlte::page')

@section('title', 'Pedidos Comercial')

@section('content')
    <div class="container-fluid">
        <x-grobdi.layout.header-card title="Pedidos Comercial"
            subtitle="Control y seguimiento de pedidos comerciales">
            <x-slot:actions>
                <x-grobdi.button variant="success" size="sm" icon="fa fa-file-excel"
                    :href="route('pedidoscomercial.export', $filters ?? [])">
                    Exportar Excel
                </x-grobdi.button>
            </x-slot:actions>

            <x-slot:filter>
                <form action="{{ route('pedidoscomercial.index') }}" method="GET" class="grobdi-form">
                    <div class="form-grid grid-cols-3">
                        <div class="form-group-grobdi">
                            <label for="fecha_inicio" class="grobdi-label">
                                <span class="label-icon"><i class="fa fa-calendar"></i></span>
                                Fecha inicio
                            </label>
                            <div class="input-group-grobdi">
                                <span class="input-group-text"><i class="fa fa-calendar"></i></span>
                                <input type="date" name="fecha_inicio" id="fecha_inicio" class="grobdi-input"
                                    value="{{ $filters['fecha_inicio'] ?? '' }}">
                            </div>
                        </div>
                        <div class="form-group-grobdi">
                            <label for="fecha_fin" class="grobdi-label">
                                <span class="label-icon"><i class="fas fa-calendar-check"></i></span>
                                Fecha fin
                            </label>
                            <div class="input-group-grobdi">
                                <span class="input-group-text"><i class="fas fa-calendar-check"></i></span>
                                <input type="date" name="fecha_fin" id="fecha_fin" class="grobdi-input"
                                    value="{{ $filters['fecha_fin'] ?? '' }}">
                            </div>
                        </div>
                        <div class="form-group-grobdi">
                            <label for="order_id" class="grobdi-label">
                                <span class="label-icon"><i class="fa fa-barcode"></i></span>
                                Order ID
                            </label>
                            <div class="input-group-grobdi">
                                <span class="input-group-text"><i class="fa fa-barcode"></i></span>
                                <input type="text" name="order_id" id="order_id" class="grobdi-input"
                                    placeholder="Buscar order ID" value="{{ $filters['order_id'] ?? '' }}">
                            </div>
                        </div>
                        <div class="form-group-grobdi">
                            <label for="cliente" class="grobdi-label">
                                <span class="label-icon"><i class="fa fa-user"></i></span>
                                Cliente
                            </label>
                            <div class="input-group-grobdi">
                                <span class="input-group-text"><i class="fa fa-user"></i></span>
                                <input type="text" name="cliente" id="cliente" class="grobdi-input"
                                    placeholder="Buscar cliente" value="{{ $filters['cliente'] ?? '' }}">
                            </div>
                        </div>
                        <div class="form-group-grobdi">
                            <label for="visitadora" class="grobdi-label">
                                <span class="label-icon"><i class="fa fa-id-badge"></i></span>
                                Visitadora
                            </label>
                            <div class="input-group-grobdi">
                                <span class="input-group-text"><i class="fa fa-id-badge"></i></span>
                                <input type="text" name="visitadora" id="visitadora" class="grobdi-input"
                                    placeholder="Buscar visitadora" value="{{ $filters['visitadora'] ?? '' }}">
                            </div>
                        </div>
                        <div class="form-group-grobdi">
                            <label for="doctor" class="grobdi-label">
                                <span class="label-icon"><i class="fa fa-user-md"></i></span>
                                Doctor
                            </label>
                            @php
                                $selectedDoctor = $filters['doctor'] ?? '';
                                $hasSelectedDoctorOption = false;

                                if ($selectedDoctor !== '') {
                                    foreach ($doctorOptions as $doctorCandidate) {
                                        if ((string) $doctorCandidate->id === (string) $selectedDoctor) {
                                            $hasSelectedDoctorOption = true;
                                            break;
                                        }
                                    }
                                }
                            @endphp
                            <select name="doctor" id="doctor" class="form-control grobdi-input"
                                data-placeholder="Buscar doctor">
                                <option value="">Seleccionar todos</option>
                                @foreach ($doctorOptions as $doctorOption)
                                    <option value="{{ $doctorOption->id }}"
                                        {{ (string) ($filters['doctor'] ?? '') === (string) $doctorOption->id ? 'selected' : '' }}>
                                        {{ $doctorOption->name }}
                                    </option>
                                @endforeach
                                @if (!$hasSelectedDoctorOption && $selectedDoctor !== '')
                                    <option value="{{ $selectedDoctor }}" selected>{{ $selectedDoctor }}</option>
                                @endif
                            </select>
                        </div>
                        <div class="form-group-grobdi">
                            <label for="distrito" class="grobdi-label">
                                <span class="label-icon"><i class="fa fa-map-marker"></i></span>
                                Distrito (Lima y Callao)
                            </label>
                            <div class="input-group-grobdi">
                                <span class="input-group-text"><i class="fa fa-map-marker"></i></span>
                                <select name="distrito" id="distrito" class="grobdi-input">
                                    <option value="">Selecciona un distrito</option>
                                    @foreach ($distritoOptions as $distritoOption)
                                        <option value="{{ $distritoOption->id }}"
                                            {{ (string) ($filters['distrito'] ?? '') === (string) $distritoOption->id ? 'selected' : '' }}>
                                            {{ $distritoOption->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="filter-actions">
                        <x-grobdi.button type="submit" icon="fa fa-search">
                            Aplicar filtros
                        </x-grobdi.button>
                        <x-grobdi.button variant="outline" icon="fa fa-undo"
                            :href="route('pedidoscomercial.index')">
                            Restablecer
                        </x-grobdi.button>
                    </div>
                </form>
            </x-slot:filter>
        </x-grobdi.layout.header-card>

        @php
            $modalPedidos = [];
        @endphp

        <x-grobdi.layout.table-card title="Pedidos Comercial"
            subtitle="Listado de pedidos con su información clave" tableClass="table-striped table-hover">
            @if ($errors->any())
                <x-slot:toolbar>
                    <div class="alert alert-danger mb-0 w-100">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </x-slot:toolbar>
            @endif

            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Fecha</th>
                    <th>Cliente</th>
                    <th>Visitadora</th>
                    <th>Doctor</th>
                    <th>Distrito de entrega</th>
                    <th>Estado</th>
                    <th>Precio total</th>
                    <th>Zona de entrega</th>
                    <th>Usuario registrado</th>
                    <th>Productos</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($pedidos as $pedido)
                    @php
                        $modalPedidos[] = $pedido;
                    @endphp
                    <tr>
                        <td>{{ $pedido->orderId }}</td>
                        <td>{{ optional($pedido->created_at)->format('d/m/Y H:i') }}</td>
                        <td>
                            <div class="font-weight-bold">{{ $pedido->customerName }}</div>
                            <small class="text-muted">{{ $pedido->customerNumber }}</small>
                        </td>
                        <td>{{ optional($pedido->visitadora)->name ?? 'Sin asignar' }}</td>
                        <td class="text-center">
                            <x-grobdi.button variant="outline" size="sm" icon="fa fa-user-md" data-toggle="modal"
                                data-target="#doctorModal-{{ $pedido->id }}">
                                Ver doctor
                            </x-grobdi.button>
                        </td>
                        <td>{{ $pedido->district ?? (optional(optional($pedido->doctor)->distrito)->name ?? 'Sin distrito') }}
                        </td>
                        <td>
                            @if ($pedido->status)
                                <span class="badge badge-success">Activo</span>
                            @else
                                <span class="badge badge-secondary">Inactivo</span>
                            @endif
                        </td>
                        <td>S/ {{ number_format($pedido->prize ?? 0, 2) }}</td>
                        <td>{{ optional($pedido->zone)->name ?? 'Sin zona' }}</td>
                        <td>{{ optional($pedido->user)->name ?? 'Sin registrar' }}</td>
                        <td>
                            <x-grobdi.button variant="outline" size="sm" icon="fa fa-box" data-toggle="modal"
                                data-target="#productosModal-{{ $pedido->id }}">
                                Ver productos
                            </x-grobdi.button>
                        </td>
                    </tr>

                @empty
                    <tr>
                        <td colspan="11" class="text-center">No se encontraron pedidos con los filtros seleccionados.</td>
                    </tr>
                @endforelse
            </tbody>

            @if (method_exists($pedidos, 'links'))
                <x-slot:footer>
                    <div class="d-flex justify-content-center">
                        {{ $pedidos->links() }}
                    </div>
                </x-slot:footer>
            @endif
        </x-grobdi.layout.table-card>

        @foreach ($modalPedidos as $modalPedido)
                <div class="modal fade" id="doctorModal-{{ $modalPedido->id }}" tabindex="-1" role="dialog"
                    aria-labelledby="doctorModalLabel-{{ $modalPedido->id }}" aria-hidden="true">
                    <div class="modal-dialog modal-lg" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="doctorModalLabel-{{ $modalPedido->id }}">Información del
                                    doctor</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                @if ($modalPedido->doctor)
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <strong>Nombre:</strong>
                                            <div>{{ $modalPedido->doctor->name }}</div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <strong>Tipo médico:</strong>
                                            <div>{{ optional($modalPedido->doctor)->tipo_medico }}</div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <strong>Especialidad:</strong>
                                            <div>
                                                {{ optional(optional($modalPedido->doctor)->especialidad)->name ?? 'No registrada' }}
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <strong>Centro de salud:</strong>
                                            <div>
                                                {{ optional(optional($modalPedido->doctor)->centrosalud)->name ?? 'No registrado' }}
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <strong>Distrito:</strong>
                                            <div>
                                                {{ optional(optional($modalPedido->doctor)->distrito)->name ?? ($modalPedido->district ?? 'No registrado') }}
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <strong>Secretaria:</strong>
                                            <div>{{ $modalPedido->doctor->name_secretariat ?? 'No registrada' }}</div>
                                        </div>
                                    </div>
                                @elseif(!empty($modalPedido->doctorName))
                                    <div class="border rounded p-3 bg-white">
                                        <h5 class="mb-3">Doctor registrado en el pedido</h5>
                                        <p class="mb-2"><strong>Nombre:</strong> {{ $modalPedido->doctorName }}</p>
                                        <p class="mb-2"><strong>Distrito:</strong>
                                            {{ $modalPedido->district ?? 'No registrado' }}</p>
                                        <p class="mb-0 text-muted">Este doctor aún no está vinculado al catálogo.
                                            Relaciónalo desde mantenimiento para ver más detalles.</p>
                                    </div>
                                @else
                                    <p class="mb-0">El pedido no tiene un doctor asignado.</p>
                                @endif
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal fade" id="productosModal-{{ $modalPedido->id }}" tabindex="-1" role="dialog"
                    aria-labelledby="productosModalLabel-{{ $modalPedido->id }}" aria-hidden="true">
                    <div class="modal-dialog modal-lg" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="productosModalLabel-{{ $modalPedido->id }}">
                                    Productos del pedido {{ $modalPedido->orderId }}
                                </h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                @if ($modalPedido->detailpedidos->isNotEmpty())
                                    <div class="table-responsive">
                                        <table class="table table-sm table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Nombre</th>
                                                    <th>Cantidad</th>
                                                    <th>Precio unitario</th>
                                                    <th>Total</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($modalPedido->detailpedidos as $detalle)
                                                    @php
                                                        $detalleTotal = $detalle->sub_total;
                                                        if ($detalleTotal === null) {
                                                            $detalleTotal =
                                                                ($detalle->unit_prize ?? 0) * ($detalle->cantidad ?? 0);
                                                        }
                                                    @endphp
                                                    <tr>
                                                        <td>{{ $detalle->articulo }}</td>
                                                        <td>{{ $detalle->cantidad }}</td>
                                                        <td>S/ {{ number_format($detalle->unit_prize ?? 0, 2) }}</td>
                                                        <td>S/ {{ number_format($detalleTotal, 2) }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <p class="mb-0">No hay productos activos para este pedido.</p>
                                @endif
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                            </div>
                        </div>
                    </div>
                </div>
        @endforeach
    </div>
@endsection

@section('css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css"
        rel="stylesheet" />
    <style>
        .select2-container--bootstrap4 .select2-selection {
            position: relative;
            padding-left: 2.5rem;
        }

        .select2-container--bootstrap4 .select2-selection::before {
            content: "\f0f1";
            font-family: "Font Awesome 5 Free";
            font-weight: 900;
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
            z-index: 1;
        }
    </style>
@endsection

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            var $doctorSelect = $('#doctor');

            $doctorSelect.select2({
                theme: 'bootstrap4',
                width: '100%',
                placeholder: $doctorSelect.data('placeholder') || 'Buscar doctor',
                allowClear: true,
                tags: true,
                createTag: function(params) {
                    var term = $.trim(params.term);

                    if (term === '') {
                        return null;
                    }

                    var exists = $doctorSelect.find('option').filter(function() {
                        return $.trim($(this).text()).toLowerCase() === term.toLowerCase();
                    }).length > 0;

                    if (exists) {
                        return null;
                    }

                    return {
                        id: term,
                        text: term,
                        newTag: true
                    };
                }
            });

            $doctorSelect.on('select2:select', function(event) {
                var data = event.params.data;

                if (data && data.newTag) {
                    var valueExists = $doctorSelect.find('option').filter(function() {
                        return $(this).val() === data.id;
                    }).length > 0;

                    if (!valueExists) {
                        var newOption = new Option(data.text, data.id, true, true);
                        $doctorSelect.append(newOption).trigger('change');
                    }
                }
            });
        });
    </script>
@endsection
