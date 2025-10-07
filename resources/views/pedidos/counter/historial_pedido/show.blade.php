@extends('adminlte::page')

@section('title', 'Detalle del pedido')

@section('content_header')
    <!-- <h1>Pedidos</h1> -->
@stop

@section('content')

<div class="card mt-5">
  <h2 class="card-header">Detalles del Pedido</h2>
  <div class="card-body">
  
    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
        <a class="btn btn-primary btn-sm" href="{{ route('historialpedidos.index') }}"><i class="fa fa-arrow-left"></i> Atras</a>
    </div>
  
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
        <div class="col-xs-4 col-sm-4 col-md-4 mt-2">
            <div class="form-group">
                <strong>Fecha Entrega:</strong> <br/>
                {{ $pedido->deliveryDate }}
            </div>
        </div>
        <div class="col-xs-4 col-sm-4 col-md-4 mt-2">
            <div class="form-group">
                <strong>Estado Producción:</strong> <br/>
                @if($pedido->productionStatus===0) Pendiente @else Elaborado @endif
            </div>
        </div><div class="col-xs-4 col-sm-4 col-md-4 mt-2">
            <div class="form-group">
                <strong>Estado Entrega:</strong> <br/>
                {{ $pedido->deliveryStatus }}
            </div>
        </div><div class="col-xs-4 col-sm-4 col-md-4 mt-2">
            <div class="form-group">
                <strong>Estado de Pago:</strong> <br/>
                {{ $pedido->paymentStatus }}
            </div>
        </div>
        </div><div class="col-xs-4 col-sm-4 col-md-4 mt-2">
            <div class="form-group">
                <strong>Precio total:</strong> <br/>
                S/ {{ $pedido->prize }}
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12 mt-2">
            <div class="form-group">
                <strong>Detalles:</strong> <br/>
                <ul class="list-group">
                    @foreach ($pedido->detailpedidos as $detail_pedidos)
                    <li class="list-group-item" data-id="{{ $detail_pedidos->id }}">
                        <form action="{{ route('historialpedidos.destroy',$detail_pedidos->id) }}" method="POST" class="form-inline">
                            <span class="descripcion-text">{{ $detail_pedidos->articulo }} - {{ $detail_pedidos->cantidad }} unid. - S/ {{ $detail_pedidos->sub_total }}</span>
                            <input type="text" class="descripcion-input form-control col-sm-5" value="{{ $detail_pedidos->articulo }}"style="display: none;" disabled/>
                            <input type="number" class="cantidad-input form-control  col-sm-1" value="{{ $detail_pedidos->cantidad }}"style="display: none;"/>
                            @if (Auth::user()->role->name == "jefe-operaciones" or Auth::user()->role->name == "admin" )
                            @csrf
                            @method('DELETE')
                                <button class="btn btn-outline-warning btn-editar" type="button"><i class="fa fa-pen"></i></button>
                                <button class="btn btn-success btn-guardar" type="button" style="display: none;">Actualizar</button>
                                <button class="btn btn-outline-danger btn-eliminar" type="submit"><I class="fa fa-trash"></I></button>
                            @endif 
                        </form>
                    </li>
                    @endforeach 
                </ul>

            </div>
        </div>
        @if ($pedido->fotoDomicilio)
            <div class="col-xs-4 col-sm-4 col-md-4">
                <strong>Imagen Foto Domicilio:</strong>
                <img src="{{ asset($pedido->fotoDomicilio) }}" alt="{{ $pedido->orderId }} width="600" height="600"">
                <br>
                <strong>Fecha y hora del registro:</strong> {{ $pedido->fechaFotoDomicilio }}
            </div>
        @endif
        @if ($pedido->fotoEntrega)
            <div class="col-xs-4 col-sm-4 col-md-4">
                <div class="form-group">
                    <strong>Imagen Foto Entregado:</strong>
                    <img src="{{ asset($pedido->fotoEntrega) }}" alt="{{ $pedido->orderId }} width="600" height="600"">
                    <br>
                    <strong>Fecha y hora del registro:</strong> {{ $pedido->fechaFotoEntrega }}
                </div>
            </div>
        @endif
    </div>
  
  </div>
</div>

@stop

@section('css')
    {{-- Add here extra stylesheets --}}
    {{-- <link rel="stylesheet" href="/css/admin_custom.css"> --}}
@stop

@section('js')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
        // Hacer que los inputs sean visibles solo cuando se presiona 'Editar'
        $(".btn-editar").on('click', function() {
            var row = $(this).closest("li");
            row.find(".descripcion-text").hide();
            row.find(".descripcion-input").show();
            row.find(".cantidad-input").show();
            row.find(".btn-editar").hide();
            row.find(".btn-eliminar").hide();
            row.find(".btn-guardar").show();
        });
            // Cuando se presiona 'Guardar', actualiza los datos
        $(".btn-guardar").on('click', function() {
            var row = $(this).closest("li");
            var id = row.data('id');
            var descripcion = row.find(".descripcion-input").val();
            var cantidad = row.find(".cantidad-input").val();

            $.ajax({
                url: '/historial/' + id + '/actualizar',
                    type: "POST",
                    data: {
                        _method: "PUT",
                        _token: '{{ csrf_token() }}',
                        descripcion: descripcion,
                        cantidad: cantidad
                    },
                success: function(response) {
                    row.find(".descripcion-text").text(descripcion+' - '+cantidad+' unid.').show();
                    row.find(".descripcion-input").hide();
                    row.find(".cantidad-input").hide();
                    row.find(".btn-editar").show();
                    row.find(".btn-eliminar").show();
                    row.find(".btn-guardar").hide();
                    alert('Producto actualizado');
                },
                error: function(error) {
                    alert('Error al actualizar el producto');
                }
            });
        });
    });
    </script>
@stop