@extends('adminlte::page')

@section('title', 'Pedidos Motorizado')

@section('content_header')

<!-- <h1>Pedidos</h1> -->
@stop

@php
$user = auth()->user();
$canEditPedido = $user?->can('pedidosmotorizado.edit');
@endphp

@section('content')
@can('pedidosmotorizado.index')
<div class="card mt-3">
    <h2 class="card-header">
        Pedidos de la zona
        @if(Auth::user()->zones && Auth::user()->zones->count() > 0)
            {{ Auth::user()->zones[0]->name }}
        @else
            <span class="text-danger">Sin zona asignada</span>
        @endif
    </h2>
    <div class="card-body">
        <form action="{{ route('pedidosmotorizado.index') }}" method="GET">
            <div class="row pb-4">
                <div class="col-xs-1 col-sm-1 col-md-1">
                    <label for="fecha">Fecha:</label>
                </div>
                <div class="col-xs-2 col-sm-2 col-md-2">
                    <input class="form-control" type="date" name="fecha" id="fecha" value="{{ request()->get('fecha') }}" required>
                </div>
                <div class="col-xs-3 col-sm-3 col-md-3">
                    <button type="submit" class="btn btn-outline-primary"><i class="fa fa-search"></i> Buscar</button>
                </div>
                <div class="col-xs-3 col-sm-3 col-md-3">
                    <select class="form-control" aria-label="Default select example" id="filter" onchange="filterTable()">
                        <option selected disabled>Selecciona un turno</option>
                        <option value="Mañana">Mañana</option>
                        <option value="Tarde">Tarde</option>
                    </select>
                </div>
            </div>
            @error('message')
            <p style="color: red;">{{ $message }}</p>
            @enderror
        </form>
        <div class="table table-responsive">
            <table class="table table-striped table-grobdi table-hover" id="tablaPedidos">
                <thead>
                    <tr>
                        <th>Nro</th>
                        <th>Id Pedido</th>
                        <th>Cliente</th>
                        <th>Est. pedido</th>
                        <th>Dirección</th>
                        <th>Referencia</th>
                        <th>Turno</th>
                        <th>Distrito</th>
                        @if($canEditPedido)
                        <th>Opciones</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach ($pedidos_zona as $arr)
                    <tr class={{ $arr["turno"]== 0 ?"table-warning":"table-success" }}>
                        <td>{{ $arr["nroOrder"] }}</td>
                        <td>{{ $arr["orderId"] }}</td>
                        <td>{{ $arr["customerName"] }}</td>
                        <td>
                            {{ $arr->currentDeliveryState ? ucfirst($arr->currentDeliveryState->state) : 'Asignado' }}
                        </td>
                        <td>{{ $arr["address"] }}</td>
                        <td>{{ $arr["reference"] }}</td>
                        <td>{{ $arr["turno"] == 0 ? "Mañana":"Tarde" }}</td>
                        <td>{{ $arr["district"] }}</td>
                        @if($canEditPedido)
                        <td>
                            <a class="btn btn-primary btn-sm" href="{{ route('pedidosmotorizado.edit',$arr->id) }}">
                                <i class="fa-pencil"></i> Actualizar
                            </a>
                        </td>
                        @endif
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
        @endif
    </div>
</div>
@endcan
@stop

@section('css')
{{-- Add here extra stylesheets --}}
{{-- <link rel="stylesheet" href="/css/admin_custom.css"> --}}
<style type="text/css">
</style>

@stop

@section('js')
<script>
    function filterTable() {
        var filter = document.getElementById('filter').value.toLowerCase(); // Obtener el valor del select
        var table = document.getElementById('tablaPedidos');
        var rows = table.getElementsByTagName('tr');

        // Recorremos todas las filas de la tabla
        for (var i = 1; i < rows.length; i++) { // Comenzamos en 1 para saltarnos la fila de encabezado
            var cells = rows[i].getElementsByTagName('td');
            var match = false;

            // Recorremos todas las celdas de cada fila (en este caso solo la segunda columna 'name' se filtra)
            if (cells[6]) { // La columna de 'name' es la segunda columna (índice 1)
                if (cells[6].innerText.toLowerCase().indexOf(filter) > -1) {
                    match = true;
                }
            }

            // Si encuentra una coincidencia, mostramos la fila, si no la ocultamos
            if (match || filter === "") {
                rows[i].style.display = '';
            } else {
                rows[i].style.display = 'none';
            }
        }
    }
</script>
@stop
