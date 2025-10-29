@extends('adminlte::page')

@section('title', 'Usuarios')

@section('content_header')
    <h1>👥 Gestión de Usuarios</h1>
@stop

@section('content')
    <div class="card shadow-sm mt-2">
        <div class="card-header">
            <div class="row align-items-center">
                <div class="col">
                    <span class="text-lg fw-bold">Lista de usuarios</span>
                </div>
                <div class="col text-right">
                    <a class="btn btn-success" href="{{ route('usuarios.create') }}"><i class="fas fa-plus"></i> Crear
                        Usuario</a>
                </div>
            </div>
        </div>
        <div class="card-body">
            @session('success')
                <div class="alert alert-success" role="alert"> {{ $value }} </div>
            @endsession
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover table-grobdi mb-0">
                    <thead>
                        <tr>
                            <th>🎃 Nombre</th>
                            <th>✉️ Email</th>
                            <th>🛡️ Rol</th>
                            <th>🗺️ Zonas</th>
                            <th>Estado</th>
                            <th>Editar</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($usuarios as $usuario)
                            @php
                                $isActive = $usuario->active == true;
                            @endphp
                            <tr class="{{ $isActive == false ? 'table-danger' : '' }}">
                                <td>{{ $usuario->name }}</td>
                                <td>{{ $usuario->email }}</td>
                                <td>{{ $usuario->role->name }}</td>
                                <td class="text-center">
                                    @forelse($usuario->zones as $zona)
                                        <span
                                            class="badge badge-info {{ count($usuario->zones) > 1 ? 'mr-1 mb-1 text-xs' : 'text-sm' }}">{{ $zona->name }}</span>
                                    @empty
                                        <span class="text-muted">Sin zonas asignadas</span>
                                    @endforelse
                                </td>
                                <td class="text-center">
                                    <span
                                        class="badge badge-{{ $isActive ? 'success' : 'secondary' }}">{{ $isActive ? 'Activo' : 'Inactivo' }}</span>
                                </td>
                                <td class="text-center">
                                    <a class="btn btn-primary btn-sm" href="{{ route('usuarios.edit', $usuario) }}">✏️
                                        Actualizar</a>
                                </td>
                                <td class="text-center">
                                    <form action="{{ route('usuarios.destroy', $usuario->id) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="btn w-100 btn-sm btn-{{ $isActive ? 'outline-dark' : 'dark' }}">{{ $isActive ? '🔴 Inhabilitar' : '🟢 Habilitar' }}</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">No hay información que mostrar.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer d-flex justify-content-between align-items-center flex-column flex-md-row">
            <span class="text-muted mb-2 mb-md-0">Total: {{ $usuarios->total() }} usuarios</span>
            {{ $usuarios->links() }}
        </div>
    </div>
@stop

@section('css')
    {{-- Add here extra stylesheets --}}
    {{-- <link rel="stylesheet" href="/css/admin_custom.css"> --}}
@stop

@section('js')
    <!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"> -->
    <link href="https://getbootstrap.com/docs/5.3/assets/css/docs.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stop
