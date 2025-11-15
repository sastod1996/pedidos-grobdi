@extends('adminlte::page')

@section('content')
@can('historialpedidos.index')
<x-grobdi.layout.header-card
    title="Historial de pedidos"
    subtitle="Consulta entregas anteriores por rango de fechas o búsqueda personalizada"
>
    <x-slot:filter>
        <form action="{{ route('historialpedidos.index') }}" method="GET">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label for="fecha_inicio">Fecha de inicio</label>
                    <input class="form-control" type="date" name="fecha_inicio" id="fecha_inicio" value="{{ request()->query('fecha_inicio') }}" required>
                </div>
                <div class="col-md-4">
                    <label for="fecha_fin">Fecha de fin</label>
                    <input class="form-control" type="date" name="fecha_fin" id="fecha_fin" value="{{ request()->query('fecha_fin') }}" required>
                </div>
                <div class="col-md-4">
                    <x-grobdi.button type="submit" icon="fa fa-search">
                        Buscar
                    </x-grobdi.button>
                </div>
            </div>
        </form>
        <form action="{{ route('historialpedidos.index') }}" method="GET" class="mt-3">
            <div class="row">
                <div class="col-12">
                    <label for="buscar">Buscar:</label>
                    <input class="form-control" type="text" name="buscar" id="buscar" value="{{ request()->query('buscar') }}">
                </div>
            </div>
        </form>
    </x-slot:filter>
</x-grobdi.layout.header-card>

@error('message')
    <p class="text-danger">{{ $message }}</p>
@enderror
@session('success')
    <div class="alert alert-success" role="alert"> {{ $value }} </div>
@endsession

<x-grobdi.layout.table-card
    title="Pedidos"
    tableClass="table-bordered table-striped"
>
    <thead>
        <tr>
            <th>
                <a href="{{ route('historialpedidos.index', ['sort_by' => 'orderId', 'direction' => $ordenarPor == 'orderId' && $direccion == 'asc' ? 'desc' : 'asc','fecha_inicio'=>request()->query('fecha_inicio')?request()->query('fecha_inicio'):date('Y-m-d'),'fecha_fin'=>request()->query('fecha_fin')?request()->query('fecha_fin'):date('Y-m-d')]) }}">
                    Id Pedido
                    @if ($ordenarPor == 'orderId')
                        {{ $direccion == 'asc' ? '↑' : '↓' }}
                    @endif
                </a>
            </th>
            <th>Cliente</th>
            <th>Fecha de Entrega</th>
            <th>Estado Producción</th>
            <th>Estado Entrega</th>
            <th>Opciones</th>
        </tr>
    </thead>

    <tbody>
    @forelse ($pedidos as $pedido)
        <tr>
            <td>{{ $pedido->orderId }}</td>
            <td>{{ $pedido->customerName }}</td>
            <td>{{ $pedido->deliveryDate }}  </td>
            <td>{{ $pedido->productionStatus === 0 ? 'Pendiente' : 'Elaborado' }}</td>
            <td>{{ $pedido->deliveryStatus}}</td>
            <td>
                <form action="{{ route('cargarpedidos.destroy',$pedido->id) }}" method="POST">

                    <x-grobdi.button href="{{ route('historialpedidos.show',$pedido->id) }}" variant="info" size="sm" icon="fa fa-info">
                        Detalles
                    </x-grobdi.button>
                    @csrf
                    @method('DELETE')
                </form>
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="6">No hay información que mostrar</td>
        </tr>
    @endforelse
    </tbody>

    <x-slot:footer>
        {!! $pedidos->appends(request()->except('page'))->links() !!}
    </x-slot:footer>
</x-grobdi.layout.table-card>

@endcan
@stop

@section('css')
{{-- Add here extra stylesheets --}}
{{-- <link rel="stylesheet" href="/css/admin_custom.css"> --}}
@stop

@section('js')
<script> console.log("Hi, I'm using the Laravel-AdminLTE package!"); </script>
@stop
