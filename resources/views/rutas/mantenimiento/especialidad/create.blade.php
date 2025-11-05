@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <!-- <h1>Pedidos</h1> -->
@stop

@section('content')
@can('especialidad.create')

<div class="grobdi-header">
    <div class="grobdi-title">
        <h1>Crear Especialidad</h1>
        <a class="btn btn-outline-grobdi btn-sm" href="{{ url()->previous() }}"><i class="fa fa-arrow-left"></i> Atrás</a>
    </div>
</div>

<div class="grobdi-form">
    <form action="{{ route('especialidad.store') }}" method="POST">
        @csrf

        <div class="row">
            <div class="col-xs-6 col-sm-6 col-md-6">
                <div class="form-group-grobdi">
                    <label class="grobdi-label">Nombre:</label>
                    <input
                        type="text"
                        name="name"
                        value=""
                        class="grobdi-input @error('name') is-invalid @enderror"
                        id="inputName"
                        placeholder="Ingresar nombre de la especialidad">
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="col-xs-6 col-sm-6 col-md-6">
                <div class="form-group-grobdi">
                    <label class="grobdi-label">Descripción:</label>
                    <input
                        type="text"
                        name="description"
                        value=""
                        class="grobdi-input @error('description') is-invalid @enderror"
                        id="description"
                        placeholder="Descripción de la especialidad">
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-primary-grobdi btn-lg"><i class="fa-solid fa-floppy-disk"></i> Registrar</button>
        </div>
    </form>
</div>

@endcan

@stop

@section('css')
    {{-- Add here extra stylesheets --}}
    {{-- <link rel="stylesheet" href="/css/admin_custom.css"> --}}
    <!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://getbootstrap.com/docs/5.3/assets/css/docs.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script> -->
@stop

