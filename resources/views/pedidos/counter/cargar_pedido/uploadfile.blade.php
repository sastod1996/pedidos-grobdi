@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
@stop

@section('content')
@can('cargarpedidos.uploadfile')

<div class="container-fluid py-4">
    <!-- Header Card -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="mb-0">
                    <i class="fas fa-shopping-cart mr-2"></i>
                    Pedido N° {{$pedido->orderId}}
                </h4>
                <a class="btn btn-light btn-sm" href="{{ route('cargarpedidos.index', ['fecha' => $pedido->deliveryDate]) }}">
                    <i class="fa fa-arrow-left"></i> Volver
                </a>
            </div>
        </div>

        <div class="card-body">
            <!-- Mensajes de estado -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            @if(session('danger'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle mr-2"></i>{{ session('danger') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            <!-- Formulario de actualización de pago -->
            <form action="{{ route('cargarpedidos.actualizarPago',$pedido->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="paymentStatus" class="form-label font-weight-bold">
                            <i class="fas fa-credit-card mr-1"></i>Estado del pago
                        </label>
                        <select class="form-control @error('paymentStatus') is-invalid @enderror" name="paymentStatus" id="paymentStatus">
                            <option value="">Seleccione una opción</option>
                            <option value="PAGADO" {{ $pedido->paymentStatus == 'PAGADO' ? 'selected' : '' }}>PAGADO</option>
                            <option value="PENDIENTE" {{ $pedido->paymentStatus == 'PENDIENTE' ? 'selected' : '' }}>PENDIENTE</option>
                        </select>
                        @error('paymentStatus')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="inputpaymentmethod" class="form-label font-weight-bold">
                            <i class="fas fa-money-bill-wave mr-1"></i>Método de pago
                        </label>
                        <input
                            type="text"
                            name="paymentMethod"
                            value="{{ $pedido->paymentMethod }}"
                            class="form-control @error('paymentMethod') is-invalid @enderror"
                            id="inputpaymentmethod"
                            placeholder="Ej: Transferencia, Yape, Plin">
                        @error('paymentMethod')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="text-right">
                    <button type="submit" class="btn btn-success">
                        <i class="fa-solid fa-save"></i> Actualizar Pago
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Sección de Vouchers -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-info text-white">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-receipt mr-2"></i>Vouchers de Pago
                </h5>
                <button type="button" class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#staticBackdrop">
                    <i class="fas fa-plus"></i> Agregar Voucher
                </button>
            </div>
        </div>

        <div class="card-body">
            @if ($pedido->voucher && count($array_voucher) > 0)
                <div class="row">
                    @foreach ($array_voucher as $voucher)
                        <div class="col-12 col-sm-6 col-lg-4 mb-4">
                            <div class="card h-100 shadow-sm">
                                <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 300px; overflow: hidden;">
                                    <img src="{{ asset($voucher['voucher']) }}"
                                         alt="Voucher {{ $pedido->orderId }}"
                                         class="img-fluid"
                                         style="max-height: 100%; max-width: 100%; object-fit: contain;">
                                </div>
                                <div class="card-body">
                                    <p class="mb-2">
                                        <strong>Nro. Operación:</strong><br>
                                        <span class="badge badge-primary">{{ $voucher['nro_operacion'] }}</span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="alert alert-warning text-center" role="alert">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    No hay vouchers cargados para este pedido
                </div>
            @endif
        </div>
    </div>

    <!-- Sección de Recetas -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-success text-white">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-file-medical mr-2"></i>Recetas Médicas
                </h5>
                <button type="button" class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#recetaModal">
                    <i class="fas fa-plus"></i> Agregar Receta
                </button>
            </div>
        </div>

        <div class="card-body">
            @if ($pedido->receta && count($recetas) > 0)
                <div class="row">
                    @foreach ($recetas as $receta)
                        <div class="col-12 col-sm-6 col-lg-4 mb-4">
                            <div class="card h-100 shadow-sm">
                                <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 300px; overflow: hidden;">
                                    <img src="{{ asset($receta) }}"
                                         alt="Receta {{ $pedido->orderId }}"
                                         class="img-fluid"
                                         style="max-height: 100%; max-width: 100%; object-fit: contain;">
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="alert alert-warning text-center" role="alert">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    No hay recetas cargadas para este pedido
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal Voucher -->
<div class="modal fade" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('cargarpedidos.cargarImagen',$pedido->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="staticBackdropLabel">
                        <i class="fas fa-receipt mr-2"></i>Agregar Vouchers de Pago
                    </h5>
                    <button type="button" class="close text-white" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="voucher-rows">
                        <div class="voucher-row mb-4 p-3 border rounded bg-light">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label font-weight-bold">
                                        <i class="fas fa-image mr-1"></i>Imagen del voucher
                                    </label>
                                    <input type="file" name="voucher[]" accept="image/*" class="form-control" required>
                                    <div class="mt-3 voucher-preview-container text-center"></div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label font-weight-bold">
                                        <i class="fas fa-hashtag mr-1"></i>Número de operación
                                    </label>
                                    <input type="text" name="operationNumber[]" class="form-control" placeholder="000000" required>
                                    <div class="mt-3 text-right">
                                        <button type="button" class="btn btn-danger btn-sm btn-remove-voucher">
                                            <i class="fas fa-times"></i> Eliminar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <button type="button" id="add-voucher-row" class="btn btn-secondary btn-block">
                        <i class="fas fa-plus"></i> Agregar otro voucher
                    </button>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Cerrar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Receta -->
<div class="modal fade" id="recetaModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="recetaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('cargarpedidos.cargarImagenReceta',$pedido->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="recetaModalLabel">
                        <i class="fas fa-file-medical mr-2"></i>Agregar Recetas Médicas
                    </h5>
                    <button type="button" class="close text-white" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="receta-rows">
                        <div class="receta-row mb-4 p-3 border rounded bg-light">
                            <div class="row">
                                <div class="col-md-9 mb-3">
                                    <label class="form-label font-weight-bold">
                                        <i class="fas fa-image mr-1"></i>Imagen de la receta
                                    </label>
                                    <input type="file" name="receta[]" accept="image/*" class="form-control" required>
                                    <div class="mt-3 receta-preview-container text-center"></div>
                                </div>
                                <div class="col-md-3 mb-3 d-flex align-items-end">
                                    <button type="button" class="btn btn-danger btn-block btn-remove-receta">
                                        <i class="fas fa-times"></i> Eliminar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <button type="button" id="add-receta-row" class="btn btn-secondary btn-block">
                        <i class="fas fa-plus"></i> Agregar otra receta
                    </button>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Cerrar
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endcan
@stop

@section('css')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .card {
            border: none;
            transition: transform 0.2s;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .card-header {
            border-bottom: 3px solid rgba(255,255,255,0.2);
        }

        .shadow-sm {
            box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075) !important;
        }

        .voucher-preview-container img,
        .receta-preview-container img {
            max-width: 100%;
            max-height: 200px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .modal-dialog {
            max-width: 800px;
        }

        .voucher-row,
        .receta-row {
            transition: all 0.3s ease;
        }

        .voucher-row:hover,
        .receta-row:hover {
            background-color: #f8f9fa !important;
        }

        .badge-primary {
            font-size: 0.9rem;
            padding: 0.5rem 0.8rem;
        }

        @media (max-width: 768px) {
            .card-img-top {
                height: 250px !important;
            }
        }
    </style>
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        (function() {
            // Voucher functionality
            const addBtn = document.getElementById('add-voucher-row');
            const rowsContainer = document.getElementById('voucher-rows');

            function createVoucherRow() {
                const div = document.createElement('div');
                div.className = 'voucher-row mb-4 p-3 border rounded bg-light';
                div.innerHTML = `
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label font-weight-bold">
                                <i class="fas fa-image mr-1"></i>Imagen del voucher
                            </label>
                            <input type="file" name="voucher[]" accept="image/*" class="form-control" required>
                            <div class="mt-3 voucher-preview-container text-center"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label font-weight-bold">
                                <i class="fas fa-hashtag mr-1"></i>Número de operación
                            </label>
                            <input type="text" name="operationNumber[]" class="form-control" placeholder="000000" required>
                            <div class="mt-3 text-right">
                                <button type="button" class="btn btn-danger btn-sm btn-remove-voucher">
                                    <i class="fas fa-times"></i> Eliminar
                                </button>
                            </div>
                        </div>
                    </div>
                `;
                return div;
            }

            addBtn.addEventListener('click', function() {
                rowsContainer.appendChild(createVoucherRow());
            });

            rowsContainer.addEventListener('click', function(e) {
                if (e.target && (e.target.classList.contains('btn-remove-voucher') || e.target.parentElement.classList.contains('btn-remove-voucher'))) {
                    const btn = e.target.classList.contains('btn-remove-voucher') ? e.target : e.target.parentElement;
                    const row = btn.closest('.voucher-row');
                    if (!row) return;
                    const totalRows = rowsContainer.querySelectorAll('.voucher-row').length;
                    if (totalRows <= 1) {
                        const fileInput = row.querySelector('input[type=file]');
                        const opInput = row.querySelector('input[name="operationNumber[]"]');
                        const preview = row.querySelector('.voucher-preview-container');
                        if (fileInput) fileInput.value = '';
                        if (opInput) opInput.value = '';
                        if (preview) preview.innerHTML = '';
                    } else {
                        row.remove();
                    }
                }
            });

            rowsContainer.addEventListener('change', function(e) {
                const input = e.target;
                if (input && input.type === 'file' && input.name && input.name.indexOf('voucher') !== -1) {
                    const file = input.files && input.files[0];
                    const container = input.closest('.voucher-row').querySelector('.voucher-preview-container');
                    if (!container) return;
                    container.innerHTML = '';
                    if (!file) return;
                    const reader = new FileReader();
                    reader.onload = function(ev) {
                        const img = document.createElement('img');
                        img.src = ev.target.result;
                        img.className = 'img-thumbnail';
                        container.appendChild(img);
                    }
                    reader.readAsDataURL(file);
                }
            });

            // Receta functionality
            const addRecetaBtn = document.getElementById('add-receta-row');
            const recetaRows = document.getElementById('receta-rows');

            function createRecetaRow() {
                const div = document.createElement('div');
                div.className = 'receta-row mb-4 p-3 border rounded bg-light';
                div.innerHTML = `
                    <div class="row">
                        <div class="col-md-9 mb-3">
                            <label class="form-label font-weight-bold">
                                <i class="fas fa-image mr-1"></i>Imagen de la receta
                            </label>
                            <input type="file" name="receta[]" accept="image/*" class="form-control" required>
                            <div class="mt-3 receta-preview-container text-center"></div>
                        </div>
                        <div class="col-md-3 mb-3 d-flex align-items-end">
                            <button type="button" class="btn btn-danger btn-block btn-remove-receta">
                                <i class="fas fa-times"></i> Eliminar
                            </button>
                        </div>
                    </div>
                `;
                return div;
            }

            addRecetaBtn.addEventListener('click', function() {
                recetaRows.appendChild(createRecetaRow());
            });

            recetaRows.addEventListener('click', function(e) {
                if (e.target && (e.target.classList.contains('btn-remove-receta') || e.target.parentElement.classList.contains('btn-remove-receta'))) {
                    const btn = e.target.classList.contains('btn-remove-receta') ? e.target : e.target.parentElement;
                    const row = btn.closest('.receta-row');
                    if (!row) return;
                    const total = recetaRows.querySelectorAll('.receta-row').length;
                    if (total <= 1) {
                        const fileInput = row.querySelector('input[type=file]');
                        const preview = row.querySelector('.receta-preview-container');
                        if (fileInput) fileInput.value = '';
                        if (preview) preview.innerHTML = '';
                    } else {
                        row.remove();
                    }
                }
            });

            recetaRows.addEventListener('change', function(e) {
                const input = e.target;
                if (input && input.type === 'file' && input.name && input.name.indexOf('receta') !== -1) {
                    const file = input.files && input.files[0];
                    const container = input.closest('.receta-row').querySelector('.receta-preview-container');
                    if (!container) return;
                    container.innerHTML = '';
                    if (!file) return;
                    const reader = new FileReader();
                    reader.onload = function(ev) {
                        const img = document.createElement('img');
                        img.src = ev.target.result;
                        img.className = 'img-thumbnail';
                        container.appendChild(img);
                    }
                    reader.readAsDataURL(file);
                }
            });
        })();
    </script>
@stop
