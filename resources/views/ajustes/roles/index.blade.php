@extends('adminlte::page')

@section('title', 'Roles')


@section('content')
 <div class="grobdi-header">
    <div class="grobdi-title">
        <div>
            <h2>Gestión de Roles</h2>
            <p>Administra los roles del sistema</p>
        </div>
        <a href="{{ route('roles.create') }}" class="btn">
            <i class="fas fa-plus"></i> Nuevo Rol
        </a>
    </div>

    <div class="grobdi-filter">
        <form method="GET" action="{{ route('roles.index') }}">
            <div class="row">
                <div class="col-12 col-md-6 col-lg-8">
                    <label for="role_id">Filtrar por rol</label>
                    <select name="role_id" id="role_id">
                        <option value="">Todos los roles</option>
                        @foreach ($roleOptions as $roleOption)
                            <option value="{{ $roleOption->id }}"
                                {{ (string) ($selectedRole ?? '') === (string) $roleOption->id ? 'selected' : '' }}>
                                {{ $roleOption->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-md-6 col-lg-4">
                    <div class="filter-actions">
                        <button type="submit">🔍 Filtrar</button>
                        <a href="{{ route('roles.index') }}" class="btn btn-outline">♻️ Limpiar</a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

    <div class="card shadow-sm">
        <div class="card-header">
            <div class="row">
                <div class="col">
                    <span class="fw-bold">Lista de roles</span>
                </div>
                <div class="col text-right">
                    <span class="badge badge-primary text-md">{{ $roles->total() }} registros</span>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover table-grobdi mb-0">
                    <thead>
                        <tr>
                            <th>Rol</th>
                            <th>Descripción</th>
                            <th>Permisos</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($roles as $role)
                            <tr>
                                <td class="font-weight-bold">{{ $role->name }}</td>
                                <td>{{ $role->description }}</td>
                                <td class="text-center">
                                    @if ($role->views->isEmpty() && $role->modules->isEmpty())
                                        <span class="text-muted">Sin permisos asignados</span>
                                    @else
                                        <button type="button" class="btn btn-sm btn-outline-info fw-bold"
                                            data-toggle="modal" data-target="#role-permissions-{{ $role->id }}">
                                            👁️ Ver permisos
                                        </button>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('roles.edit', $role) }}" class="btn btn-sm btn-warning w-75">✏️
                                        Editar</a>
                                    <a href="{{ route('roles.permissions', $role) }}"
                                        class="btn btn-sm btn-info my-2 w-75">🛡️
                                        Permisos</a>
                                    <form action="{{ route('roles.destroy', $role) }}" method="POST" class="d-inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger w-75"
                                            onclick="return confirm('¿Eliminar?')">🗑️ Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            @include('empty-table', ['colspan' => 4, 'dataLength' => 0])
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer d-flex justify-content-end">
            {{ $roles->links() }}
        </div>
    </div>

    @foreach ($roles as $role)
        @include('ajustes.roles.partials.permissions-modal', ['role' => $role])
    @endforeach
@stop

@section('css')
@stop

@section('js')

    <script></script>

@stop
