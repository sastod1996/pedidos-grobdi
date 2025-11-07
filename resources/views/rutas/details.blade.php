@can('rutas.detalledoctor')
<div class="modal fade" id="detailsModal" tabindex="-1" aria-labelledby="doctorModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div class="d-flex gap-4">
                    <h5 class="modal-title">Información de la Visita
                        <span>-</span>
                        <span id="visita-id"></span>
                    </h5>
                    <span class="badge d-flex align-items-center" id="state-badge"></span>
                </div>
                <button type="button" class="btn-close" data-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item"><strong>Nombre:</strong> <span id="doctor-name"></span></li>
                    <li class="list-group-item">
                        <div class="row gap-1 gap-md-0">
                            <div class="col-12 col-md-6">
                                <strong>CMP:</strong> <span id="doctor-cmp"></span>
                            </div>
                            <div class="col-12 col-md-6">
                                <strong>Especialidad:</strong> <span id="doctor-especialidad"></span>
                            </div>
                        </div>
                    </li>
                    <li class="list-group-item">
                        <div class="row gap-1 gap-md-0">
                            <div class="col-12 col-md-6">
                                <strong>Teléfono:</strong> <span id="doctor-phone"></span>
                            </div>
                            <div class="col-12 col-md-6">
                                <strong>Distrito:</strong> <span id="doctor-distrito"></span>
                            </div>
                        </div>
                    </li>
                    <li class="list-group-item">
                        <div class="row gap-1 gap-md-0">
                            <div class="col-12 col-md-6">
                                <strong>Centro de Salud:</strong> <a id="doctor-centro_de_salud" target="_blank"></a>
                            </div>
                            <div class="col-12 col-md-6">
                                <strong>Turno:</strong> <span id="doctor-turno"></span>
                            </div>
                        </div>
                    </li>
                    <li class="list-group-item">
                        @can('rutasmapa.guardarvisita')
                        <form id="form-visita" method="POST" enctype="multipart/form-data">
                            <div class="row gap-1 gap-md-0 mb-2">
                                <div class="col-12 col-md-6">
                                    <label for="estado-visita">Estado de la Visita:</label>
                                    <select name="estado-visita" id="estado-visita" class="form-select rounded-2" required>
                                        <option value="" disabled selected>Seleccione un estado</option>
                                        <option value="3">No Visitado</option>
                                        <option value="4">Visitado</option>
                                        <option value="5">Reprogramado</option>
                                    </select>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label for="fecha-visita" class="form-label">Fecha reprogramada:</label>
                                    <input type="text" id="fecha-visita" name="fecha-visita" class="form-control" placeholder="Selecciona una fecha" required>
                                </div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-12">
                                    <label class="form-label">Días disponibles:</label>
                                    <div class="row gy-2" id="doctor-days-container">
                                        @isset($days)
                                            @foreach ($days as $day)
                                                <div class="col-12 col-md-6">
                                                    <div class="form-check form-switch">
                                                        <input
                                                            class="form-check-input doctor-day-checkbox"
                                                            type="checkbox"
                                                            id="modal-dia-{{ $day->id }}"
                                                            name="dias[]"
                                                            value="{{ $day->id }}"
                                                            data-day-id="{{ $day->id }}">
                                                        <label class="form-check-label" for="modal-dia-{{ $day->id }}">{{ $day->name }}</label>
                                                    </div>
                                                    <div class="mt-2 turno-container" data-day-id="{{ $day->id }}" style="display: none;">
                                                        <label class="form-label" for="modal-turno-{{ $day->id }}">Turno</label>
                                                        <select
                                                            class="form-select doctor-turno-select"
                                                            id="modal-turno-{{ $day->id }}"
                                                            name="turno_{{ $day->id }}"
                                                            disabled>
                                                            <option value="" selected disabled>Seleccione</option>
                                                            <option value="0">Turno Mañana</option>
                                                            <option value="1">Turno Tarde</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            @endforeach
                                        @endisset
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <label for="observaciones" class="form-label">Observaciones</label>
                                    <textarea name="observaciones" id="observaciones" class="form-control"></textarea>
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col">
                                    <button type="submit" class="btn btn-primary w-100" id="submit-btn">Guardar Cambios</button>
                                </div>
                            </div>
                        </form>
                        @endcan
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endcan
