@extends('adminlte::page')

@section('title', 'Pedidos Contabilidad')

@php
    $user = auth()->user();
    $canDownloadExcel = $user?->can('pedidoscontabilidad.downloadExcel');
    $canUpdatePedido = $user?->can('pedidoscontabilidad.update');
    $defaultDate = date('Y-m-d');
    $fechaInicio = request('fecha_inicio');
    $fechaFin = request('fecha_fin');
@endphp

@section('content')
    @can('pedidoscontabilidad.index')
        <x-grobdi.layout.header-card
            title="Pedidos para contabilidad"
            subtitle="Filtra por rango de fechas, revisa estados y exporta tus reportes"
        >
            <x-slot:actions>
                @if ($canDownloadExcel)
                    <form
                        action="{{ route('pedidoscontabilidad.downloadExcel', [
                            'fechainicio' => $fechaInicio ?? $defaultDate,
                            'fechafin' => $fechaFin ?? $defaultDate,
                        ]) }}"
                        method="GET"
                    >
                        <x-grobdi.button type="submit" icon="fa fa-file-excel" variant="outline">
                            Descargar Excel
                        </x-grobdi.button>
                    </form>
                @endif
            </x-slot:actions>

            <x-slot:filter>
                <form action="{{ route('pedidoscontabilidad.index') }}" method="GET">
                    <div class="row g-3">
                        <div class="col-md-6 col-lg-4">
                            <label for="fecha_inicio" class="form-label"><i class="fa fa-calendar-alt"></i> Fecha inicio</label>
                            <input
                                type="date"
                                class="form-control"
                                id="fecha_inicio"
                                name="fecha_inicio"
                                value="{{ $fechaInicio ?? $defaultDate }}"
                            >
                            @error('fecha_inicio')
                                <small class="text-danger d-block">{{ $message }}</small>
                            @enderror
                        </div>
                        <div class="col-md-6 col-lg-4">
                            <label for="fecha_fin" class="form-label"><i class="fa fa-calendar"></i> Fecha fin</label>
                            <input
                                type="date"
                                class="form-control"
                                id="fecha_fin"
                                name="fecha_fin"
                                value="{{ $fechaFin ?? $defaultDate }}"
                            >
                            @error('fecha_fin')
                                <small class="text-danger d-block">{{ $message }}</small>
                            @enderror
                        </div>
                        <div class="col-md-6 col-lg-2 d-flex align-items-end">
                            <x-grobdi.button type="submit" icon="fa fa-search" class="w-100" variant="outline">
                                Buscar
                            </x-grobdi.button>
                        </div>
                        <div class="col-md-6 col-lg-2 d-flex align-items-end">
                            <x-grobdi.button
                                href="{{ route('pedidoscontabilidad.index') }}"
                                icon="fa fa-eraser"
                                class="w-100"
                                variant="outline"
                            >
                                Limpiar
                            </x-grobdi.button>
                        </div>
                    </div>
                </form>
            </x-slot:filter>
        </x-grobdi.layout.header-card>

        @if (session('success'))
            <div class="alert alert-success" role="alert">{{ session('success') }}</div>
        @endif

        <x-grobdi.layout.table-card
            title="Pedidos"
            tableId="contabilidad-table"
            tableClass="table-bordered table-striped"
        >
            <thead>
                <tr>
                    <th>Nro pedido</th>
                    <th>Cliente</th>
                    <th>Fecha de registro</th>
                    <th>Estado de pago</th>
                    <th>Estado contabilidad</th>
                    <th>Voucher</th>
                    @if ($canUpdatePedido)
                        <th width="120px">Opciones</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @forelse ($pedidos as $pedido)
                    <tr id="pedido-row-{{ $pedido->id }}">
                        <td class="order-id">{{ $pedido->orderId }}</td>
                        <td class="customer-name">{{ $pedido->customerName }}</td>
                        <td class="created-at">{{ date('d-m-Y', strtotime($pedido->created_at)) }}</td>
                        <td class="payment-status">{{ $pedido->paymentStatus }}</td>
                        <td class="accounting-status">
                            @if ($pedido->accountingStatus === 0)
                                <i class="fa fa-times" aria-hidden="true"></i> Sin revisar
                            @else
                                <i class="fa fa-check" aria-hidden="true"></i> Revisado
                            @endif
                        </td>
                        <td class="voucher-status">
                            @if ($pedido->voucher == 0)
                                <span class="badge rounded-pill bg-danger">Sin imagen</span>
                            @else
                                <span class="badge rounded-pill bg-success">Imagen</span>
                            @endif
                        </td>
                        @if ($canUpdatePedido)
                            <td>
                                <x-grobdi.button
                                    variant="info"
                                    size="sm"
                                    icon="fa fa-info"
                                    data-toggle="modal"
                                    :data-target="'#ModalPedido' . $pedido->id"
                                >
                                    Detalles
                                </x-grobdi.button>
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $canUpdatePedido ? 7 : 6 }}" class="text-center">
                            No hay información que mostrar
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </x-grobdi.layout.table-card>

        @if ($canUpdatePedido)
            @foreach ($pedidos as $pedido)
                <div
                    class="modal fade"
                    id="ModalPedido{{ $pedido->id }}"
                    tabindex="-1"
                    aria-labelledby="labelPedido{{ $pedido->id }}"
                    aria-hidden="true"
                >
                    <div class="modal-dialog modal-xl">
                        <form class="update-pedido-form" data-id="{{ $pedido->id }}">
                            @csrf
                            @method('PUT')
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="labelPedido{{ $pedido->id }}">Editar Pedido</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <strong>Nro del Pedido:</strong>
                                                <br>
                                                {{ $pedido->orderId }}
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <strong>Cliente:</strong>
                                                <br>
                                                {{ $pedido->customerName }}
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <strong>Precio:</strong>
                                                <br>
                                                {{ $pedido->prize }}
                                            </div>
                                        </div>
                                        <div class="col-md-4 mt-2">
                                            <div class="form-group">
                                                <strong>Fecha Entrega:</strong>
                                                <br>
                                                {{ $pedido->deliveryDate }}
                                            </div>
                                        </div>
                                        <div class="col-md-4 mt-2">
                                            <div class="form-group">
                                                <strong>Estado de pago:</strong>
                                                <br>
                                                {{ $pedido->paymentStatus }}
                                            </div>
                                        </div>
                                        <div class="col-md-4 mt-2">
                                            <div class="form-group">
                                                <strong>Método de pago:</strong>
                                                <br>
                                                {{ $pedido->paymentMethod }}
                                            </div>
                                        </div>
                                        @if ($pedido->voucher)
                                            @php
                                                $images = explode(',', $pedido->voucher);
                                                $nro_operaciones = explode(',', $pedido->operationNumber);
                                                $array_voucher = [];
                                                foreach ($images as $key => $voucher) {
                                                    $array_voucher[] = [
                                                        'nro_operacion' => $nro_operaciones[$key] ?? '-',
                                                        'voucher' => $voucher,
                                                    ];
                                                }
                                            @endphp
                                            @foreach ($array_voucher as $voucher)
                                                <div class="col-md-4">
                                                    Nro de Operación: <strong>{{ $voucher['nro_operacion'] }}</strong><br>
                                                    <img src="{{ asset($voucher['voucher']) }}" alt="{{ $pedido->orderId }}" width="400" height="400">
                                                </div>
                                            @endforeach
                                        @endif
                                        <div class="col-md-4 mt-2">
                                            <label for="accountingStatus" class="form-label"><strong>Estado de contabilidad:</strong></label>
                                            <select class="form-control" name="accountingStatus" id="accountingStatus">
                                                <option disabled>Selecciona una opción</option>
                                                <option value="0" {{ $pedido->accountingStatus === 0 ? 'selected' : '' }}>Sin revisar</option>
                                                <option value="1" {{ $pedido->accountingStatus === 1 ? 'selected' : '' }}>Revisado</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4 mt-2">
                                            <label for="bancoDestino" class="form-label">Banco Destino:</label>
                                            <input class="form-control" name="bancoDestino" value="{{ $pedido->bancoDestino }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer d-flex gap-2">
                                    <x-grobdi.button type="submit" variant="success" icon="fa fa-save">
                                        Guardar cambios
                                    </x-grobdi.button>
                                    <x-grobdi.button type="button" variant="secondary" data-dismiss="modal">
                                        Cerrar
                                    </x-grobdi.button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            @endforeach
        @endif
    @endcan
