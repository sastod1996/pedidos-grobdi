@extends('adminlte::page')

@section('title', 'Nueva Vista')

@section('content')
    <div class="grobdi-header">
        <div class="grobdi-title">
            <div>
                <h2>Crear Nueva Vista</h2>
                <p>Completa el formulario para agregar una nueva vista al sistema</p>
            </div>
        </div>
    </div>

    <div class="card-grobdi">
        <div class="card-header-grobdi">
            <i class="fas fa-file-alt"></i> Información de la Vista
        </div>
        <div class="card-body-grobdi">
            @php
                $fontawesomeSuggestions = [
                    'fas fa-cog',
                    'fas fa-users',
                    'fas fa-chart-line',
                    'fas fa-clipboard-list',
                    'fas fa-map-marker-alt',
                    'fas fa-truck',
                    'fas fa-file-alt',
                    'fas fa-flask',
                    'fas fa-briefcase',
                    'fas fa-cash-register',
                    'fas fa-database',
                    'fas fa-laptop'
                ];
            @endphp

            @if ($errors->any())
                <div class="alert-grobdi alert-danger-grobdi mb-4">
                    <strong><i class="fas fa-exclamation-triangle"></i> Por favor corrige los siguientes errores:</strong>
                    <ul class="mb-0 mt-2">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('views.store') }}" method="POST" id="createViewForm">
                @csrf

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="grobdi-label">
                                <i class="fas fa-tag text-danger"></i> Nombre de la Vista <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   name="name"
                                   class="form-control grobdi-input"
                                   placeholder="Ej: Gestión de Usuarios"
                                   required
                                   value="{{ old('name') }}">
                            <small class="form-text text-muted">
                                <i class="fas fa-info-circle"></i> Nombre descriptivo de la vista
                            </small>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="grobdi-label">
                                <i class="fas fa-link text-danger"></i> Ruta (URL) <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   name="url"
                                   class="form-control grobdi-input"
                                   placeholder="Ej: /admin/usuarios"
                                   required
                                   value="{{ old('url') }}">
                            <small class="form-text text-muted">
                                <i class="fas fa-info-circle"></i> URL de acceso a la vista
                            </small>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="grobdi-label">
                        <i class="fas fa-align-left text-danger"></i> Descripción
                    </label>
                    <textarea name="description"
                              class="form-control grobdi-input"
                              rows="3"
                              placeholder="Describe brevemente la funcionalidad de esta vista...">{{ old('description') }}</textarea>
                    <small class="form-text text-muted">
                        <i class="fas fa-info-circle"></i> Información adicional sobre la vista (opcional)
                    </small>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="grobdi-label">
                                <i class="fas fa-cube text-danger"></i> Módulo <span class="text-danger">*</span>
                            </label>
                            <select name="module_id" class="form-control grobdi-input" required>
                                <option value="">Selecciona un módulo</option>
                                @foreach($modules as $module)
                                    <option value="{{ $module->id }}" {{ old('module_id') == $module->id ? 'selected' : '' }}>
                                        {{ $module->name }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">
                                <i class="fas fa-info-circle"></i> Módulo al que pertenece la vista
                            </small>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="grobdi-label">
                                <i class="fas fa-icons text-danger"></i> Icono (Font Awesome)
                            </label>
                            <input type="text"
                                   id="icon-input"
                                   name="icon"
                                   list="icon-options"
                                   class="form-control grobdi-input"
                                   placeholder="Ej: fas fa-user"
                                   value="{{ old('icon') }}">
                            <datalist id="icon-options">
                                @foreach ($fontawesomeSuggestions as $iconOption)
                                    <option value="{{ $iconOption }}"></option>
                                @endforeach
                            </datalist>
                            <div class="icon-picker-preview" id="icon-preview-wrapper">
                                <i id="icon-preview" class="{{ old('icon') ? old('icon') : 'fas fa-icons text-muted' }}"></i>
                                <span id="icon-preview-label">{{ old('icon') ? old('icon') : 'Sin icono seleccionado' }}</span>
                                <button type="button" class="btn btn-link btn-sm icon-clear-btn" id="icon-clear-button" style="display: {{ old('icon') ? 'inline-flex' : 'none' }};">Quitar</button>
                            </div>
                            <div class="icon-suggestions">
                                <span class="icon-suggestions-title">Iconos sugeridos</span>
                                <div class="icon-suggestion-list">
                                    @foreach ($fontawesomeSuggestions as $iconOption)
                                        <button type="button" class="icon-chip" data-icon-pick="{{ $iconOption }}">
                                            <i class="{{ $iconOption }}"></i>
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                            <small class="form-text text-muted">
                                <i class="fas fa-info-circle"></i> Usa clases Font Awesome como <code>fas fa-home</code>. Campo opcional.
                            </small>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="grobdi-label">
                                <i class="fas fa-bars text-danger"></i> Visibilidad en Menú
                            </label>
                            <div class="grobdi-switch-container">
                                <input type="hidden" name="is_menu" value="0">
                                <label class="grobdi-switch">
                                    <input type="checkbox"
                                           name="is_menu"
                                           value="1"
                                           {{ old('is_menu', true) ? 'checked' : '' }}>
                                    <span class="switch-slider"></span>
                                    <span class="switch-label">Mostrar esta vista en el menú de navegación</span>
                                </label>
                            </div>
                            <small class="form-text text-muted">
                                <i class="fas fa-info-circle"></i> Activa si deseas que aparezca en el menú principal
                            </small>
                        </div>
                    </div>
                </div>

                <hr style="border-top: 1px solid #e2e8f0; margin: 2rem 0;">

                <div class="form-actions" style="display: flex; gap: 0.75rem; justify-content: flex-end;">
                    <a href="{{ route('views.index') }}" class="btn btn-grobdi btn-outline-grobdi">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                    <button type="submit" class="btn btn-grobdi btn-primary-grobdi">
                        <i class="fas fa-save"></i> Guardar Vista
                    </button>
                </div>
            </form>
        </div>
    </div>
@stop

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
        }
        .swal2-confirm:hover {
            background-color: #dc2626 !important;
            box-shadow: 0 4px 8px rgba(239, 68, 68, 0.3) !important;
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

        /* Icon picker */
        .icon-picker-preview {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-top: 0.75rem;
            color: #475569;
        }
        .icon-picker-preview i {
            font-size: 1.5rem;
            min-width: 1.5rem;
            text-align: center;
        }
        .icon-clear-btn {
            padding: 0;
            font-size: 0.8rem;
        }
        .icon-suggestions {
            margin-top: 0.75rem;
        }
        .icon-suggestions-title {
            display: block;
            font-size: 0.8rem;
            font-weight: 600;
            color: #64748b;
            margin-bottom: 0.25rem;
        }
        .icon-suggestion-list {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }
        .icon-chip {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2.25rem;
            height: 2.25rem;
            border-radius: 9999px;
            border: 1px solid #e2e8f0;
            background-color: #f8fafc;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .icon-chip:hover {
            background-color: #e2e8f0;
            border-color: #cbd5f5;
            transform: translateY(-1px);
        }
        .icon-chip i {
            font-size: 1.1rem;
            color: #1f2937;
        }
    </style>
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const iconInput = document.getElementById('icon-input');
            if (!iconInput) {
                return;
            }

            const previewIcon = document.getElementById('icon-preview');
            const previewLabel = document.getElementById('icon-preview-label');
            const clearButton = document.getElementById('icon-clear-button');
            const suggestionButtons = document.querySelectorAll('[data-icon-pick]');
            const defaultIconClass = 'fas fa-icons text-muted';
            const defaultLabel = 'Sin icono seleccionado';

            const updatePreview = (value) => {
                const className = value || '';
                if (previewIcon) {
                    previewIcon.className = className || defaultIconClass;
                }
                if (previewLabel) {
                    previewLabel.textContent = className || defaultLabel;
                }
                if (clearButton) {
                    clearButton.style.display = className ? 'inline-flex' : 'none';
                }
            };

            updatePreview(iconInput.value.trim());

            iconInput.addEventListener('input', (event) => {
                updatePreview(event.target.value.trim());
            });

            suggestionButtons.forEach((button) => {
                button.addEventListener('click', () => {
                    const value = button.getAttribute('data-icon-pick');
                    iconInput.value = value;
                    iconInput.dispatchEvent(new Event('input'));
                    iconInput.focus();
                });
            });

            if (clearButton) {
                clearButton.addEventListener('click', () => {
                    iconInput.value = '';
                    iconInput.dispatchEvent(new Event('input'));
                    iconInput.focus();
                });
            }
        });
    </script>
@stop
