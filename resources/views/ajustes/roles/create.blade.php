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
    <form action="{{ route('roles.store') }}" method="POST" class="grobdi-form">
        @csrf
        <x-grobdi.form.input
            label="Nombre"
            name="name"
            required
        />
        <x-grobdi.form.input
            label="Descripción"
            name="description"
        />
        <button class="btn btn-grobdi btn-primary-grobdi">Guardar</button>
        <a href="{{ route('roles.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>
@stop
