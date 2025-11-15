@extends('adminlte::page')

@section('title', 'Usuarios')

@section('content')
    <x-grobdi.layout.header-card
        title="Lista de usuarios"
        subtitle="Gestiona altas, roles y zonas asignadas"
    >
        <x-slot:actions>
            <x-grobdi.button href="{{ route('usuarios.create') }}" icon="fas fa-plus">
                Crear Usuario
            </x-grobdi.button>
        </x-slot:actions>
    </x-grobdi.layout.header-card>

    @session('success')
        <div class="alert alert-success" role="alert"> {{ $value }} </div>
    @endsession

    <x-grobdi.layout.table-card
        title="Usuarios"
        tableClass="table-bordered table-striped table-hover mb-0"
    >
        <x-slot:actions>
            <span class="badge badge-primary text-md">Total: {{ $usuarios->total() }}</span>
        </x-slot:actions>

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
                            <span class="badge badge-info {{ count($usuario->zones) > 1 ? 'mr-1 mb-1 text-xs' : 'text-sm' }}">{{ $zona->name }}</span>
                        @empty
                            <span class="text-muted">Sin zonas asignadas</span>
                        @endforelse
                    </td>
                    <td class="text-center">
                        <span class="badge badge-{{ $isActive ? 'success' : 'secondary' }}">{{ $isActive ? 'Activo' : 'Inactivo' }}</span>
                    </td>
                    <td class="text-center">
                        <x-grobdi.button
                            href="{{ route('usuarios.edit', $usuario) }}"
                            variant="primary"
                            size="sm"
                            icon="fas fa-pen"
                        >
                            Actualizar
                        </x-grobdi.button>
                    </td>
                    <td class="text-center">
                        <form action="{{ route('usuarios.destroy', $usuario->id) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <x-grobdi.button
                                type="submit"
                                variant="{{ $isActive ? 'outline' : 'secondary' }}"
                                size="sm"
                                :full="true"
                            >
                                {{ $isActive ? '🔴 Inhabilitar' : '🟢 Habilitar' }}
                            </x-grobdi.button>
                        </form>
                    </td>
                </tr>
            @empty
                @include('empty-table', ['colspan' => 7, 'dataLength' => 0])
            @endforelse
        </tbody>

        <x-slot:footer>
            <div class="d-flex justify-content-between align-items-center flex-column flex-md-row gap-2">
                <span class="text-muted">Total: {{ $usuarios->total() }} usuarios</span>
                {{ $usuarios->links() }}
            </div>
        </x-slot:footer>
    </x-grobdi.layout.table-card>
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
