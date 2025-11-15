@extends('adminlte::page')

@section('title', 'Conexiones de Usuarios')

@section('content')
	<x-grobdi.layout.header-card
		title="📡 Monitoreo de Conexiones"
		subtitle="Visualiza quién está conectado, desde qué dispositivo y cuándo fue su última actividad."
	/>

	<div class="row g-3 mb-4">
		<div class="col-sm-6 col-lg-3">
			<div class="card shadow-sm h-100">
				<div class="card-body">
					<span class="text-muted text-uppercase small">Sesiones totales</span>
					<h3 class="mt-2 mb-0 fw-bold">{{ number_format($statistics['total_sessions']) }}</h3>
					<p class="mb-0 text-secondary">Historial registrado en la tabla de sesiones.</p>
				</div>
			</div>
		</div>
		<div class="col-sm-6 col-lg-3">
			<div class="card shadow-sm h-100">
				<div class="card-body">
					<span class="text-muted text-uppercase small">Sesiones activas</span>
					<h3 class="mt-2 mb-0 fw-bold text-success">{{ number_format($statistics['active_sessions']) }}</h3>
					<p class="mb-0 text-secondary">Última actividad dentro del tiempo de sesión.</p>
				</div>
			</div>
		</div>
		<div class="col-sm-6 col-lg-3">
			<div class="card shadow-sm h-100">
				<div class="card-body">
					<span class="text-muted text-uppercase small">Usuarios únicos</span>
					<h3 class="mt-2 mb-0 fw-bold">{{ number_format($statistics['unique_users']) }}</h3>
					<p class="mb-0 text-secondary">Usuarios que iniciaron sesión alguna vez.</p>
				</div>
			</div>
		</div>
		<div class="col-sm-6 col-lg-3">
			<div class="card shadow-sm h-100">
				<div class="card-body">
					<span class="text-muted text-uppercase small">Usuarios en línea</span>
					<h3 class="mt-2 mb-0 fw-bold text-primary">{{ number_format($statistics['active_users']) }}</h3>
					<p class="mb-0 text-secondary">Usuarios con al menos una sesión activa.</p>
				</div>
			</div>
		</div>
	</div>

	<x-grobdi.layout.table-card
		title="Sesiones por usuario"
		tableClass="table-bordered table-striped table-hover mb-0"
	>
		<x-slot:actions>
			<span class="badge badge-primary">{{ $statistics['active_users'] }} usuarios activos</span>
		</x-slot:actions>

		<thead>
			<tr>
				<th>Usuario</th>
				<th>Correo</th>
				<th>ID asociado</th>
				<th>Sesiones activas</th>
				<th>Sesiones totales</th>
				<th>Última actividad</th>
			</tr>
		</thead>
		<tbody>
			@forelse ($sessionsByUser as $summary)
				<tr>
								<td class="fw-bold">{{ $summary['display_name'] }}</td>
								<td>{{ $summary['display_email'] }}</td>
								<td>
									@if ($summary['user_id'])
										<span class="badge badge-dark">{{ $summary['user_id'] }}</span>
									@elseif ($summary['payload_user_id'])
										<span class="badge badge-dark">{{ $summary['payload_user_id'] }}</span>
									@else
										<span class="text-muted">—</span>
									@endif
								</td>
								<td>
									<span class="badge badge-success">{{ $summary['active_count'] }}</span>
								</td>
								<td>
									<span class="badge badge-info">{{ $summary['total_count'] }}</span>
								</td>
								<td>
									@if ($summary['last_activity_at'])
										<div class="d-flex flex-column">
											<span>{{ $summary['last_activity_at']->format('d/m/Y H:i') }}</span>
											<small class="text-muted">{{ $summary['last_activity_at']->diffForHumans() }}</small>
										</div>
									@else
										<span class="text-muted">Sin registros</span>
									@endif
								</td>
							</tr>
			@empty
				@include('empty-table', ['colspan' => 6, 'dataLength' => 0, 'personalizedMessage' => 'Sin usuarios conectados'])
			@endforelse
		</tbody>
	</x-grobdi.layout.table-card>

	<x-grobdi.layout.table-card
		title="Detalle de sesiones"
		tableClass="table-bordered table-striped table-hover mb-0"
	>
		<x-slot:actions>
			<span class="badge badge-secondary">{{ $statistics['total_sessions'] }} registros</span>
		</x-slot:actions>

		<thead>
			<tr>
				<th>Usuario</th>
				<th>Estado</th>
				<th>Dispositivo</th>
				<th>Navegador</th>
				<th>IP</th>
				<th>ID usuario</th>
				<th>Sesión</th>
				<th>Última actividad</th>
				<th>Detalles</th>
			</tr>
		</thead>
		<tbody>
			@forelse ($sessions as $session)
							@php($modalId = 'session-modal-' . md5($session->id))
							<tr>
								<td>
									<div class="d-flex flex-column">
										<span class="fw-bold">{{ $session->user_display_name }}</span>
										<small class="text-muted">{{ $session->user_display_email }}</small>
									</div>
								</td>
								<td>
									@if ($session->is_online)
										<span class="badge badge-success">En línea</span>
									@else
										<span class="badge badge-secondary">Finalizada</span>
									@endif
								</td>
								<td>
									<div class="d-flex flex-column">
										<span>{{ $session->device_type }}</span>
										<small class="text-muted">{{ $session->platform_name }}</small>
									</div>
								</td>
								<td>{{ $session->browser_name }}</td>
								<td>{{ $session->ip_address ?? 'N/D' }}</td>
								<td>
									@if ($session->user_id)
										<span class="badge badge-dark">{{ $session->user_id }}</span>
									@elseif ($session->payload_user_id)
										<span class="badge badge-dark">{{ $session->payload_user_id }}</span>
									@else
										<span class="text-muted">—</span>
									@endif
								</td>
								<td>
									<code class="small">{{ \Illuminate\Support\Str::limit($session->id, 16) }}</code>
								</td>
								<td>
									@if ($session->last_activity_at)
										<div class="d-flex flex-column">
											<span>{{ $session->last_activity_at->format('d/m/Y H:i') }}</span>
											<small class="text-muted">{{ $session->last_activity_at->diffForHumans() }}</small>
										</div>
									@else
										<span class="text-muted">Sin registros</span>
									@endif
								</td>
							<td class="text-center">
								<x-grobdi.button variant="outline" size="sm" icon="fa fa-eye" type="button" data-toggle="modal" :data-target="'#' . $modalId">
									Ver detalles
								</x-grobdi.button>
							</td>
							</tr>
			@empty
				@include('empty-table', ['colspan' => 9, 'dataLength' => 0, 'personalizedMessage' => 'Sin sesiones registradas'])
			@endforelse
		</tbody>
	</x-grobdi.layout.table-card>
