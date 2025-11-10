@extends('adminlte::page')

@section('title', 'Pedidos')

@php
$user = auth()->user();
$canDownloadExcel = $user?->can('pedidoscontabilidad.downloadExcel');
$canUpdatePedido = $user?->can('pedidoscontabilidad.update');
@endphp

@section('content')
@can('pedidoscontabilidad.index')
<div class="container-fluid">
    <div class="grobdi-header">
        <div class="grobdi-title">
            <div>
                <h2>📑 Pedidos - Contabilidad</h2>
                <p>Filtra y revisa los pedidos para contabilidad</p>
            </div>
            <div>
                @can('pedidoscontabilidad.downloadExcel')
                    @if(request()->get('fecha_inicio'))
                        <a class="btn" href="{{ route('pedidoscontabilidad.downloadExcel',['fechainicio' => request()->get('fecha_inicio'),'fechafin' => request()->get('fecha_fin')]) }}"><i class="fa fa-file-excel"></i> Descargar Excel</a>
                    @else
                        <a class="btn" href="{{ route('pedidoscontabilidad.downloadExcel',['fechainicio' => date('Y-m-d'),'fechafin' => date('Y-m-d')]) }}"><i class="fa fa-file-excel"></i> Descargar Excel</a>
                    @endif
                @endcan
            </div>
        </div>

        <div class="grobdi-filter">
            <form action="{{ route('pedidoscontabilidad.index') }}" method="GET">
                <div class="row align-items-end">
                    <div class="col-12 col-md-4 mb-3 mb-md-0">
                        <label for="fecha_inicio">Fecha de inicio</label>
                        <input class="form-control" type="date" name="fecha_inicio" id="fecha_inicio" value="{{ request()->get('fecha_inicio') }}" required>
                    </div>

                    <div class="col-12 col-md-4 mb-3 mb-md-0">
                        <label for="fecha_fin">Fecha de fin</label>
                        <input class="form-control" type="date" name="fecha_fin" id="fecha_fin" value="{{ request()->get('fecha_fin') }}" required>
                    </div>

                    <div class="col-12 col-md-4">
                        <div class="filter-actions">
                            <button type="submit" class="btn">🔍 Filtrar</button>
                            <a href="{{ route('pedidoscontabilidad.index') }}" class="btn btn-outline">♻️ Limpiar</a>
                        </div>
                    </div>
                </div>
                @error('message')
                    <p style="color: red;">{{ $message }}</p>
                @enderror
            </form>
        </div>
    </div>

    <div class="card mt-3">
        <h2 class="card-header">Pedidos</h2>
        <div class="card-body">
            @session('success')
                <div class="alert alert-success" role="alert"> {{ $value }} </div>
            @endsession
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-grobdi">
            <thead>
                <tr>
                    <th>Nro pedido</th>
                    <th>Cliente</th>
                    <th>Fecha de registro</th>
                    <th>Estado de pago</th>
                    <th>Estado Contabilidad</th>
                    <th>Voucher</th>
                    @if($canUpdatePedido)
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
                    @if($canUpdatePedido)
                    <td>
                        <button type="button" class="btn btn-info" data-toggle="modal" data-target="#ModalPedido{{ $pedido->id }}">
                            <i class="fa fa-info"></i> Detalles
                        </button>
                    </td>
                    @endif
                </tr>
                <!-- Modal -->
                @if($canUpdatePedido)
                <div class="modal fade" id="ModalPedido{{ $pedido->id }}" tabindex="-1" aria-labelledby="labelPedido{{ $pedido->id }}" aria-hidden="true">
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
                                        <div class="col-xs-4 col-sm-4 col-md-4">
                                            <div class="form-group">
                                                <strong>Nro del Pedido:</strong> <br/>
                                                {{ $pedido->orderId }}
                                            </div>
                                        </div>
                                        <div class="col-xs-4 col-sm-4 col-md-4">
                                            <div class="form-group">
                                                <strong>cliente:</strong> <br/>
                                                {{ $pedido->customerName }}
                                            </div>
                                        </div>
                                        <div class="col-xs-4 col-sm-4 col-md-4">
                                            <div class="form-group">
                                                <strong>Precio:</strong> <br/>
                                                {{ $pedido->prize }}
                                            </div>
                                        </div>
                                        <div class="col-xs-4 col-sm-4 col-md-4 mt-2">
                                            <div class="form-group">
                                                <strong>Fecha Entrega:</strong> <br/>
                                                {{ $pedido->deliveryDate }}
                                            </div>
                                        </div>
                                        <div class="col-xs-4 col-sm-4 col-md-4 mt-2">
                                            <div class="form-group">
                                                <strong>Estado de pago:</strong> <br/>
                                                {{ $pedido->paymentStatus }}
                                            </div>
                                        </div>
                                        <div class="col-xs-4 col-sm-4 col-md-4 mt-2">
                                            <div class="form-group">
                                                <strong>Metodo de pago:</strong> <br/>
                                                {{ $pedido->paymentMethod }}
                                            </div>
                                        </div>
                                        @if ($pedido->voucher)
                                            @php
                                                $images = explode(",",$pedido->voucher);
                                                $nro_operaciones = explode(",",$pedido->operationNumber);
                                                $array_voucher = [];
                                                foreach ($images as $key => $voucher) {
                                                    array_push($array_voucher,['nro_operacion'=>$nro_operaciones[$key],'voucher'=>$voucher]);
                                                }
                                            @endphp
                                            @foreach ($array_voucher as $voucher)
                                                <div class="col-xs-4 col-sm-4 col-md-4">
                                                    Nro de Operación: <strong>{{ $voucher['nro_operacion'] }}</strong><br>
                                                    <img src="{{ asset($voucher['voucher']) }}" alt="{{ $pedido->orderId }}" width="400" height="400">
                                                </div>
                                            @endforeach
                                        @endif
                                        <div class="col-xs-4 col-sm-4 col-md-4 mt-2">
                                            <label for="accountingStatus" class="form-select"><strong>Estado de contabilidad:</strong></label>
                                            <select class="form-control" name="accountingStatus" id="accountingStatus">
                                                <option disabled select>Selecciona una opción</option>
                                                <option value="0" {{ $pedido->accountingStatus === 0 ? 'selected' : '' }}>Sin revisar</option>
                                                <option value="1" {{ $pedido->accountingStatus === 1 ? 'selected' : '' }}>Revisado</option>
                                            </select>
                                        </div>
                                        <div class="col-xs-4 col-sm-4 col-md-4 mt-2">
                                            <label>Banco Destino:</label>
                                            <input class="form-control" name="bancoDestino" value="{{ $pedido->bancoDestino }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="submit" class="btn btn-success">Guardar cambios</button>
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                @endif
            @empty
                <tr>
                    <td colspan="{{ $canUpdatePedido ? 7 : 6 }}">No hay información que mostrar</td>
                </tr>
            @endforelse
            </tbody>

        </table>


  </div>
</div>

@endcan
@stop

@section('css')
    {{-- Add here extra stylesheets --}}
    {{-- <link rel="stylesheet" href="/css/admin_custom.css"> --}}
@stop

@section('js')
<script>
    $(document).ready(function () {
        $('.table').DataTable({
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json'
            },
        pageLength: 25,
        lengthMenu: [ [10, 25, 50, 100, -1], [10, 25, 50, 100, "Todos"] ],
            dom: '<"row mb-3"<"col-md-6"l><"col-md-6"Bf>>' +
                '<"row"<"col-md-12"tr>>' +
                '<"row mt-3"<"col-md-5"i><"col-md-7"p>>'
        });
        const canUpdatePedido = @json($canUpdatePedido);
        if (canUpdatePedido) {
        $('.update-pedido-form').on('submit', function (e) {
            e.preventDefault();

            let form = $(this);
            let pedidoId = form.data('id');
            let formData = form.serialize();
            $.ajax({
                url: `/pedidoscontabilidad/${pedidoId}`,
                type: 'POST',
                data: formData,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function (response) {
                    // Cierra el modal
                    $('#ModalPedido' + pedidoId).modal('hide');

                    // Actualiza cada celda
                    let row = $('#pedido-row-' + pedidoId);
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
