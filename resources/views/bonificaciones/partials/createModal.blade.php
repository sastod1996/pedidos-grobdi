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

                    <form id="formCrearBonificacion" action="{{ route('visitadoras.metas.store') }}" method="POST">
                        @csrf
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label for="bonificacionMes" class="form-label text-muted text-uppercase small mb-1">Mes</label>
                                <input type="month" id="bonificacionMes" name="month" class="form-control form-control-lg">
                            </div>
                            <div class="col-12 col-md-6">
                                <label for="bonificacionTipoMedico" class="form-label text-muted text-uppercase small mb-1">Tipo de médico</label>
                                <select id="bonificacionTipoMedico" name="tipo_medico" class="form-select form-select-lg">
                                    <option value="" selected>Selecciona un tipo</option>
                                    <option value="prescriptor">Prescriptor</option>
                                    <option value="comprador">Comprador</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <div id="doctorsForTipo" class="mt-3">
                                    <!-- Doctors list for selected tipo will be rendered here -->
                                    <div class="small text-muted">Selecciona un tipo de médico para ver los doctores disponibles.</div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <div class="d-flex align-items-center">
                                <label class="fw-semibold mb-0" for="aplicarGeneral">¿Aplicar porcentaje y monto de la meta para todas las visitadoras?</label>
                                    <div class="form-check form-switch ms-3 flex-shrink-0" style="padding-left: 2.5em;">
                                        <input class="form-check-input" type="checkbox" role="switch" id="aplicarGeneral" data-trigger="generales" checked>
                                        <input type="hidden" name="is_general_goal" id="isGeneralGoalInput" value="1">
                                </div>
                            </div>

                            <div class="row g-3 mt-3 bonificaciones-extra-fields d-none" data-target="generales">
                                    <div class="col-12 col-md-6 col-xl-4">
                                        <label for="porcentajeGeneral" class="form-label text-muted text-uppercase small mb-1">Porcentaje comisión</label>
                                        <div class="input-group">
                                            <input type="number" min="0" max="100" step="0.01" id="porcentajeGeneral" name="commission_percentage" class="form-control form-control-lg" placeholder="Ej. 3.5">
                                            <span class="input-group-text">%</span>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-6 col-xl-4">
                                        <label for="montoGeneral" class="form-label text-muted text-uppercase small mb-1">Monto meta</label>
                                        <div class="input-group">
                                            <span class="input-group-text">S/</span>
                                            <input type="number" min="0" step="0.01" id="montoGeneral" name="goal_amount" class="form-control form-control-lg" placeholder="Ej. 15000.00">
                                        </div>
                                    </div>
                            </div>
                        </div>

                        <div data-target="visitadoras" class="visitadoras-container">
                            @if(isset($visitadoras) && $visitadoras->isNotEmpty())
                                @foreach($visitadoras as $index => $v)
                                    <div class="row mt-3 align-items-center">
                                        <div class="col-4 text-start">
                                            <label class="mb-0">{{ $v->name }}</label>
                                            <input type="hidden" name="visitor_goals[{{ $index }}][user_id]" value="{{ $v->id }}">
                                        </div>
                                        <div class="col-4">
                                            <input type="number" step="0.01" min="0" name="visitor_goals[{{ $index }}][commission_percentage]" class="form-control" placeholder="% Comisión (Ej. 3.50)">
                                        </div>
                                        <div class="col">
                                            <input type="number" step="0.01" min="0" name="visitor_goals[{{ $index }}][goal_amount]" class="form-control" placeholder="Monto meta (Ej. 15000)">
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div class="alert alert-warning">No se encontraron visitadoras para asignar metas.</div>
                            @endif
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

