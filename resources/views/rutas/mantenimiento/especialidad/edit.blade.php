@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <!-- <h1>Pedidos</h1> -->
@stop

@section('content')
@can('especialidad.edit')

<div class="grobdi-header">
    <div class="grobdi-title">
        <h1>Actualizar Especialidad</h1>
        <a class="btn btn-outline-grobdi btn-sm" href="{{ url()->previous() }}"><i class="fa fa-arrow-left"></i> Atrás</a>
    </div>
</div>

<div class="grobdi-form">
    <form action="{{ route('especialidad.update',$especialidad->id) }}" method="POST">
    @csrf
    @method('PUT')
        <div class="row">

            <div class="col-xs-6 col-sm-6 col-md-6">
                <x-grobdi.form.input
                    label="Nombre"
                    name="name"
                    id="inputName"
                    placeholder="Ingresar nombre de la especialidad"
                    :value="old('name', $especialidad->name)"
                />
            </div>
            <div class="col-xs-6 col-sm-6 col-md-6">
                <x-grobdi.form.input
                    label="Descripción"
                    name="description"
                    id="description"
                    placeholder="Descripción de la especialidad"
                    :value="old('description', $especialidad->description)"
                />
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-primary-grobdi btn-lg"><i class="fa-solid fa-floppy-disk"></i> Actualizar</button>
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

@section('js')
    <!-- <script> console.log("Hi, I'm using the Laravel-AdminLTE package!"); </script> -->
@stop
