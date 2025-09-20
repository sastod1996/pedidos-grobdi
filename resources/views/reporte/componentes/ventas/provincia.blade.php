<!-- Tab Provincia -->
<div class="tab-pane fade" id="provincia" role="tabpanel">
    <h4 class="mb-4">
        <i class="fas fa-map-marker-alt text-danger"></i> Reporte por Departamento
    </h4>

    <!-- Filtros -->
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-primary text-white">
            <h6 class="mb-0">
                <i class="fas fa-filter me-2"></i>Filtros de Búsqueda
            </h6>
        </div>
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label for="fecha_inicio_provincia" class="form-label fw-bold">
                        <i class="fas fa-calendar-alt text-primary me-1"></i>Fecha Inicio
                    </label>
                    <input type="date" class="form-control form-control-lg" id="fecha_inicio_provincia"
                        placeholder="Seleccionar fecha inicio">
                </div>
                <div class="col-md-4">
                    <label for="fecha_fin_provincia" class="form-label fw-bold">
                        <i class="fas fa-calendar-alt text-primary me-1"></i>Fecha Fin
                    </label>
                    <input type="date" class="form-control form-control-lg" id="fecha_fin_provincia"
                        placeholder="Seleccionar fecha fin">
                </div>
                <div class="col-md-4">
                    <div class="d-grid gap-2">
                        <button class="btn btn-primary btn-lg" id="filtrar_provincia">
                            <i class="fas fa-search me-2"></i>Aplicar Filtros
                        </button>
                        <button class="btn btn-outline-secondary" id="limpiar_provincia">
                            <i class="fas fa-eraser me-2"></i>Limpiar Filtros
                        </button>
                    </div>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-12">
                    <div class="alert alert-info mb-0" role="alert">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Nota:</strong> Los filtros de fecha son opcionales.
                        Si no selecciona fechas, se mostrarán todos los datos disponibles.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráfico de Barras Principal -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-bar"></i> Ventas por <span id="tituloBarGeo">Departamento</span>
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="provinciaChart" width="800" height="400"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráfico de Pie y Tabla -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-pie"></i> Distribución de Ventas por <span
                            id="tituloPieGeo">Departamento</span>
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="provinciaPieChart" style="height: 300px;"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-table"></i> Estadísticas por <span id="tituloTablaGeo">Departamento</span>
                    </h5>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th id="thGeoCol">Departamento</th>
                                <th>Ventas (S/)</th>
                                <th>%</th>
                                <th>Pedidos</th>
                                <th>Detalles</th>
                            </tr>
                        </thead>
                        <tbody id="tablaProvinciaBody">
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted"></td>
                            </tr>
                        </tbody>
                        <tfoot class="table-secondary" id="tablaProvinciaFoot">
                            <tr>
                                <th>Total</th>
                                <th id="totalVentasProvincia">S/ 0.00</th>
                                <th><span class="badge bg-dark">100%</span></th>
                                <th id="totalPedidosProvincia">0</th>
                                <th>-</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Botón Descargar Excel -->
    <div class="text-center mt-4">
        <button class="btn btn-success" id="descargar-excel-provincia">
            <i class="fas fa-download"></i> Descargar Detallado Excel
        </button>
    </div>
</div>

<!-- Modal para mostrar pedidos detallados por departamento -->
<div class="modal fade" id="modalPedidosDepartamento" tabindex="-1" aria-labelledby="modalPedidosDepartamentoLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalPedidosDepartamentoLabel">
                    <i class="fas fa-list-alt me-2"></i>
                    Pedidos Detallados - <span id="modalDepartamentoNombre"></span>
                </h5>
                <button type="button" class="btn-close btn-close-white" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Resumen del departamento -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card bg-info text-white">
                            <div class="card-body text-center">
                                <i class="fas fa-shopping-cart fa-2x mb-2"></i>
                                <h4 id="modalTotalPedidos">0</h4>
                                <small>Total Pedidos</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-success text-white">
                            <div class="card-body text-center">
                                <i class="fas fa-money-bill-wave fa-2x mb-2"></i>
                                <h4 id="modalTotalVentas">S/ 0.00</h4>
                                <small>Total Ventas</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-warning text-dark">
                            <div class="card-body text-center">
                                <i class="fas fa-calculator fa-2x mb-2"></i>
                                <h4 id="modalPromedioVenta">S/ 0.00</h4>
                                <small>Promedio por Pedido</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabla de pedidos detallados -->
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>ID Pedido</th>
                                <th>Fecha</th>
                                <th>Total (S/)</th>
                                <th>Distrito Original</th>
                                <th>Visitadora</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody id="tablaPedidosDetallados">
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary">
                    <i class="fas fa-times me-1"></i>Cerrar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    // Función para cerrar el modal manualmente
    function cerrarModalDepartamento() {
        const modalElement = document.getElementById('modalPedidosDepartamento');

        // Ocultar el modal
        modalElement.style.display = 'none';
        modalElement.classList.remove('show');
        modalElement.setAttribute('aria-hidden', 'true');

        // Remover backdrop
        const backdrop = document.querySelector('.modal-backdrop');
        if (backdrop) {
            backdrop.remove();
        }

        // Remover clase modal-open del body
        document.body.classList.remove('modal-open');
        document.body.style.overflow = '';
        document.body.style.paddingRight = '';
    }

    // Agregar eventos a los botones de cerrar
    document.addEventListener('DOMContentLoaded', function() {
        // Botón X del header
        const btnCloseHeader = document.querySelector('#modalPedidosDepartamento .btn-close');
        if (btnCloseHeader) {
            btnCloseHeader.addEventListener('click', cerrarModalDepartamento);
        }

        // Botón Cerrar del footer
        const btnCloseFooter = document.querySelector('#modalPedidosDepartamento .btn-secondary');
        if (btnCloseFooter) {
            btnCloseFooter.addEventListener('click', cerrarModalDepartamento);
        }

        // Cerrar al hacer clic fuera del modal
        const modal = document.getElementById('modalPedidosDepartamento');
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                cerrarModalDepartamento();
            }
        });
    });
</script>
