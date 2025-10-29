<div class="modal fade" id="configuracionModal" tabindex="-1" role="dialog" aria-labelledby="configuracionModalLabel" aria-hidden="true">
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
                        <button class="nav-link active" id="table-tab" data-toggle="tab" data-target="#table-pane" data-bs-toggle="tab" data-bs-target="#table-pane" type="button" role="tab" aria-controls="table-pane" aria-selected="false">Rangos configurados</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="form-tab" data-toggle="tab" data-target="#form-pane" data-bs-toggle="tab" data-bs-target="#form-pane" type="button" role="tab" aria-controls="form-pane" aria-selected="true">Configurar rangos</button>
                    </li>
                </ul>

                <div class="tab-content" id="configTabsContent">
                    <div class="tab-pane fade" id="form-pane" role="tabpanel" aria-labelledby="form-tab">
                        <form id="configuracionRangoForm" action="{{ route('visitadoras.metas.not-reached-config.store') }}" method="POST">
                            @csrf

                            <div id="rangeFields" class="d-flex flex-column gap-3">
                                <div class="bonificaciones-range-group p-3 border rounded" data-index="0">
                                    <div class="row g-3 align-items-end">
                                        <div class="col-12 col-md-4">
                                            <label class="form-label small text-uppercase text-muted" data-label-for="inicio">Porcentaje inicial</label>
                                            <input type="number" class="form-control range-input" min="0" max="99.99" step="0.01" data-field="inicio" placeholder="Ej. 0" value="0">
                                        </div>
                                        <div class="col-12 col-md-4">
                                            <label class="form-label small text-uppercase text-muted" data-label-for="fin">Porcentaje final</label>
                                            <input type="number" class="form-control range-input" min="0" max="99.99" step="0.01" data-field="fin" placeholder="Ej. 79" value="79">
                                        </div>
                                        <div class="col-12 col-md-3">
                                            <label class="form-label small text-uppercase text-muted" data-label-for="comision">Comisión (%)</label>
                                            <input type="number" class="form-control range-input" min="0" max="99.99" step="0.01" data-field="comision" placeholder="Ej. 0" value="0">
                                        </div>
                                        <div class="col-12 col-md-1 text-md-right">
                                            <button type="button" class="btn btn-link text-danger remove-range-row d-none">Eliminar</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-3 d-flex gap-2">
                                <button type="button" class="btn btn-outline-primary btn-sm" id="addRangeRow">
                                    <i class="fas fa-plus"></i> Agregar otro rango
                                </button>
                                <button type="submit" class="btn btn-primary btn-sm" id="saveRangeConfig">Guardar rangos</button>
                            </div>
                        </form>
                    </div>

                    <div class="tab-pane fade show active" id="table-pane" role="tabpanel" aria-labelledby="table-tab">
                        <div class="table-responsive">
                            <table class="table table-striped mb-0 align-middle table-grobdi">
                                <thead>
                                    <tr>
                                        <th scope="col">Porcentaje inicial</th>
                                        <th scope="col">Porcentaje final</th>
                                        <th scope="col">Comisión</th>
                                    </tr>
                                </thead>
                                <tbody id="configTableBody">
                                    <tr class="text-center"><td colspan="4">No hay configuración activa.</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secodary" data-dismiss="modal" data-bs-dismiss="modal">Cancelar</button>
            </div>
        </div>
    </div>
