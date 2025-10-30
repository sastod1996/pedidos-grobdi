@php
    $modalId = 'role-permissions-' . $role->id;
    $modules = $role->modules->sortBy('name');
    $viewsGrouped = $role->views
        ->sortBy(function ($view) {
            return strtolower($view->name ?? '');
        })
        ->groupBy(function ($view) {
            return optional($view->module)->name ?: 'Sin módulo';
        });
    $moduleGroups = collect();

    foreach ($modules as $module) {
        $moduleGroups->push([
            'name' => $module->name,
            'views' => $viewsGrouped->get($module->name, collect()),
            'isAssignedModule' => true,
        ]);
        $viewsGrouped->forget($module->name);
    }

    foreach ($viewsGrouped->sortKeys() as $name => $views) {
        $moduleGroups->push([
            'name' => $name,
            'views' => $views,
            'isAssignedModule' => false,
        ]);
    }

    $totalViews = $role->views->count();
@endphp

<div class="modal fade" id="{{ $modalId }}" tabindex="-1" role="dialog" aria-labelledby="{{ $modalId }}Label" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document" style="max-width: 95%;">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <div class="d-flex flex-column">
                    <h5 class="modal-title mb-1" id="{{ $modalId }}Label">
                        <span class="text-primary">🛡️</span> Permisos de <strong>{{ $role->name }}</strong>
                    </h5>
                    <small class="text-muted">Total de vistas asignadas: <strong>{{ $totalViews }}</strong></small>
                </div>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                @if ($moduleGroups->isEmpty())
                    <div class="alert alert-info mb-0">
                        <i class="fas fa-info-circle"></i> Este rol no tiene permisos asignados.
                    </div>
                @else
                    <div class="accordion" id="permissions-accordion-{{ $role->id }}">
                        @foreach ($moduleGroups as $index => $group)
                            @php
                                $collapseId = 'collapse-' . $role->id . '-' . $index;
                                $headingId = 'heading-' . $role->id . '-' . $index;
                            @endphp
                            <div class="card mb-2 shadow-sm">
                                <div class="card-header p-0" id="{{ $headingId }}">
                                    <button class="btn btn-link btn-block text-left d-flex justify-content-between align-items-center py-3 px-3"
                                            type="button"
                                            data-toggle="collapse"
                                            data-target="#{{ $collapseId }}"
                                            aria-expanded="{{ $loop->first ? 'true' : 'false' }}"
                                            aria-controls="{{ $collapseId }}"
                                            style="text-decoration: none; color: inherit;">
                                        <span class="d-flex align-items-center">
                                            <i class="fas fa-folder mr-2 text-primary"></i>
                                            <strong>{{ $group['name'] }}</strong>
                                        </span>
                                        <div class="d-flex align-items-center">
                                            <span class="badge badge-primary mr-2">{{ $group['views']->count() }} vistas</span>
                                            <i class="fas fa-chevron-down transition-icon"></i>
                                        </div>
                                    </button>
                                </div>
                                <div id="{{ $collapseId }}"
                                     class="collapse {{ $loop->first ? 'show' : '' }}"
                                     aria-labelledby="{{ $headingId }}"
                                     data-parent="#permissions-accordion-{{ $role->id }}">
                                    <div class="card-body bg-light">
                                        @if ($group['views']->isNotEmpty())
                                            <div class="row">
                                                @foreach ($group['views'] as $view)
                                                    <div class="col-12 col-sm-6 col-md-4 col-lg-3 mb-2">
                                                        <div class="d-flex align-items-start p-2 bg-white rounded border">
                                                            <i class="fas fa-check-circle text-success mr-2 mt-1" style="font-size: 0.875rem;"></i>
                                                            <span class="small">{{ $view->name }}</span>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @elseif ($group['isAssignedModule'])
                                            <div class="alert alert-info mb-0">
                                                <i class="fas fa-unlock-alt"></i> Acceso general al módulo completo.
                                            </div>
                                        @else
                                            <div class="alert alert-warning mb-0">
                                                <i class="fas fa-exclamation-triangle"></i> Sin vistas detalladas asignadas.
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times"></i> Cerrar
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    #{{ $modalId }} .btn-link:hover {
        background-color: rgba(0, 123, 255, 0.05);
    }
    #{{ $modalId }} .transition-icon {
        transition: transform 0.3s ease;
    }
    #{{ $modalId }} .btn-link[aria-expanded="true"] .transition-icon {
        transform: rotate(180deg);
    }
    #{{ $modalId }} .card {
        border-radius: 8px;
        overflow: hidden;
    }
</style>
