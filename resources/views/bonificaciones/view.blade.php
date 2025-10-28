@extends('adminlte::page')

@section('title', 'Ver bonificaciones de visitadoras')

@section('content')
	<div class="bonificaciones-wrapper">
        <div class="card bonificaciones-hero-card shadow-sm border-0 mb-4">
            <div class="card-body d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
                <div class="d-flex align-items-start gap-3">
                    <div>
                        <h1 class="h3 text-dark mb-1">Octubre, 2025 - Bonificación de médicos prescriptores</h1>
                        <p class="text-muted mb-0">Resumen del cumplimiento, comisiones y desembolsos para las visitadoras durante el mes.</p>
                    </div>
                </div>
                <div class="text-muted small mt-2 mt-md-0">
                    Última actualización: 12/10/2025 08:15 a. m.
                </div>
                 <div class="align-items-end d-flex mt-2 mt-md-0 ms-auto"></div>
                    <a href="/dev/bonificaciones" class="btn btn-secondary align-items-end">
                        &larr; Volver
                    </a>
                 </div>
            </div>
        </div>

		<div class="card shadow-sm border-0">
			<div class="card-header bg-primary text-dark d-flex flex-column flex-md-row justify-content-between align-items-md-center">
				<h5 class="fw-semibold">Detalle consolidado</h5>
				<h6 class="small text-muted text-white ms-auto">Información preliminar sujeta a validación contable</h6>
			</div>
			<div class="card-body p-0">
				<div class="table-responsive">
					<table class="table table-striped mb-0 align-middle table-grobdi">
						<thead class="bonificaciones-table-head text-uppercase small">
							<tr data-pedidos="237">
								<th scope="col">Nombre</th>
								<th scope="col">Comisión por meta cumplida</th>
								<th scope="col">Monto meta</th>
								<th scope="col">Porcentaje actual</th>
								<th scope="col">Comisión actual</th>
								<th scope="col">Monto sin IGV</th>
								<th scope="col">Monto comisionado</th>
								<th scope="col">Monto debitado</th>
								<th scope="col">Fecha debitado</th>
								<th scope="col" class="text-center">Opciones</th>
							</tr>
						</thead>
						<tbody>
							<tr data-pedidos="182">
								<td class="fw-semibold">Visitadora 1</td>
								<td>3.50%</td>
								<td>S/ 15,000.00</td>
								<td>50%</td>
								<td>0%</td>
								<td>S/ 7,500.00</td>
								<td>S/ 0.00</td>
								<td>S/ -</td>
								<td>30/10/2025</td>
								<td>
									<div class="bonificaciones-actions">
										<a href="#" class="bonificaciones-link-action bonificaciones-link-action--view" data-bs-toggle="modal" data-bs-target="#avanceModal">ver avance</a>
										<a href="#" class="bonificaciones-link-action bonificaciones-link-action--debit" data-bs-toggle="modal" data-bs-target="#debitarModal">debitar</a>
									</div>
								</td>
							</tr>
							<tr data-pedidos="205">
								<td class="fw-semibold">Visitadora 2</td>
								<td>4.50%</td>
								<td>S/ 10,000.00</td>
								<td>95%</td>
								<td>1.00%</td>
								<td>S/ 13,500.00</td>
								<td>S/ 130.50</td>
								<td>S/ 130.50</td>
								<td>31/10/2025</td>
								<td>
									<div class="bonificaciones-actions">
										<a href="#" class="bonificaciones-link-action bonificaciones-link-action--view" data-bs-toggle="modal" data-bs-target="#avanceModal">ver avance</a>
										<a href="#" class="bonificaciones-link-action bonificaciones-link-action--debit" data-bs-toggle="modal" data-bs-target="#debitarModal">debitar</a>
									</div>
								</td>
							</tr>
							<tr>
								<td class="fw-semibold">Visitadora 3</td>
								<td>3.50%</td>
								<td>S/ 25,000.00</td>
								<td>100%</td>
								<td>3.50%</td>
								<td>S/ 20,000.00</td>
								<td>S/ 700.00</td>
								<td>S/ 700.00</td>
								<td>01/11/2025</td>
								<td>
									<div class="bonificaciones-actions">
										<a href="#" class="bonificaciones-link-action bonificaciones-link-action--view" data-bs-toggle="modal" data-bs-target="#avanceModal">ver avance</a>
										<a href="#" class="bonificaciones-link-action bonificaciones-link-action--debit" data-bs-toggle="modal" data-bs-target="#debitarModal">debitar</a>
									</div>
								</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
		</div>
		@include('bonificaciones.partials.debitarModal')
        @include('bonificaciones.partials.avanceModal')
	</div>