</div>
    <script>
        // Initialize after full load so jQuery (from layout) is available
        window.addEventListener('load', function () {
            if (typeof window.jQuery === 'undefined') {
                console.warn('configModal: jQuery not found after load — modal features will be disabled');
                return;
            }

            const $ = window.jQuery;

            // Manejo de tabs (soporta atributos v4 y v5)
            $(document).on('click', 'button[data-toggle="tab"], button[data-bs-toggle="tab"]', function (e) {
                e.preventDefault();
                const $btn = $(this);
                const target = $btn.attr('data-target') || $btn.attr('data-bs-target');
                if (!target) return;

                $btn.closest('.nav').find('.nav-link').removeClass('active');
                $btn.addClass('active');

                const $tabContent = $($btn.closest('[role="tablist"]').attr('data-target') || '#configTabsContent');
                if ($tabContent && $tabContent.length) {
                    $tabContent.find('.tab-pane').removeClass('show active');
                } else {
                    $('.tab-pane').removeClass('show active');
                }

                $(target).addClass('show active');
            });

            // Manejo de cierre para compatibilidad: si Bootstrap no elimina el backdrop,
            // nos aseguramos de limpiar el DOM y las clases para que la página vuelva a la normalidad.
            function cleanModalBackdrop($modal) {
                // remover cualquier backdrop
                $('.modal-backdrop').remove();
                // quitar clase que bloquea el scroll
                $('body').removeClass('modal-open');
                // opcional: restaurar estilo overflow
                $('body').css('padding-right', '');
                // ocultar modal si aún está mostrado
                if ($modal && $modal.length) {
                    $modal.removeClass('show').attr('aria-hidden', 'true').css('display', 'none');
                }
            }

            // Interceptamos clicks en elementos que solicitan dismiss (v4 y v5)
            $(document).on('click', '[data-dismiss="modal"], [data-bs-dismiss="modal"]', function (e) {
                // Dejar que Bootstrap haga su trabajo si está disponible (v4 plugin o v5 JS)
                const $trigger = $(this);
                const $modal = $trigger.closest('.modal');

                try {
                    // Si existe el plugin jQuery .modal (Bootstrap 4), usarlo
                    if (typeof $.fn.modal === 'function' && $modal.length) {
                        $modal.modal('hide');
                        return;
                    }

                    // Si Bootstrap 5 está presente como namespace, intentar usarlo
                    if (window.bootstrap && window.bootstrap.Modal) {
                        // obtener instancia si existe
                        const modalEl = $modal.get(0);
                        if (modalEl) {
                            const instance = window.bootstrap.Modal.getInstance(modalEl);
                            if (instance) {
                                instance.hide();
                                return;
                            }
                        }
                    }
                } catch (err) {
                    // continuar al fallback
                }

                // Fallback manual: limpiar backdrop y ocultar modal
                cleanModalBackdrop($modal.length ? $modal : $('#configuracionModal'));
                // disparar evento para que otros handlers (por ejemplo, reset tabs) respondan
                $(document).trigger('hidden.bs.modal');
            });

            // Cuando bootstrap emite el evento hidden.bs.modal, reestablecemos la primera pestaña
            $(document).on('hidden.bs.modal', '#configuracionModal', function () {
                const $firstBtn = $(this).find('button[data-toggle="tab"], button[data-bs-toggle="tab"]').first();
                $firstBtn.trigger('click');
                // limpieza adicional por si queda backdrop
                cleanModalBackdrop($(this));
            });

                // Dynamic ranges: add/remove rows, load active config and submit via AJAX
            (function(){
                var $modal = $('#configuracionModal');
                var $rangeContainer = $modal.find('#rangeFields');
                var nextIndex = 1; // first row is index 0

                function makeRow(index, values){
                    values = values || { inicio: '', fin: '', comision: '' };
                    var $row = $(
                        '<div class="bonificaciones-range-group p-3 border rounded" data-index="'+index+'">' +
                        '  <div class="row g-3 align-items-end">' +
                        '    <div class="col-12 col-md-4">' +
                        '      <label class="form-label small text-uppercase text-muted">Porcentaje inicial</label>' +
                        '      <input type="number" class="form-control range-input" min="0" max="99.99" step="0.01" data-field="inicio" placeholder="Ej. 0" value="'+values.inicio+'">' +
                        '    </div>' +
                        '    <div class="col-12 col-md-4">' +
                        '      <label class="form-label small text-uppercase text-muted">Porcentaje final</label>' +
                        '      <input type="number" class="form-control range-input" min="0" max="99.99" step="0.01" data-field="fin" placeholder="Ej. 79" value="'+values.fin+'">' +
                        '    </div>' +
                        '    <div class="col-12 col-md-3">' +
                        '      <label class="form-label small text-uppercase text-muted">Comisión (%)</label>' +
                        '      <input type="number" class="form-control range-input" min="0" max="99.99" step="0.01" data-field="comision" placeholder="Ej. 0" value="'+values.comision+'">' +
                        '    </div>' +
                        '    <div class="col-12 col-md-1 text-md-right">' +
                        '      <button type="button" class="btn btn-link text-danger remove-range-row">Eliminar</button>' +
                        '    </div>' +
                        '  </div>' +
                        '</div>'
                    );
                    return $row;
                }

                // Add row handler
                $modal.on('click', '#addRangeRow', function(e){
                    e.preventDefault();
                    var $new = makeRow(nextIndex);
                    $rangeContainer.append($new);
                    nextIndex++;
                    console.debug('configModal: added range row, index=', nextIndex-1);
                    // show remove buttons when more than 1
                    $rangeContainer.find('.remove-range-row').toggle($rangeContainer.find('.bonificaciones-range-group').length > 1);
                });

                // Remove row handler (delegated)
                $modal.on('click', '.remove-range-row', function(e){
                    e.preventDefault();
                    var $btn = $(this);
                    var $group = $btn.closest('.bonificaciones-range-group');
                    $group.remove();
                    // update visibility of remove buttons
                    $rangeContainer.find('.remove-range-row').toggle($rangeContainer.find('.bonificaciones-range-group').length > 1);
                });

                // Client-side validation: no overlapping intervals and numeric checks
                function collectRanges(){
                    var ranges = [];
                    $rangeContainer.find('.bonificaciones-range-group').each(function(){
                        var $g = $(this);
                        var inicio = parseFloat($g.find('[data-field="inicio"]').val());
                        var fin = parseFloat($g.find('[data-field="fin"]').val());
                        var com = parseFloat($g.find('[data-field="comision"]').val());
                        ranges.push({ inicio: inicio, fin: fin, com: com, el: $g });
                    });
                    return ranges;
                }

                function validateRanges(ranges){
                    var msgs = [];
                    // basic checks
                    ranges.forEach(function(r, idx){
                        if (isNaN(r.inicio) || isNaN(r.fin) || isNaN(r.com)) {
                            msgs.push('Todos los campos deben ser numéricos en la fila ' + (idx+1));
                            return;
                        }
                        if (r.inicio < 0 || r.inicio > 99.99) msgs.push('Porcentaje inicial fuera de rango en fila ' + (idx+1));
                        if (r.fin < 0 || r.fin > 99.99) msgs.push('Porcentaje final fuera de rango en fila ' + (idx+1));
                        if (r.com < 0 || r.com > 99.99) msgs.push('Comisión fuera de rango en fila ' + (idx+1));
                        if (r.inicio > r.fin) msgs.push('El porcentaje inicial debe ser menor o igual al porcentaje final en fila ' + (idx+1));
                    });
                    // check overlaps: sort by inicio
                    var sorted = ranges.slice().sort(function(a,b){ return a.inicio - b.inicio; });
                    for (var i=0;i<sorted.length-1;i++){
                        var cur = sorted[i];
                        var next = sorted[i+1];
                        // require cur.fin < next.inicio (strict) to avoid overlap
                        if (cur.fin >= next.inicio) {
                            msgs.push('Los intervalos se solapan: ['+cur.inicio+','+cur.fin+'] y ['+next.inicio+','+next.fin+']');
                            break;
                        }
                    }
                    return msgs;
                }

                // Load active configuration and populate table
                function loadActiveConfig(){
                    var targetUrl = '{{ url("bonificaciones/metas/not-reached-config") }}';
                    fetch(targetUrl, { method: 'GET', headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                        .then(function(res){ return res.json(); })
                        .then(function(json){
                            console.debug('configModal: loadActiveConfig response', json);
                            var tbody = $modal.find('#configTableBody');
                            tbody.empty();
                            if (json && json.details && Array.isArray(json.details) && json.details.length) {
                                json.details.forEach(function(d){
                                    var $tr = $('<tr />');
                                    $tr.append('<td>' + (d.initial_percentage ?? '-') + '</td>');
                                    $tr.append('<td>' + (d.final_percentage ?? '-') + '</td>');
                                    $tr.append('<td>' + (d.commission ?? '-') + '</td>');
                                    tbody.append($tr);
                                });
                            } else {
                                tbody.append('<tr class="text-center"><td colspan="4">No hay configuración activa.</td></tr>');
                            }
                        }).catch(function(err){
                            console.error('Error loading active config', err);
                        });
                }

                // Submit handler: send JSON payload to backend
                $modal.on('submit', '#configuracionRangoForm', function(e){
                    e.preventDefault();
                    var $form = $(this);
                    var ranges = collectRanges();
                    var errors = validateRanges(ranges);
                    if (errors.length) {
                        if (window.Swal) {
                            Swal.fire({ icon: 'error', title: 'Errores', html: '<ul style="text-align:left">' + errors.map(function(m){ return '<li>'+m+'</li>'; }).join('') + '</ul>' });
                        } else {
                            alert(errors.join('\n'));
                        }
                        return;
                    }

                    var details = ranges.map(function(r){ return { initial_percentage: r.inicio, final_percentage: r.fin, commission: r.com }; });
                    var payload = { details: details };

                    var token = $form.find('input[name="_token"]').val();
                    var action = $form.attr('action');

                    fetch(action, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': token,
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify(payload)
                    }).then(function(res){
                        return res.json().then(function(data){ return { ok: res.ok, status: res.status, data: data }; });
                    }).then(function(result){
                        if (result.ok && result.data && result.data.success) {
                            if (window.Swal) {
                                Swal.fire({ icon: 'success', title: 'Guardado', text: result.data.message || 'Configuración guardada.' });
                            } else alert(result.data.message || 'Configuración guardada.');
                            // close modal
                            try { var modalEl = document.getElementById('configuracionModal'); if (modalEl && window.bootstrap && window.bootstrap.Modal) { var m = window.bootstrap.Modal.getInstance(modalEl) || window.bootstrap.Modal.getOrCreateInstance(modalEl); m.hide(); } } catch(e){ }
                            // refresh table inside modal (in case user re-opens it) and switch to table tab
                            setTimeout(function(){ loadActiveConfig(); $('#table-tab').trigger('click'); }, 400);
                        } else if (result.status === 422 && result.data && result.data.errors) {
                            var messages = [];
                            for (var k in result.data.errors) { result.data.errors[k].forEach(function(m){ messages.push(m); }); }
                            if (window.Swal) {
                                Swal.fire({ icon: 'error', title: 'Errores de validación', html: '<ul style="text-align:left">' + messages.map(function(m){ return '<li>' + m + '</li>'; }).join('') + '</ul>' });
                            } else alert(messages.join('\n'));
                        } else {
                            var m = (result.data && result.data.message) ? result.data.message : 'Ocurrió un error al guardar.';
                            if (window.Swal) Swal.fire({ icon: 'error', title: 'Error', text: m }); else alert(m);
                        }
                    }).catch(function(err){
                        console.error(err);
                        if (window.Swal) Swal.fire({ icon: 'error', title: 'Error', text: 'Error de red al guardar.' }); else alert('Error de red al guardar.');
                    });
                });

                // Load active config when modal is shown
                $modal.on('show.bs.modal', function(){
                    loadActiveConfig();
                    // ensure remove button visibility
                    $rangeContainer.find('.remove-range-row').toggle($rangeContainer.find('.bonificaciones-range-group').length > 1);
                });
            })();
    });
    </script>
