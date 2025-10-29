<div class="modal fade" id="avanceModal" tabindex="-1" aria-labelledby="avanceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 debitar-modal">
            <div class="modal-header border-0 pb-0">
                <div>
                    <h5 class="modal-title fw-semibold text-dark" id="avanceModalLabel">Visitadora 1 - Octubre, 2025, Médico Prescriptor</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body py-4">
                <div class="table-responsive mb-4">
                    <table class="table table-striped mb-0 align-middle table-grobdi">
                        <thead class="bonificaciones-table-head text-uppercase small">
                            <tr>
                                <th>Doctor</th>
                                <th>Pedidos totales</th>
                                <th>Monto sin IGV</th>
                            </tr>
                        </thead>
                        <tbody id="doctorsTableBody">
                            <!-- Rows will be injected from backend via JS -->
                        </tbody>
                    </table>
                </div>
                <div class="row g-4 align-items-start">
                    <div class="col-12 col-lg-5">
                        <div class="avance-summary-card h-100">
                            <ul class="list-unstyled mb-0">
                                <li>
                                    <span class="avance-summary-label">Pedidos total:</span>
                                    <span class="avance-summary-value" id="modal-pedidos-total">N/D</span>
                                </li>
                                <li>
                                    <span class="avance-summary-label">Monto total sin IGV:</span>
                                    <span class="avance-summary-value" id="modal-monto-sinigv">S/ 0.00</span>
                                </li>
                                <li>
                                    <span class="avance-summary-label">Faltante para la meta general:</span>
                                    <span class="avance-summary-value text-danger" id="modal-faltante">S/ 0.00</span>
                                </li>
                                <li>
                                    <span class="avance-summary-label">Avance meta general (%):</span>
                                    <span class="avance-summary-value text-success" id="modal-estado">Meta lograda</span>
                                </li>
                                <li>
                                    <span class="avance-summary-label">Monto total comisionado:</span>
                                    <span class="avance-summary-value" id="modal-comisionado">S/ 0.00</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="col-12 col-lg-7">
                        <div class="avance-chart-container">
                            <div class="d-flex flex-column w-100">
                                <div style="height:260px;">
                                    <canvas id="avanceChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0 d-flex justify-content-end">
                <button type="button" class="btn btn-primary fw-semibold" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
