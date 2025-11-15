@extends('adminlte::page')

@section('title', 'Editar Rol')


@section('content')
    <div class="grobdi-header">
        <div class="grobdi-title">
            <div>
                <h2>Editar Rol</h2>
                <p>Completa el formulario para editar el rol</p>
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
    <form action="{{ route('roles.update', $role) }}" method="POST" class="grobdi-form">
        @csrf @method('PUT')
        <x-grobdi.form.input
            label="Nombre"
            name="name"
            :value="old('name', $role->name)"
            required
        />
        <x-grobdi.form.input
            label="Descripcion"
            name="description"
            :value="old('description', $role->description)"
        />
        <button class="btn btn-grobdi btn-primary-grobdi">Actualizar</button>
        <a href="{{ route('roles.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>
@stop
