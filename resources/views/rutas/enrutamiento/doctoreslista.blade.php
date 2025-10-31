@extends('adminlte::page')

@section('title', 'Doctores Lista')

@section('content_header')
    <!-- <h1>Pedidos</h1> -->
@stop

@section('content')
@can('enrutamientolista.doctores')
    <div class="row justify-content-md-center">
        <div class="col-12">
            <div class="card mt-2">
                <div class="card-header">
                    <div class="row">
                        <div class="col-4">
                            <h2 class="m-0">Doctores</h2>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @can('enrutamientolista.doctoresupdate')
                    <div class="card card-danger">
                        <div class="card-header">
                            <h5 class="card-title">
                                <i class="fas fa-user-tie"></i>
                                <i class="fas fa-stethoscope"></i>
                                Asignar Doctor a la Semana
                            </h5>
                        </div>
                        <div class="card-body">
                            <form id="form-add-new" class="m-0">
                                <div class="row">
                                    <div class="col-12 col-lg-8">
                                        <div class="form-group position-relative mb-lg-0">
                                            <input type="text" id="name-query" name="name-query" class="form-control"
                                                placeholder="Escriba el nombre de un doctor..." autocomplete="off" />
                                            <div id="suggestions-list"
                                                class="list-group position-absolute overflow-auto border"
                                                style="z-index: 1000; max-height: 200px; width: 100%;"></div>
                                            <input type="hidden" name="id-doctor" id="id-doctor" />
                                        </div>
                                    </div>
                                    <div class="col-12 col-lg-3 form-group mb-lg-0">
                                        <input type="text" id="fecha_visita" name="fecha_visita" class="form-control"
                                            placeholder="Seleccionar fecha" readonly
                                            data-min="{{ $dateRange['start_date'] }}"
                                            data-max="{{ $dateRange['end_date'] }}">
                                    </div>
                                    <div class="col-12 col-lg-1">
                                        <button type="submit" class="btn btn-outline-success mb-lg-0 h-100 w-100">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    @endcan
                    @include('messages')
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a class="btn btn-primary btn-sm" href="{{ route('enrutamiento.agregarlista', $id) }}"><i
                                class="fa fa-arrow-left"></i> Atrás</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped mt-4">
                            <thead>
                                <tr>
                                    <th>Distrito</th>
                                    <th>Doctores</th>
                                    <th>Fecha</th>
                                    <th>Fecha y Hora visitado</th>
                                    <th>Estado</th>
                                    <th>Observaciones</th>
                                    <th>Opciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($doctores as $doctor)
                                    <tr>
                                        <td>{{ $doctor->doctor->distrito->name ? $doctor->doctor->distrito->name : '' }}
                                        </td>
                                        <td>{{ $doctor->doctor->name . ' ' . $doctor->doctor->first_lastname . ' ' . $doctor->doctor->second_lastname }}
                                        </td>
                                        @if ($doctor->estado_visita->id == 4)
                                            <td>{{ $doctor->fecha }}</td>
                                            <td>{{ $doctor->updated_at }}</td>
                                            <td><span class="badge bg-success">{{ $doctor->estado_visita->name }}</span>
                                            </td>
                                            <td>{{ $doctor->observaciones_visita }}</td>
                                            <td>
                                                <button type="button" class="btn btn-success btn-sm btn-ver-mapa"
                                                    data-lat="{{ $doctor->latitude }}" data-lng="{{ $doctor->longitude }}"
                                                    data-nombre="{{ $doctor->doctor->name . ' ' . $doctor->doctor->first_lastname . ' ' . $doctor->doctor->second_lastname }}"
                                                    data-toggle="modal" data-target="#mapModal">
                                                    <i class="fa fa-map-marker" aria-hidden="true">

                                                    </i>
                                                    Ver Mapa
                                                </button>
                                            </td>
                                        @else
                                            @can('enrutamientolista.doctoresupdate')
                                            <form action="{{ route('enrutamientolista.doctoresupdate', $doctor->id) }}"
                                                method="POST">
                                                @csrf
                                                @method('PUT')
                                                <td>
                                                    <input min="{{ $doctor->enrutamientolista->fecha_inicio }}"
                                                        max="{{ $doctor->enrutamientolista->fecha_fin }}" type="date"
                                                        name="fecha" class="form-control" value="{{ $doctor->fecha }}">
                                                </td>
                                                <td></td>
                                                @if ($doctor->estado_visita->id == 1)
                                                    <td><span
                                                            class="badge bg-warning">{{ $doctor->estado_visita->name }}</span>
                                                    </td>
                                                @elseif($doctor->estado_visita->id == 5)
                                                    <td><span
                                                            class="badge bg-secondary">{{ $doctor->estado_visita->name }}</span>
                                                    </td>
                                                @elseif($doctor->estado_visita->id == 3)
                                                    <td><span
                                                            class="badge bg-danger">{{ $doctor->estado_visita->name }}</span>
                                                    </td>
                                                @else
                                                    <td><span
                                                            class="badge bg-primary">{{ $doctor->estado_visita->name }}</span>
                                                    </td>
                                                @endif
                                                <td>{{ $doctor->observaciones_visita }}</td>
                                                <td>
                                                    <button type="submit" class="btn btn-primary btn-sm"><i
                                                            class="fa fa-pencil-square"></i> Actualizar</button>
                                                    {{-- <form action="{{ route('enrutamientolista.doctoresdestroy', $doctor->id) }}" method="POST" style="display:inline;">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de eliminar esta visita?')">
                                                            <i class="fa fa-trash"></i> Eliminar
                                                        </button>
                                                    </form> --}}
                                                </td>

                                            </form>
                                            @endcan
                                        @endif

                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="mapModal" tabindex="-1" role="dialog" aria-labelledby="mapModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Ubicación del registro</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="map" style="height: 400px; width: 100%;"></div>
                </div>
            </div>
        </div>
    </div>
