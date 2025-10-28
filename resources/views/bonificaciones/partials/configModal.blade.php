<div class="modal fade" id="configuracionModal" tabindex="-1" role="dialog" aria-labelledby="configuracionModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title" id="configuracionModalLabel">Crear nuevo rango de bonificación</h5>
                <button type="button" class="close" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <ul class="nav nav-tabs mb-3" id="configTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="table-tab" data-toggle="tab" data-target="#table-pane"
                            data-bs-toggle="tab" data-bs-target="#table-pane" type="button" role="tab"
                            aria-controls="table-pane" aria-selected="false">Rangos configurados</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="form-tab" data-toggle="tab" data-target="#form-pane"
                            data-bs-toggle="tab" data-bs-target="#form-pane" type="button" role="tab"
                            aria-controls="form-pane" aria-selected="true">Configurar rangos</button>
                    </li>
                </ul>

                <div class="tab-content" id="configTabsContent">
                    <div class="tab-pane fade" id="form-pane" role="tabpanel" aria-labelledby="form-tab">
                        <form id="configuracionRangoForm">
                            <div id="rangeFields" class="d-flex flex-column gap-3">
                                <!-- Rango 0 (base) -->
                                <div class="bonificaciones-range-group p-3 border rounded" data-index="0">
                                    <div class="row g-3 align-items-end">
                                        <div class="col-12 col-md-4">
                                            <label class="form-label small text-uppercase text-muted"
                                                data-label-for="inicio">Porcentaje inicial</label>
                                            <input type="number" name="rangos[0][inicio]"
                                                class="form-control inicio-field" min="0" max="100"
                                                step="1" data-field="inicio" placeholder="Ej. 80">
                                        </div>
                                        <div class="col-12 col-md-4">
                                            <label class="form-label small text-uppercase text-muted"
                                                data-label-for="fin">Porcentaje final</label>
                                            <input type="number" name="rangos[0][fin]" class="form-control fin-field"
                                                min="0" max="100" step="1" data-field="fin"
                                                placeholder="Ej. 89">
                                        </div>
                                        <div class="col-12 col-md-3">
                                            <label class="form-label small text-uppercase text-muted"
                                                data-label-for="comision">Comisión (%)</label>
                                            <input type="number" name="rangos[0][comision]"
                                                class="form-control comision-field" min="0" max="100"
                                                step="0.01" data-field="comision" placeholder="Ej. 1.5">
                                        </div>
                                        <div class="col-12 col-md-1 text-md-right">
                                            <!-- primer renglon no puede eliminarse -->
                                            <button type="button"
                                                class="btn btn-link text-danger remove-range-row d-none">Eliminar</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-3">
                                <button type="button" class="btn btn-outline-primary btn-sm" id="añadirFila">
                                    <i class="fas fa-plus"></i> Agregar otro rango
                                </button>
                            </div>
                        </form>
                    </div>

                    <div class="tab-pane fade show active" id="table-pane" role="tabpanel"
                        aria-labelledby="table-tab">
                        <div class="table-responsive">
                            <table class="table table-striped mb-0 align-middle table-grobdi">
                                <thead>
                                    <tr>
                                        <th scope="col">Porcentaje inicial</th>
                                        <th scope="col">Porcentaje final</th>
                                        <th scope="col">comision</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>0%</td>
                                        <td>79%</td>
                                        <td>0%</td>
                                    </tr>
                                    <tr>
                                        <td>80%</td>
                                        <td>89%</td>
                                        <td>1%</td>
                                    </tr>
                                    <tr>
                                        <td>90%</td>
                                        <td>99%</td>
                                        <td>1.50%</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary" data-dismiss="modal"
                    data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary">Guardar rangos</button>
            </div>
        </div>
    </div>
</div>
<script>
(function () {
    const $ = window.jQuery;

    // Función util: formatea nuevos names según índice
    function updateNames($group, index) {
        $group.attr('data-index', index);
        $group.find('input').each(function () {
            const $inp = $(this);
            const field = $inp.attr('data-field') || $inp.attr('name') || 'campo';
            // poner name como rangos[index][field]
            $inp.attr('name', `rangos[${index}][${field}]`);
        });
    }

    // Al hacer click en "Agregar otro rango"
    $(document).on('click', '#añadirFila', function (e) {
        e.preventDefault();
        const $container = $('#rangeFields');
        const $last = $container.find('.bonificaciones-range-group').last();

        // Si no hay ninguno (raro) crear uno base
        if ($last.length === 0) {
            const $template = $('<div class="bonificaciones-range-group p-3 border rounded" data-index="0">\
                <div class="row g-3 align-items-end">\
                    <div class="col-12 col-md-4">\
                        <label class="form-label small text-uppercase text-muted">Porcentaje inicial</label>\
                        <input type="number" name="rangos[0][inicio]" class="form-control inicio-field" min="0" max="100" step="1" data-field="inicio" placeholder="Ej. 80">\
                    </div>\
                    <div class="col-12 col-md-4">\
                        <label class="form-label small text-uppercase text-muted">Porcentaje final</label>\
                        <input type="number" name="rangos[0][fin]" class="form-control fin-field" min="0" max="100" step="1" data-field="fin" placeholder="Ej. 89">\
                    </div>\
                    <div class="col-12 col-md-3">\
                        <label class="form-label small text-uppercase text-muted">Comisión (%)</label>\
                        <input type="number" name="rangos[0][comision]" class="form-control comision-field" min="0" max="100" step="0.01" data-field="comision" placeholder="Ej. 1.5">\
                    </div>\
                    <div class="col-12 col-md-1 text-md-right">\
                        <button type="button" class="btn btn-link text-danger remove-range-row d-none">Eliminar</button>\
                    </div>\
                </div>\
            </div>');
            $container.append($template);
            return;
        }

        // calcular nuevo índice
        const lastIndex = Number($last.attr('data-index') ?? 0);
        const newIndex = isNaN(lastIndex) ? 1 : lastIndex + 1;

        // clonar, limpiar valores e insertar
        const $clone = $last.clone(false, false);
        // Limpiar valores
        $clone.find('input').val('');
        // asegurar que el boton eliminar esté visible en clones
        $clone.find('.remove-range-row').removeClass('d-none');
        // actualizar names e índice
        updateNames($clone, newIndex);

        $container.append($clone);
    });

    // Eliminar renglon (delegado)
    $(document).on('click', '.remove-range-row', function (e) {
        e.preventDefault();
        const $group = $(this).closest('.bonificaciones-range-group');
        if ($group.length === 0) return;
        $group.remove();

        // Reindexar los rangos para evitar gaps en los nombres
        $('#rangeFields .bonificaciones-range-group').each(function (i) {
            updateNames($(this), i);
            // esconder el boton eliminar en el primero
            if (i === 0) {
                $(this).find('.remove-range-row').addClass('d-none');
            } else {
                $(this).find('.remove-range-row').removeClass('d-none');
            }
        });
    });

    // (Opcional) Al enviar el formulario, evitar submit por defecto y mostrar los datos por consola
    // elimina o ajusta esto para hacer el submit real a Laravel.
    $('#configuracionRangoForm').on('submit', function (e) {
        // si quieres enviar normal, borra el preventDefault y esto
        // e.preventDefault();
        // const datos = $(this).serializeArray();
        // console.log('Datos a enviar:', datos);
    });

})();
</script>

