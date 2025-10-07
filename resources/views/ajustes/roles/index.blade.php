@extends('adminlte::page')

@section('title', 'Roles')

@section('content_header')
    <h1>Sistema de control de roles</h1>
@stop

@section('content')
    <a href="{{ route('roles.create') }}" class="btn btn-primary mb-3">+ Nuevo Rol</a>
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header">
                    <label>Lista</label>
                </div>
                <div class="card-body">
                    <div class="table table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="thead">
                                <tr>
                                    <th>Rol</th>
                                    <th>Descripción</th>
                                    <th>Permisos</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($roles as $role)
                                    <tr>
                                        <td>{{ $role->name }}</td>
                                        <td>{{ $role->description }}</td> 
                                        <td>
                                            @foreach ($role->views as $view)
                                                <span class="badge badge-info">{{ $view->module->name }} - {{ $view->description }}</span>
                                            @endforeach
                                        </td>
                                        <td>
                                            <a href="{{ route('roles.edit', $role) }}" class="btn btn-sm btn-warning">Editar</a>
                                            <a href="{{ route('roles.permissions', $role) }}" class="btn btn-sm btn-info">Permisos</a>
                                            <form action="{{ route('roles.destroy', $role) }}" method="POST" class="d-inline">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar?')">Eliminar</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>

                        </table>

                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
@stop

@section('js')

    <script>
    </script>
    
@stop