@endcan
@stop

@section('plugins.Sweetalert2', true)
@section('plugins.Flatpickr', true)

@section('css')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />


@section('js')
    <script src="{{ asset('js/sweetalert2-factory.js') }}"></script>
    <script src="{{ asset('js/autocomplete-input.js') }}"></script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        let map = null;
        let marker = null;

        $(document).ready(function() {
            let lat = 0;
            let lng = 0;
            let nombre = '';
            const defaultLat = 19.4326; // CDMX
            const defaultLng = -99.1332;

            $('.btn-ver-mapa').on('click', function() {
                lat = parseFloat($(this).data('lat'));
                lng = parseFloat($(this).data('lng'));
                nombre = $(this).data('nombre');
            });

            // Inicializa el mapa solo cuando el modal se muestra
            $('.btn-ver-mapa').on('click', function() {
                lat = parseFloat($(this).data('lat'));
                lng = parseFloat($(this).data('lng'));
                nombre = $(this).data('nombre');

                // Si lat o lng no son válidos, usa ubicación por defecto
                if (isNaN(lat) || isNaN(lng)) {
                    lat = defaultLat;
                    lng = defaultLng;
                    nombre = "Ubicación no disponible";
                }
            });

            $('#mapModal').on('shown.bs.modal', function() {
                // Limpia si ya hay un mapa anterior
                if (map !== null) {
                    map.remove();
                    map = null;
                }

                // Crear nuevo mapa
                map = L.map('map').setView([lat, lng], 13);

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; OpenStreetMap contributors'
                }).addTo(map);

                marker = L.marker([lat, lng]).addTo(map)
                    .bindPopup(nombre)
                    .openPopup();
            });

            $('#mapModal').on('hidden.bs.modal', function() {
                if (map !== null) {
                    map.remove();
                    map = null;
                }
            });
            $('.table').DataTable({
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json'
                },
                pageLength: 20,
                lengthMenu: [
                    [10, 20, 50, 100, -1],
                    [10, 20, 50, 100, "Todos"]
                ],
                dom: '<"row mb-3"<"col-md-6"l><"col-md-6"Bf>>' +
                    '<"row"<"col-md-12"tr>>' +
                    '<"row mt-3"<"col-md-5"i><"col-md-7"p>>'
            });
        });
    </script>

    <script>
        const pathSegments = window.location.pathname.split('/').filter(Boolean);
        const idEnrutamiento = pathSegments[pathSegments.length - 1];
        const dateInput = document.querySelector('#fecha_visita');
        flatpickr(dateInput, {
            dateFormat: "Y-m-d",
            defaultDate: new Date(),
            minDate: dateInput.dataset.min,
            maxDate: dateInput.dataset.max,
            disableMobile: true,
            locale: "es"
        });

        const doctorIdInput = $('#id-doctor');
        initAutocompleteInput({
            apiUrl: `{{ route('doctors.search') }}`,
            inputSelector: '#name-query',
            listSelector: '#suggestions-list',
            hiddenIdSelector: doctorIdInput,
        });

        $('#form-add-new').on('submit', function(e) {
            e.preventDefault();
            const doctorId = doctorIdInput.val();
            const fechaVisita = $('#fecha_visita').val();

            $.ajax({
                url: `{{ route('visita.doctor.add.spontaneous') }}`,
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    doctor_id: doctorId,
                    enrutamientolista_id: idEnrutamiento,
                    fecha: fechaVisita
                },
                success: function(response) {
                    const icon = response.success ? ToastIcon.SUCCESS : ToastIcon.WARNING;
                    toast(response.message, icon);

                    if (response.success) {
                        setTimeout(() => location.reload(), 1500);
                    }
                },
                error: function(err) {
                    console.error(err);
                    let errorMsg = 'Ocurrió un error al procesar la solicitud.';
                    if (err.responseJSON && err.responseJSON.message) errorMsg = err.responseJSON
                        .message;
                    toast(errorMsg, ToastIcon.ERROR);
                }
            })
        })
    </script>
@stop
