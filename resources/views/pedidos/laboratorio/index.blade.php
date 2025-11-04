@extends('adminlte::page')

@section('title', 'Dashboard')

@section('adminlte_css')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@stop

@php
    $user = auth()->user();
    $canDownloadWord = $user?->can('pedidoslaboratorio.downloadWord');
    $canUpdatePedido = $user?->can('pedidoslaboratorio.update');
    $canShowPedido = $user?->can('pedidoslaboratorio.show');
    $cambioMasivoUrl = $canUpdatePedido ? route('pedidoslaboratorio.cambioMasivo') : null;
    $detalleUrlTemplate = $canShowPedido ? route('pedidoslaboratorio.show', ['pedidoslaboratorio' => '__ID__']) : null;
@endphp

@section('content')
    @can('pedidoslaboratorio.index')
        <div class="grobdi-header">
            <div class="grobdi-title">
                <div>
                    <h2>🧪 Laboratorio</h2>
                    <p>Gestión de pedidos para el laboratorio</p>
                </div>
                {{-- Acción: mostrar botón de descarga si tiene permiso (se mantiene funcionalidad) --}}
                <div>
                    @can('pedidoslaboratorio.downloadWord')
                        @if (request()->get('fecha'))
                            <a class="btn"
                                href="{{ route('pedidoslaboratorio.downloadWord', ['fecha' => request()->get('fecha'), 'turno' => $turno]) }}"><i
                                    class="fa fa-file-word"></i> Descargar Word</a>
                        @else
                            <a class="btn"
                                href="{{ route('pedidoslaboratorio.downloadWord', ['fecha' => date('Y-m-d'), 'turno' => $turno]) }}"><i
                                    class="fa fa-file-word"></i> Descargar Word</a>
                        @endif
                    @endcan
                </div>
            </div>

            <div class="grobdi-filter">
                <form method="GET" action="{{ route('pedidoslaboratorio.index') }}">
                    <div class="row align-items-end">
                        <div class="col-12 col-md-4 col-lg-3 mb-3 mb-md-0">
                            <label for="fecha">Fecha Entrega</label>
                            <input class="form-control" type="date" name="fecha" id="fecha"
                                value="{{ request()->fecha }}" required>
                        </div>

                        <div class="col-12 col-md-3 col-lg-2 mb-3 mb-md-0">
                            <label for="turno">Turno</label>
                            <select onchange="this.form.submit()" class="form-control" aria-label="Default select example"
                                name="turno" id="turno">
                                <option value="">-- Seleccionar --</option>
                                <option {{ (string) $turno === '0' ? 'selected' : '' }} value="0">Mañana</option>
                                <option {{ (string) $turno === '1' ? 'selected' : '' }} value="1">Tarde</option>
                            </select>
                        </div>

                        <div class="col-12 col-md-3 col-lg-3 mb-3 mb-md-0">
                            <label for="zona_id">Zona</label>
                            <select class="form-control" name="zona_id" id="zona_id">
                                <option value="">Todas las zonas</option>
                                @foreach ($zonas as $zona)
                                    <option value="{{ $zona->id }}" {{ request()->zona_id == $zona->id ? 'selected' : '' }}>
                                        {{ $zona->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12 col-md-2 col-lg-4">
                            <div class="filter-actions">
                                <button type="submit" class="btn">🔍 Filtrar</button>
                                <a href="{{ route('pedidoslaboratorio.index') }}" class="btn btn-outline">♻️ Limpiar</a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="card mt-2">
            <h2 class="card-header">Pedidos</h2>
            <div class="card-body">
                {{-- Filters moved to grobdi-header .grobdi-filter --}}
                @session('success')
                    <div class="alert alert-success" role="alert"> {{ $value }} </div>
                @endsession
                <!-- Botones de acción masiva -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <button type="button" class="btn btn-success" id="btnCambioMasivo" disabled>
                            <i class="fa fa-check-square"></i> Marcar como Preparado (<span id="contadorSeleccionados">0</span>
                            seleccionados)
                        </button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-grobdi" id="tablaPedidos">
                        <thead>
                            <tr>
                                <th>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="selectAll">
                                        <label class="form-check-label" for="selectAll">Todo</label>
                                    </div>
                                </th>
                                <th>Nro</th>
                                <th>Nro pedido</th>
                                <th>Cliente</th>
                                <th>Turno</th>
                                <th>Zona</th>
                                <th>Estado Producción</th>
                                @if ($canUpdatePedido)
                                    <th>Actualizar estado</th>
                                @endif
                                <th>Opciones</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($pedidos as $pedido)
                                <tr>
                                    <td>
                                        <div class="form-check">
                                            <input class="form-check-input border border-info pedido-checkbox" type="checkbox"
                                                value="{{ $pedido->id }}" id="checkbox{{ $pedido->id }}"
                                                {{ $pedido->productionStatus === 1 ? 'disabled' : '' }}>
                                            <label class="form-check-label" for="checkbox{{ $pedido->id }}"></label>
                                        </div>
                                    </td>
                                    <td>{{ $pedido->nroOrder }}</td>
                                    <td>{{ $pedido->orderId }}</td>
                                    <td>{{ $pedido->customerName }}</td>
                                    <td>{{ $pedido->turno === 0 ? 'Mañana' : 'Tarde' }}</td>
                                    <td>{{ $pedido->zone->name }} </td>

                                    <td>
                                        @if ($pedido->productionStatus === 1)
                                            <span class="badge bg-success">Preparado</span>
                                        @elseif ($pedido->productionStatus === 2)
                                            <span class="badge bg-info">Reprogramado</span>
                                            @if ($pedido->fecha_reprogramacion)
                                                <br><small>{{ \Carbon\Carbon::parse($pedido->fecha_reprogramacion)->format('d/m/Y') }}</small>
                                            @endif
                                        @else
                                            <span class="badge bg-warning">Pendiente</span>
                                        @endif
                                    </td>
                                    @if ($canUpdatePedido)
                                        <td>
                                            @if ($pedido->productionStatus === 0)
                                                <button type="button" class="btn btn-outline-primary btn-sm"
                                                    data-toggle="modal" data-target="#estadoModal{{ $pedido->id }}">
                                                    <i class="fa fa-edit"></i> Actualizar
                                                </button>
                                            @elseif ($pedido->productionStatus === 2)
                                                <button type="button" class="btn btn-outline-secondary btn-sm"
                                                    data-toggle="modal" data-target="#estadoModal{{ $pedido->id }}">
                                                    <i class="fa fa-eye"></i> Ver/Editar
                                                </button>
                                            @else
                                                <button type="button" class="btn btn-outline-secondary btn-sm" disabled>
                                                    <i class="fa fa-eye"></i> Ver
                                                </button>
                                            @endif
                                        </td>
                                    @endif
                                    <td>
                                        @php
                                            // Normalizar diferentes formatos de "receta" a array de URLs
                                            $images = [];
                                            try {
                                                if (is_array($pedido->receta)) {
                                                    $images = $pedido->receta;
                                                } else {
                                                    $decoded = json_decode($pedido->receta, true);
                                                    if (is_array($decoded)) {
                                                        $images = $decoded;
                                                    } elseif (
                                                        !empty($pedido->receta) &&
                                                        strpos($pedido->receta, ',') !== false
                                                    ) {
                                                        $images = array_map('trim', explode(',', $pedido->receta));
                                                    } elseif (!empty($pedido->receta)) {
                                                        $images = [$pedido->receta];
                                                    }
                                                }
                                            } catch (\Throwable $e) {
                                                $images = [$pedido->receta];
                                            }

                                            // Convertir rutas relativas a URLs con asset(), mantener http(s) tal cual
                                            $images = array_values(
                                                array_filter(
                                                    array_map(function ($img) {
                                                        if (empty($img)) {
                                                            return null;
                                                        }
                                                        $img = trim($img);
                                                        if (preg_match('/^https?:\/\//i', $img)) {
                                                            return $img;
                                                        }
                                                        return asset($img);
                                                    }, $images),
                                                ),
                                            );
                                        @endphp

                                        @if (!empty($images))
                                            @php
                                                $delivery = '';
                                                $reprogram = '';
                                                try {
                                                    if (!empty($pedido->deliveryDate)) {
                                                        $delivery = \Carbon\Carbon::parse(
                                                            $pedido->deliveryDate,
                                                        )->format('d/m/Y');
                                                    }
                                                    if (!empty($pedido->fecha_reprogramacion)) {
                                                        $reprogram = \Carbon\Carbon::parse(
                                                            $pedido->fecha_reprogramacion,
                                                        )->format('d/m/Y');
                                                    }
                                                } catch (\Throwable $e) {
                                                    $delivery = $pedido->deliveryDate ?? '';
                                                    $reprogram = $pedido->fecha_reprogramacion ?? '';
                                                }
                                            @endphp
                                            <button type="button" class="btn btn-primary btn-sm btn-open-images"
                                                data-images='@json($images)' data-order="{{ $pedido->orderId }}"
                                                data-delivery="{{ $delivery }}"
                                                data-fecha-reprogramacion="{{ $reprogram }}">
                                                <i class="fa fa-file-image"></i> Imagen
                                            </button>
                                        @else
                                            <span class="badge bg-danger">Sin imagen</span>
                                        @endif

                                        <!-- Modal para actualizar estado -->
                                        @if ($canUpdatePedido)
                                            <div class="modal fade" id="estadoModal{{ $pedido->id }}" tabindex="-1"
                                                role="dialog" aria-labelledby="estadoModalLabel{{ $pedido->id }}"
                                                aria-hidden="true">
                                                <div class="modal-dialog" role="document">
                                                    <div class="modal-content">
                                                        <form action="{{ route('pedidoslaboratorio.update', $pedido->id) }}"
                                                            method="POST">
                                                            @csrf
                                                            @method('PUT')
                                                            <div class="modal-header">
                                                                <h5 class="modal-title"
                                                                    id="estadoModalLabel{{ $pedido->id }}">Actualizar Estado
                                                                    - Pedido: {{ $pedido->orderId }}</h5>
                                                                <button type="button" class="close" data-dismiss="modal"
                                                                    aria-label="Cerrar">
                                                                    <span aria-hidden="true">&times;</span>
                                                                </button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <div class="form-group">
                                                                    <label
                                                                        for="productionStatus{{ $pedido->id }}">Estado:</label>
                                                                    <select class="form-control" name="productionStatus"
                                                                        id="productionStatus{{ $pedido->id }}" required
                                                                        {{ $pedido->productionStatus === 1 ? 'disabled' : '' }}>
                                                                        <option value="0"
                                                                            {{ $pedido->productionStatus === 0 ? 'selected' : '' }}>
                                                                            Pendiente</option>
                                                                        <option value="1"
                                                                            {{ $pedido->productionStatus === 1 ? 'selected' : '' }}>
                                                                            Preparado</option>
                                                                        <option value="2"
                                                                            {{ $pedido->productionStatus === 2 ? 'selected' : '' }}>
                                                                            Reprogramado</option>
                                                                    </select>
                                                                </div>

                                                                <div class="form-group fecha-reprogramacion-group{{ $pedido->id }}"
                                                                    style="display: {{ $pedido->productionStatus === 2 ? 'block' : 'none' }};">
                                                                    <label for="fecha_reprogramacion{{ $pedido->id }}">Fecha
                                                                        de Reprogramación:</label>
                                                                    <input type="date" class="form-control"
                                                                        name="fecha_reprogramacion"
                                                                        id="fecha_reprogramacion{{ $pedido->id }}"
                                                                        value="{{ $pedido->fecha_reprogramacion }}"
                                                                        min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                                                                        {{ $pedido->productionStatus === 1 ? 'disabled' : '' }}>
                                                                </div>

                                                                <div class="form-group">
                                                                    <label
                                                                        for="observacion_laboratorio{{ $pedido->id }}">Observación:</label>
                                                                    <textarea class="form-control" name="observacion_laboratorio" id="observacion_laboratorio{{ $pedido->id }}"
                                                                        rows="3" maxlength="500" placeholder="Escriba una observación (opcional)"
                                                                        {{ $pedido->productionStatus === 1 ? 'disabled' : '' }}>{{ $pedido->observacion_laboratorio }}</textarea>
                                                                    <small class="form-text text-muted">Máximo 500
                                                                        caracteres</small>
                                                                </div>

                                                                @if ($pedido->observacion_laboratorio)
                                                                    <div class="alert alert-info">
                                                                        <strong>Observación actual:</strong>
                                                                        {{ $pedido->observacion_laboratorio }}
                                                                    </div>
                                                                @endif
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary"
                                                                    data-dismiss="modal">Cerrar</button>
                                                                @if ($pedido->productionStatus !== 1)
                                                                    <button type="submit" class="btn btn-primary">Guardar
                                                                        Cambios</button>
                                                                @endif
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif

                                        @can('pedidoslaboratorio.show')
                                            <button class="btn btn-secondary btn-sm btn-detalle" type="button"
                                                data-id="{{ $pedido->id }}"><i class="fa fa-info"></i> Detalles</button>
                                        @endcan
                                        <!-- <a class="btn btn-secondary btn-sm" href="{{ route('pedidoslaboratorio.show', $pedido->id) }}"><i class="fa fa-info"></i> Detalles</a> -->


                                    </td>
                                </tr>
                                <div class="modal fade" id="detalleModal" tabindex="-1" role="dialog"
                                    aria-labelledby="ModalLabel" aria-hidden="true">
                                    <div class="modal-dialog modal-lg" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="exampleModalLabel">Pedido:</h5>
                                                <button type="button" class="close" data-dismiss="modal"
                                                    aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="card">
                                                    <div class="card-header">
                                                    </div>
                                                    <div class="card-body">
                                                        <ul id="detalle-lista" class="list-group"></ul>

                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary"
                                                    data-dismiss="modal">Cerrar</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            @empty
                                <tr>
                                    <td colspan="9">No hay información que mostrar</td>
                                </tr>
                            @endforelse
                        </tbody>

                    </table>

                </div>

                <!-- Modal para cambio masivo de estado -->
                <div class="modal fade" id="cambioMasivoModal" tabindex="-1" role="dialog"
                    aria-labelledby="cambioMasivoModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg" role="document">
                        <div class="modal-content">
                            <form id="formCambioMasivo" action="" method="POST">
                                @csrf
                                @method('PUT')
                                <div class="modal-header bg-success text-white">
                                    <h5 class="modal-title" id="cambioMasivoModalLabel">
                                        <i class="fa fa-check-square"></i> Cambio Masivo de Estado a Preparado
                                    </h5>
                                    <button type="button" class="close text-white" data-dismiss="modal"
                                        aria-label="Cerrar">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <div class="alert alert-info">
                                        <i class="fa fa-info-circle"></i>
                                        <strong>Información:</strong> Los siguientes pedidos cambiarán su estado de <span
                                            class="badge badge-warning">Pendiente</span> a <span
                                            class="badge badge-success">Preparado</span>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <h6><strong>Pedidos seleccionados:</strong></h6>
                                            <ul id="listaPedidosSeleccionados" class="list-group list-group-flush"
                                                style="max-height: 300px; overflow-y: auto;">
                                                <!-- Se llena dinámicamente con JavaScript -->
                                            </ul>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="observacion_masiva"><strong>Observación General
                                                        (Opcional):</strong></label>
                                                <textarea class="form-control" name="observacion_masiva" id="observacion_masiva" rows="4" maxlength="500"
                                                    placeholder="Escriba una observación que se aplicará a todos los pedidos seleccionados..."></textarea>
                                                <small class="form-text text-muted">Máximo 500 caracteres</small>
                                            </div>

                                            <div class="alert alert-warning">
                                                <i class="fa fa-exclamation-triangle"></i>
                                                <strong>Importante:</strong> Esta acción no se puede deshacer. Todos los pedidos
                                                seleccionados cambiarán a estado "Preparado".
                                            </div>
                                        </div>
                                    </div>

                                    <input type="hidden" name="pedidos_ids" id="pedidos_ids" value="">
                                    <input type="hidden" name="accion_masiva" value="preparado">
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                                        <i class="fa fa-times"></i> Cancelar
                                    </button>
                                    <button type="submit" class="btn btn-success">
                                        <i class="fa fa-check"></i> Confirmar Cambio Masivo
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Modal global con carousel para mostrar una o varias imágenes -->
                <div class="modal fade" id="imageModal" tabindex="-1" role="dialog" aria-labelledby="ModalLabel"
                    aria-hidden="true">
                    <div class="modal-dialog modal-lg" role="document">
                        <div class="modal-content"
                            style="border: none; border-radius: 8px; box-shadow: 0 10px 40px rgba(0,0,0,0.2);">
                            <div class="modal-header"
                                style="border-bottom: 1px solid #e9ecef; padding: 0.75rem 1.25rem; background-color: #f8f9fa;">
                                <div style="flex: 1;">
                                    <h6 class="modal-title mb-0" style="font-weight: 600; color: #2c3e50;">Imágenes de la
                                        Receta</h6>
                                    <small id="imageModalMeta" class="text-muted" style="font-size: 0.8rem;"></small>
                                </div>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"
                                    style="padding: 0; margin: 0; font-size: 1.5rem; font-weight: 300; color: #6c757d; opacity: 0.7; transition: opacity 0.2s;">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body"
                                style="padding: 1rem; background-color: #fff; display: flex; align-items: center; justify-content: center;">
                                <div id="imageCarousel" class="carousel slide w-100" data-ride="carousel"
                                    data-interval="false">
                                    <ol class="carousel-indicators" id="imageCarouselIndicators"
                                        style="bottom: -35px; margin-bottom: 0;"></ol>
                                    <div class="carousel-inner" id="imageCarouselInner" style="border-radius: 4px;"></div>
                                    <a class="carousel-control-prev" href="#imageCarousel" role="button" data-slide="prev"
                                        style="width: 50px; opacity: 1;">
                                        <span class="carousel-control-prev-icon" aria-hidden="true"
                                            style="background-color: rgba(33, 37, 41, 0.75); border-radius: 50%; padding: 18px; background-size: 20px 20px; transition: all 0.3s; box-shadow: 0 2px 8px rgba(0,0,0,0.2);"></span>
                                        <span class="sr-only">Anterior</span>
                                    </a>
                                    <a class="carousel-control-next" href="#imageCarousel" role="button" data-slide="next"
                                        style="width: 50px; opacity: 1;">
                                        <span class="carousel-control-next-icon" aria-hidden="true"
                                            style="background-color: rgba(33, 37, 41, 0.75); border-radius: 50%; padding: 18px; background-size: 20px 20px; transition: all 0.3s; box-shadow: 0 2px 8px rgba(0,0,0,0.2);"></span>
                                        <span class="sr-only">Siguiente</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endcan
@stop

@section('css')
    <style>
        .fecha-reprogramacion-group {
            transition: all 0.3s ease;
        }

        .modal-body .form-group label {
            font-weight: bold;
            color: #495057;
        }

        .badge {
            font-size: 0.75em;
        }

        .table td {
            vertical-align: middle;
        }



        /* Estilos para checkboxes */
        .form-check-input:disabled {
            opacity: 0.3;
        }

        /* Estilos para botón de acción masiva */
        #btnCambioMasivo:disabled {
            opacity: 0.6;
        }

        /* Resaltar filas seleccionadas */
        .table tbody tr.selected {
            background-color: #e3f2fd !important;
        }

        /* Estilos para modal de cambio masivo */
        #listaPedidosSeleccionados .list-group-item {
            border: none;
            padding: 0.5rem 0;
            border-bottom: 1px solid #eee;
        }

        #listaPedidosSeleccionados .list-group-item:last-child {
            border-bottom: none;
        }

        .pedido-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .pedido-numero {
            font-weight: bold;
            color: #495057;
        }

        .pedido-cliente {
            color: #6c757d;
            font-size: 0.9em;
        }

        /* === ESTILOS OPTIMIZADOS DEL MODAL DE IMÁGENES === */

        /* Modal principal - Máximo aprovechamiento del espacio */
        #imageModal .modal-dialog {
            max-width: 1100px;
            margin: 1rem auto;
        }

        #imageModal .modal-content {
            border: none;
            border-radius: 8px;
            overflow: hidden;
        }

        #imageModal .modal-body {
            padding: 1rem;
            height: 80vh;
            background-color: #ffffff;
        }

        /* Controles del carousel - Hover effect */
        #imageModal .carousel-control-prev:hover .carousel-control-prev-icon,
        #imageModal .carousel-control-next:hover .carousel-control-next-icon {
            background-color: rgba(33, 37, 41, 0.9);
            transform: scale(1.1);
        }

        /* Botón cerrar - Hover effect */
        #imageModal .close:hover {
            opacity: 1 !important;
            color: #343a40 !important;
        }

        /* Carousel inner y items */
        #imageCarousel .carousel-inner {
            width: 100%;
            height: 100%;
        }

        #imageCarousel .carousel-item {
            transition: transform 0.4s ease-in-out;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        #imageCarousel .carousel-item img {
            max-height: calc(80vh - 100px);
            max-width: 100%;
            height: auto;
            width: auto;
            object-fit: contain;
            border-radius: 4px;
        }

        /* Indicadores del carousel - Más compactos */
        #imageCarousel .carousel-indicators {
            margin-bottom: -35px;
        }

        #imageCarousel .carousel-indicators li {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background-color: #6c757d;
            opacity: 0.5;
            margin: 0 4px;
            border: 2px solid transparent;
            transition: all 0.3s;
        }

        #imageCarousel .carousel-indicators li.active {
            background-color: #495057;
            opacity: 1;
            transform: scale(1.2);
        }

        /* Responsive para móviles */
        @media (max-width: 768px) {
            #imageModal .modal-dialog {
                max-width: 98%;
                margin: 0.5rem auto;
            }

            #imageModal .modal-body {
                padding: 0.75rem;
                height: 75vh;
            }

            #imageCarousel .carousel-item img {
                max-height: calc(75vh - 80px);
            }

            #imageModal .carousel-control-prev,
            #imageModal .carousel-control-next {
                width: 40px;
            }

            #imageModal .carousel-control-prev-icon,
            #imageModal .carousel-control-next-icon {
                padding: 14px !important;
                background-size: 16px 16px !important;
            }

            #imageCarousel .carousel-indicators {
                margin-bottom: -30px;
            }

            #imageCarousel .carousel-indicators li {
                width: 8px;
                height: 8px;
                margin: 0 3px;
            }
        }

        @media (max-width: 576px) {
            #imageModal .modal-header {
                padding: 0.6rem 1rem;
            }

            #imageModal .modal-body {
                padding: 0.5rem;
                height: 70vh;
            }

            #imageCarousel .carousel-item img {
                max-height: calc(70vh - 70px);
            }
        }

        /* Animación de entrada del modal */
        #imageModal.fade .modal-dialog {
            transition: transform 0.3s ease-out;
        }
    </style>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            const cambioMasivoUrl = @json($cambioMasivoUrl);
            const detalleUrlTemplate = @json($detalleUrlTemplate);

            if (cambioMasivoUrl) {
                // Variables para manejo de selección masiva
                let pedidosSeleccionados = [];

                // Funcionalidad para checkbox "Seleccionar todo"
                $('#selectAll').change(function() {
                    const isChecked = $(this).is(':checked');
                    $('.pedido-checkbox:not(:disabled)').prop('checked', isChecked);

                    if (isChecked) {
                        $('.pedido-checkbox:not(:disabled)').each(function() {
                            $(this).closest('tr').addClass('selected');
                        });
                    } else {
                        $('.table tbody tr').removeClass('selected');
                    }

                    actualizarContadorSeleccionados();
                });

                // Funcionalidad para checkboxes individuales
                $('.pedido-checkbox').change(function() {
                    const row = $(this).closest('tr');

                    if ($(this).is(':checked')) {
                        row.addClass('selected');
                    } else {
                        row.removeClass('selected');
                        $('#selectAll').prop('checked', false);
                    }

                    // Verificar si todos están seleccionados
                    const totalCheckboxes = $('.pedido-checkbox:not(:disabled)').length;
                    const selectedCheckboxes = $('.pedido-checkbox:checked').length;

                    if (totalCheckboxes === selectedCheckboxes && totalCheckboxes > 0) {
                        $('#selectAll').prop('checked', true);
                    }

                    actualizarContadorSeleccionados();
                });

                // Función para actualizar contador y habilitar/deshabilitar botón
                function actualizarContadorSeleccionados() {
                    const selectedCount = $('.pedido-checkbox:checked').length;
                    $('#contadorSeleccionados').text(selectedCount);

                    if (selectedCount > 0) {
                        $('#btnCambioMasivo').prop('disabled', false);
                    } else {
                        $('#btnCambioMasivo').prop('disabled', true);
                    }
                }

                // Funcionalidad para botón de cambio masivo
                $('#btnCambioMasivo').click(function() {
                    pedidosSeleccionados = [];
                    $('#listaPedidosSeleccionados').empty();

                    // Recopilar información de pedidos seleccionados
                    $('.pedido-checkbox:checked').each(function() {
                        const row = $(this).closest('tr');
                        const pedidoId = $(this).val();
                        const nroPedido = row.find('td:nth-child(3)').text()
                            .trim(); // Columna Nro pedido
                        const cliente = row.find('td:nth-child(4)').text()
                            .trim(); // Columna Cliente

                        pedidosSeleccionados.push({
                            id: pedidoId,
                            nroPedido: nroPedido,
                            cliente: cliente
                        });

                        // Agregar a la lista del modal
                        $('#listaPedidosSeleccionados').append(`
                    <li class="list-group-item">
                        <div class="pedido-info">
                            <div>
                                <span class="pedido-numero">#${nroPedido}</span><br>
                                <span class="pedido-cliente">${cliente}</span>
                            </div>
                            <span class="badge badge-warning">Pendiente → Preparado</span>
                        </div>
                    </li>
                `);
                    });

                    // Preparar IDs para enviar
                    const idsArray = pedidosSeleccionados.map(p => p.id);
                    $('#pedidos_ids').val(idsArray.join(','));

                    // Mostrar modal
                    $('#cambioMasivoModal').modal('show');
                });

                // Envío del formulario de cambio masivo
                $('#formCambioMasivo').submit(function(e) {
                    e.preventDefault();

                    // Preparar datos manualmente para debug
                    const pedidosIds = $('#pedidos_ids').val();
                    const observacion = $('#observacion_masiva').val();
                    const token = $('meta[name="csrf-token"]').attr('content') || $('input[name="_token"]')
                        .val();

                    console.log('Debug - Datos a enviar:');
                    console.log('pedidos_ids:', pedidosIds);
                    console.log('observacion_masiva:', observacion);
                    console.log('accion_masiva: preparado');
                    console.log('_token:', token);

                    const formDataObject = {
                        pedidos_ids: pedidosIds,
                        observacion_masiva: observacion,
                        accion_masiva: 'preparado',
                        _token: token
                    };

                    // Mostrar loading en el botón
                    const submitBtn = $(this).find('button[type="submit"]');
                    const originalText = submitBtn.html();
                    submitBtn.html('<i class="fa fa-spinner fa-spin"></i> Procesando...').prop('disabled',
                        true);

                    $.ajax({
                        url: cambioMasivoUrl,
                        type: 'POST',
                        data: formDataObject,
                        success: function(response) {
                            console.log('Respuesta exitosa:', response);
                            $('#cambioMasivoModal').modal('hide');

                            $('body').prepend(`
                        <div class="alert alert-success alert-dismissible fade show" role="alert" style="position: fixed; top: 20px; right: 20px; z-index: 9999;">
                            <i class="fa fa-check-circle"></i> ${response.message || 'Cambio masivo realizado correctamente'}
                            <button type="button" class="close" data-dismiss="alert">
                                <span>&times;</span>
                            </button>
                        </div>
                    `);

                            setTimeout(() => {
                                window.location.reload();
                            }, 2000);
                        },
                        error: function(xhr, status, error) {
                            console.error('=== ERROR COMPLETO ===');
                            console.error('XHR:', xhr);
                            console.error('Status:', status);
                            console.error('Error:', error);
                            console.error('Response Text:', xhr.responseText);
                            console.error('Status Code:', xhr.status);

                            if (xhr.responseJSON) {
                                console.error('Response JSON:', xhr.responseJSON);
                            }

                            let errorMessage = 'Error al procesar el cambio masivo';

                            if (xhr.responseJSON) {
                                if (xhr.responseJSON.message) {
                                    errorMessage = xhr.responseJSON.message;
                                } else if (xhr.responseJSON.errors) {
                                    const errors = Object.values(xhr.responseJSON.errors)
                                        .flat();
                                    errorMessage = 'Errores de validación: ' + errors.join(
                                        ', ');
                                }
                            } else if (xhr.responseText) {
                                errorMessage = 'Error del servidor: ' + xhr.responseText
                                    .substring(0, 200);
                            }

                            $('body').prepend(`
                        <div class="alert alert-danger alert-dismissible fade show" role="alert" style="position: fixed; top: 20px; right: 20px; z-index: 9999;">
                            <i class="fa fa-exclamation-triangle"></i> ${errorMessage}
                            <button type="button" class="close" data-dismiss="alert">
                                <span>&times;</span>
                            </button>
                        </div>
                    `);
                        },
                        complete: function() {
                            // Restaurar botón
                            submitBtn.html(originalText).prop('disabled', false);
                        }
                    });
                });

                // Limpiar modal al cerrarlo
                $('#cambioMasivoModal').on('hidden.bs.modal', function() {
                    $('#observacion_masiva').val('');
                    $('#pedidos_ids').val('');
                    $('#listaPedidosSeleccionados').empty();
                });
            }

            // Funcionalidad existente para mostrar detalles del pedido
            if (detalleUrlTemplate) {
                $('.btn-detalle').click(function() {
                    const id = $(this).data('id');
                    const detalleUrl = detalleUrlTemplate.replace('__ID__', id);

                    $.ajax({
                        url: detalleUrl,
                        type: 'GET',
                        success: function(pedido) {
                            $('#detalle-lista').empty();

                            // Agregar información del estado
                            let estadoTexto = '';
                            let estadoClass = '';
                            switch (pedido.productionStatus) {
                                case 1:
                                    estadoTexto = 'Preparado';
                                    estadoClass = 'success';
                                    break;
                                case 2:
                                    estadoTexto = 'Reprogramado';
                                    estadoClass = 'info';
                                    break;
                                default:
                                    estadoTexto = 'Pendiente';
                                    estadoClass = 'warning';
                            }

                            $('#detalle-lista').append(`<li class="list-group-item">
                        <label>Estado:</label> <span class="badge bg-${estadoClass}">${estadoTexto}</span>
                        ${pedido.fecha_reprogramacion ? ` | <label>Fecha Reprogramación:</label> ${new Date(pedido.fecha_reprogramacion).toLocaleDateString('es-ES')}` : ''}
                    </li>`);

                            if (pedido.observacion_laboratorio) {
                                $('#detalle-lista').append(`<li class="list-group-item">
                            <label>Observación:</label> ${pedido.observacion_laboratorio}
                        </li>`);
                            }

                            // Agregar detalles de productos con su estado individual
                            if (pedido.productos_procesados && pedido.productos_procesados
                                .length > 0) {
                                pedido.productos_procesados.forEach(detalle => {
                                    let estadoProducto = '';
                                    let estadoProductoClass = '';

                                    switch (detalle.estado_produccion) {
                                        case 1:
                                            estadoProducto = 'Preparado';
                                            estadoProductoClass = 'success';
                                            break;
                                        case 2:
                                            estadoProducto = 'Reprogramado';
                                            estadoProductoClass = 'info';
                                            break;
                                        default:
                                            estadoProducto = 'Pendiente';
                                            estadoProductoClass = 'warning';
                                    }

                                    $('#detalle-lista').append(`<li class="list-group-item">
                                <div class="row">
                                    <div class="col-md-6">
                                        <label>Producto:</label> ${detalle.articulo}
                                    </div>
                                    <div class="col-md-3">
                                        <label>Cantidad:</label> ${detalle.cantidad}
                                    </div>
                                    <div class="col-md-3">
                                        <label>Estado:</label> <span class="badge bg-${estadoProductoClass}">${estadoProducto}</span>
                                    </div>
                                </div>
                            </li>`);
                                });
                            } else if (pedido.detailpedidos && pedido.detailpedidos.length >
                                0) {
                                // Fallback para pedidos sin productos_procesados
                                pedido.detailpedidos.forEach(detalle => {
                                    $('#detalle-lista').append(`<li class="list-group-item">
                                <div class="row">
                                    <div class="col-md-6">
                                        <label>Producto:</label> ${detalle.articulo}
                                    </div>
                                    <div class="col-md-3">
                                        <label>Cantidad:</label> ${detalle.cantidad}
                                    </div>
                                    <div class="col-md-3">
                                        <label>Estado:</label> <span class="badge bg-secondary">No disponible</span>
                                    </div>
                                </div>
                            </li>`);
                                });
                            } else {
                                $('#detalle-lista').append(`<li class="list-group-item">
                            No hay productos disponibles para este pedido.
                        </li>`);
                            }
                            $('#detalleModal').modal('show');
                        },
                        error: function(xhr, status, error) {
                            console.error('Error al cargar detalles:', error);
                            console.error('Status:', status);
                            console.error('Response:', xhr.responseText);

                            $('#detalle-lista').empty();
                            $('#detalle-lista').append(`<li class="list-group-item">
                        <div class="alert alert-danger">
                            Error al cargar los detalles del pedido. Por favor, intente nuevamente.
                        </div>
                    </li>`);
                            $('#detalleModal').modal('show');
                        }
                    });
                });
            }

            // Manejar apertura del modal carousel con múltiples imágenes (robusto)
            $(document).on('click', '.btn-open-images', function(e) {
                e.preventDefault();

                let raw = $(this).attr('data-images');
                let images = [];

                if (!raw) {
                    images = [];
                } else if (typeof raw === 'object') {
                    images = raw;
                } else {
                    // raw is string — try parse JSON, else split by comma
                    try {
                        images = JSON.parse(raw);
                    } catch (err) {
                        // fallback: comma separated
                        images = raw.split(',').map(s => s.trim()).filter(Boolean);
                    }
                }

                // Ensure images is an array
                if (!Array.isArray(images)) images = [images];

                const $indicators = $('#imageCarouselIndicators');
                const $inner = $('#imageCarouselInner');
                $indicators.empty();
                $inner.empty();

                if (!images || images.length === 0) {
                    $inner.append(
                        '<div class="carousel-item active text-center"><div class="alert alert-warning">No hay imágenes disponibles</div></div>'
                    );
                } else {
                    images.forEach((src, idx) => {
                        const active = idx === 0 ? ' active' : '';
                        $indicators.append(
                            `<li data-target="#imageCarousel" data-slide-to="${idx}" class="${idx === 0 ? 'active' : ''}"></li>`
                        );
                        $inner.append(`
                        <div class="carousel-item${active}">
                            <div class="text-center">
                                <img src="${src}" class="img-fluid d-block mx-auto" alt="Imagen ${idx+1}" style="max-height:70vh;" loading="lazy" />
                            </div>
                        </div>
                    `);
                    });
                }

                // Mostrar metadata (pedido, fechas) en el modal
                const order = $(this).attr('data-order') || '';
                const delivery = $(this).attr('data-delivery') || '';
                const reprogram = $(this).attr('data-fecha-reprogramacion') || '';
                let metaText = '';
                if (order) metaText += `Pedido: ${order}`;
                if (delivery) metaText += `${metaText ? ' | ' : ''}Entrega: ${delivery}`;
                if (reprogram) metaText += `${metaText ? ' | ' : ''}Reprogramado: ${reprogram}`;
                $('#imageModalMeta').text(metaText);

                // Inicializar carousel en modo manual (sin auto-advance) y forzar al primer slide
                try {
                    $('#imageCarousel').carousel({
                        interval: false
                    });
                    $('#imageCarousel').carousel(0);
                } catch (e) {
                    console.warn('Carousel init warning', e);
                }

                // Mostrar modal
                $('#imageModal').modal({
                    show: true
                });
            });

            // Limpiar carousel al cerrar el modal
            $('#imageModal').on('hidden.bs.modal', function() {
                $('#imageCarouselIndicators').empty();
                $('#imageCarouselInner').empty();
            });

            // Funcionalidad para mostrar/ocultar fecha de reprogramación
            $('[id^="productionStatus"]').change(function() {
                const pedidoId = $(this).attr('id').replace('productionStatus', '');
                const selectedValue = $(this).val();
                const fechaGroup = $(`.fecha-reprogramacion-group${pedidoId}`);
                const fechaInput = $(`#fecha_reprogramacion${pedidoId}`);

                if (selectedValue == '2') {
                    fechaGroup.show();
                    fechaInput.attr('required', true);
                } else {
                    fechaGroup.hide();
                    fechaInput.attr('required', false);
                    fechaInput.val('');
                }
            });

            // Auto-dismiss alerts después de 5 segundos
            setTimeout(function() {
                $('.alert-dismissible').alert('close');
            }, 5000);
        });
    </script>
@stop