@stop

@section('css')
	<style>
		.bonificaciones-wrapper {
			background-color: #f7f7fb;
			border-radius: 16px;
			padding: 1.5rem;
		}

		.bonificaciones-hero-card {
			background-color: #f8efef;
			border-radius: 20px;
		}

		.bonificaciones-hero-card .card-body {
			padding: 1.75rem;
		}

		.card {
			border-radius: 20px;
		}

		.card-header {
			border-top-left-radius: 20px !important;
			border-top-right-radius: 20px !important;
		}

		.bonificaciones-actions {
			display: flex;
			align-items: center;
			justify-content: center;
			gap: 0.75rem;
		}

		.bonificaciones-link-action {
			font-weight: 600;
			text-transform: lowercase;
			text-decoration: none;
			transition: color 0.2s ease, transform 0.2s ease;
		}

		.bonificaciones-link-action--view {
			color: #2f6bd7;
		}

		.bonificaciones-link-action--view:hover {
			color: #1e4ea8;
			transform: translateY(-1px);
		}

		.bonificaciones-link-action--debit {
			color: #d9534f;
		}

		.bonificaciones-link-action--debit:hover {
			color: #b52c28;
			transform: translateY(-1px);
		}

		.debitar-modal {
			border-radius: 20px;
			box-shadow: 0 15px 45px rgba(15, 26, 58, 0.08);
		}

		.debitar-modal .modal-body {
			padding-left: 2rem;
			padding-right: 2rem;
		}

		.debitar-input-group .input-group-text {
			background-color: #f7f7fb;
			border: 1px solid #d8dbe6;
			font-weight: 600;
		}

		.debitar-input-group .form-control {
			border: 1px solid #d8dbe6;
			border-left: 0;
			padding: 0.75rem 1rem;
		}

		.debitar-textarea {
			border: 1px solid #d8dbe6;
			padding: 0.75rem 1rem;
			resize: vertical;
		}

		.debitar-form label {
			margin-bottom: 0.5rem;
		}

		.debitar-modal .btn-primary {
			border-radius: 50px;
			padding-left: 1.5rem;
			padding-right: 1.5rem;
		}

		.avance-summary-card {
			background-color: #ffffff;
			border: 1px solid #e6e9f4;
			border-radius: 16px;
			padding: 1.5rem;
			box-shadow: 0 12px 35px rgba(15, 26, 58, 0.08);
		}

		.avance-summary-card li {
			display: flex;
			justify-content: space-between;
			align-items: center;
			gap: 1rem;
			margin-bottom: 0.75rem;
			font-size: 0.95rem;
		}

		.avance-summary-card li:last-child {
			margin-bottom: 0;
		}

		.avance-summary-label {
			color: #6c757d;
			font-weight: 600;
		}

		.avance-summary-value {
			font-weight: 700;
			color: #1f3f88;
		}

		.avance-chart-container {
			background-color: #ffffff;
			border: 1px solid #e6e9f4;
			border-radius: 16px;
			padding: 1.5rem;
			box-shadow: 0 12px 35px rgba(15, 26, 58, 0.06);
			min-height: 280px;
			display: flex;
			align-items: center;
			justify-content: center;
		}

		.avance-chart-container canvas {
			width: 100% !important;
			height: 100% !important;
		}

		@media (max-width: 767.98px) {
			.bonificaciones-wrapper {
				padding: 1rem;
			}

			.debitar-modal .modal-body {
				padding-left: 1.5rem;
				padding-right: 1.5rem;
			}

			.avance-summary-card,
			.avance-chart-container {
				padding: 1.25rem;
			}
		}
	</style>
