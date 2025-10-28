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
				<form class="debitar-form">
					<div class="row g-4">
						<div class="col-12">
							<label for="debitarMonto" class="form-label fw-semibold text-dark">Monto debitado</label>
                            <div class="input-group debitar-input-group">
                                <span class="input-group-text text-muted">S/</span>
                                <input type="number" min="0" step="0.01" class="form-control w-full" id="debitarMonto" placeholder="0.00" />
                            </div>
						</div>
						<div class="col-12">
							<label for="debitarObservacion" class="form-label fw-semibold text-dark">Observación</label>
							<textarea class="form-control debitar-textarea" id="debitarObservacion" rows="4" placeholder="Agrega los detalles del débito..." ></textarea>
						</div>
					</div>
				</form>
			</div>
			<div class="modal-footer border-0 pt-0 d-flex justify-content-between">
				<button type="button" class="btn btn-secondary text-white" data-bs-dismiss="modal">Cancelar</button>
				<button type="button" class="btn btn-primary fw-bold">Guardar</button>
			</div>
		</div>
	</div>
</div>
