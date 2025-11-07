@extends('adminlte::page')

@section('title', 'Ver bonificaciones de visitadoras')

@section('content')
	@php
		$metaData = $meta ?? [];
		$periodLabel = $metaData['period_label'] ?? null;
		if (! $periodLabel) {
			$monthInfo = $metaData['month'] ?? null;
			if (is_array($monthInfo) && (($monthInfo['type'] ?? null) === 'month')) {
				$monthYear = $monthInfo['year'] ?? now()->year;
				$monthValue = $monthInfo['value'] ?? now()->month;
				$periodLabel = \Carbon\Carbon::createFromDate($monthYear, $monthValue, 1)
					->locale('es')
					->translatedFormat('F, Y');
			} elseif (! empty($metaData['start_date']) && ! empty($metaData['end_date'])) {
				$periodLabel = \Carbon\Carbon::parse($metaData['start_date'])->format('d/m/Y') . ' - '
					. \Carbon\Carbon::parse($metaData['end_date'])->format('d/m/Y');
			}
		}
		$tipoMedicoLabel = $metaData['tipo_medico_label'] ?? null;
		$tipoMedicoSlug = $metaData['tipo_medico_slug'] ?? ($metaData['tipo_medico'] ?? null);
		if (! $tipoMedicoLabel && $tipoMedicoSlug) {
			$tipoMedicoLabel = ucfirst($tipoMedicoSlug);
		}
		$titleSegments = [];
		if ($periodLabel) {
			$titleSegments[] = $periodLabel;
		}
		$titleSegments[] = 'Bonificación de médicos ' . ($tipoMedicoLabel ?? '');
		$heroTitle = trim(implode(' - ', array_filter($titleSegments)));
		$formatCurrency = static function ($value) {
			if ($value === null || $value === '') {
				return '-';
			}
			if (is_numeric($value)) {
				return 'S/ ' . number_format((float) $value, 2, '.', ',');
			}
			// try to extract numeric amount from string, fallback to original text
			if (is_string($value)) {
				$numeric = preg_replace('/[^0-9\\.\-]/', '', $value);
				if ($numeric !== '' && is_numeric($numeric)) {
					return 'S/ ' . number_format((float) $numeric, 2, '.', ',');
				}
			}
			return (string) $value;
		};
		$formatPercentage = static function ($value, int $decimals = 2, bool $clampTo100 = false) {
			if ($value === null || $value === '') {
				return '-';
			}
			if (! is_numeric($value)) {
				return $value;
			}
			$numeric = (float) $value;
			if ($clampTo100) {
				$numeric = max(0, min(100, $numeric));
			}
			return number_format($numeric, $decimals, '.', ',') . '%';
		};
	@endphp
	<div class="bonificaciones-wrapper">
        <div class="card bonificaciones-hero-card shadow-sm border-0 mb-4">
            <div class="card-body d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
                <div class="d-flex align-items-start gap-3">
				<div>
					<h1 class="h3 text-dark mb-1">{{ $heroTitle !== '' ? $heroTitle : 'Bonificación de médicos prescriptores' }}</h1>
					<p class="text-muted mb-0">Resumen del cumplimiento, comisiones y desembolsos para las visitadoras durante el periodo seleccionado.</p>
				</div>
                </div>
                 <div class="align-items-end d-flex mt-2 mt-md-0 ms-auto"></div>
                    <a href="{{ route('bonificaciones.index') }}" class="btn btn-secondary align-items-end">
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
							@forelse($visitorGoals as $vg)
								<tr data-pedidos="{{ $vg['total_sub_total_sin_igv'] ?? '' }}" data-visitor-goal-id="{{ $vg['id'] }}">
									<td class="fw-semibold">{{ $vg['visitadora']['name'] ?? '-' }}</td>
									<td>{{ $formatPercentage($vg['commission_percentage'] ?? null) }}</td>
									<td>{{ $formatCurrency($vg['goal_amount'] ?? null) }}</td>
								@php
									$__pct = isset($vg['porcentaje_actual']) ? (float) $vg['porcentaje_actual'] : null;
									$__pct_clamped = $formatPercentage($__pct, 2, true);
									$__pct_value = isset($vg['porcentaje_actual']) ? max(0, min(100, (float) $vg['porcentaje_actual'])) : null;
								@endphp
									<td>
										<div class="d-flex align-items-center gap-2">
										<div class="me-2 fw-semibold">{{ $__pct_clamped }}</div>
										<div class="progress flex-grow-1">
											<div class="progress-bar bg-success" role="progressbar" style="width: {{ $__pct_value !== null ? $__pct_value.'%' : '0%' }};" aria-valuenow="{{ $__pct_value ?? 0 }}" aria-valuemin="0" aria-valuemax="100"></div>
											</div>
										</div>
									</td>
									<td>{{ $formatPercentage($vg['comision_actual'] ?? null) }}</td>
									<td>{{ $formatCurrency($vg['total_sub_total_sin_igv'] ?? null) }}</td>
									<td>{{ $formatCurrency($vg['monto_comisionado'] ?? null) }}</td>
									<td>
										@php
											$debited = $vg['debited_amount'] ?? null;
											$debitedAmountValue = null;
											// numeric directly
											if (is_numeric($debited)) {
												$debitedAmountValue = (float) $debited;
											} elseif (is_string($debited)) {
												// try to extract numeric from string
												$clean = preg_replace('/[^0-9\.,\-]/', '', $debited);
												$clean = str_replace(',', '', $clean);
												if ($clean !== '') {
													$debitedAmountValue = (float) $clean;
												}
											} elseif (is_array($debited) || is_object($debited)) {
												// try common keys/properties
												$keys = ['amount', 'monto', 'valor', 'value', 'debited_amount', 'total'];
												foreach ($keys as $k) {
													if (is_array($debited) && isset($debited[$k])) { $debitedAmountValue = (float) $debited[$k]; break; }
													if (is_object($debited) && isset($debited->{$k})) { $debitedAmountValue = (float) $debited->{$k}; break; }
												}
											}
										@endphp
										@if($debitedAmountValue !== null)
											S/ {{ number_format($debitedAmountValue, 2, '.', ',') }}
										@elseif(is_string($debited) && trim($debited) !== '')
											{{ $debited }}
										@else
											-
										@endif
									</td>
									<td>
										@php
											$debitedAt = $vg['debited_datetime'] ?? null;
											$debitedAtFormatted = null;
											if (! empty($debitedAt) && $debitedAt !== 'No se ha debitado aún') {
												try {
													$debitedAtFormatted = \Carbon\Carbon::parse($debitedAt)->format('d/m/Y H:i');
												} catch (\Throwable $e) {
													$debitedAtFormatted = $debitedAt;
												}
											}
										@endphp
										@if($debitedAtFormatted)
											{{ $debitedAtFormatted }}
										@else
											{{ $debitedAt ?? '-' }}
										@endif
									</td>
									<td>
										<div class="bonificaciones-actions">
											<a href="#" data-visitor-goal-id="{{ $vg['id'] }}" class="bonificaciones-link-action bonificaciones-link-action--view" data-bs-toggle="modal" data-bs-target="#avanceModal">ver avance</a>
											<a href="#" data-visitor-goal-id="{{ $vg['id'] }}" class="bonificaciones-link-action bonificaciones-link-action--debit" data-bs-toggle="modal" data-bs-target="#debitarModal">debitar</a>
										</div>
									</td>
								</tr>
							@empty
								<tr class="text-center">
									<td colspan="10">No hay datos para esta meta.</td>
								</tr>
							@endforelse
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
	<script>
		window.bonificacionesMetaContext = {
			periodLabel: @json($periodLabel),
			tipoMedicoLabel: @json($tipoMedicoLabel ?? null),
			tipoMedico: @json($tipoMedicoSlug ?? null),
			tipoMedicoSlug: @json($tipoMedicoSlug ?? null)
		};
	</script>
	<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
	<script>
		$(document).ready(function() {
			var avanceChartInstance = null;
			var doctorsChartInstance = null;
			var metaContext = window.bonificacionesMetaContext || {};
			var currencyFormatter = new Intl.NumberFormat('es-PE', {
				style: 'currency',
				currency: 'PEN',
				minimumFractionDigits: 2
			});

			function resolveMetaLabel(overrideMeta) {
				var meta = overrideMeta || metaContext || {};
				var period = meta.period_label || meta.periodLabel || '';
				var tipoLabel = meta.tipo_medico_label || meta.tipoMedicoLabel || '';
				if (!tipoLabel) {
					var slug = meta.tipo_medico_slug || meta.tipoMedicoSlug || meta.tipo_medico || meta.tipoMedico || '';
					if (slug) {
						tipoLabel = slug;
					}
				}
				if (tipoLabel) {
					tipoLabel = tipoLabel.toString();
					tipoLabel = tipoLabel.charAt(0).toUpperCase() + tipoLabel.slice(1);
				}

				var pieces = [];
				if (period) {
					pieces.push(period);
				}
				if (tipoLabel) {
					pieces.push('Médicos ' + tipoLabel);
				}

				return pieces.join(', ');
			}

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

			// Update modal summary using API response data
			function updateSummaryFromApi(apiData, nombre, metaData) {
				var pedidos = apiData.total_pedidos ?? 'N/D';
				var sinIgv = parseFloat(apiData.total_amount_without_igv) || 0;
				var comisionado = parseFloat(apiData.commissioned_amount) || 0;
				var faltante = parseFloat(apiData.faltante_para_meta) || 0;
				var porcentaje = parseFloat(apiData.avance_meta_general) || 0;

				var metaLabel = resolveMetaLabel(metaData);
				if (metaLabel) {
					$('#avanceModalLabel').text(nombre + ' - ' + metaLabel);
				} else {
					$('#avanceModalLabel').text(nombre);
				}
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

				// Defer rendering the chart until the modal is visible (canvas needs layout).
				// Store the porcentaje on the modal and let the shown.bs.modal handler render it.
				$('#avanceModal').data('avancePercent', porcentaje);
			}

			$('.bonificaciones-link-action--debit').on('click', function(e) {
				e.preventDefault();
				// Ensure form action in the debitar modal is set before showing it
				var trigger = this;
				var visitorGoalId = trigger.getAttribute('data-visitor-goal-id') || $(this).closest('tr').data('visitor-goal-id');
				var container = document.getElementById('debitarModalBody');
				if (container && visitorGoalId) {
					var template = container.getAttribute('data-update-url-template');
					if (template) {
						var actionUrl = template.replace('__ID__', visitorGoalId);
						var form = document.getElementById('formDebitar');
						if (form) {
							form.setAttribute('action', actionUrl);
							form.dataset.visitorGoalId = visitorGoalId;
						}
					}
				}

				$('#debitarModal').modal('show');
			});

			$('.bonificaciones-link-action--view').on('click', function(e) {
				e.preventDefault();
				var trigger = this;
				var visitorGoalId = trigger.getAttribute('data-visitor-goal-id') || $(this).closest('tr').data('visitor-goal-id');
				var nombre = $(this).closest('tr').find('td').eq(0).text();

				if (!visitorGoalId) {
					// fallback to static behavior
					var row = $(this).closest('tr');
					updateSummaryFromApi({
						total_pedidos: row.data('pedidos'),
						total_amount_without_igv: row.find('td').eq(5).text().replace(/[^0-9.-]+/g, ''),
						faltante_para_meta: 0,
						avance_meta_general: parseFloat(row.find('td').eq(3).text().replace('%','')) || 0,
						commissioned_amount: row.find('td').eq(6).text().replace(/[^0-9.-]+/g, '')
					}, nombre, metaContext);
					$('#avanceModal').modal('show');
					return;
				}

				// call backend for chart data
				var tokenEl = document.querySelector('meta[name="csrf-token"]');
				var token = tokenEl ? tokenEl.getAttribute('content') : '';

				// Use the bonificaciones route for details
				fetch('{{ url('/bonificaciones/metas/details/') }}' + '/' + visitorGoalId, {
					method: 'POST',
					headers: {
						'Content-Type': 'application/json',
						'X-CSRF-TOKEN': token
					},
					body: JSON.stringify({})
				}).then(function(res){
					return res.json();
				}).then(function(json){
					if (json && json.success) {
						var chartData = json['chart-data'] || {};
						var doctorsData = json['doctors-data'] || [];
						if (json.meta) {
							metaContext = json.meta;
							window.bonificacionesMetaContext = metaContext;
						}
						updateSummaryFromApi(chartData, nombre, metaContext);
						updateDoctorsFromApi(doctorsData);
						$('#avanceModal').modal('show');
					} else {
						alert('No se pudo obtener los datos de avance.');
					}
				}).catch(function(err){
					console.error(err);
					alert('Error al obtener datos de avance.');
				});
			});

			$('#avanceModal').on('shown.bs.modal', function() {
				var porcentaje = $(this).data('avancePercent') || 0;
				renderAvanceChart(porcentaje);
			}).on('hidden.bs.modal', function() {
				if (avanceChartInstance) {
					avanceChartInstance.destroy();
					avanceChartInstance = null;
				}
				if (typeof doctorsChartInstance !== 'undefined' && doctorsChartInstance) {
					doctorsChartInstance.destroy();
					doctorsChartInstance = null;
				}
			});

			// Render doctors list and chart
			function updateDoctorsFromApi(doctors) {
				// doctors: array of objects { name, total_pedidos, total_amount_without_igv } or similar
				const listEl = document.getElementById('doctorsList');
				const tableBody = document.getElementById('doctorsTableBody');
				// If neither list nor table exist, nothing to do
				if (!listEl && !tableBody) return;
				if (listEl) listEl.innerHTML = '';
				if (tableBody) tableBody.innerHTML = '';
				const labels = [];
				const values = [];
				const bgColors = [];
				(doctors || []).forEach(function(d, idx){
					const name = d.doctor_name || d.name || d.nombre || d.doctor || ('Doctor ' + (idx+1));
					const pedidos = d.total_pedidos ?? d.pedidos ?? d.count ?? 0;
					const monto = parseFloat(d.total_amount_without_igv ?? d.monto_sin_igv ?? d.amount ?? 0) || 0;
					if (!pedidos && monto <= 0) {
						return;
					}
					labels.push(name);
					values.push(monto);
					const color = 'hsl(' + ((idx * 47) % 360) + ' 70% 50%)';
					bgColors.push(color);

					const item = document.createElement('div');
					item.className = 'list-group-item d-flex justify-content-between align-items-center';
					item.innerHTML = '<div class="small fw-semibold">' + name + '</div>' +
						'<div class="text-end small text-muted">' + pedidos + ' pedidos<br><strong>S/ ' + monto.toLocaleString('es-PE', {minimumFractionDigits:2, maximumFractionDigits:2}) + '</strong></div>';
					if (listEl) listEl.appendChild(item);

					// Also append row to the table in the modal (if present)
					if (tableBody) {
						const tr = document.createElement('tr');
						const tdName = document.createElement('td'); tdName.className = 'fw-semibold'; tdName.textContent = name;
						const tdPedidos = document.createElement('td'); tdPedidos.textContent = pedidos;
						const tdMonto = document.createElement('td'); tdMonto.textContent = 'S/ ' + monto.toLocaleString('es-PE', {minimumFractionDigits:2, maximumFractionDigits:2});
						tr.appendChild(tdName);
						tr.appendChild(tdPedidos);
						tr.appendChild(tdMonto);
						tableBody.appendChild(tr);
					}
				});

				// draw horizontal bar chart
				const ctx = document.getElementById('doctorsChart');
				if (!ctx) return;
				if (typeof doctorsChartInstance !== 'undefined' && doctorsChartInstance) {
					doctorsChartInstance.destroy();
				}

				doctorsChartInstance = new Chart(ctx, {
					type: 'bar',
					data: {
						labels: labels,
						datasets: [{
							label: 'Monto sin IGV',
							data: values,
							backgroundColor: bgColors,
						}]
					},
					options: {
						indexAxis: 'y',
						responsive: true,
						maintainAspectRatio: false,
						plugins: { legend: { display: false } },
						scales: { x: { ticks: { callback: function(value){ return 'S/ ' + value.toLocaleString(); } } } }
					}
				});
			}
		});
	</script>
@stop