@stop

@section('js')
	<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
	<script>
		$(document).ready(function() {
			var avanceChartInstance = null;
			var currencyFormatter = new Intl.NumberFormat('es-PE', {
				style: 'currency',
				currency: 'PEN',
				minimumFractionDigits: 2
			});

			function parseCurrency(value) {
				if (!value) {
					return 0;
				}
				var sanitized = value.replace(/[^0-9,.-]/g, '').replace(/,/g, '');
				if (sanitized === '' || sanitized === '-') {
					return 0;
				}
				return parseFloat(sanitized);
			}

			function renderAvanceChart(percentage) {
				var ctx = document.getElementById('avanceChart');
				if (!ctx) {
					return;
				}
				var avance = Math.max(0, Math.min(percentage, 100));
				var restante = Math.max(0, 100 - avance);

				if (avanceChartInstance) {
					avanceChartInstance.destroy();
				}

				avanceChartInstance = new Chart(ctx, {
					type: 'doughnut',
					data: {
						labels: ['Avance', 'Restante'],
						datasets: [{
							data: [avance, restante],
							backgroundColor: ['#2f9e44', '#d9534f'],
							hoverBackgroundColor: ['#27923d', '#c84844'],
							borderWidth: 0
						}]
					},
					options: {
						responsive: true,
						maintainAspectRatio: false,
						cutout: '65%',
						plugins: {
							legend: {
								position: 'top',
								labels: {
									usePointStyle: true,
									boxWidth: 12,
									boxHeight: 12,
									padding: 16
								}
							},
							tooltip: {
								callbacks: {
									label: function(context) {
										return context.label + ': ' + context.parsed.toFixed(2) + '%';
									}
								}
							}
						}
					}
				});
			}

			function updateSummary(row) {
				var pedidos = row.data('pedidos') || 'N/D';
				var porcentaje = parseFloat(row.find('td').eq(3).text().replace('%', '').trim()) || 0;
				var meta = parseCurrency(row.find('td').eq(2).text());
				var sinIgv = parseCurrency(row.find('td').eq(5).text());
				var comisionado = parseCurrency(row.find('td').eq(6).text());
				var faltante = meta - sinIgv;

				$('#modal-pedidos-total').text(pedidos);
				$('#modal-monto-sinigv').text(currencyFormatter.format(sinIgv));
				$('#modal-comisionado').text(currencyFormatter.format(comisionado));

				var faltanteEl = $('#modal-faltante');
				faltanteEl.text(currencyFormatter.format(faltante));
				faltanteEl.removeClass('text-success text-danger');
				if (faltante <= 0) {
					faltanteEl.addClass('text-success');
				} else {
					faltanteEl.addClass('text-danger');
				}

				var estadoEl = $('#modal-estado');
				estadoEl.removeClass('text-success text-primary');
				if (porcentaje >= 100) {
					estadoEl.text('Meta lograda').addClass('text-success');
				} else {
					estadoEl.text(porcentaje.toFixed(2) + '%').addClass('text-primary');
				}

				$('#avanceModal').data('avancePercent', porcentaje);
			}

			$('.bonificaciones-link-action--debit').on('click', function(e) {
				e.preventDefault();
				$('#debitarModal').modal('show');
			});

			$('.bonificaciones-link-action--view').on('click', function(e) {
				e.preventDefault();
				var row = $(this).closest('tr');
				var nombre = row.find('td').eq(0).text();

				$('#avanceModalLabel').text(nombre + ' - Octubre, 2025, Médico Prescriptor');
				updateSummary(row);

				$('#avanceModal').modal('show');
			});

			$('#avanceModal').on('shown.bs.modal', function() {
				var porcentaje = $(this).data('avancePercent') || 0;
				renderAvanceChart(porcentaje);
			}).on('hidden.bs.modal', function() {
				if (avanceChartInstance) {
					avanceChartInstance.destroy();
					avanceChartInstance = null;
				}
			});
		});
	</script>
@stop
