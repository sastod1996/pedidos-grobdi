@extends('adminlte::page')

@section('title', 'Editar Módulo')

@section('content_header')
    <h1>Editar Módulo</h1>
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
    <form action="{{ route('modules.update', $module) }}" method="POST">
        @csrf @method('PUT')
        <div class="form-group">
            <label>Nombre</label>
            <input type="text" name="name" value="{{ $module->name }}" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Descripción</label>
            <input type="text" name="description" value="{{ $module->description }}" class="form-control">
        </div>
        <button class="btn btn-primary">Actualizar</button>
        <a href="{{ route('modules.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>
@stop
