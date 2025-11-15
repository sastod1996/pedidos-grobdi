@extends('adminlte::page')

@section('title', 'Roles')


@section('content')
    <x-grobdi.layout.header-card
        title="Gestión de Roles"
        subtitle="Administra los roles del sistema"
    >
        <x-slot:actions>
            <x-grobdi.button href="{{ route('roles.create') }}" icon="fas fa-plus">
                Nuevo Rol
            </x-grobdi.button>
        </x-slot:actions>

        <x-slot:filter>
            <form method="GET" action="{{ route('roles.index') }}">
                <div class="row">
                    <div class="col-12 col-md-6 col-lg-8">
                        <label for="role_id">Filtrar por rol</label>
                        <select name="role_id" id="role_id" class="form-control">
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
                            <x-grobdi.button type="submit" icon="fas fa-search">
                                Filtrar
                            </x-grobdi.button>
                            <x-grobdi.button
                                href="{{ route('roles.index') }}"
                                variant="outline"
                                icon="fas fa-sync"
                            >
                                Limpiar
                            </x-grobdi.button>
                        </div>
                    </div>
                </div>
            </form>
        </x-slot:filter>
    </x-grobdi.layout.header-card>

    <x-grobdi.layout.table-card
        title="Lista de roles"
        tableClass="table-bordered table-striped table-hover mb-0"
    >
        <x-slot:actions>
            <span class="badge badge-primary text-md">{{ $roles->total() }} registros</span>
        </x-slot:actions>

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
                            <x-grobdi.button
                                variant="outline"
                                size="sm"
                                icon="fas fa-eye"
                                data-toggle="modal"
                                :data-target="'#role-permissions-' . $role->id"
                            >
                                Ver permisos
                            </x-grobdi.button>
                        @endif
                    </td>
                    <td class="text-center">
                        <div class="d-flex flex-column gap-2">
                            <x-grobdi.button
                                href="{{ route('roles.edit', $role) }}"
                                variant="warning"
                                size="sm"
                                icon="fas fa-pen"
                                class="w-100"
                            >
                                Editar
                            </x-grobdi.button>
                            <x-grobdi.button
                                href="{{ route('roles.permissions', $role) }}"
                                variant="info"
                                size="sm"
                                icon="fas fa-shield-alt"
                                class="w-100"
                            >
                                Permisos
                            </x-grobdi.button>
                            <form action="{{ route('roles.destroy', $role) }}" method="POST" class="d-inline">
                                @csrf @method('DELETE')
                                <x-grobdi.button
                                    type="submit"
                                    variant="danger"
                                    size="sm"
                                    icon="fas fa-trash"
                                    class="w-100"
                                    onclick="return confirm('¿Eliminar?')"
                                >
                                    Eliminar
                                </x-grobdi.button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                @include('empty-table', ['colspan' => 4, 'dataLength' => 0])
            @endforelse
        </tbody>

        <x-slot:footer>
            <div class="d-flex justify-content-end">
                {{ $roles->links() }}
            </div>
        </x-slot:footer>
    </x-grobdi.layout.table-card>

    @foreach ($roles as $role)
        @include('ajustes.roles.partials.permissions-modal', ['role' => $role])
    @endforeach
@stop

@section('css')
@stop

@section('js')

    <script></script>

@stop
