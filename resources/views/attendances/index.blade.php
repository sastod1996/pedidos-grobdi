@extends('adminlte::page')

@section('title', 'Modulo de conexion con el huellero ZKTeco')

@section('content')
<div class="container">
    <h1>Asistencias importadas</h1>

    @if(!empty($deviceUsersMapped) && count($deviceUsersMapped) > 0)
        <h3>Usuarios en el huellero</h3>
        <div class="list-group mb-4">
            @foreach($deviceUsersMapped as $du)
                <div class="list-group-item">
                    <strong>PIN:</strong> {{ $du['pin'] }}
                    @if($du['name']) - <strong>Nombre:</strong> {{ $du['name'] }} @endif
                    @if($du['last_attendance']) - <strong>Última marca:</strong> {{ $du['last_attendance'] }} @endif
                    <div class="small text-muted">Raw: <code>{{ json_encode($du['raw'], JSON_UNESCAPED_UNICODE) }}</code></div>
                </div>
            @endforeach
        </div>
    @endif

    <form method="get" class="mb-3">
        <div class="row">
            <div class="col-md-3">
                <input type="text" name="user_pin" class="form-control" placeholder="User PIN" value="{{ request('user_pin') }}">
            </div>
            <div class="col-md-3">
                <input type="datetime-local" name="from" class="form-control" value="{{ request('from') }}">
            </div>
            <div class="col-md-3">
                <input type="datetime-local" name="to" class="form-control" value="{{ request('to') }}">
            </div>
            <div class="col-md-3">
                <button class="btn btn-primary">Filtrar</button>
                <a href="{{ route('attendances.index') }}" class="btn btn-secondary">Limpiar</a>
            </div>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>PIN</th>
                    <th>Timestamp</th>
                    <th>Type</th>
                    <th>Raw</th>
                </tr>
            </thead>
            <tbody>
                @forelse($attendances as $att)
                    <tr>
                        <td>{{ $att->user_pin }}</td>
                        <td>{{ optional($att->timestamp)->toDateTimeString() }}</td>
                        <td>{{ $att->type }}</td>
                        <td><pre style="white-space:pre-wrap">{{ json_encode($att->raw, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4">No hay registros</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $attendances->links() }}
</div>
@endsection
