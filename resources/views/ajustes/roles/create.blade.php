@extends('adminlte::page')

@section('title', 'Nuevo Rol')


@section('content')

    <div class="grobdi-header">
        <div class="grobdi-title">
            <div>
                <h2>Crear Nuevo Rol</h2>
                <p>Completa el formulario para agregar un nuevo rol</p>
            </div>
        </div>
    </div>
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <form action="{{ route('roles.store') }}" method="POST">
        @csrf
        <div class="form-group">
            <div class="form-group">
                <label class="grobdi-label">Nombre</label>
                <input type="text" name="name" class="form-control grobdi-input" required>
            </div>
            <div class="form-group">
                <label class="grobdi-label">Descripción</label>
                <input type="text" name="description" class="form-control grobdi-input">
            </div>
        </div>
        <button class="btn btn-grobdi btn-primary-grobdi">Guardar</button>
        <a href="{{ route('roles.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>
@stop