@stop

@section('css')
    {{-- Add here extra stylesheets --}}
@stop

@section('js')
    <script>
        $(document).ready(function () {
            $('#contabilidad-table').DataTable({
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json'
                },
                pageLength: 25,
                lengthMenu: [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, 'Todos']
                ],
                dom: '<"row mb-3"<"col-md-6"l><"col-md-6"Bf>>' +
                    '<"row"<"col-md-12"tr>>' +
                    '<"row mt-3"<"col-md-5"i><"col-md-7"p>>'
            });

            const canUpdatePedido = @json($canUpdatePedido);
            if (canUpdatePedido) {
                $('.update-pedido-form').on('submit', function (e) {
                    e.preventDefault();

                    const form = $(this);
                    const pedidoId = form.data('id');
                    const formData = form.serialize();

                    $.ajax({
                        url: `/pedidoscontabilidad/${pedidoId}`,
                        type: 'POST',
                        data: formData,
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function (response) {
                            $(`#ModalPedido${pedidoId}`).modal('hide');

                            const row = $(`#pedido-row-${pedidoId}`);
                            row.find('.order-id').text(response.orderId);
                            row.find('.customer-name').text(response.customerName);
                            row.find('.created-at').text(response.created_at);
                            row.find('.payment-status').text(response.paymentStatus);
                            row.find('.accounting-status').html(response.accountingStatusLabel);
                            row.find('.voucher-status').html(response.voucherLabel);

                            alert('Pedido actualizado con éxito.');
                        },
                        error: function (xhr) {
                            console.error(xhr.responseText);
                            alert('Ocurrió un error al guardar.');
                        }
                    });
                });
            }
        });
    </script>
@stop
