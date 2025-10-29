<div class="modal fade border-1" id="debitarModal" tabindex="-1" aria-labelledby="debitarModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered modal-md">
		<div class="modal-content border-0 debitar-modal">
			<div class="modal-header border-0 pb-0 p-4" style="background-color: #f8efef">
				<div>
					<h5 class="modal-title fw-bold text-dark" id="debitarModalLabel">Registrar débito</h5>
					<p class="text-muted small mb-0">Registra el monto debitado y agrega una observación para el seguimiento.</p>
				</div>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
			</div>

			<div class="modal-body py-4">
				<div id="debitarModalBody" data-update-url-template="{{ url('bonificaciones/metas/update-debited-amount/__ID__') }}">
					<form class="debitar-form" id="formDebitar" action="" method="POST">
						@csrf
						@method('PUT')
						<input type="hidden" name="debited_datetime" id="debitedDatetime" value="" />
					<div class="row g-4">
						<div class="col-12">
							<label for="debitarMonto" class="form-label fw-semibold text-dark">Monto debitado</label>
                            <div class="input-group debitar-input-group">
                                <span class="input-group-text text-muted">S/</span>
								<input type="number" min="0" step="0.01" class="form-control w-full" id="debitarMonto" name="debited_amount" placeholder="0.00" />
                            </div>
						</div>
						<div class="col-12">
							<label for="debitarObservacion" class="form-label fw-semibold text-dark">Observación</label>
							<textarea class="form-control debitar-textarea" id="debitarObservacion" name="debit_comment" rows="4" placeholder="Agrega los detalles del débito..." ></textarea>
						</div>
					</div>
				</form>
			</div>
			<div class="modal-footer border-0 pt-0 d-flex justify-content-between">
				<button type="button" class="btn btn-secondary text-white" data-bs-dismiss="modal">Cancelar</button>
				{{-- Submit button targets the form via form attribute to avoid moving it inside the form markup --}}
				<button type="submit" form="formDebitar" class="btn btn-primary fw-bold">Guardar</button>
			</div>
		</div>
	</div>
</div>

