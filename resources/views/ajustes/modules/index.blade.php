@extends('adminlte::page')

@section('title', 'Módulos')

@section('content_header')
    <h1>Gestión de Módulos</h1>
@stop

@section('content')
    <a href="{{ route('modules.create') }}" class="btn btn-primary mb-3">+ Nuevo Módulo</a>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Descripción</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($modules as $module)
                <tr>
                    <td>{{ $module->id }}</td>
                    <td>{{ $module->name }}</td>
                    <td>{{ $module->description }}</td>
                    <td>
                        @can('modules.edit')
                            <a href="{{ route('modules.edit', $module) }}" class="btn btn-sm btn-warning">Editar</a>
                        @endcan
                        @can('modules.delete')
                            <form action="{{ route('modules.destroy', $module) }}" method="POST" class="d-inline">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar?')">Eliminar</button>
                            </form>
                        @endcan
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@stop
