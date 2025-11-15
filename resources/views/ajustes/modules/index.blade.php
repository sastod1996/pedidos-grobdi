@extends('adminlte::page')

@section('title', 'Módulos')

@section('content')
    <x-grobdi.layout.header-card
        title="🧩 Gestión de Módulos"
        subtitle="Administra los módulos del sistema"
    >
        <x-slot:actions>
            <x-grobdi.button href="{{ route('modules.create') }}" icon="fas fa-plus">
                Nuevo Módulo
            </x-grobdi.button>
        </x-slot:actions>
    </x-grobdi.layout.header-card>

    <x-grobdi.layout.table-card
        title="Listado de módulos"
        tableClass="table-bordered table-striped table-hover mb-0"
    >
        <x-slot:actions>
            <span class="badge badge-primary text-md">{{ $modules->total() }} registros</span>
        </x-slot:actions>

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
                            <x-grobdi.button
                                href="{{ route('modules.edit', $module) }}"
                                variant="warning"
                                size="sm"
                                class="mb-1 mb-xl-0"
                                icon="fas fa-pen"
                            >
                                Editar
                            </x-grobdi.button>
                        @endcan
                        @can('modules.delete')
                            <form action="{{ route('modules.destroy', $module) }}" method="POST" class="d-inline">
                                @csrf @method('DELETE')
                                <x-grobdi.button
                                    type="submit"
                                    variant="danger"
                                    size="sm"
                                    icon="fas fa-trash"
                                    onclick="return confirm('¿Eliminar este módulo?')"
                                >
                                    Eliminar
                                </x-grobdi.button>
                            </form>
                        @endcan
                    </td>
                </tr>
            @empty
                @include('empty-table', ['colspan' => 4, 'dataLength' => 0])
            @endforelse
        </tbody>

        <x-slot:footer>
            <div class="d-flex justify-content-end">
                {{ $modules->links() }}
            </div>
        </x-slot:footer>
    </x-grobdi.layout.table-card>
@stop