<script>
	// Minimal vanilla JS to set the form action and debited datetime when modal is shown (Bootstrap 5)
	document.addEventListener('DOMContentLoaded', function(){
		var debitarModalEl = document.getElementById('debitarModal');
		if (!debitarModalEl) return;

		debitarModalEl.addEventListener('show.bs.modal', function (event) {
			var trigger = event.relatedTarget; // element that triggered the modal
			if (!trigger) return;
			var visitorGoalId = trigger.getAttribute('data-visitor-goal-id');
			var container = document.getElementById('debitarModalBody');
			if (!container) return;
			var template = container.getAttribute('data-update-url-template');
			if (!template || !visitorGoalId) return;
			var actionUrl = template.replace('__ID__', visitorGoalId);
			var form = document.getElementById('formDebitar');
			if (form) {
				form.setAttribute('action', actionUrl);
				// store the visitorGoalId on the form for fallback use
				form.dataset.visitorGoalId = visitorGoalId;
			}

			// set current datetime in SQL format 'YYYY-MM-DD HH:MM:SS'
			var now = new Date();
			function pad(n){ return n<10 ? '0'+n : n; }
			var formatted = now.getFullYear() + '-' + pad(now.getMonth()+1) + '-' + pad(now.getDate()) + ' ' + pad(now.getHours()) + ':' + pad(now.getMinutes()) + ':' + pad(now.getSeconds());
			var dtInput = document.getElementById('debitedDatetime');
			if (dtInput) dtInput.value = formatted;
		});

		// AJAX submit handler to update debited amount without full page reload
		var formDebitar = document.getElementById('formDebitar');
		if (formDebitar) {
			formDebitar.addEventListener('submit', function(e){
				e.preventDefault();
				var action = formDebitar.getAttribute('action');
				// If action is not set for any reason, try to build it from the template and stored visitorGoalId
				if (!action) {
					var container = document.getElementById('debitarModalBody');
					var template = container ? container.getAttribute('data-update-url-template') : null;
					var vgId = formDebitar.dataset.visitorGoalId || null;
					if (template && vgId) {
						action = template.replace('__ID__', vgId);
						formDebitar.setAttribute('action', action);
					} else {
						return; // nothing we can do
					}
				}

				// Ensure debited_datetime exists and has a value just before building the payload
				var tokenEl = document.querySelector('meta[name="csrf-token"]');
				var token = tokenEl ? tokenEl.getAttribute('content') : '';
				var dtEl = document.getElementById('debitedDatetime');
				if (dtEl && (!dtEl.value || dtEl.value === 'null')) {
					// compute now in SQL format 'YYYY-MM-DD HH:MM:SS'
					var now2 = new Date();
					function pad2(n){ return n<10 ? '0'+n : n; }
					dtEl.value = now2.getFullYear() + '-' + pad2(now2.getMonth()+1) + '-' + pad2(now2.getDate()) + ' ' + pad2(now2.getHours()) + ':' + pad2(now2.getMinutes()) + ':' + pad2(now2.getSeconds());
				}
				console.debug('debitedDatetime value before submit:', dtEl ? dtEl.value : null);

				var fd = new FormData(formDebitar);
				// Ensure debited_datetime is present and set (in case FormData didn't pick hidden input)
				if (dtEl) {
					try { fd.set('debited_datetime', dtEl.value); } catch (e) { fd.append('debited_datetime', dtEl.value); }
				}
				// Use POST with _method=PUT so form fields are parsed correctly by PHP/Laravel
				try { fd.set('_method', 'PUT'); } catch (e) { fd.append('_method', 'PUT'); }
				// DEBUG: log FormData entries so developer can inspect what is sent
				try {
					for (var pair of fd.entries()) {
						console.debug('formDebitar fd:', pair[0], pair[1]);
					}
				} catch (e) { /* ignore in old browsers */ }
				fetch(action, {
					method: 'POST',
					headers: {
						'X-Requested-With': 'XMLHttpRequest',
						'X-CSRF-TOKEN': token
					},
					body: fd
				}).then(function(res){
					return res.json().then(function(data){ return { status: res.status, ok: res.ok, data: data }; });
				}).then(function(result){
					if (result.ok && result.data && result.data.success) {
						var vg = result.data.data || {};
						// Close modal (Bootstrap 5)
						try {
							var modalEl = document.getElementById('debitarModal');
							if (modalEl && window.bootstrap && window.bootstrap.Modal) {
								var m = window.bootstrap.Modal.getInstance(modalEl) || window.bootstrap.Modal.getOrCreateInstance(modalEl);
								m.hide();
							}
						} catch (err) { /* ignore */ }

						// Update row in main table
						var visitorGoalId = action.split('/').pop();
						var row = document.querySelector('tr[data-visitor-goal-id="' + visitorGoalId + '"]');
						if (row) {
							var tds = row.querySelectorAll('td');
							// debited amount is at index 7, debited datetime at index 8 (0-based)
							var debitedAmount = vg.debited_amount ?? vg.debitedAmount ?? null;
							var debitedDatetime = vg.debited_datetime ?? vg.debitedDatetime ?? null;
							if (typeof debitedAmount !== 'undefined' && tds[7]) {
								tds[7].textContent = isNaN(parseFloat(debitedAmount)) ? (debitedAmount ?? '-') : ('S/ ' + parseFloat(debitedAmount).toLocaleString('es-PE', {minimumFractionDigits:2, maximumFractionDigits:2}));
							}
							if (debitedDatetime && tds[8]) {
								tds[8].textContent = debitedDatetime;
							}
						}

						// Show success toast using SweetAlert2 if available
						if (window.Swal) {
							Swal.fire({
								toast: true,
								position: 'top-end',
								icon: 'success',
								title: result.data.message || 'Monto debitado actualizado.',
								showConfirmButton: false,
								timer: 2500,
								timerProgressBar: true
							});
						} else if (window.toastr) {
							toastr.success(result.data.message || 'Monto debitado actualizado.');
						} else {
							alert(result.data.message || 'Monto debitado actualizado.');
						}
					} else if (result.status === 422 && result.data && result.data.errors) {
						// validation errors
						var messages = [];
						for (var k in result.data.errors) { result.data.errors[k].forEach(function(m){ messages.push(m); }); }
						// If server complains that debited_datetime is missing, retry using JSON + X-HTTP-Method-Override
						var missingDt = result.data.errors.hasOwnProperty('debited_datetime');
						if (missingDt) {
							// Build JSON payload from form fields and retry as a JSON request
							var payload = {
								debited_amount: formDebitar.querySelector('[name="debited_amount"]').value,
								debit_comment: formDebitar.querySelector('[name="debit_comment"]').value,
								debited_datetime: dtEl ? dtEl.value : null
							};
							console.debug('Retrying debit update as JSON payload', payload);
							return fetch(action, {
								method: 'POST',
								headers: {
									'Content-Type': 'application/json',
									'X-Requested-With': 'XMLHttpRequest',
									'X-CSRF-TOKEN': token,
									'X-HTTP-Method-Override': 'PUT'
								},
								body: JSON.stringify(payload)
							}).then(function(res2){
								return res2.json().then(function(data2){ return { status: res2.status, ok: res2.ok, data: data2 }; });
							}).then(function(result2){
								// reuse main success/error handling by throwing to outer chain structure
								if (result2.ok && result2.data && result2.data.success) {
									// imitate previous success handling
									var vg = result2.data.data || {};
									try {
										var modalEl = document.getElementById('debitarModal');
										if (modalEl && window.bootstrap && window.bootstrap.Modal) {
											var m = window.bootstrap.Modal.getInstance(modalEl) || window.bootstrap.Modal.getOrCreateInstance(modalEl);
											m.hide();
										}
									} catch (err) { /* ignore */ }
									var visitorGoalId = action.split('/').pop();
									var row = document.querySelector('tr[data-visitor-goal-id="' + visitorGoalId + '"]');
									if (row) {
										var tds = row.querySelectorAll('td');
										var debitedAmount = vg.debited_amount ?? vg.debitedAmount ?? null;
										var debitedDatetime = vg.debited_datetime ?? vg.debitedDatetime ?? null;
										if (typeof debitedAmount !== 'undefined' && tds[7]) {
											tds[7].textContent = isNaN(parseFloat(debitedAmount)) ? (debitedAmount ?? '-') : ('S/ ' + parseFloat(debitedAmount).toLocaleString('es-PE', {minimumFractionDigits:2, maximumFractionDigits:2}));
										}
										if (debitedDatetime && tds[8]) {
											tds[8].textContent = debitedDatetime;
										}
									}
									if (window.Swal) {
										Swal.fire({ toast:true, position:'top-end', icon:'success', title: result2.data.message || 'Monto debitado actualizado.', showConfirmButton:false, timer:2500, timerProgressBar:true });
									} else if (window.toastr) {
										toastr.success(result2.data.message || 'Monto debitado actualizado.');
									} else {
										alert(result2.data.message || 'Monto debitado actualizado.');
									}
									return;
								} else {
									// fallthrough to show validation messages below
									var messages2 = [];
									if (result2.data && result2.data.errors) {
										for (var k2 in result2.data.errors) { result2.data.errors[k2].forEach(function(m){ messages2.push(m); }); }
									}
									if (window.Swal) {
										Swal.fire({ icon: 'error', title: 'Errores de validación', html: '<ul style="text-align:left">' + messages2.map(function(m){ return '<li>' + m + '</li>'; }).join('') + '</ul>' });
									} else {
										alert(messages2.join('\n'));
									}
									return;
								}
							}).catch(function(err2){
								console.error('Retry JSON error', err2);
								if (window.Swal) {
									Swal.fire({ icon: 'error', title: 'Error', text: 'Error al reintentar la solicitud como JSON.' });
								} else { alert('Error al reintentar la solicitud como JSON.'); }
								return;
							});
						}
						if (window.Swal) {
							Swal.fire({ icon: 'error', title: 'Errores de validación', html: '<ul style="text-align:left">' + messages.map(function(m){ return '<li>' + m + '</li>'; }).join('') + '</ul>' });
						} else {
							alert(messages.join('\n'));
						}
					} else {
						var msg = (result.data && result.data.message) ? result.data.message : 'Ocurrió un error al actualizar el monto debitado.';
						if (window.Swal) {
							Swal.fire({ icon: 'error', title: 'Error', text: msg });
						} else {
							alert(msg);
						}
					}
				}).catch(function(err){
					console.error(err);
					if (window.Swal) {
						Swal.fire({ icon: 'error', title: 'Error', text: 'Error de red al enviar el formulario.' });
					} else {
						alert('Error de red al enviar el formulario.');
					}
				});
			});
		}
	});
</script>
		</div>
	</div>
</div>
