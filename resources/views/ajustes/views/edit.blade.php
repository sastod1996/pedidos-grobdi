@extends('adminlte::page')

@section('title', 'Editar Vista')

@section('content_header')
    <h1>Editar Vista</h1>
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
    <form action="{{ route('views.update', $view) }}" method="POST">
        @csrf @method('PUT')
        <div class="form-group">
            <label>Nombre</label>
            <input type="text" name="description" value="{{ $view->description }}" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Ruta</label>
            <input type="text" name="url" value="{{ $view->url }}" class="form-control" required>
        </div>
        <div class="form-group">
            <label>Módulo</label>
            <select name="module_id" class="form-control" required>
                @foreach($modules as $module)
                    <option value="{{ $module->id }}" {{ $view->module_id == $module->id ? 'selected' : '' }}>
                        {{ $module->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label for="is_menu">¿Mostrar en el menú?</label>
            <input type="hidden" name="is_menu" value="0"> {{-- 👈 valor por defecto --}}
            <input type="checkbox" name="is_menu" value="1"
                {{ old('is_menu', $view->is_menu ?? true) ? 'checked' : '' }}>
        </div>
        <button class="btn btn-primary">Actualizar</button>
        <a href="{{ route('views.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>
@stop
