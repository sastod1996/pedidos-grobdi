<!-- Modal partial: crear mes de bonificaciones -->
<div class="modal fade" id="createBonificacionModal" tabindex="-1" aria-labelledby="createBonificacionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createBonificacionModalLabel">Crear mes de bonificaciones</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body">
                <div class="bonificaciones-wrapper">
                    <div class="card bonificaciones-hero-card shadow-none border-0 mb-3">
                        <div class="card-body p-2">
                            <p class="text-muted mb-0">Registra un nuevo periodo mensual y personaliza las bonificaciones para las visitadoras.</p>
                        </div>
                    </div>

                    <form id="formCrearBonificacion">
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label for="bonificacionMes" class="form-label text-muted text-uppercase small mb-1">Mes</label>
                                <input type="month" id="bonificacionMes" name="mes" class="form-control form-control-lg">
                            </div>
                            <div class="col-12 col-md-6">
                                <label for="bonificacionTipoMedico" class="form-label text-muted text-uppercase small mb-1">Tipo de médico</label>
                                <select id="bonificacionTipoMedico" name="tipo_medico" class="form-select form-select-lg">
                                    <option value="" selected>Selecciona un tipo</option>
                                    <option value="prescriptor">Prescriptor</option>
                                    <option value="comprador">Comprador</option>
                                </select>
                            </div>
                        </div>

                        <div class="mt-4">
                            <div class="d-flex align-items-center">
                                <label class="fw-semibold mb-0" for="aplicarGeneral">¿Aplicar porcentaje y monto de la meta para todas las visitadoras?</label>
                                <div class="form-check form-switch ms-3 flex-shrink-0" style="padding-left: 2.5em;">
                                    <input class="form-check-input" type="checkbox" role="switch" id="aplicarGeneral" data-trigger="generales">
                                </div>
                            </div>

                            <div class="row g-3 mt-3 bonificaciones-extra-fields d-none" data-target="generales">
                                <div class="col-12 col-md-6 col-xl-4">
                                    <label for="porcentajeGeneral" class="form-label text-muted text-uppercase small mb-1">Porcentaje comisión</label>
                                    <div class="input-group">
                                        <input type="number" min="0" max="100" step="0.01" id="porcentajeGeneral" name="porcentaje_general" class="form-control form-control-lg" placeholder="Ej. 3.5">
                                        <span class="input-group-text">%</span>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6 col-xl-4">
                                    <label for="montoGeneral" class="form-label text-muted text-uppercase small mb-1">Monto meta</label>
                                    <div class="input-group">
                                        <span class="input-group-text">S/</span>
                                        <input type="number" min="0" step="0.01" id="montoGeneral" name="monto_general" class="form-control form-control-lg" placeholder="Ej. 15,000">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div data-target="visitadoras" class="visitadoras-container">
                            <div class="row mt-4 align-items-center">
                                <div class="col-4 text-center">
                                    <label for="visitadora1">Visitadora 1</label>
                                </div>
                                <div class="col-4">
                                    <input type="text" id="visitadora1" name="visitadora1" class="form-control" placeholder="Ej. 3.5">
                                </div>
                                <div class="col">
                                    <input type="text" id="monto1" name="monto1" class="form-control" placeholder="Ej. 15,000">
                                </div>
                            </div>

                            <div class="row mt-4 align-items-center">
                                <div class="col-4 text-center">
                                    <label for="visitadora2">Visitadora 2</label>
                                </div>
                                <div class="col-4">
                                    <input type="text" id="visitadora2" name="visitadora2" class="form-control" placeholder="Ej. 4.5">
                                </div>
                                <div class="col">
                                    <input type="text" id="monto2" name="monto2" class="form-control" placeholder="Ej. 15,000">
                                </div>
                            </div>

                            <div class="row mt-4 align-items-center">
                                <div class="col-4 text-center">
                                    <label for="visitadora3">Visitadora 3</label>
                                </div>
                                <div class="col-4">
                                    <input type="text" id="visitadora3" name="visitadora3" class="form-control" placeholder="Ej. 3.5">
                                </div>
                                <div class="col">
                                    <input type="text" id="monto3" name="monto3" class="form-control" placeholder="Ej. 25,000">
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="guardarBonificacionBtn">Guardar mes</button>
            </div>
        </div>
    </div>
</div>


@section('css')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <style>
        .bonificaciones-wrapper { background-color: #f7f7fb; border-radius: 12px; padding: 1rem; }
        .bonificaciones-hero-card { background-color: #f8efef; border-radius: 12px; }
        .bonificaciones-extra-fields { background-color: #fff8f3; border-radius: 10px; border: 1px dashed #f0c7a8; padding: .75rem; }
    </style>
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>

    <script>
        (function($){
            $(function(){
                // Toggle campos generales y ocultar/mostrar visitadoras
                $(document).on('change', '[data-trigger="generales"]', function(){
                    const shouldShow = $(this).is(':checked');
                    // Mostrar/ocultar la sección de campos generales
                    $('[data-target="generales"]').toggleClass('d-none', !shouldShow);
                    // Si se aplican los generales (switch checked) ocultar las visitadoras,
                    // en caso contrario mostrarlas
                    $('[data-target="visitadoras"]').toggleClass('d-none', shouldShow);
                });

                // Ejemplo: enviar formulario desde el modal (ajustar acción AJAX o submit real según necesidad)
                $('#guardarBonificacionBtn').on('click', function(){
                    // Aquí puedes serializar y enviar via AJAX o validar y hacer submit
                    $('#formCrearBonificacion').submit();
                });
            });
        })(window.jQuery);
    </script>
@stop
