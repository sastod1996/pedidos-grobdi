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
                    <button type="button" class="btn btn-danger btn-lg px-4" id="openConfigModal"
                        data-toggle="modal" data-target="#configuracionModal" data-bs-toggle="modal" data-bs-target="#configuracionModal">
                        Configurar meta
                    </button>
                    <!-- Abrir modal en lugar de navegar a create -->
                    <button type="button" class="btn btn-primary btn-lg px-4" data-bs-toggle="modal" data-bs-target="#createBonificacionModal">Nuevo mes</button>
                </div>
            </div>
        </div>
        <div class="card bonificaciones-filters-card shadow-sm border-0 mb-4">
            <div class="card-body">
                <form class="row g-3 align-items-end">
                    <div class="col-12 col-md-4 col-xl-3">
                        <label for="bonificacionMes" class="form-label text-muted text-uppercase small mb-1">Mes y año</label>
                       <input type="month" id="bonificacionMes" class="form-control form-control-lg">
                    </div>
                    <div class="col-12 col-md-4 col-xl-3">
                        <label for="bonificacionTipoMedico" class="form-label text-muted text-uppercase small mb-1">Tipo de
                            médico</label>
                        <select id="bonificacionTipoMedico" class="form-select form-select-lg">
                            <option value="" selected>Todos los tipos</option>
                            <option value="prescriptor">Prescriptor</option>
                            <option value="comprador">Comprador</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-4 col-xl-3">
                        <label class="form-label text-muted text-uppercase small mb-1">&nbsp;</label>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary btn-lg px-4">Aplicar filtros</button>
                            <button type="button" class="btn btn-outline-secondary btn-lg">Limpiar</button>
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
                <div class="text-white small mt-2 mt-md-0">
                    Última actualización: 12/10/2025 08:15 a. m.
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped mb-0 align-middle table-grobdi">
                        <thead class="table-light">
                            <tr>
                                <th scope="col">Meta</th>
                                <th scope="col">Tipo Médico</th>
                                {{-- <th scope="col">Avance</th> --}}
                                <th scope="col" class="text-center">Opciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="text-center">
                                <td>Octubre 2025</td>
                                <td>Prescriptor</td>
                                {{-- <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="progress flex-grow-1" style="height: 6px;">
                                            <div class="progress-bar bg-primary" role="progressbar" style="width: 82%;"
                                                aria-valuenow="82" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                        <span class="fw-semibold">82%</span>
                                    </div>
                                </td> --}}
                                <td class="text-center">
                                    <a href="/dev/bonificaciones/view" class="text-warning fw-semibold me-2" title="Ver" style="text-decoration: none;">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            <tr class="text-center">
                                <td>Octubre 2025</td>
                                <td>Comprador</td>
                                {{-- <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="progress flex-grow-1" style="height: 6px;">
                                            <div class="progress-bar bg-primary" role="progressbar" style="width: 70%;"
                                                aria-valuenow="70" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                        <span class="fw-semibold">70%</span>
                                    </div>
                                </td> --}}
                                <td class="text-center">
                                    <a href="/dev/bonificaciones/view" class="text-warning fw-semibold me-2" title="Ver" style="text-decoration: none;">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
							<tr class="text-center">
                                <td>Septiembre 2025</td>
                                <td>Prescriptor</td>
                                {{-- <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="progress flex-grow-1" style="height: 6px;">
                                            <div class="progress-bar bg-primary" role="progressbar" style="width: 82%;"
                                                aria-valuenow="82" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                        <span class="fw-semibold">82%</span>
                                    </div>
                                </td> --}}
                                <td class="text-center">
                                    <a href="/dev/bonificaciones/view" class="text-warning fw-semibold me-2" title="Ver" style="text-decoration: none;">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            <tr class="text-center">
                                <td>Septiembre 2025</td>
                                <td>Comprador</td>
                                {{-- <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="progress flex-grow-1" style="height: 6px;">
                                            <div class="progress-bar bg-primary" role="progressbar" style="width: 70%;"
                                                aria-valuenow="70" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                        <span class="fw-semibold">70%</span>
                                    </div>
                                </td> --}}
                                <td class="text-center">
                                    <a href="/dev/bonificaciones/view" class="text-warning fw-semibold me-2" title="Ver" style="text-decoration: none;">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
							<tr class="text-center">
                                <td>Agosto 2025</td>
                                <td>Prescriptor</td>
                                {{-- <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="progress flex-grow-1" style="height: 6px;">
                                            <div class="progress-bar bg-primary" role="progressbar" style="width: 82%;"
                                                aria-valuenow="82" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                        <span class="fw-semibold">82%</span>
                                    </div>
                                </td> --}}
                                <td class="text-center">
                                    <a href="/dev/bonificaciones/view" class="text-warning fw-semibold me-2" title="Ver" style="text-decoration: none;">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            <tr class="text-center">
                                <td>Agosto 2025</td>
                                <td>Comprador</td>
                                {{-- <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="progress flex-grow-1" style="height: 6px;">
                                            <div class="progress-bar bg-primary" role="progressbar" style="width: 70%;"
                                                aria-valuenow="70" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                        <span class="fw-semibold">70%</span>
                                    </div>
                                </td> --}}
                                <td class="text-center">
                                    <a href="/dev/bonificaciones/view" class="text-warning fw-semibold me-2" title="Ver" style="text-decoration: none;">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
							<tr class="text-center">
                                <td>Julio 2025</td>
                                <td>Prescriptor</td>
                                {{-- <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="progress flex-grow-1" style="height: 6px;">
                                            <div class="progress-bar bg-primary" role="progressbar" style="width: 82%;"
                                                aria-valuenow="82" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                        <span class="fw-semibold">82%</span>
                                    </div>
                                </td> --}}
                                <td class="text-center">
                                    <a href="/dev/bonificaciones/view" class="text-warning fw-semibold me-2" title="Ver" style="text-decoration: none;">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            <tr class="text-center">
                                <td>Julio 2025</td>
                                <td>Comprador</td>
                                {{-- <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="progress flex-grow-1" style="height: 6px;">
                                            <div class="progress-bar bg-primary" role="progressbar" style="width: 70%;"
                                                aria-valuenow="70" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                        <span class="fw-semibold">70%</span>
                                    </div>
                                </td> --}}
                                <td class="text-center">
                                    <a href="/dev/bonificaciones/view" class="text-warning fw-semibold me-2" title="Ver" style="text-decoration: none;">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
							<tr class="text-center">
                                <td>Junio 2025</td>
                                <td>Prescriptor</td>
                                {{-- <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="progress flex-grow-1" style="height: 6px;">
                                            <div class="progress-bar bg-primary" role="progressbar" style="width: 82%;"
                                                aria-valuenow="82" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                        <span class="fw-semibold">82%</span>
                                    </div>
                                </td> --}}
                                <td class="text-center">
                                    <a href="/dev/bonificaciones/view" class="text-warning fw-semibold me-2" title="Ver" style="text-decoration: none;">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            <tr class="text-center">
                                <td>Junio 2025</td>
                                <td>Comprador</td>
                                {{-- <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="progress flex-grow-1" style="height: 6px;">
                                            <div class="progress-bar bg-primary" role="progressbar" style="width: 70%;"
                                                aria-valuenow="70" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                        <span class="fw-semibold">70%</span>
                                    </div>
                                </td> --}}
                                <td class="text-center">
                                    <a href="/dev/bonificaciones/view" class="text-warning fw-semibold me-2" title="Ver" style="text-decoration: none;">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    {{-- Include modal partial for creating bonificaciones --}}
    @include('bonificaciones.partials.createModal')
    @include('bonificaciones.partials.configModal')
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
