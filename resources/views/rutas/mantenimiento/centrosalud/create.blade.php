@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <!-- <h1>Pedidos</h1> -->
@stop

@section('content')

<div class="card mt-5">
  <h2 class="card-header">Crear Centro de Salud</h2>
  <div class="card-body">
  
    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
        <a class="btn btn-primary btn-sm" href="{{ url()->previous() }}"><i class="fa fa-arrow-left"></i> Atrás</a>
    </div>
  
    <form action="{{ route('centrosalud.store') }}" method="POST">
        @csrf
  
        <div class="row">

            <div class="col-xs-6 col-sm-6 col-md-6">
                <label for="inputName" class="form-label"><strong>Nombre:</strong></label>
                <input 
                    type="text" 
                    name="name" 
                    value=""
                    class="form-control @error('name') is-invalid @enderror" 
                    id="inputName" 
                    placeholder="Ingresar nombre del centro de salud">
                @error('name')
                    <div class="form-text text-danger">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-xs-6 col-sm-6 col-md-6">
                <label for="adress" class="form-label"><strong>Dirección:</strong></label>
                <input 
                    type="text" 
                    name="adress" 
                    value=""
                    class="form-control @error('adress') is-invalid @enderror" 
                    id="adress" 
                    placeholder="Ingresar la dirección del centro de salud">
                @error('adress')
                    <div class="form-text text-danger">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-xs-12 col-sm-12 col-md-12">
                <label for="description" class="form-label"><strong>Descripción:</strong></label>
                <input 
                    type="text" 
                    name="description" 
                    value=""
                    class="form-control @error('description') is-invalid @enderror" 
                    id="description" 
                    placeholder="Descripción del centro de salud">
                @error('description')
                    <div class="form-text text-danger">{{ $message }}</div>
                @enderror
            </div>
        </div>
        <br>
        <button type="submit" class="btn btn-success"><i class="fa-solid fa-floppy-disk"></i> Registrar</button>
    </form>
  
  </div>
</div>

@stop

@section('css')
    {{-- Add here extra stylesheets --}}
    {{-- <link rel="stylesheet" href="/css/admin_custom.css"> --}}
    <!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://getbootstrap.com/docs/5.3/assets/css/docs.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script> -->
@stop

@section('js')
    <script> console.log("Hi, I'm using the Laravel-AdminLTE package!"); </script>
@stop