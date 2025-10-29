@extends('adminlte::page')

@section('title', 'Módulos')

@section('content_header')
    <h1>🧩 Gestión de Módulos</h1>
@stop

@section('content')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3">
        <a href="{{ route('modules.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Nuevo Módulo</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-header">
            <div class="row">
                <div class="col">
                    <span class="fw-bold">Listado de módulos</span>
                </div>
                <div class="col text-right">
                    <span class="badge badge-primary text-md">{{ $modules->total() }} registros</span>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover table-grobdi mb-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Descripción</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($modules as $module)
                            <tr>
                                <td class="text-center">#{{ $module->id }}</td>
                                <td>{{ $module->name }}</td>
                                <td class="text-secondary">{{ $module->description ?: 'Sin descripción' }}</td>
                                <td class="text-center">
                                    @can('modules.edit')
                                        <a href="{{ route('modules.edit', $module) }}"
                                            class="btn btn-sm btn-warning mb-1 mb-xl-0">✏️
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
