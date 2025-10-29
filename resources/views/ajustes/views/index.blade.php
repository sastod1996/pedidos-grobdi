@extends('adminlte::page')

@section('title', 'Vistas')

@section('content_header')
    <h1>🖼️ Gestión de Vistas</h1>
@stop

@section('content')
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3">
        <a href="{{ route('views.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Nueva Vista</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <form method="GET" action="{{ route('views.index') }}" class="w-100">
                <div class="row align-items-end">
                    <div class="form-group col-12 col-md-6 col-lg-5 mb-3 mb-md-0">
                        <label for="module_id" class="mb-1">Filtrar por módulo</label>
                        <select name="module_id" id="module_id" class="form-control">
                            <option value="">Todos los módulos</option>
                            @foreach ($modules as $module)
                                <option value="{{ $module->id }}"
                                    {{ (string) ($selectedModule ?? '') === (string) $module->id ? 'selected' : '' }}>
                                    {{ $module->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-6 col-lg-7">
                        <div class="d-flex flex-column flex-sm-row justify-content-between"
                            style="place-items: end; gap: 0.5rem;">
                            <button type="submit" class="btn btn-primary btn-block">🔍
                                Filtrar</button>
                            <a href="{{ route('views.index') }}" class="btn btn-outline-dark btn-block">♻️ Limpiar</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-striped table-hover table-grobdi mb-0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Ruta</th>
                    <th>Módulo</th>
                    <th>Menú</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($views as $view)
                    <tr>
                        <td class="text-center">{{ $view->id }}</td>
                        <td>{{ $view->description }}</td>
                        <td>{{ $view->url }}</td>
                        <td>{{ $view->module->name }}</td>
                        <td class="text-center text-lg">
                            <span
                                class="badge badge-{{ $view->is_menu ? 'success' : 'secondary' }}">{{ $view->is_menu ? 'Si' : 'No' }}</span>
                        </td>
                        <td>
                            <a href="{{ route('views.edit', $view) }}" class="btn btn-sm btn-warning">Editar</a>
                            <form action="{{ route('views.destroy', $view) }}" method="POST" class="d-inline">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger"
                                    onclick="return confirm('¿Eliminar?')">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    @include('empty-table', ['dataLength' => 0, 'colspan' => 6])
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $views->links() }}
    </div>
@stop
