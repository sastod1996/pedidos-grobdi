@extends('adminlte::page')

@section('title', 'Enrutamiento')

@section('content_header')
    <h1>rutas</h1>
@stop

@section('content')
@can('enrutamiento.index')
<div class="card mt-2">
    <div class="card-header">
        <div class="d-grid gap-2 d-md-flex justify-content-md-medium">
            @can('enrutamiento.store')
                <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#itemModal">Agregar Nuevo Mes</button>
            @endcan
        </div>
    </div>
    <div class="card-body">
    @session('success')
        <div class="alert alert-success" role="alert"> {{ $value }} </div>
    @endsession
    @session('danger')
        <div class="alert alert-danger" role="alert"> {{ $value }} </div>
    @endsession
        <table class="table table-bordered table-striped table-grobdi">
            <thead>
                <tr>
                    <th>Fecha - Mes</th>
                    <th>Zona</th>
                    <th>Acciones</th>
                </tr>
            </thead>

            <tbody>
            @forelse ($rutas as $ruta)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($ruta->fecha)->locale('es')->monthName.', '. \Carbon\Carbon::parse($ruta->fecha)->year}}</td>
                    <td>{{ $ruta->zone->name }}</td>
                    <td>
                        @can('enrutamiento.agregarlista')
                            <a class="btn btn-primary btn-sm" href="{{ route('enrutamiento.agregarlista',$ruta->id) }}"><i class=" fa fa-plus"></i> Semanas</a>
                        @endcan
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="3">No hay información que mostrar</td>
                </tr>
            @endforelse
            </tbody>

        </table>
    </div>
</div>
@endcan
@can('enrutamiento.store')
<div class="modal fade" id="itemModal" tabindex="-1" aria-labelledby="itemModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="itemModalLabel">Registrar Nuevo Mes</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('enrutamiento.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="fecha_mes" class="form-label">Registrar Fecha:</label>
                        <input class="form-control" type="month" id="fecha_mes" name="fecha_mes" min="{{ date('Y-m') }}" value="{{ date('Y-m') }}" />
                        @error('fecha_mes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endcan
@stop

@section('css')
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">


@section('js')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
@stop
