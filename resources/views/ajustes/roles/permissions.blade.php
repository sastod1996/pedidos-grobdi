@extends('adminlte::page')

@section('title', 'Asignar Permisos')

@section('content_header')
<h2>Asignar permisos a {{ $role->name }}</h2>
@stop

@section('content')
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <form action="{{ route('roles.updatePermissions', $role->id) }}" method="POST">
        @csrf
        @method('PUT')
        @php
            $selectedModules = collect(old('modules', $role->modules->pluck('id')->toArray()));
            $selectedViews = collect(old('views', $role->views->pluck('id')->toArray()));
        @endphp

        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <p class="mb-0 text-muted">Selecciona los módulos y vistas que formarán parte del rol. Usa los acordeones para explorar cada módulo.</p>
            </div>
        </div>

        <div class="accordion" id="modulesAccordion">
            @foreach($modules as $module)
                @php
                    $collapseId = 'module-collapse-' . $module->id;
                    $headingId = 'module-heading-' . $module->id;
                    $moduleChecked = $selectedModules->contains($module->id);
                    $selectedCount = $module->views->whereIn('id', $selectedViews)->count();
                @endphp
                <div class="card shadow-sm border-0 mb-3 overflow-hidden">
                    <div class="card-header bg-white border-0 p-0" id="{{ $headingId }}">
                        <h2 class="mb-0">
                            <button class="btn w-100 text-left d-flex justify-content-between align-items-center px-3 py-3 border-0 rounded-0" type="button" data-toggle="collapse" data-target="#{{ $collapseId }}" aria-expanded="{{ $loop->first ? 'true' : 'false' }}" aria-controls="{{ $collapseId }}" style="background-color: var(--grobdi-slate-150); color: var(--grobdi-text-strong);">
                                <span class="d-flex align-items-center">
                                    <span class="toggle-icon mr-3" data-open="▲" data-closed="▼">{{ $loop->first ? '▲' : '▼' }}</span>
                                    <span class="mr-2">📁</span>
                                    <span class="font-weight-bold mb-0">{{ $module->name }}</span>
                                </span>
                                <span class="d-flex flex-column flex-sm-row align-items-sm-center">
                                    <span class="badge badge-primary mb-1 mb-sm-0 mr-sm-2">{{ $module->views->count() }} vistas</span>
                                    <span class="badge {{ $selectedCount ? 'badge-success' : 'badge-secondary' }}">Seleccionadas {{ $selectedCount }}</span>
                                </span>
                            </button>
                        </h2>
                    </div>
                    <div id="{{ $collapseId }}" class="collapse {{ $loop->first ? 'show' : '' }}" aria-labelledby="{{ $headingId }}" data-parent="#modulesAccordion">
                        <div class="card-body px-3 py-3" style="background-color: var(--grobdi-slate-100);">
                            <div class="d-flex flex-column flex-sm-row align-items-sm-center mb-3">
                                <div class="custom-control custom-checkbox mr-sm-3 mb-2 mb-sm-0">
                                    <input type="checkbox" class="custom-control-input module-checkbox" id="module-{{ $module->id }}" name="modules[]" value="{{ $module->id }}" data-module-id="{{ $module->id }}" {{ $moduleChecked ? 'checked' : '' }}>
                                    <label class="custom-control-label font-weight-bold" for="module-{{ $module->id }}">Incluir módulo completo</label>
                                </div>
                                @if($module->views->isNotEmpty())
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input select-all-checkbox" id="select-all-{{ $module->id }}" data-module-id="{{ $module->id }}" {{ $selectedCount && $selectedCount === $module->views->count() ? 'checked' : '' }}>
                                        <label class="custom-control-label font-weight-bold" for="select-all-{{ $module->id }}">Seleccionar todas las vistas</label>
                                    </div>
                                @endif
                            </div>
                            <div class="row">
                                @forelse($module->views as $view)
                                    @php
                                        $viewChecked = $selectedViews->contains($view->id);
                                    @endphp
                                    <div class="col-12 col-md-6 col-xl-4 mb-2">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" class="custom-control-input view-checkbox" id="view-{{ $module->id }}-{{ $view->id }}" name="views[]" value="{{ $view->id }}" data-module-id="{{ $module->id }}" {{ $viewChecked ? 'checked' : '' }}>
                                            <label class="custom-control-label" for="view-{{ $module->id }}-{{ $view->id }}">
                                                <div class="d-flex flex-column">
                                                    <span class="mr-2">{{ $view->name }}</span>
                                                    {{-- Descripción en gris, visible completa y con wrap cuando sea larga --}}
                                                    <small class="text-muted mt-1">{{ $view->description ?? 'Sin descripción' }}</small>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                @empty
                                    <div class="col-12">
                                        <span class="text-muted">No hay vistas disponibles para este módulo.</span>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="d-flex justify-content-end mt-4">
            <button type="submit" class="btn btn-success">💾 Guardar permisos</button>
        </div>
    </form>
@endsection

@section('js')
    <script>
        $(function () {
            const $accordion = $('#modulesAccordion');

            function updateSelectAll(moduleId) {
                const moduleSelector = '[data-module-id="' + moduleId + '"]';
                const $views = $accordion.find('.view-checkbox' + moduleSelector);
                const $selectAll = $accordion.find('.select-all-checkbox' + moduleSelector);

                if (!$selectAll.length) {
                    return;
                }

                const total = $views.length;
                const checked = $views.filter(':checked').length;

                if (total === 0) {
                    $selectAll.prop({ checked: false, indeterminate: false });
                    return;
                }

                if (checked === 0) {
                    $selectAll.prop({ checked: false, indeterminate: false });
                } else if (checked === total) {
                    $selectAll.prop({ checked: true, indeterminate: false });
                } else {
                    $selectAll.prop({ checked: false, indeterminate: true });
                }
            }

            $accordion.on('show.bs.collapse', function (event) {
                $(event.target).prev('.card-header').find('.toggle-icon').text(function () {
                    return $(this).data('open');
                });
            });

            $accordion.on('hide.bs.collapse', function (event) {
                $(event.target).prev('.card-header').find('.toggle-icon').text(function () {
                    return $(this).data('closed');
                });
            });

            $accordion.on('change', '.select-all-checkbox', function () {
                const moduleId = $(this).data('module-id');
                const isChecked = $(this).is(':checked');
                const moduleSelector = '[data-module-id="' + moduleId + '"]';
                $accordion.find('.view-checkbox' + moduleSelector).prop('checked', isChecked);
                updateSelectAll(moduleId);
            });

            $accordion.on('change', '.view-checkbox', function () {
                updateSelectAll($(this).data('module-id'));
            });

            $accordion.find('.select-all-checkbox').each(function () {
                updateSelectAll($(this).data('module-id'));
            });

            // No se usan popovers ni tooltips aquí: la descripción se muestra inline en gris.
        });
    </script>
@endsection
