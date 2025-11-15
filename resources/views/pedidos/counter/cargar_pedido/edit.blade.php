@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <!-- <h1>Pedidos</h1> -->
@stop

@section('content')
@can('cargarpedidos.edit')

<x-grobdi.layout.header-card
    title="Actualizar Pedido"
    subtitle="Actualiza datos de contacto, entrega y estados"
>
    <x-slot:actions>
        <x-grobdi.button href="{{ url()->previous() }}" variant="outline" size="sm" icon="fa fa-arrow-left">
            Atrás
        </x-grobdi.button>
    </x-slot:actions>
</x-grobdi.layout.header-card>

<div class="card mt-5">
  <div class="card-body">

    <form action="{{ route('cargarpedidos.update',$pedido->id) }}" method="POST">
        @csrf
        @method('PUT')
        @php
            $currentDeliveryStatus = old('deliveryStatus', $pedido->deliveryStatus);
            $normalizedDeliveryStatus = is_string($currentDeliveryStatus) ? strtolower($currentDeliveryStatus) : 'pendiente';
            if (!in_array($normalizedDeliveryStatus, ['pendiente', 'entregado'])) {
                $normalizedDeliveryStatus = 'pendiente';
            }
            $deliveryStatusOptions = ['pendiente' => 'Pendiente', 'entregado' => 'Entregado'];
            $isDeliveryLocked = $normalizedDeliveryStatus === 'entregado';
        @endphp

        <div class="row">

            <div class="col-xs-4 col-sm-4 col-md-4">
                <label for="inputName" class="form-label"><strong>Cliente:</strong></label>
                <input
                    type="text"
                    name="customerName"
                    value="{{ $pedido->customerName }}"
                    class="form-control @error('customerName') is-invalid @enderror"
                    id="inputName"
                    placeholder="Name"
                    disabled>
                @error('customerName')
                    <div class="form-text text-danger">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-xs-2 col-sm-2 col-md-2">
                <label for="customerNumber" class="form-label"><strong>Telefono:</strong></label>
                <input
                    type="text"
                    name="customerNumber"
                    value="{{ old('customerNumber', $pedido->customerNumber) }}"
                    class="form-control @error('customerNumber') is-invalid @enderror"
                    id="customerNumber"
                    placeholder="Número de teléfono">
                @error('customerNumber')
                    <div class="form-text text-danger">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-xs-4 col-sm-4 col-md-4">
                <label for="doctorName" class="form-label"><strong>Doctor:</strong></label>
                <div class="doctor-search-container position-relative">
                    <div class="input-group">
                        <input
                            type="text"
                            name="doctorName"
                            value="{{ old('doctorName', $pedido->doctorName) }}"
                            class="form-control @error('doctorName') is-invalid @enderror"
                            id="doctorName"
                            placeholder="Nombre del doctor"
                            autocomplete="off">
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary" type="button" id="searchDoctorBtn">
                                <i class="fa fa-search"></i>
                            </button>
                        </div>
                    </div>
                    <div id="doctorSuggestions" class="list-group position-absolute" style="z-index: 1000; max-height: 200px; overflow-y: auto; width: 100%; display: none;"></div>
                </div>
                <input type="hidden" name="id_doctor" id="id_doctor" value="{{ old('id_doctor', $pedido->id_doctor) }}">
                @error('doctorName')
                    <div class="form-text text-danger">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-xs-2 col-sm-2 col-md-2">
                <label for="deliveryDate" class="form-label"><strong>Fecha de entrega:</strong></label>
                <input
                    type="date"
                    name="deliveryDate"
                    value="{{ old('deliveryDate', $pedido->deliveryDate ? \Carbon\Carbon::parse($pedido->deliveryDate)->format('Y-m-d') : '') }}"
                    class="form-control @error('deliveryDate') is-invalid @enderror"
                    id="deliveryDate"
                    min="{{ date('Y-m-d') }}"
                    placeholder="ingresar fecha">
                @error('deliveryDate')
                    <div class="form-text text-danger">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-xs-4 col-sm-4 col-md-4">
                <label for="address" class="form-label"><strong>Dirección:</strong></label>
                <input
                    type="text"
                    name="address"
                    value="{{ old('address', $pedido->address) }}"
                    class="form-control @error('address') is-invalid @enderror"
                    id="address"
                    placeholder="Dirección">
                @error('address')
                    <div class="form-text text-danger">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-xs-3 col-sm-3 col-md-3">
                <label for="district" class="form-label"><strong>Distrito:</strong></label>
                <input
                    type="text"
                    name="district"
                    value="{{ old('district', $pedido->district) }}"
                    class="form-control @error('district') is-invalid @enderror"
                    id="district"
                    placeholder="distrito">
                @error('district')
                    <div class="form-text text-danger">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-xs-2 col-sm-2 col-md-2">
                <label for="zone_id" class="form-label"><strong>Zonas:</strong></label>
                @php
                    // Mostrar solo las zonas con id entre 1 y 5 (inclusive) en este select
                    $zonesCollection = $zonas instanceof \Illuminate\Support\Collection ? $zonas : collect($zonas);
                    $displayZonasForSelect = $zonesCollection->filter(function($z){
                        $id = data_get($z, 'id');
                        return is_numeric($id) && $id >= 1 && $id <= 5;
                    })->values();
                @endphp
                <select class="form-control @error('zone_id') is-invalid @enderror" name="zone_id" id="zone_id">
                    <option value="" disabled>Selecciona una zona</option>
                    @foreach ($displayZonasForSelect as $zona)
                        <option value="{{ $zona->id }}" {{ (old('zone_id', $pedido->zone_id) == $zona->id) ? 'selected' : '' }}>
                            {{ $zona->name }}
                        </option>
                    @endforeach
                </select>
                @error('zone_id')
                    <div class="form-text text-danger">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-xs-3 col-sm-3 col-md-3">
                <label for="deliveryStatus" class="form-label"><strong>Estado de entrega:</strong></label>
                <select
                    class="form-control @error('deliveryStatus') is-invalid @enderror"
                    name="deliveryStatus"
                    id="deliveryStatus"
                    {{ $isDeliveryLocked ? 'disabled' : '' }}
                >
                    @foreach ($deliveryStatusOptions as $value => $label)
                        <option value="{{ $value }}" {{ $normalizedDeliveryStatus === $value ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
                @if($isDeliveryLocked)
                    <input type="hidden" name="deliveryStatus" value="entregado">
                @endif
                @error('deliveryStatus')
                    <div class="form-text text-danger">{{ $message }}</div>
                @enderror
            </div>
        </div>
        <br>
                <x-grobdi.button type="submit" variant="success" icon="fa-solid fa-floppy-disk">
                        Actualizar
                </x-grobdi.button>
    </form>

  </div>
</div>

@endcan
@stop

@section('css')
    {{-- Add here extra stylesheets --}}
    {{-- <link rel="stylesheet" href="/css/admin_custom.css"> --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://getbootstrap.com/docs/5.3/assets/css/docs.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        #doctorSuggestions {
            border: 1px solid #ddd;
            border-top: none;
            border-radius: 0 0 4px 4px;
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .doctor-item:hover {
            background-color: #f8f9fa;
        }

        .position-relative {
            position: relative;
        }

        .doctor-search-container {
            position: relative;
        }
    </style>
@stop

@section('js')
    <script> console.log("Hi, I'm using the Laravel-AdminLTE package!"); </script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            let searchTimeout;

            // Función para mostrar sugerencias
            function showSuggestions(doctors) {
                const suggestionsDiv = $('#doctorSuggestions');
                suggestionsDiv.empty().show();

                if (doctors.length === 0) {
                    suggestionsDiv.append('<div class="list-group-item">No se encontraron doctores</div>');
                    return;
                }

                doctors.forEach(function(doctor) {
                    // Construir nombre a mostrar: priorizar name_softlynn; si no, concatenar name + apellidos
                    let displayName = doctor.name_softlynn && doctor.name_softlynn.trim() !== ''
                        ? doctor.name_softlynn
                        : [doctor.name, doctor.first_lastname, doctor.second_lastname]
                            .filter(Boolean)
                            .join(' ')
                            .replace(/\s+/g, ' ')
                            .trim();
                    const item = $(`
                        <a href="#" class="list-group-item list-group-item-action doctor-item"
                           data-id="${doctor.id}"
                           data-name="${displayName}">
                            ${displayName}
                        </a>
                    `);
                    suggestionsDiv.append(item);
                });
            }

            // Función para buscar doctores via API
            function searchDoctors(searchTerm) {
                $.ajax({
                    url: '{{ route("api.doctores.search") }}',
                    method: 'GET',
                    data: { search: searchTerm },
                    success: function(response) {
                        showSuggestions(response);
                    },
                    error: function() {
                        $('#doctorSuggestions').html('<div class="list-group-item text-danger">Error al buscar doctores</div>').show();
                    }
                });
            }

            // Buscar al escribir en el input (con debounce)
            $('#doctorName').on('input', function() {
                const searchTerm = $(this).val().trim();

                // Limpiar timeout anterior
                clearTimeout(searchTimeout);

                if (searchTerm.length < 2) {
                    $('#doctorSuggestions').hide();
                    $('#id_doctor').val('');
                    return;
                }

                // Establecer nuevo timeout para evitar muchas llamadas API
                searchTimeout = setTimeout(function() {
                    searchDoctors(searchTerm);
                }, 300);
            });

            // Buscar con el botón
            $('#searchDoctorBtn').on('click', function() {
                const searchTerm = $('#doctorName').val().trim();

                if (searchTerm.length === 0) {
                    searchDoctors(''); // Buscar todos
                } else {
                    searchDoctors(searchTerm);
                }
            });

            // Seleccionar doctor
            $(document).on('click', '.doctor-item', function(e) {
                e.preventDefault();
                const doctorId = $(this).data('id');
                const doctorName = $(this).data('name');

                $('#doctorName').val(doctorName);
                $('#id_doctor').val(doctorId);
                $('#doctorSuggestions').hide();
            });

            // Ocultar sugerencias al hacer clic fuera
            $(document).on('click', function(e) {
                if (!$(e.target).closest('.col-xs-4:has(#doctorName)').length) {
                    $('#doctorSuggestions').hide();
                }
            });

            // Validar que se seleccione un doctor válido
            $('form').on('submit', function(e) {
                const doctorName = $('#doctorName').val().trim();
                const doctorId = $('#id_doctor').val();

                if (doctorName && !doctorId) {
                    e.preventDefault();
                    alert('Por favor, seleccione un doctor válido de la lista.');
                    return false;
                }
            });
        });
    </script>
@stop
