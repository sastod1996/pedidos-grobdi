@extends('adminlte::page')

@section('title', 'Rutas Visitadora')

@section('content_header')
    <h1></h1>
@stop

@section('content')
@can('rutasvisitadora.listadoctores')
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header">
                    <label>Lista de Doctores de: {{ $semana_ruta->lista->name }} ({{ $fecha_inicio }} al {{ $fecha_fin }})</label>
                </div>
                <div class="card-body">
                    @can('rutasvisitadora.guardardoctor')
                    <div class="mb-3">
                        <button type="button" class="btn btn-success" data-toggle="modal" data-target="#crearDoctor">Agregar Doctor</button>
                    </div>
                    @endcan
                    @php
                        $centrosSaludOpciones = collect($centrosSaludFiltro ?? [])->filter();
                        $distritosOpciones = collect($distritosFiltro ?? [])->filter();
                    @endphp
                    <div class="row mb-3">
                        <div class="col-md-4 col-lg-3">
                            <label for="filtroCentro" class="form-label mb-1">Centro de Salud</label>
                            <select id="filtroCentro" class="form-control">
                                <option value="">Todos</option>
                                @foreach ($centrosSaludOpciones as $centro)
                                    <option value="{{ $centro }}">{{ $centro }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 col-lg-3">
                            <label for="filtroDistrito" class="form-label mb-1">Distrito</label>
                            <select id="filtroDistrito" class="form-control">
                                <option value="">Todos</option>
                                @foreach ($distritosOpciones as $distritoNombre)
                                    <option value="{{ $distritoNombre }}">{{ $distritoNombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 col-lg-3">
                            <label for="filtroEstado" class="form-label mb-1">Estado</label>
                            <select id="filtroEstado" class="form-control">
                                <option value="">Todos</option>
                                @php
                                    $estadosOpciones = collect($visitadoctores)->map(function ($visita) {
                                        if ($visita->reprogramar == 1) {
                                            return 'Reprogramada';
                                        } else {
                                            return $visita->estado_visita->name;
                                        }
                                    })->unique()->filter()->sort()->values();
                                @endphp
                                @foreach ($estadosOpciones as $estadoNombre)
                                    <option value="{{ $estadoNombre }}">{{ $estadoNombre }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="table table-responsive">
                        <table class="table table-grobdi" id="miTabla">
                            <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th>Centro de Salud</th>
                                    <th>Distrito</th>
                                    <th>Estado</th>
                                    <th>Fecha Visita</th>
                                    <th>Turno</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($visitadoctores as $visitadoctor)
                                <tr>
                                    @php
                                        $doctor = optional($visitadoctor->doctor);
                                        $categoriaDoctor = optional($doctor->categoriadoctor)->name;
                                    @endphp
                                    <td>{{ $categoriaDoctor ? $categoriaDoctor . ' - ' : '' }}{{ $doctor->name ?? 'Sin asignar' }}</td>
                                    <td>{{ optional($doctor->centrosalud)->name ?? '—' }}</td>
                                    <td>{{ optional($doctor->distrito)->name ?? '—' }}</td>
                                    @if ($visitadoctor->reprogramar == 1)
                                        <td><span class="badge bg-secondary">Reprogramada</span></td>
                                    @elseif ( $visitadoctor->estado_visita->id  == 1)
                                        <td><span class="badge bg-warning">{{ $visitadoctor->estado_visita->name }}</span></td>
                                    @elseif($visitadoctor->estado_visita->id == 5)
                                        <td><span class="badge bg-secondary">{{ $visitadoctor->estado_visita->name }}</span></td>
                                    @elseif($visitadoctor->estado_visita->id == 3)
                                        <td><span class="badge bg-danger">{{ $visitadoctor->estado_visita->name }}</span></td>
                                    @elseif($visitadoctor->estado_visita->id == 4)
                                        <td><span class="badge bg-primary">{{ $visitadoctor->estado_visita->name }}</span></td>
                                    @elseif($visitadoctor->estado_visita->id == 6)
                                        <td><span class="badge bg-info">{{ $visitadoctor->estado_visita->name }}</span></td>
                                    @else
                                        <td><span class="badge bg-primary">{{ $visitadoctor->estado_visita->name }}</span></td>
                                    @endif
                                    <td>{{ $visitadoctor->fecha ?? '—' }}</td>
                                    <td>{{ $visitadoctor->turno ? 'Tarde' : 'Mañana' }}</td>
                                    @if ($visitadoctor->reprogramar == 1)
                                        <td></td>
                                    @elseif ($visitadoctor->estado_visita->name == 'Asignado')
                                        <td>
                                            @can('rutasvisitadora.reprogramar')
                                            <button class="btn btn-warning btn-reprogramar"
                                                data-id="{{ $visitadoctor->id }}"
                                                data-nombre="{{ $visitadoctor->doctor->name }}">
                                                Reprogramar
                                            </button>
                                            @endcan
                                        </td>
                                    @elseif ($visitadoctor->estado_visita_id == 1)
                                        <td>
                                            @can('rutasvisitadora.asignar')
                                            <button class="btn btn-success btn-asignar"
                                                data-id="{{ $visitadoctor->id }}"
                                                data-nombre="{{ $visitadoctor->doctor->name }}">
                                                Asignar
                                            </button>
                                            @endcan
                                        </td>
                                    @else
                                        <td></td>
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
<!-- Modal reprogramar visita -->
@can('rutasvisitadora.reprogramar')
<div class="modal fade" id="modalReprogramar" tabindex="-1" aria-labelledby="modalReprogramarLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form id="formReprogramar">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalReprogramarLabel">Reprogramar Visita</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="visitadoctor_id_reprogramar" name="visitadoctor_id_reprogramar">
                <div class="mb-3">
                    <label for="fecha_reprogramar" class="form-label">Nueva Fecha de Visita</label>
                    <input type="date" class="form-control" id="fecha_reprogramar" name="fecha_reprogramar" min="{{ $fecha_inicio }}" max="{{ $fecha_fin }}" required>
                </div>
                <div class="mb-3">
                    <label for="turno_reprogramar" class="form-label">Turno</label>
                    <select class="form-control" name="turno_reprogramar" id="turno_reprogramar" required>
                        <option value="0">Mañana</option>
                        <option value="1">Tarde</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">Guardar</button>
            </div>
        </div>
        </form>
    </div>
</div>
@endcan

<!-- Modal asignar visita -->
@can('rutasvisitadora.asignar')
<div class="modal fade" id="modalAsignar" tabindex="-1" aria-labelledby="modalAsignarLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form id="formAsignar">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalAsignarLabel">Asignar Visita</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="visitadoctor_id" name="visitadoctor_id">
                <div class="mb-3">
                    <label for="fecha" class="form-label">Fecha de Visita</label>
                    <input type="date" class="form-control" id="fecha" name="fecha" min="{{ $fecha_inicio }}" max="{{ $fecha_fin }}" required>
                </div>
                <div class="mb-3">
                    <label for="turno" class="form-label">Turno</label>
                    <select class="form-control" name="turno" id="turno" required>
                        <option value="0">Mañana</option>
                        <option value="1">tarde</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">Guardar</button>
            </div>
        </div>
        </form>
    </div>
</div>
@endcan
<!-- Modal crear doctor -->
@can('rutasvisitadora.guardardoctor')
<div class="modal fade" id="crearDoctor" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Crear Doctor</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="erroresDoctor" class="alert alert-danger d-none"></div>
                <form>
                    <div class="form-group row">
                        <label class="form-label col-2">CMP:</label>
                        <div class="col-8">
                            <input type="text" class="form-control" name="CMP" placeholder="Ingresar CMP">
                        </div>
                        <button type="button" id="btnBuscarCMP" class="btn btn-primary col-2">Validar</button>
                    </div>
                    <div class="form-group row">
                        <label class="col-2">Apellido Paterno:</label>
                        <div class="col-4">
                            <input type="text" class="form-control" name="first_lastname" placeholder="apellido paterno del doctor" disabled>
                        </div>
                        <label class="col-2">Apellido Materno:</label>
                        <div class="col-4">
                            <input type="text" class="form-control" name="second_lastname" placeholder="apellido materno del doctor" disabled>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-2">Nombre:</label>
                        <div class="col-10">
                            <input type="text" class="form-control" name="name" placeholder="Nombre del doctor" disabled>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-2">Centro de Salud:</label>
                        <div class="col-10">
                            <select id="centrosalud_id" name="centrosalud_id" class="form-control" style="width: 100%;">
                            </select>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-2">Telefono:</label>
                        <div class="col-4">
                            <input type="text" class="form-control" name="phone" placeholder="Ingresar telefono celular" required>
                        </div>
                        <label class="col-2">Fecha de nacimiento:</label>
                        <div class="col-4">
                            <input type="date" class="form-control" name="birthdate" placeholder="Ingresar fecha de nacimiento">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-2">Especialidad:</label>
                        <div class="col-4">
                            <select class="form-control" name="especialidad_id">
                                <option value="" selected disabled>Seleccione</option>
                                @foreach ( $especialidades as $especialidad)
                                <option value="{{ $especialidad->id }}">{{ $especialidad->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <label class="col-2">Distrito:</label>
                        <div class="col-4">
                            <select class="form-control" name="distrito_id">
                                <option value="" selected disabled>Seleccione</option>
                                @foreach ($distritos as $distrito)
                                <option value="{{ $distrito->id }}">{{ $distrito->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="categoria" class="form-label col-2"><strong>Categoría Médico:</strong></label>
                        <div class="col-4">
                            <select class="form-control" aria-label="categoria" name="categoria_medico">
                                <option value="" selected disabled>Seleccione</option>
                                <option value="empresa">Empresa</option>
                                <option value="visitador">Visitador</option>
                            </select>
                        </div>
                        <label for="hijos" class="form-label col-2"><strong>¿Tiene hijos?</strong></label>
                        <div class="col-4">
                            <select class="form-control" aria-label="hijos" name="songs">
                                <option value="" selected disabled>Seleccione</option>
                                <option value="0">No</option>
                                <option value="1">Si</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-2">Nombre secretaria:</label>
                        <div class="col-4">
                            <input type="text" class="form-control" name="name_secretariat" placeholder="Ingresar el nombre de la secretaria">
                        </div>
                        <label class="col-2">Telefono secreataria:</label>
                        <div class="col-4">
                            <input type="text" class="form-control" name="phone_secretariat" placeholder="Ingresar telefono de la secretaria">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-2">Fecha de visita:</label>
                        <div class="col-4">
                            <input
                                type="date"
                                class="form-control"
                                name="fecha_visita"
                                placeholder="Ingresar la fecha que fue visitado"
                                min="{{ $fecha_inicio }}"
                                max="{{ $fecha_fin }}"
                            >
                        </div>
                        <label for="observaciones" class="form-label col-2">Observaciones</label>
                        <textarea name="observaciones" id="observaciones" class="form-control col-4"></textarea>
                    </div>
                    <div class="form-group row">
                        <label for="tipo_medico" class="form-label col-2"><strong>Días disponible:</strong></label>
                        @foreach ($dias as $dia)
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="checkbox" value="{{ $dia->id }}" id="dia_{{ $dia->id }}" name="dias[]">
                                <label class="form-check-label" for="dia_{{ $dia->id }}">
                                    {{ $dia->name }}
                                </label>
                                <br>
                            </div>
                        @endforeach
                    </div>
                    <input type="hidden" name="id_enrutamientolista" value="{{ $semana_ruta->id }}">
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                <button type="button" id="btnGuardarDoctor" class="btn btn-primary">Guardar</button>
            </div>
        </div>
    </div>
</div>
@endcan
@endcan
@stop

@section('css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            const tabla = $('#miTabla').DataTable({
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.10.19/i18n/Spanish.json'
                },
                pageLength: 25, // 👈 Número por defecto (puedes cambiar a 25, 50, etc.)
                lengthMenu: [ [10, 25, 50, -1], [10, 25, 50, "Todos"] ], // Opciones de cantidad
                columnDefs: [
                    { orderable: false, targets: -1 }
                ]
            });

            $('#filtroCentro').on('change', function () {
                const valor = $(this).val();
                tabla.column(1).search(valor ? '^' + $.fn.dataTable.util.escapeRegex(valor) + '$' : '', true, false).draw();
            });

            $('#filtroDistrito').on('change', function () {
                const valor = $(this).val();
                tabla.column(2).search(valor ? '^' + $.fn.dataTable.util.escapeRegex(valor) + '$' : '', true, false).draw();
            });

            $('#filtroEstado').on('change', function () {
                const valor = $(this).val();
                tabla.column(3).search(valor ? '^' + $.fn.dataTable.util.escapeRegex(valor) + '$' : '', true, false).draw();
            });

            // Abre modal y pasa el ID
            $('.btn-asignar').on('click', function () {
                let id = $(this).data('id');
                $('#visitadoctor_id').val(id);
                $('#modalAsignar').modal('show');
            });

            // Abre modal reprogramar y pasa el ID
            $('.btn-reprogramar').on('click', function () {
                let id = $(this).data('id');
                $('#visitadoctor_id_reprogramar').val(id);
                $('#modalReprogramar').modal('show');
            });
            // Envía formulario por AJAX para asignar
            $('#formAsignar').on('submit', function (e) {
                e.preventDefault();

                let formData = {
                    _token: '{{ csrf_token() }}',
                    id: $('#visitadoctor_id').val(),
                    fecha: $('#fecha').val(),
                    turno: $('#turno').val()
                };

                $.ajax({
                    url: '{{ route("rutasvisitadora.asignar") }}', // Ruta backend
                    type: 'POST',
                    data: formData,
                    success: function (response) {
                        $('#modalAsignar').modal('hide');

                        Swal.fire({
                            icon: 'success',
                            title: 'Asignado correctamente',
                            showConfirmButton: false,
                            timer: 1500
                        });

                        setTimeout(() => location.reload(), 1500);
                    },
                    error: function (xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: xhr.responseText || 'Ocurrió un error al guardar.'
                        });
                    }
                });
            });

            // Envía formulario por AJAX para reprogramar
            $('#formReprogramar').on('submit', function (e) {
                e.preventDefault();

                let formData = {
                    _token: '{{ csrf_token() }}',
                    id: $('#visitadoctor_id_reprogramar').val(),
                    fecha: $('#fecha_reprogramar').val(),
                    turno: $('#turno_reprogramar').val(),
                    reprogramar: true
                };

                $.ajax({
                    url: '{{ route("rutasvisitadora.reprogramar") }}', // Ruta backend (deberás crearla)
                    type: 'POST',
                    data: formData,
                    success: function (response) {
                        $('#modalReprogramar').modal('hide');

                        Swal.fire({
                            icon: 'success',
                            title: 'Reprogramado correctamente',
                            showConfirmButton: false,
                            timer: 1500
                        });

                        setTimeout(() => location.reload(), 1500);
                    },
                    error: function (xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: xhr.responseText || 'Ocurrió un error al reprogramar.'
                        });
                    }
                });
            });
            $('#crearDoctor').on('shown.bs.modal', function () {
                $('#centrosalud_id').select2({
                    dropdownParent: $('#crearDoctor'),
                    placeholder: 'Buscar centro de salud...',
                    allowClear: true,
                    ajax: {
                        url: "{{ route('centrosalud.buscar') }}",
                        dataType: 'json',
                        delay: 250,
                        data: function (params) {
                            return {
                                term: params.term
                            };
                        },
                        processResults: function (data) {
                            return {
                                results: data
                            };
                        },
                        cache: true
                    }
                });
            });
            $('#crearDoctor').on('hidden.bs.modal', function () {
                // Limpiar todos los inputs, selects y textarea dentro del modal
                $(this).find('form')[0].reset();

                // Si usas select2, también hay que resetearlo manualmente
                $(this).find('select').val(null).trigger('change');
            });
            $("#btnBuscarCMP").click(function(){
                let cmp = $("input[name='CMP']").val();

                if(cmp.trim() === ""){
                    alert("Ingrese un CMP");
                    return;
                }

                $.ajax({
                    url: `/rutasvisitadora/buscardoctor/${cmp}`,
                    method: 'GET',
                    success: function(response){
                        if(response.success){
                            $("input[name='first_lastname']").val(response.data[2]);
                            $("input[name='second_lastname']").val(response.data[3]);
                            $("input[name='name']").val(response.data[4]);
                        } else {
                            alert(response.message);
                        }
                    },
                    error: function(xhr){
                        alert(xhr.responseJSON?.message || "Error al buscar el CMP");
                    }
                });
            });
            // Botón guardar doctor
            $("#btnGuardarDoctor").click(function(){
                let diasSeleccionados = [];
                $("input[name='dias[]']:checked").each(function(){
                    diasSeleccionados.push($(this).val());
                });
                let formData = {
                    CMP: $("input[name='CMP']").val(),
                    first_lastname: $("input[name='first_lastname']").val(),
                    second_lastname: $("input[name='second_lastname']").val(),
                    name: $("input[name='name']").val(),
                    phone: $("input[name='phone']").val(),
                    birthdate: $("input[name='birthdate']").val(),
                    distrito_id: $("select[name='distrito_id']").val(),
                    especialidad_id: $("select[name='especialidad_id']").val(),
                    categoria_medico: $("select[name='categoria_medico']").val(),
                    songs: $("select[name='songs']").val(),
                    id_enrutamientolista: $("input[name='id_enrutamientolista']").val(),
                    name_secretariat: $("input[name='name_secretariat']").val(),
                    phone_secretariat: $("input[name='phone_secretariat']").val(),
                    fecha_visita: $("input[name='fecha_visita']").val(),
                    observaciones: $("textarea[name='observaciones']").val(),
                    centrosalud_id: $("select[name='centrosalud_id']").val(),
                    dias: diasSeleccionados, // Array con los días seleccionados
                    _token: "{{ csrf_token() }}" // Para Laravel
                };

                $.ajax({
                    url: '/rutasvisitadora/doctores',
                    method: 'POST',
                    data: formData,
                    success: function(response){
                        if(response.success){
                            alert(response.message);
                            $("#crearDoctor").modal('hide');
                        } else {
                            alert("No se pudo guardar el doctor");
                        }
                    },
                    error: function(xhr){
                        if(xhr.status === 422){
                            let errores = xhr.responseJSON.errors;
                            let mensaje = "";
                            for (let campo in errores) {
                                mensaje += errores[campo].join("<br>") + "<br>";
                            }
                            $("#erroresDoctor").removeClass("d-none").html(mensaje);
                        }
                    }
                });
            });
        });
    </script>

@stop
