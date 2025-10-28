@extends('adminlte::page')

@section('title', 'Módulos')


@section('content')
    <div class="grobdi-header">
        <div class="grobdi-title">
            <div>
                <h2>Gestión de Módulos</h2>
                <p>Administra los módulos del sistema</p>
            </div>
            <a href="{{ route('modules.create') }}" class="btn">➕ Nuevo Módulo</a>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span class="font-weight-bold mb-0">Listado de módulos</span>
            <span class="badge badge-primary">{{ $modules->total() }} registros</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover table-grobdi mb-0">
                    <thead>
                        <tr>
                            <th class="text-center">ID</th>
                            <th>Nombre</th>
                            <th>Descripción</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($modules as $module)
                            <tr>
                                <td class="text-center align-middle">#{{ $module->id }}</td>
                                <td class="align-middle">{{ $module->name }}</td>
                                <td class="align-middle text-muted">{{ $module->description ?: 'Sin descripción' }}</td>
                                <td class="text-center align-middle">
                                    @can('modules.edit')
                                        <a href="{{ route('modules.edit', $module) }}" class="btn btn-sm btn-warning mb-1">✏️
                                            Editar</a>
                                    @endcan
                                    @can('modules.delete')
                                        <form action="{{ route('modules.destroy', $module) }}" method="POST" class="d-inline">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger"
                                                onclick="return confirm('¿Eliminar este módulo?')">🗑️ Eliminar</button>
                                        </form>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">No hay módulos registrados
                                    actualmente.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer d-flex justify-content-end">
            {{ $modules->links() }}
        </div>
    </div>
@stop
