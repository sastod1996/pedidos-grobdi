@extends('adminlte::page')

@section('title', 'Vistas')

@section('css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        /* Personalización SweetAlert2 con paleta Grobdi */
        .swal2-popup {
            border-radius: 0.75rem !important;
            box-shadow: 0 20px 25px -5px rgba(15, 23, 42, 0.15) !important;
        }
        .swal2-title {
            color: #1f2937 !important;
            font-weight: 700 !important;
            font-size: 1.5rem !important;
        }
        .swal2-html-container {
            color: #0f172a !important;
            font-size: 1rem !important;
        }
        .swal2-confirm {
            background-color: #ef4444 !important;
            border-radius: 0.5rem !important;
            font-weight: 600 !important;
            padding: 0.625rem 1.5rem !important;
            font-size: 0.95rem !important;
            box-shadow: 0 2px 4px rgba(239, 68, 68, 0.2) !important;
            margin-right: 0.5rem !important;
        }
        .swal2-confirm:hover {
            background-color: #dc2626 !important;
            box-shadow: 0 4px 8px rgba(239, 68, 68, 0.3) !important;
        }
        .swal2-cancel {
            background-color: #475569 !important;
            border-radius: 0.5rem !important;
            font-weight: 600 !important;
            padding: 0.625rem 1.5rem !important;
            font-size: 0.95rem !important;
            box-shadow: 0 2px 4px rgba(71, 85, 105, 0.2) !important;
        }
        .swal2-cancel:hover {
            background-color: #334155 !important;
            box-shadow: 0 4px 8px rgba(71, 85, 105, 0.3) !important;
        }
        .swal2-icon.swal2-success {
            border-color: #10b981 !important;
            color: #10b981 !important;
        }
        .swal2-icon.swal2-success [class^='swal2-success-line'] {
            background-color: #10b981 !important;
        }
        .swal2-icon.swal2-success .swal2-success-ring {
            border-color: rgba(16, 185, 129, 0.3) !important;
        }
        .swal2-icon.swal2-error {
            border-color: #ef4444 !important;
            color: #ef4444 !important;
        }
        .swal2-icon.swal2-error [class^='swal2-x-mark-line'] {
            background-color: #ef4444 !important;
        }
        .swal2-icon.swal2-warning {
            border-color: #f59e0b !important;
            color: #f59e0b !important;
        }

        /* Botones de acción en la tabla */
        .btn-action {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            padding: 0.5rem 0.875rem;
            font-size: 0.875rem;
            font-weight: 600;
            border-radius: 0.375rem;
            border: none;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .btn-action i {
            font-size: 0.875rem;
        }

        .btn-edit {
            background-color: #f59e0b;
            color: white;
        }

        .btn-edit:hover {
            background-color: #d97706;
            transform: translateY(-1px);
            box-shadow: 0 4px 6px rgba(245, 158, 11, 0.3);
            color: white;
        }

        .btn-delete {
            background-color: #ef4444;
            color: white;
        }

        .btn-delete:hover {
            background-color: #dc2626;
            transform: translateY(-1px);
            box-shadow: 0 4px 6px rgba(239, 68, 68, 0.3);
        }

        @media (max-width: 768px) {
            .btn-action {
                font-size: 0.8rem;
                padding: 0.4rem 0.7rem;
            }
        }
    </style>
@stop

@section('content')
    <div class="grobdi-header">
        <div class="grobdi-title">
            <div>
                <h2>Gestión de Vistas</h2>
                <p>Administra las vistas del sistema</p>
            </div>
            <a href="{{ route('views.create') }}" class="btn">
                <i class="fas fa-plus"></i> Nueva Vista
            </a>
        </div>

        <div class="grobdi-filter">
            <form method="GET" action="{{ route('views.index') }}">
                <div class="row">
                    <div class="col-12 col-md-6 col-lg-8">
                        <label for="module_id">Filtrar por módulo</label>
                        <select name="module_id" id="module_id">
                            <option value="">Todos los módulos</option>
                            @foreach ($modules as $module)
                                <option value="{{ $module->id }}"
                                    {{ (string) ($selectedModule ?? '') === (string) $module->id ? 'selected' : '' }}>
                                    {{ $module->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 col-md-6 col-lg-4">
                        <div class="filter-actions">
                            <button type="submit">🔍 Filtrar</button>
                            <a href="{{ route('views.index') }}" class="btn btn-outline">♻️ Limpiar</a>
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
                    <th>Descripción</th>
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
                        <td>{{ $view->name }}</td>
                        <td class="text-secondary small">{{ $view->description ?? 'Sin descripción' }}</td>
                        <td>{{ $view->url }}</td>
                        <td>{{ $view->module->name }}</td>
                        <td class="text-center text-lg">
                            <span
                                class="badge badge-{{ $view->is_menu ? 'success' : 'secondary' }}">{{ $view->is_menu ? 'Si' : 'No' }}</span>
                        </td>
                        <td>
                            <div style="display: flex; gap: 0.5rem; justify-content: center; flex-wrap: wrap;">
                                <a href="{{ route('views.edit', $view) }}"
                                   class="btn-action btn-edit"
                                   title="Editar vista">
                                    <i class="fas fa-edit"></i> Editar
                                </a>
                                <button type="button"
                                        class="btn-action btn-delete"
                                        onclick="confirmDelete({{ $view->id }})"
                                        title="Eliminar vista">
                                    <i class="fas fa-trash-alt"></i> Eliminar
                                </button>
                                <form id="delete-form-{{ $view->id }}"
                                      action="{{ route('views.destroy', $view) }}"
                                      method="POST"
                                      style="display: none;">
                                    @csrf
                                    @method('DELETE')
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    @include('empty-table', ['colspan' => 6, 'dataLength' => 0])
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $views->links() }}
    </div>
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Función para confirmar eliminación con SweetAlert2
        function confirmDelete(viewId) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: "Esta acción eliminará la vista permanentemente",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: '<i class="fas fa-trash-alt"></i> Sí, eliminar',
                cancelButtonText: '<i class="fas fa-times"></i> Cancelar',
                reverseButtons: true,
                customClass: {
                    confirmButton: 'swal2-confirm',
                    cancelButton: 'swal2-cancel'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('delete-form-' + viewId).submit();
                }
            });
        }

        // Mostrar alerta de éxito si existe mensaje en sesión
        @if(session('success'))
            Swal.fire({
                icon: 'success',
                title: '¡Éxito!',
                text: '{{ session('success') }}',
                confirmButtonText: 'Aceptar',
                timer: 3000,
                timerProgressBar: true,
                showClass: {
                    popup: 'animate__animated animate__fadeInDown'
                },
                hideClass: {
                    popup: 'animate__animated animate__fadeOutUp'
                }
            });
        @endif

        // Mostrar alerta de error si existe mensaje en sesión
        @if(session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: '{{ session('error') }}',
                confirmButtonText: 'Aceptar'
            });
        @endif
    </script>
@stop