@foreach ($sessions as $session)
	@php($modalId = 'session-modal-' . md5($session->id))
	<div class="modal fade" id="{{ $modalId }}" tabindex="-1" role="dialog" aria-labelledby="{{ $modalId }}Label" aria-hidden="true">
		<div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="{{ $modalId }}Label">Detalles de la sesión</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<div class="mb-3">
						<h6 class="fw-bold">Información general</h6>
						<ul class="list-unstyled mb-0 small">
							<li><strong>Usuario:</strong> {{ $session->user_display_name }}</li>
							<li><strong>Email:</strong> {{ $session->user_display_email }}</li>
							<li><strong>ID (tabla):</strong> {{ $session->user_id ?? '—' }}</li>
							<li><strong>ID (payload):</strong> {{ $session->payload_user_id ?? '—' }}</li>
							<li><strong>IP:</strong> {{ $session->ip_address ?? 'N/D' }}</li>
							<li><strong>Navegador:</strong> {{ $session->browser_name }}</li>
							<li><strong>Dispositivo:</strong> {{ $session->device_type }} ({{ $session->platform_name }})</li>
							<li><strong>Estado:</strong> {{ $session->is_online ? 'En línea' : 'Finalizada' }}</li>
							<li><strong>Última actividad:</strong> {{ $session->last_activity_at ? $session->last_activity_at->format('d/m/Y H:i:s') : 'Sin registros' }}</li>
							<li><strong>ID de sesión:</strong> {{ $session->id }}</li>
						</ul>
					</div>
					<div>
						<h6 class="fw-bold">Payload decodificado</h6>
						<pre class="bg-light p-3 rounded small mb-0">{{ $session->payload_json }}</pre>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
				</div>
			</div>
		</div>
	</div>
@endforeach
@stop

@section('js')
	<script>
		// Interacción ligera: recarga manual usando F5 mantiene los datos actualizados.
	</script>
@stop
