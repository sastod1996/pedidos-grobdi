@extends('adminlte::page')
@section('title', 'Dashboard')

@section('content_header')
    <!-- <h1>cotizador</h1> -->
@stop

@section('content')
    <div class="container container-fluid" style="user-select: none;">
     @include('messages')

        <div class="d-flex mb-3 justify-content-between align-items-center">
            <h1 class="text-center flex-grow-1">Listado de Compras</h1>
            <a href="{{ route('compras.create') }}" class="btn btn_crear">
                <i class="fas fa-plus"></i> Nueva Compra
            </a>
        </div>

        <!-- Filtros de búsqueda -->
        <div class="row mb-3">
    <form method="GET" action="{{ route('compras.index') }}" class="w-100">
        <div class="d-flex flex-wrap align-items-center" style="gap: 15px;">
            <!-- Proveedor -->
            <div class="flex-grow-1" style="min-width: 200px; max-width: 300px;">
                <div class="form-group">
                    <label for="proveedor" class="mr-2">Proveedor:</label>
                    <select name="proveedor_id" id="proveedor" class="form-control select2 w-100">
                        <option value="">Todos</option>
                        @foreach($proveedores as $proveedor)
                            <option value="{{ $proveedor->id }}" {{ request('proveedor_id') == $proveedor->id ? 'selected' : '' }}>
                                {{ $proveedor->razon_social }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="d-flex" style="gap: 10px; min-width: 200px;">
                <!-- Fecha Desde -->
                <div style="min-width: 180px;">
                    <div class="form-group">
                        <label for="fecha_inicio" class="mr-2">Desde:</label>
                        <input type="date" name="fecha_inicio" id="fecha_inicio" class="form-control" value="{{ request('fecha_inicio') }}">
                    </div>
                </div>

                <!-- Fecha Hasta -->
                <div style="min-width: 180px;">
                    <div class="form-group">
                        <label for="fecha_fin" class="mr-2">Hasta:</label>
                        <input type="date" name="fecha_fin" id="fecha_fin" class="form-control" value="{{ request('fecha_fin') }}">
                    </div>
                </div>
            </div>

            <!-- Botones Filtrar y Limpiar -->
            <div class="d-flex" style="gap: 10px; min-width: 200px;">
                <button type="submit" class="btn btn-primary" style="border: 1px solid #fe495f; background-color: rgb(255, 113, 130);">  
                    <i class="fas fa-filter"></i> Filtrar
                </button>
                <a href="{{ route('compras.index') }}" class="btn btn-secondary">
                    <i class="fas fa-sync-alt"></i> Limpiar
                </a>
            </div>
        </div>
    </form>
</div>

        <!-- Tabla de compras -->
        <div class="table-responsive">
            <table id="tablaCompras" class="table table-bordered table-striped table-hover">
                <thead class="bg-secondary text-white">
                    <tr>
                        <th width="5%">N°</th>
                        <th width="10%">Fecha</th>
                        <th>Proveedor</th>
                        <th width="10%">Referencia</th>
                        <th width="12%">Total</th>
                        <th width="10%">Usuario</th>
                        <th width="10%">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($compras as $index => $compra)
                        <tr>
                            <td>{{ $index + 1}}</td>
                            <td>{{ $compra->fecha_emision->format('d/m/Y') }}</td>
                            <td style="max-width: 150px;overflow: hidden; 
                                text-overflow: ellipsis; 
                                white-space: nowrap;">
                                {{ $compra->proveedor->razon_social }}
                            </td>
                            <td>{{ $compra->serie }} - {{ $compra->numero }}</td>
                            <td class="text-right">{{ number_format($compra->precio_total, 4) }}</td>
                            <td>{{ $compra->creador->name ?? 'en sysgrob' }}</td>
                            <td class="text-center">
                                @include('compras.show')
                                <button type="button" class="btn btn-info btn-ver-detalle" title="Ver detalle" data-toggle="modal" data-target="#modalDetalleCompra{{ $compra->id }}">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <form action="{{ route('compras.destroy', $compra->id) }}" method="POST" style="display: inline-block;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger" title="Eliminar" onclick="return confirm('¿Está seguro?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center">No se encontraron compras registradas</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@stop

@section('css')
    <link href="{{ asset('css/muestras/home.css') }}" rel="stylesheet" />
@stop
@section('js')
    <script>
        $(document).ready(function() {
                $('.select2').select2({
                    placeholder: 'Seleccione un proveedor',
                    allowClear: true,
                    width: '100%' 
                });

                // Inicializar DataTable
            $('#tablaCompras').DataTable({
                    language: {
                        url: 'https://cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json',
                    },
                    ordering: false,
                    responsive: true,
                    // quitamos "l" del DOM para eliminar el selector de cantidad de registros
                    dom: '<"row"<"col-sm-12 col-md-12"f>>rt<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
                    pageLength: 10,
                    initComplete: function() {
                        $('.dataTables_filter')
                            .addClass('mb-3')
                            .find('input')
                            .attr('placeholder', 'Buscar') // <- aquí el placeholder
                            .end()
                            .find('label')
                            .contents().filter(function() {
                                return this.nodeType === 3;
                            }).remove()
                            .end()
                            .prepend('Buscar:');
                    }
                });
            });
    </script>
@stop

