@extends('adminlte::page')

@section('title', 'Nueva Vista')

@section('content_header')
    <h1>Crear Vista</h1>
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
    <form action="{{ route('views.store') }}" method="POST">
        @csrf
        <div class="form-group">
            <label>Nombre</label>
            <input type="text" name="description" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Ruta</label>
            <input type="text" name="url" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Módulo</label>
            <select name="module_id" class="form-control" required>
                @foreach($modules as $module)
                    <option value="{{ $module->id }}">{{ $module->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label for="is_menu">¿Mostrar en el menú?</label>
            <input type="hidden" name="is_menu" value="0"> {{-- 👈 valor por defecto --}}
            <input type="checkbox" name="is_menu" value="1"
                {{ old('is_menu', $view->is_menu ?? true) ? 'checked' : '' }}>
        </div>
        <button class="btn btn-success">Guardar</button>
        <a href="{{ route('views.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>
@stop
