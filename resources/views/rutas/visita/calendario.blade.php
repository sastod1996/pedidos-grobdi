@extends('adminlte::page')

@section('title', 'Mis rutas')

@section('content_header')
<h1>Calendario de visitas</h1>
@stop

@section('content')
@can('enrutamientolista.calendariovisitadora')
<div class="row">
    <div class="col-sm-12">
        <div class="card">
            <div class="card-header">
                <label>Indicadores de Pedidos</label>
            </div>
            <div class="card-body">
                <div id="calendar"></div>
                <div class="mt-4">
                    <h5>Leyenda de estados:</h5>
                    <div class="d-flex flex-wrap gap-3">
                        @foreach ($estados as $estado)
                        <div class="d-flex align-items-center">
                            <span style="display:inline-block; width:20px; height:20px; background-color:{{ $estado->color }}; border-radius:4px; margin-right:8px;"></span>
                            <span>{{ $estado->name }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
                <div class="modal fade" id="doctorModal" tabindex="-1" aria-labelledby="doctorModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="doctorModalLabel">Información del Doctor</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                            </div>
                            <div class="modal-body" id="doctor-info">
                                @can('rutas.guardarvisita')
                                <form id="form-visita">
                                    <div id="info-doctor"></div>

                                    <div class="mb-3">
                                        <label for="estado" class="form-label">Estado de Visita</label>
                                        <select name="estado_visita_id" id="estado" class="form-select"></select>
                                    </div>

                                    <div class="mb-3">
                                        <label for="observaciones" class="form-label">Observaciones</label>
                                        <textarea name="observaciones" id="observaciones" class="form-control"></textarea>
                                    </div>
                                    <div class="mb-3" id="fecha_visita_group" style="display: none;">
                                        <label for="fecha_visita" class="form-label">Fecha reprogramada:</label>
                                        <input type="text" id="fecha_visita" name="fecha_visita" class="form-control" placeholder="Selecciona una fecha">
                                    </div>
                                    <input type="hidden" name="doctor_id" id="doctor_id">
                                    <input type="hidden" name="visita_id" id="visita_id">
                                    <input type="hidden" name="latitude" id="latitude">
                                    <input type="hidden" name="longitude" id="longitude">

                                    <div class="text-end">
                                        <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                                    </div>
                                </form>
                                @endcan
                            </div>
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
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Llenar estado
    const estadoSelect = document.getElementById('estado');
    const fechaGroup = document.getElementById('fecha_visita_group');
    const fechaInput = document.getElementById('fecha_visita');
    const estadoReprogramadoId = 5; // Cambia este valor si es diferente en tu BD

    document.getElementById('form-visita').addEventListener('submit', function(e) {
        const estado = parseInt(document.getElementById('estado').value);
        const fecha = document.getElementById('fecha_visita').value;

        if (estado === estadoReprogramadoId && fecha.trim() === '') {
            e.preventDefault();
            alert('Debe seleccionar una fecha para la reprogramación.');
            document.getElementById('fecha_visita').focus();
        }
    });
    // Función que muestra u oculta el campo
    function toggleFechaField() {
        const estadoId = parseInt(estadoSelect.value);
        if (estadoId === estadoReprogramadoId) {
            fechaGroup.style.display = 'block';
        } else {
            fechaGroup.style.display = 'none';
            fechaInput.value = ''; // Limpiar campo si no es "Reprogramado"
        }
    }
    estadoSelect.addEventListener('change', toggleFechaField);

    function mostrarDoctor(id) {

        fetch('/rutasdoctor/' + id)
            .then(res => res.json())
            .then(data => {
                const doctor = data.doctor;
                const visita = data.visita;
                const estados = data.estados;
                const turno = data.turno;
                const estadoActual = visita?.estado_visita_id;
                const estadosPermitidos = [2, 5];
                const fechaVisita = visita?.fecha;
                const latitude = doctor.centro_salud.latitude ?? '-12.000905'; // Valor por defecto si no hay centro de salud
                const longitude = doctor.centro_salud.longitude?? '-71.227592';
                const hoy = new Date().toISOString().split('T')[0]; // formato YYYY-MM-DD
                const esHoy = fechaVisita === hoy;
                if (estadoActual && !estadosPermitidos.includes(estadoActual)) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Estado no editable',
                        text: 'Solo se puede editar una visita con estado Asignado o Reprogramado.',
                    });
                    return;
                }

                document.getElementById('info-doctor').innerHTML = `
                        <h5>${doctor.name}</h5>
                        <p><strong>CMP:</strong> ${doctor.CMP}</p>
                        <p><strong>Teléfono:</strong> ${doctor.phone ?? 'No registrado'}</p>
                        <p><strong>Distrito:</strong> ${doctor.distrito?.name ?? 'No asignado'}</p>
                        <p><strong>Especialidad:</strong> ${doctor.especialidad?.name ?? 'No asignada'}</p>
                        <p><strong>Centro de Salud:</strong> ${doctor.centro_salud?.name ?? 'No asignado'}</p>
                        <a href="https://www.google.com/maps?q=${latitude},${longitude}" target="_blank">Abrir ubicación en Google Maps</a>
                        <p><strong>Turno:</strong> ${turno ?? 'No asignado'}</p>
                    `;
                // Select de estados según reglas
                const estadoSelect = document.getElementById('estado');
                estadoSelect.innerHTML = ''; // limpiar

                estados.forEach(e => {
                    if (esHoy && (e.name === 'Visitado' || e.name === 'No Visitado' || e.name === 'Reprogramado')) {
                        estadoSelect.innerHTML += `<option value="${e.id}" ${visita?.estado_visita_id == e.id ? 'selected' : ''}>
                                ${e.name}
                            </option>`;
                    } else if (!esHoy && e.name === 'Reprogramado') {
                        estadoSelect.innerHTML += `<option value="${e.id}" ${visita?.estado_visita_id == e.id ? 'selected' : ''}>
                                ${e.name}
                            </option>`;
                    }
                });

                // Set campos ocultos
                document.getElementById('doctor_id').value = doctor.id;
                document.getElementById('visita_id').value = visita?.id ?? '';
                document.getElementById('fecha_visita').value = visita?.fecha_visita ?? '';
                document.getElementById('observaciones').value = visita?.observaciones_visita ?? '';
                flatpickr("#fecha_visita", {
                    dateFormat: "Y-m-d",
                    minDate: data.rango.fecha_inicio,
                    maxDate: data.rango.fecha_fin,
                });
                toggleFechaField();
                // Mostrar modal
                const modal = new bootstrap.Modal(document.getElementById('doctorModal'));
                modal.show();
            });
    }

    let calendar;
    document.addEventListener('DOMContentLoaded', function() {
        if ("geolocation" in navigator) {
            navigator.geolocation.getCurrentPosition(function(position) {
                document.getElementById('latitude').value = position.coords.latitude;
                document.getElementById('longitude').value = position.coords.longitude;
            }, function(error) {
                console.error("Error al obtener ubicación:", error.message);
            });
        } else {
            console.error("Geolocalización no es compatible con este navegador.");
        }

        const mediaQueryMovil = window.matchMedia('(max-width: 768px)');

        function obtenerVistaInicial() {
            return mediaQueryMovil.matches ? 'listMonth' : 'dayGridMonth';
        }

        function aplicarVistaResponsiva() {
            if (!calendar) {
                return;
            }
            const vistaObjetivo = obtenerVistaInicial();
            if (calendar.view.type !== vistaObjetivo) {
                calendar.changeView(vistaObjetivo);
            }
        }

        const calendarEl = document.getElementById('calendar');
        calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: obtenerVistaInicial(),
            locale: 'es',
            editable: true,
            hiddenDays: [0, 6], // Oculta domingos (0) y sábados (6)
            buttonText: {
                today: 'Hoy',
                month: 'Mes',
                list: 'Lista'
            },
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,listMonth'
            },
            events: @json($eventos),
            eventOrder: 'extendedProps.turno',
            eventDidMount(info) {
                try {
                    const turno = info.event.extendedProps?.turno ?? '';
                    if (!info.view.type.startsWith('list')) return;
                    const timeCell = info.el.querySelector('.fc-list-event-time');
                    if (!timeCell) return;
                    const txt = timeCell.textContent.trim().toLowerCase();
                    const normalized = txt
                        .replace(/\s+/g, ' ')
                        .replace('todo el día', 'todo el dia')
                        .replace('todo el día', 'todo el dia');
                    const ALL_DAY_VARIANTS = ['all-day', 'all day', 'todo el dia', 'todo el día', 'todo el día'.toLowerCase()];
                    if (ALL_DAY_VARIANTS.includes(txt) || ALL_DAY_VARIANTS.includes(normalized)) {
                        timeCell.textContent = turno || '';
                    }
                } catch (e) {
                    console.warn('eventDidMount error:', e);
                }
            },
            eventClick: function(info) {
                mostrarDoctor(info.event.id);
            }
        });
        calendar.render();

        if (typeof mediaQueryMovil.addEventListener === 'function') {
            mediaQueryMovil.addEventListener('change', aplicarVistaResponsiva);
        } else if (typeof mediaQueryMovil.addListener === 'function') {
            mediaQueryMovil.addListener(aplicarVistaResponsiva);
        }
        aplicarVistaResponsiva();
        // Para doctores sin fecha asignada
        // document.querySelectorAll('.detalle-doctor').forEach(el => {
        //     el.addEventListener('click', function(e) {
        //         e.preventDefault();
        //         mostrarDoctor(this.dataset.id);
        //     });
        // });
    });
    document.getElementById('form-visita').addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);

        fetch('/guardar-visita', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: formData
            })
            .then(res => {
                if (!res.ok) {
                    // Si la respuesta no es OK (por ejemplo, 404), lanza el error del JSON
                    return res.json().then(err => {
                        throw err
                    });
                }
                return res.json();
            })
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        position: "top-end",
                        type: 'success',
                        title: '¡Éxito!',
                        text: 'Visita actualizada correctamente',
                        showConfirmButton: false,
                        timer: 2000
                    });
                    // Ocultar modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('doctorModal'));
                    modal.hide();

                    const existingEvent = calendar.getEventById(data.visita_id.toString());
                    console.log(existingEvent);
                    if (existingEvent) {
                        existingEvent.remove(); // borra el antiguo
                    }

                    // agrega uno nuevo
                    calendar.addEvent({
                        id: data.visita_id.toString(),
                        title: data.doctor_name,
                        start: data.fecha_visita,
                        color: data.color
                    });
                }
            })
            .catch(err => {
                // Mostrar el error devuelto por Laravel
                if (err.error) {
                    Swal.fire({
                        icon: 'error',
                        title: '¡Error!',
                        text: err.error
                    });
                } else {
                    console.error("Error inesperado:", err);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Ocurrió un error al guardar la visita.'
                    });
                }
            });
    });
</script>
@stop
