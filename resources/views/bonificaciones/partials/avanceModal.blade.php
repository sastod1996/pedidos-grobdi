<div class="modal fade modal-grobdi" id="avanceModal" tabindex="-1" aria-labelledby="avanceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="avanceModalLabel">Detalle de avance de la visitadora</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive mb-4">
                    <table class="table table-striped mb-0 align-middle table-grobdi">
                        <thead>
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
                        <div class="info-section">
                            <ul class="list-unstyled mb-0">
                                <li class="mb-2">
                                    <span class="fw-semibold">Pedidos total:</span>
                                    <span id="modal-pedidos-total">N/D</span>
                                </li>
                                <li class="mb-2">
                                    <span class="fw-semibold">Monto total sin IGV:</span>
                                    <span id="modal-monto-sinigv">S/ 0.00</span>
                                </li>
                                <li class="mb-2">
                                    <span class="fw-semibold">Faltante para la meta general:</span>
                                    <span class="text-danger" id="modal-faltante">S/ 0.00</span>
                                </li>
                                <li class="mb-2">
                                    <span class="fw-semibold">Avance meta general (%):</span>
                                    <span class="text-success" id="modal-estado">Meta lograda</span>
                                </li>
                                <li class="mb-0">
                                    <span class="fw-semibold">Monto total comisionado:</span>
                                    <span id="modal-comisionado">S/ 0.00</span>
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
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
