@extends('adminlte::page')

@section('title', 'Nuevo Módulo')

@section('content_header')
    <h1>Crear Módulo</h1>
@stop

@section('content')
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <form action="{{ route('modules.store') }}" method="POST">
        @csrf
        <div class="form-group">
            <label>Nombre</label>
            <input type="text" name="name" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Descripción</label>
            <input type="text" name="description" class="form-control">
        </div>
        <button class="btn btn-success">Guardar</button>
        <a href="{{ route('modules.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>
@stop