@section('plugins.Sweetalert2', true)

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <script src="{{ asset('js/sweetalert2-factory.js') }}"></script>

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
    <script>
        // Render doctors list for selected tipo in the create modal
        document.addEventListener('DOMContentLoaded', function () {
            const selectTipo = document.getElementById('bonificacionTipoMedico');
            const target = document.getElementById('doctorsForTipo');
            // doctors grouped by tipo injected from controller
            const doctorsByTipo = @json($doctors ?? []);

            function renderDoctors(tipo) {
                if (!target) return;
                target.innerHTML = '';
                if (!tipo) {
                    target.innerHTML = '<div class="small text-muted">Selecciona un tipo de médico para ver los doctores disponibles.</div>';
                    return;
                }
                const list = doctorsByTipo[tipo] || [];
                if (!list.length) {
                    target.innerHTML = '<div class="small text-warning">No se encontraron doctores para este tipo.</div>';
                    return;
                }
                const ul = document.createElement('div');
                ul.className = 'list-group list-group-flush';
                list.forEach(function(d){
                    const item = document.createElement('div');
                    item.className = 'list-group-item py-2 small d-flex justify-content-between align-items-center';
                    item.innerHTML = '<div>' + (d.name || 'Doctor ' + d.id) + '</div>' +
                        '<div class="text-muted">ID: ' + d.id + '</div>';
                    ul.appendChild(item);
                });
                target.appendChild(ul);
            }

            if (selectTipo) {
                selectTipo.addEventListener('change', function () {
                    renderDoctors(this.value);
                });

                // render initial selection if set
                if (selectTipo.value) renderDoctors(selectTipo.value);
            }
        });
    </script>
    <script>
        // Vanilla JS fallback and AJAX submit for create modal.
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('formCrearBonificacion');
            const guardarBtn = document.getElementById('guardarBonificacionBtn');
            const modalEl = document.getElementById('createBonificacionModal');
            let bsModal = null;
            try {
                bsModal = bootstrap.Modal.getOrCreateInstance(modalEl);
            } catch (e) {
                // bootstrap not available
            }

                // If jQuery wasn't present the previous handlers won't run; ensure the guardar button triggers form submit
            if (guardarBtn && form) {
                guardarBtn.addEventListener('click', function () {
                    // Use requestSubmit when available so HTML5 validation runs
                    if (typeof form.requestSubmit === 'function') {
                        form.requestSubmit();
                    } else {
                        form.submit();
                    }
                });
            }

            // Toggle handler for the generales switch (in case jQuery isn't loaded)
            const trigger = document.querySelector('[data-trigger="generales"]');
            const isGeneralInput = document.getElementById('isGeneralGoalInput');
            if (trigger) {
                // Ensure the hidden input matches initial checked state
                if (isGeneralInput) isGeneralInput.value = trigger.checked ? '1' : '0';

                trigger.addEventListener('change', function () {
                    const shouldShow = this.checked;
                    const generales = document.querySelector('[data-target="generales"]');
                    const visitadoras = document.querySelector('[data-target="visitadoras"]');
                    if (generales) generales.classList.toggle('d-none', !shouldShow);
                    if (visitadoras) visitadoras.classList.toggle('d-none', shouldShow);
                    if (isGeneralInput) isGeneralInput.value = shouldShow ? '1' : '0';
                });
            }

            // AJAX submit to keep modal open/close controlled and show feedback
            if (form) {
                form.addEventListener('submit', function (e) {
                    e.preventDefault();

                    // remove old alerts
                    const oldAlerts = form.querySelectorAll('.bv-alert');
                    oldAlerts.forEach(a => a.remove());

                    const url = form.getAttribute('action') || window.location.href;
                    const fd = new FormData(form);

                    // Send request
                    fetch(url, {
                        method: 'POST',
                        body: fd,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                    })
                    .then(async (res) => {
                        const contentType = res.headers.get('content-type') || '';
                        let data = {};
                        if (contentType.indexOf('application/json') !== -1) {
                            data = await res.json();
                        } else {
                            data = { success: res.ok, message: await res.text() };
                        }

                            if (res.ok && data.success) {
                                // Use SweetAlert2 to show success and then redirect to index
                                if (window.Swal) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Guardado',
                                        text: data.message || 'Metas creadas exitosamente.',
                                        timer: 1200,
                                        showConfirmButton: false
                                    }).then(() => {
                                        window.location = '{{ route('bonificaciones.index') }}';
                                    });
                                } else {
                                    // fallback: show inline alert then redirect
                                    const success = document.createElement('div');
                                    success.className = 'alert alert-success bv-alert';
                                    success.textContent = data.message || 'Guardado correctamente.';
                                    form.querySelector('.bonificaciones-wrapper').prepend(success);
                                    setTimeout(() => {
                                        if (bsModal && typeof bsModal.hide === 'function') bsModal.hide();
                                        window.location = '{{ route('bonificaciones.index') }}';
                                    }, 900);
                                }
                            } else if (res.status === 422 && data.errors) {
                                // Validation errors: show with SweetAlert2 or inline
                                const messages = [];
                                for (const key in data.errors) {
                                    data.errors[key].forEach(msg => messages.push(msg));
                                }
                                const html = '<ul style="text-align:left;">' + messages.map(m => '<li>' + m + '</li>').join('') + '</ul>';
                                if (window.Swal) {
                                    Swal.fire({ icon: 'error', title: 'Errores de validación', html });
                                } else {
                                    const alertDiv = document.createElement('div');
                                    alertDiv.className = 'alert alert-danger bv-alert';
                                    const ul = document.createElement('ul');
                                    messages.forEach(msg => {
                                        const li = document.createElement('li'); li.textContent = msg; ul.appendChild(li);
                                    });
                                    alertDiv.appendChild(ul);
                                    form.querySelector('.bonificaciones-wrapper').prepend(alertDiv);
                                }
                            } else {
                                // General error
                                const message = data.message || 'Ocurrió un error al procesar la solicitud.';
                                if (window.Swal) {
                                    Swal.fire({ icon: 'error', title: 'Error', text: message });
                                } else {
                                    const alertDiv = document.createElement('div');
                                    alertDiv.className = 'alert alert-danger bv-alert';
                                    alertDiv.textContent = message;
                                    form.querySelector('.bonificaciones-wrapper').prepend(alertDiv);
                                }
                            }
                    })
                    .catch((err) => {
                        console.error(err);
                        const alertDiv = document.createElement('div');
                        alertDiv.className = 'alert alert-danger bv-alert';
                        alertDiv.textContent = 'Error de red al enviar el formulario.';
                        form.querySelector('.bonificaciones-wrapper').prepend(alertDiv);
                    });
                });
            }
        });
    </script>
@stop
