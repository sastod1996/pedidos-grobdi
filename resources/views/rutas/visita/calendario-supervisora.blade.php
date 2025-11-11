@extends('adminlte::page')

@section('title', 'Calendario Supervisora')


@section('content')
    <div class="calendar-supervisor">
        @if ($visitadoras->isEmpty())
            <div class="alert alert-warning shadow-sm">Aún no se registran visitadoras. Asigna visitadoras a las zonas para
                visualizar el calendario.</div>
        @else
            <section class="grobdi-header">
                <div class="grobdi-title">
                    <div class="calendar-heading">
                        <h2 class="mb-1">Agenda del equipo</h2>
                        <p class="mb-0">Administra las visitas asignadas, reprogramaciones y cierres diarios por
                            visitadora.</p>
                    </div>
                    <div class="calendar-controls">
                        <div class="calendar-control">
                            <select id="visitadora-filter" class="form-select">
                                @foreach ($visitadoras as $visitadora)
                                    <option value="{{ $visitadora->id }}" @selected((string) $visitadora->id === (string) $selectedVisitadoraId)>
                                        {{ $visitadora->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="grobdi-filter calendar-filter">
                    <div class="row g-3 align-items-end">
                        @foreach ($estadisticasEstados as $estado)
                            <div class="col-12 col-sm-6 col-lg-3">
                                <div class="calendar-state">
                                    <span class="calendar-state__dot"
                                        style="background-color: {{ $estado['color'] }}"></span>
                                    <div class="calendar-state__info">
                                        <span class="calendar-state__label">{{ $estado['name'] }}</span>
                                        <span class="calendar-state__value"
                                            id="metric-state-{{ $estado['id'] }}">{{ $estado['count'] }}</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>

            <div class="calendar-layout">
                <div class="calendar-panel">
                    <div id="calendar-supervisor"></div>
                    <div id="empty-state" class="empty-state {{ $totalVisitas > 0 ? 'd-none' : '' }}">
                        <div class="empty-state__icon">📅</div>
                        <h5 class="empty-state__title">Sin visitas programadas</h5>
                        <p class="empty-state__text">Selecciona otra visitadora o planifica nuevas visitas para visualizar
                            su agenda.</p>
                    </div>
                </div>
                {{-- <aside class="calendar-sidebar">
                <header class="calendar-sidebar__header">
                    <h5 class="mb-1">Estados operativos</h5>
                    <p class="mb-0 text-muted">Referencias cromáticas aplicadas en el calendario.</p>
                </header>
                <ul class="calendar-legend list-unstyled mb-0">
                    @foreach ($estados as $estado)
                        <li class="calendar-legend__item">
                            <span class="calendar-legend__dot" style="background-color: {{ $estado->color }}"></span>
                            <span class="calendar-legend__label">{{ $estado->name }}</span>
                        </li>
                    @endforeach
                </ul>
            </aside> --}}
            </div>

            <div class="modal fade modal-grobdi" id="doctorModal" tabindex="-1" aria-labelledby="doctorModalLabel"
                aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <div>
                                <h5 class="modal-title" id="doctorModalLabel">Detalle de la visita</h5>
                                <span class="modal-subtitle">Gestiona la información operativa, observaciones y
                                    reprogramaciones.</span>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>
                        <div class="modal-body">
                            <form id="form-visita" class="modal-form">
                                <div id="info-doctor" class="info-doctor mb-4"></div>
                                <div class="row g-3">
                                    <div class="col-md-6 form-group">
                                        <label for="estado" class="form-label fw-semibold">Estado de visita</label>
                                        <select name="estado_visita_id" id="estado"
                                            class="form-select grobdi-input"></select>
                                    </div>
                                    <div class="col-md-6 form-group d-none" id="fecha_visita_group">
                                        <label for="fecha_visita" class="form-label fw-semibold">Nueva fecha de
                                            visita</label>
                                        <input type="text" id="fecha_visita" name="fecha_visita"
                                            class="form-control grobdi-input" placeholder="Selecciona una fecha">
                                    </div>
                                </div>
                                <div class="mt-3 form-group">
                                    <label for="observaciones" class="form-label fw-semibold">Observaciones</label>
                                    <textarea name="observaciones" id="observaciones" class="form-control grobdi-input" rows="3"
                                        placeholder="Añade notas relevantes para la supervisión"></textarea>
                                </div>
                                <input type="hidden" name="doctor_id" id="doctor_id">
                                <input type="hidden" name="visita_id" id="visita_id">
                                <input type="hidden" name="latitude" id="latitude">
                                <input type="hidden" name="longitude" id="longitude">
                            </form>
                        </div>
                        <div class="modal-footer justify-between">
                            <button type="button" class="btn-grobdi btn-outline-grobdi"
                                data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" form="form-visita" class="btn-grobdi btn-primary-grobdi">Guardar
                                cambios</button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
@stop

@section('css')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/locales-all.global.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const calendarElement = document.getElementById('calendar-supervisor');
            const emptyState = document.getElementById('empty-state');
            const visitadoraSelect = document.getElementById('visitadora-filter');
            const calendarPanel = document.querySelector('.calendar-panel');
            const estadoSelect = document.getElementById('estado');
            const fechaGroup = document.getElementById('fecha_visita_group');
            const fechaInput = document.getElementById('fecha_visita');
            const formVisita = document.getElementById('form-visita');
            const estadosData = @json($estados);
            const initialEvents = @json($eventos);
            const initialMetrics = {
                total: {{ $totalVisitas }},
                estados: @json($estadisticasEstados)
            };
            const eventosUrl = '{{ route('enrutamientolista.calendariosupervisora.eventos') }}';
            const estadoReprogramadoId = {{ (int) $estadoReprogramadoId }};
            let calendar;
            let fechaPicker;

            const metricStateMap = new Map(initialMetrics.estados.map((item) => [item.id, item.count]));

            function cerrarPopoversCalendar() {
                document.querySelectorAll('.fc-popover').forEach((popover) => popover.remove());
            }

            function toggleEmptyState() {
                if (!calendar || !emptyState) {
                    return;
                }
                if (calendar.getEvents().length === 0) {
                    emptyState.classList.remove('d-none');
                } else {
                    emptyState.classList.add('d-none');
                }
            }

            function actualizarMetricas(metrics) {
                const totalElement = document.getElementById('metric-total');
                if (totalElement) {
                    totalElement.textContent = metrics.total ?? 0;
                }
                const valores = new Map((metrics.estados ?? []).map((estado) => [estado.id, estado.count]));
                metricStateMap.forEach((_, estadoId) => {
                    const stateElement = document.getElementById(`metric-state-${estadoId}`);
                    if (stateElement) {
                        stateElement.textContent = valores.has(estadoId) ? valores.get(estadoId) : 0;
                    }
                });
            }

            function toggleFechaField() {
                if (!estadoSelect || !fechaGroup) {
                    return;
                }
                const estadoSeleccionado = parseInt(estadoSelect.value, 10);
                if (estadoSeleccionado === estadoReprogramadoId) {
                    fechaGroup.classList.remove('d-none');
                } else {
                    fechaGroup.classList.add('d-none');
                    if (fechaInput) {
                        fechaInput.value = '';
                    }
                }
            }

            async function cargarEventos(visitadoraId) {
                if (!calendar || !visitadoraId) {
                    if (calendar) {
                        calendar.removeAllEvents();
                    }
                    actualizarMetricas({
                        total: 0,
                        estados: []
                    });
                    toggleEmptyState();
                    return;
                }

                try {
                    visitadoraSelect.disabled = true;
                    if (calendarPanel) {
                        calendarPanel.classList.add('is-loading');
                    }
                    const response = await fetch(`${eventosUrl}?visitadora_id=${visitadoraId}`);
                    if (!response.ok) {
                        throw new Error('No se pudo cargar la agenda.');
                    }
                    const data = await response.json();
                    calendar.removeAllEvents();
                    (data.events || []).forEach((event) => calendar.addEvent(event));
                    actualizarMetricas(data.metrics ?? {
                        total: 0,
                        estados: []
                    });
                } catch (error) {
                    console.error(error);
                    if (window.Swal) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'No se pudieron cargar las visitas de la visitadora seleccionada.'
                        });
                    }
                } finally {
                    if (calendarPanel) {
                        calendarPanel.classList.remove('is-loading');
                    }
                    visitadoraSelect.disabled = false;
                    toggleEmptyState();
                }
            }

            function construirOpcionesEstado(visita) {
                if (!estadoSelect) {
                    return;
                }
                estadoSelect.innerHTML = '';
                estadosData.forEach((estado) => {
                    const option = document.createElement('option');
                    option.value = estado.id;
                    option.textContent = estado.name;
                    if (visita && visita.estado_visita_id === estado.id) {
                        option.selected = true;
                    }
                    estadoSelect.appendChild(option);
                });
            }

            async function mostrarDoctor(id) {
                try {
                    cerrarPopoversCalendar();
                    const response = await fetch(`/rutasdoctor/${id}`);
                    if (!response.ok) {
                        throw new Error('No se encontró información del doctor');
                    }
                    const data = await response.json();
                    const doctor = data.doctor;
                    const visita = data.visita;
                    const turno = data.turno;
                    const fechaVisita = visita?.fecha ?? null;

                    const latitude = doctor?.centro_salud?.latitude ?? '-12.000905';
                    const longitude = doctor?.centro_salud?.longitude ?? '-71.227592';
                    const nombreDoctor = [doctor?.name, doctor?.first_lastname, doctor?.second_lastname].filter(
                        Boolean).join(' ');
                    const especialidad = doctor?.especialidad?.name ?? 'No asignada';
                    const distrito = doctor?.distrito?.name ?? 'No asignado';
                    const centro = doctor?.centro_salud?.name ?? 'No asignado';
                    const cmp = doctor?.CMP ?? 'No registrado';
                    const telefono = doctor?.phone ?? 'No registrado';

                    const infoDoctor = document.getElementById('info-doctor');
                    if (infoDoctor) {
                        infoDoctor.innerHTML = `
                        <div class="info-doctor__header">
                            <span class="badge bg-primary rounded-pill align-self-start">${turno ?? 'Turno no asignado'}</span>
                            <span class="info-doctor__name">${nombreDoctor}</span>
                        </div>
                        <div class="info-doctor__meta">
                            <span><strong>CMP:</strong> ${cmp}</span>
                            <span><strong>Especialidad:</strong> ${especialidad}</span>
                            <span><strong>Distrito:</strong> ${distrito}</span>
                            <span><strong>Centro de salud:</strong> ${centro}</span>
                            <span><strong>Teléfono:</strong> ${telefono}</span>
                            <span><strong>Fecha programada:</strong> ${fechaVisita ?? 'Sin definir'}</span>
                        </div>
                        <a class="info-doctor__link" href="https://www.google.com/maps?q=${latitude},${longitude}" target="_blank" rel="noopener">
                            Ver ubicación en Google Maps
                        </a>
                    `;
                    }

                    construirOpcionesEstado(visita);

                    const estadoActual = visita?.estado_visita_id;
                    const puedeEditar = estadoActual === 2 || estadoActual === 5; // Asignado o Reprogramado

                    estadoSelect.disabled = !puedeEditar;
                    document.getElementById('observaciones').disabled = !puedeEditar;
                    if (fechaInput) {
                        fechaInput.disabled = !puedeEditar;
                    }

                    const btnGuardar = document.querySelector('.modal-footer .btn-primary-grobdi');
                    if (btnGuardar) {
                        btnGuardar.disabled = !puedeEditar;
                    }

                    document.getElementById('doctor_id').value = doctor?.id ?? '';
                    document.getElementById('visita_id').value = visita?.id ?? '';
                    document.getElementById('fecha_visita').value = visita?.fecha_visita ?? '';
                    document.getElementById('observaciones').value = visita?.observaciones_visita ?? '';

                    if (fechaPicker) {
                        fechaPicker.destroy();
                    }
                    fechaPicker = flatpickr('#fecha_visita', {
                        dateFormat: 'Y-m-d',
                        minDate: data.rango?.fecha_inicio ?? null,
                        maxDate: data.rango?.fecha_fin ?? null,
                    });

                    toggleFechaField();

                    if (!puedeEditar) {
                        fechaGroup.classList.add('d-none');
                    }

                    const modalElement = document.getElementById('doctorModal');
                    if (modalElement && modalElement.parentElement !== document.body) {
                        document.body.appendChild(modalElement);
                    }
                    const modal = new bootstrap.Modal(modalElement);
                    modal.show();
                } catch (error) {
                    console.error(error);
                    if (window.Swal) {
                        Swal.fire({
                            icon: 'error',
                            title: 'No se pudo cargar la información',
                            text: 'Inténtalo nuevamente en unos segundos.'
                        });
                    }
                }
            }

            window.mostrarDoctor = mostrarDoctor;

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

            if (calendarElement) {
                calendar = new FullCalendar.Calendar(calendarElement, {
                    initialView: obtenerVistaInicial(),
                    locale: 'es',
                    buttonText: {
                        today: 'Hoy',
                        month: 'Mes',
                        list: 'Lista'
                    },
                    dayMaxEventRows: 3,
                    height: 'auto',
                    events: initialEvents,
                    eventOrder: 'extendedProps.turno',
                    headerToolbar: {
                        left: 'prev,next today',
                        center: 'title',
                        right: 'dayGridMonth,listMonth'
                    },
                    eventClick(info) {
                        info.jsEvent.preventDefault();
                        cerrarPopoversCalendar();
                        mostrarDoctor(info.event.id);
                    },
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
                            const ALL_DAY_VARIANTS = ['all-day', 'all day', 'todo el dia', 'todo el día',
                                'todo el día'.toLowerCase()
                            ];
                            if (ALL_DAY_VARIANTS.includes(txt) || ALL_DAY_VARIANTS.includes(normalized)) {
                                timeCell.textContent = turno || '';
                            }
                        } catch (e) {
                            console.warn('eventDidMount error:', e);
                        }
                    },


                    eventContent(arg) {
                        const estado = arg.event.extendedProps.estado;
                        const turno = arg.event.extendedProps.turno;
                        const lines = [
                            `<span class="fc-event-title">${arg.event.title}</span>`
                        ];
                        if (turno) {
                            lines.push(`<span class="fc-event-meta">${turno}</span>`);
                        }
                        if (estado) {
                            lines.push(`<span class="fc-event-meta">${estado}</span>`);
                        }
                        return {
                            html: `<div class="fc-event-content">${lines.join('')}</div>`
                        };
                    }
                });
                calendar.render();
                toggleEmptyState();
                actualizarMetricas(initialMetrics);
                if (typeof mediaQueryMovil.addEventListener === 'function') {
                    mediaQueryMovil.addEventListener('change', aplicarVistaResponsiva);
                } else if (typeof mediaQueryMovil.addListener === 'function') {
                    mediaQueryMovil.addListener(aplicarVistaResponsiva);
                }
                aplicarVistaResponsiva();
            }

            if (visitadoraSelect) {
                visitadoraSelect.addEventListener('change', (event) => {
                    const visitadoraId = event.target.value;
                    if (calendar) {
                        calendar.removeAllEvents();
                    }
                    actualizarMetricas({
                        total: 0,
                        estados: []
                    });
                    toggleEmptyState();
                    if (visitadoraId) {
                        cargarEventos(visitadoraId);
                    }
                });
            }

            if (estadoSelect) {
                estadoSelect.addEventListener('change', () => {
                    if (!estadoSelect.disabled) {
                        toggleFechaField();
                    }
                });
            }

            if ('geolocation' in navigator) {
                navigator.geolocation.getCurrentPosition((position) => {
                    const latInput = document.getElementById('latitude');
                    const lngInput = document.getElementById('longitude');
                    if (latInput) {
                        latInput.value = position.coords.latitude;
                    }
                    if (lngInput) {
                        lngInput.value = position.coords.longitude;
                    }
                }, (error) => {
                    console.warn('No se pudo obtener la ubicación:', error.message);
                });
            }

            if (formVisita) {
                formVisita.addEventListener('submit', async (event) => {
                    event.preventDefault();
                    if (!estadoSelect) {
                        return;
                    }
                    const estadoSeleccionado = parseInt(estadoSelect.value, 10);
                    if (estadoSeleccionado === estadoReprogramadoId && fechaInput && fechaInput.value
                        .trim() === '') {
                        if (window.Swal) {
                            Swal.fire({
                                icon: 'info',
                                title: 'Fecha requerida',
                                text: 'Selecciona una nueva fecha para completar la reprogramación.'
                            });
                        }
                        fechaInput.focus();
                        return;
                    }

                    try {
                        const formData = new FormData(formVisita);
                        const response = await fetch('/guardar-visita', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector(
                                    'meta[name="csrf-token"]').content
                            },
                            body: formData
                        });

                        const data = await response.json();
                        if (!response.ok || !data.success) {
                            throw data;
                        }

                        if (window.Swal) {
                            Swal.fire({
                                icon: 'success',
                                title: '¡Visita actualizada!',
                                text: 'Los cambios se guardaron correctamente.',
                                timer: 2000,
                                showConfirmButton: false
                            });
                        }

                        const modalElement = document.getElementById('doctorModal');
                        const modalInstance = bootstrap.Modal.getInstance(modalElement);
                        modalInstance?.hide();

                        if (calendar) {
                            calendar.removeAllEvents();
                        }
                        if (visitadoraSelect && visitadoraSelect.value) {
                            await cargarEventos(visitadoraSelect.value);
                        } else {
                            toggleEmptyState();
                        }
                    } catch (error) {
                        console.error(error);
                        const mensaje = error?.error ?? 'Ocurrió un error al guardar la visita.';
                        if (window.Swal) {
                            Swal.fire({
                                icon: 'error',
                                title: 'No se pudo guardar',
                                text: mensaje
                            });
                        }
                    }
                });
            }
        });
    </script>
@stop
