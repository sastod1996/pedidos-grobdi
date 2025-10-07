@extends('adminlte::page')

@section('title', 'Vistas')

@section('content_header')
    <h1>Gestión de Vistas</h1>
@stop

@section('content')
    <a href="{{ route('views.create') }}" class="btn btn-primary mb-3">+ Nueva Vista</a>

    <table class="table table-bordered">
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
            @foreach ($views as $view)
                <tr>
                    <td>{{ $view->id }}</td>
                    <td>{{ $view->description }}</td>
                    <td>{{ $view->url }}</td>
                    <td>{{ $view->module->name }}</td>
                    <td>
                        @if($view->is_menu)
                            <span class="badge badge-success">Sí</span>
                        @else
                            <span class="badge badge-secondary">No</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('views.edit', $view) }}" class="btn btn-sm btn-warning">Editar</a>
                        <form action="{{ route('views.destroy', $view) }}" method="POST" class="d-inline">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar?')">Eliminar</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@stop
