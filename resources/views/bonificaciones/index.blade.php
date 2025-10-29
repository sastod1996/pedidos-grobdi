@extends('adminlte::page')

@section('title', 'Módulo de Bonificaciones')


@section('content')
    <div class="bonificaciones-wrapper">
        <div class="card bonificaciones-hero-card shadow-sm border-0 mb-4">
            <div
                class="card-body d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
                <div>
                    <h1 class="h3 text-dark mb-1">Módulo de Bonificaciones</h1>
                    <p class="text-muted mb-0">Visualiza metas mensuales, configura porcentajes y revisa el avance de tu
                        equipo de visitadoras.</p>
                </div>
                <div class="bonificaciones-action-group d-flex gap-2">
                    <button type="button" class="btn btn-danger btn-lg px-4" id="openConfigModal" data-toggle="modal"
                        data-target="#configuracionModal">
                        Configurar meta
                    </button>
                    <!-- Abrir modal en lugar de navegar a create -->
                    <button type="button" class="btn btn-primary btn-lg px-4" data-bs-toggle="modal"
                        data-bs-target="#createBonificacionModal">Nuevo mes</button>
                </div>
            </div>
        </div>
        <div class="card bonificaciones-filters-card shadow-sm border-0 mb-4">
            <div class="card-body">
                <form id="filtersForm" class="row g-3 align-items-end" method="GET" action="{{ route('bonificaciones.index') }}">
                    <div class="col-12 col-md-4 col-xl-3">
                        <label for="bonificacionMes" class="form-label text-muted text-uppercase small mb-1">Mes y
                            año</label>
                        <input type="month" id="bonificacionMes" name="month" class="form-control form-control-lg" value="{{ request('month') }}">
                    </div>
                    <div class="col-12 col-md-4 col-xl-3">
                        <label for="bonificacionTipoMedico" class="form-label text-muted text-uppercase small mb-1">Tipo de
                            médico</label>
                        <select id="bonificacionTipoMedico" name="tipo_medico" class="form-select form-select-lg">
                            <option value="" {{ request('tipo_medico') === null || request('tipo_medico') === '' ? 'selected' : '' }}>Todos los tipos</option>
                            <option value="prescriptor" {{ request('tipo_medico') === 'prescriptor' ? 'selected' : '' }}>Prescriptor</option>
                            <option value="comprador" {{ request('tipo_medico') === 'comprador' ? 'selected' : '' }}>Comprador</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-4 col-xl-3">
                        <label class="form-label text-muted text-uppercase small mb-1">&nbsp;</label>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary btn-lg px-4">Aplicar filtros</button>
                            <button type="button" id="clearFiltersBtn" class="btn btn-outline-secondary btn-lg">Limpiar</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        {{-- <div class="row gy-3 mb-4">
            <div class="col-12 col-md-6 col-xl-3">
                <div class="bonificaciones-card bg-white shadow-sm d-flex align-items-center gap-3">
                    <div class="bonificaciones-icon bg-primary text-white">
                        <i class="fas fa-bullseye"></i>
                    </div>
                    <div>
                        <p class="text-muted text-uppercase small mb-1">Metas activas</p>
                        <h4 class="mb-0 fw-semibold text-dark">3</h4>
                        <span class="small text-muted">Meses abiertos este trimestre</span>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6 col-xl-3">
                <div class="bonificaciones-card bg-white shadow-sm d-flex align-items-center gap-3">
                    <div class="bonificaciones-icon bg-success text-white">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div>
                        <p class="text-muted text-uppercase small mb-1">Avance promedio</p>
                        <h4 class="mb-0 fw-semibold text-success">89%</h4>
                        <span class="small text-muted">Promedio ponderado</span>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6 col-xl-3">
                <div class="bonificaciones-card bg-white shadow-sm d-flex align-items-center gap-3">
                    <div class="bonificaciones-icon bg-info text-white">
                        <i class="fas fa-wallet"></i>
                    </div>
                    <div>
                        <p class="text-muted text-uppercase small mb-1">Bonificación proyectada</p>
                        <h4 class="mb-0 fw-semibold text-info">89%</h4>
                        <span class="small text-muted">Sumatoria estimada</span>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6 col-xl-3">
                <div class="bonificaciones-card bg-white shadow-sm d-flex align-items-center gap-3">
                    <div class="bonificaciones-icon bg-warning text-dark">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div>
                        <p class="text-muted text-uppercase small mb-1">Alertas</p>
                        <h4 class="mb-0 fw-semibold text-warning">3</h4>
                        <span class="small text-muted">Metas en riesgo</span>
                    </div>
                </div>
            </div>
        </div> --}}

        <div class="card shadow-sm border-0">
            <div
                class="card-header bg-primary text-white d-flex flex-column flex-md-row justify-content-between align-items-md-center">
                <div>
                    <h2 class="h5 mb-1">Bonificación por mes</h2>
                    <h6 class="small mb-0 text-white">Selecciona una meta para revisar visitadoras, avances y opciones de
                        cierre.</h6>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped mb-0 align-middle table-grobdi">
                        <thead class="table-light">
                            <tr>
                                <th scope="col">Mes y año</th>
                                <th scope="col">Tipo Médico</th>
                                {{-- <th scope="col">Avance</th> --}}
                                <th scope="col" class="text-center">Opciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($listOfMetas as $meta)
                                <tr class="text-center">
                                    <td>
                                        @php $date = $meta['date']; @endphp
                                        @if (is_array($date))
                                            @if (isset($date['type']) && $date['type'] === 'month')
                                                {{ \Carbon\Carbon::createFromDate($date['year'], $date['value'], 1)->locale('es')->translatedFormat('F Y') }}
                                            @else
                                                {{ \Carbon\Carbon::parse($date['start_date'])->format('d/m/Y') }} -
                                                {{ \Carbon\Carbon::parse($date['end_date'])->format('d/m/Y') }}
                                            @endif
                                        @else
                                            {{ $date }}
                                        @endif
                                    </td>
                                    <td>{{ ucfirst($meta['tipo_medico'] ?? '') }}</td>
                                    <td class="text-center">
                                        <a href="{{ route('visitadoras.metas.show', $meta['id']) }}"
                                            class="text-warning fw-semibold me-2" title="Ver"
                                            style="text-decoration: none;">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr class="text-center">
                                    <td colspan="3">No hay metas registradas.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-3 d-flex justify-content-end">
                    {!! $listOfMetas->links() !!}
                </div>
            </div>
        </div>
    </div>
    {{-- Include modal partials for creating and configuring bonificaciones --}}
    @include('bonificaciones.partials.createModal')
    @include('bonificaciones.partials.configModal')

    {{-- Small script to ensure the "Configurar meta" button opens the modal (Bootstrap 5 or fallback to jQuery) --}}
    @section('js')
        @parent
        <script>
            (function() {
                var btn = document.getElementById('openConfigModal');
                if (!btn) return;

                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    // Try Bootstrap 5 modal API
                    try {
                        if (window.bootstrap && window.bootstrap.Modal) {
                            var el = document.getElementById('configuracionModal');
                            if (el) {
                                var modal = new window.bootstrap.Modal(el);
                                modal.show();
                                return;
                            }
                        }
                    } catch (err) {
                        // continue to jQuery fallback
                    }

                    // jQuery fallback (Bootstrap 4)
                    if (window.jQuery) {
                        $('#configuracionModal').modal('show');
                    }
                });
                    // Clear filters button: redirect to base index route (removes query string)
                    var clearBtn = document.getElementById('clearFiltersBtn');
                    if (clearBtn) {
                        clearBtn.addEventListener('click', function(e) {
                            e.preventDefault();
                            window.location.href = "{{ route('bonificaciones.index') }}";
                        });
                    }
            })();
        </script>
    @endsection
@stop

@section('css')

    <style>
        .bonificaciones-hero-card {
            background-color: #f8efef;
            border-radius: 20px;
        }

        .bonificaciones-hero-card .card-body {
            padding: 1.75rem;
        }

        .bonificaciones-wrapper {
            background-color: #f7f7fb;
            border-radius: 16px;
            padding: 1.5rem;
        }

        .bonificaciones-filters-card {
            border-radius: 20px;
            background-color: #ffffff;
        }

        .bonificaciones-action-group {
            margin-left: auto;
            margin-top: 1.25rem;
        }

        .bonificaciones-card {
            border-radius: 18px;
            padding: 1.25rem;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .bonificaciones-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
        }

        .bonificaciones-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 56px;
            height: 56px;
            border-radius: 14px;
            font-size: 1.5rem;
        }

        .card-header {
            border-top-left-radius: 20px !important;
            border-top-right-radius: 20px !important;
        }

        .card {
            border-radius: 20px;
        }

        @media (max-width: 767.98px) {
            .bonificaciones-wrapper {
                padding: 1rem;
            }
        }
    </style>
@stop

@section('js')
    {{-- No scripts required for la vista en duro --}}
@stop
