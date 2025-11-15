@extends('adminlte::page')

@section('title', 'Asignar Pedidos')
@section('content')
@can('asignarpedidos.index')
<x-grobdi.layout.header-card
    title="Pedidos"
    subtitle="Asigna y reubica pedidos por zona"
>
    <x-slot:filter>
        <form id="filterForm" action="{{ route('asignarpedidos.index') }}" method="GET">
            <div class="row">
                <div class="col-md-4">
                    <label for="fecha"><i class="fa fa-calendar"></i> Fecha</label>
                    <input type="date" name="fecha" id="fecha" value="{{ request()->query('fecha') }}" required>
                </div>

                <div class="col-md-4">
                    <label for="orderId"><i class="fa fa-hashtag"></i> Nro de Pedido</label>
                    <input type="text" name="orderId" id="orderId" value="{{ request()->query('orderId') }}" placeholder="Ingrese número de pedido">
                </div>

                <div class="col-md-4 d-flex align-items-end">
                    <x-grobdi.button id="searchBtn" type="submit" icon="fa fa-search">
                        Buscar
                    </x-grobdi.button>
                </div>
            </div>
        </form>
    </x-slot:filter>
</x-grobdi.layout.header-card>

@error('message')
    <div class="alert alert-danger my-3">
        {{ $message }}
    </div>
@enderror

<div class="row mt-4">
    @php
        // Mostrar únicamente las zonas cuyo id esté entre 1 y 5 (inclusive).
        // Conservamos $zonas original para los selects (todas las zonas).
        $zonesCollection = $zonas instanceof \Illuminate\Support\Collection ? $zonas : collect($zonas);
        $displayZonas = $zonesCollection->filter(function($z){
            $id = data_get($z, 'id');
            return is_numeric($id) && $id >= 1 && $id <= 5;
        })->values();
    @endphp

    @foreach($displayZonas as $zona)
        <div class="col-md-6 mb-4">
            <x-grobdi.layout.table-card
                :title="$zona->name"
                tableClass="table-striped table-hover"
            >
                <thead>
                    <tr>
                        <th>Nro</th>
                        <th>Nro pedido</th>
                        <th>Fecha creada</th>
                        <th>Distrito</th>
                        <th>Zonas</th>
                        <th width="120px">Opciones</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($pedidos as $pedido)
                        @if ($pedido->zone_id == $zona->id)
                            <tr>
                                <td>{{ $pedido->nroOrder }}</td>
                                <td>{{ $pedido->orderId }}</td>
                                <td>{{ $pedido->created_at }}</td>
                                <td>{{ $pedido->district }}</td>

                                <form action="{{ route('asignarpedidos.update', $pedido->id) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <td>
                                        <select name="zone_id" id="zone_id" class="form-select form-select-sm">
                                            <option disabled>Cambiar zona</option>
                                            @foreach ($zonas as $zon)
                                                <option value="{{ $zon->id }}" {{ $pedido->zone_id === $zon->id ? 'selected' : '' }}>
                                                    {{ $zon->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <x-grobdi.button type="submit" variant="primary" size="sm" icon="fa fa-pencil-square">
                                            Cambiar
                                        </x-grobdi.button>
                                    </td>
                                </form>
                            </tr>
                        @endif
                    @empty
                        <tr>
                            <td colspan="6">No hay información que mostrar</td>
                        </tr>
                    @endforelse
                </tbody>
            </x-grobdi.layout.table-card>
        </div>
    @endforeach
</div>

@error('message')
    <p class="text-danger">{{ $message }}</p>
@enderror

@endcan
@stop
@section('css')
    {{-- Add here extra stylesheets --}}
    {{-- <link rel="stylesheet" href="/css/admin_custom.css"> --}}
@stop

@section('js')
    <script> console.log("Hi, I'm using the Laravel-AdminLTE package!"); </script>
    <script>
        $(document).ready(function() {
            // Cambiar texto del botón al enviar formulario
            $('#filterForm').on('submit', function() {
                $('#searchBtn').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Buscando...');
            });
        });
    </script>
@stop
