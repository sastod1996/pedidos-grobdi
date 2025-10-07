@extends('adminlte::page')

@section('title', 'Asignar Pedidos')
@section('content')
<div class="card mt-5">
    <h2 class="card-header">Pedidos</h2>
    <div class="card-body">
        <div class="card border-info shadow-lg mb-4">
            <div class="card-header bg-info text-white">
                <h5 class="card-title mb-0"><i class="fa fa-filter"></i> Filtros de Búsqueda</h5>
            </div>
            <div class="card-body bg-light">
                <form id="filterForm" action="{{ route('asignarpedidos.index') }}" method="GET">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="fecha" class="form-label fw-bold text-info"><i class="fa fa-calendar"></i> Fecha</label>
                            <input class="form-control border-info bg-light shadow-sm" type="date" name="fecha" id="fecha" value="{{ request()->query('fecha') }}" required>
                        </div>
                        <div class="col-md-4">
                            <label for="orderId" class="form-label fw-bold text-info"><i class="fa fa-hashtag"></i> Nro de Pedido</label>
                            <input class="form-control border-info bg-light shadow-sm" type="text" name="orderId" id="orderId" value="{{ request()->query('orderId') }}" placeholder="Ingrese número de pedido">
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button id="searchBtn" type="submit" class="btn btn-info w-100 shadow-sm"><i class="fa fa-search"></i> Buscar</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        @error('message')
            <div class="alert alert-danger mt-2 mb-2">
                {{ $message }}
            </div>
        @enderror
        <br>
        <div class="row">
            @foreach($zonas as $zona)
            <div class="table table-responsive">
                <div class="col-xs-6 col-sm-6 col-md-6">
                    <label for="fecha_inicio">{{ $zona->name }}</label>

                    <table class="table table-striped table-hover border shadow-sm">
                        <thead class="bg-dark text-white">
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
                                <td>{{ $pedido->created_at }}  </td>
                                <td>{{ $pedido->district}}</td>
                                <form action="{{ route('asignarpedidos.update',$pedido->id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <td>
                                    <select class="form-select form-select-sm" aria-label=".form-select-sm example"  name="zone_id" id="zone_id">
                                        <option disabled>Cambiar zona</option>
                                        @foreach ($zonas as $zon)
                                            <option value={{ $zon->id }} {{ $pedido->zone_id ===  $zon->id  ? 'selected' : '' }}>{{$zon->name}}</option>
                                        
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <!-- <a class="btn btn-success btn-sm" data-toggle="modal" data-target="#createModal" href="{{ route('asignarpedidos.show',$pedido->id) }}"><i class="fa fa-eye"></i> ver</a> -->
                        
                                    <button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-pencil-square"></i> cambiar</button>
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
            
                    </table>
                </div>
            </div>
            @endforeach
        </div>
        @error('message')
            <p style="color: red;">{{ $message }}</p>
        @enderror
        
  </div>
</div> 
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