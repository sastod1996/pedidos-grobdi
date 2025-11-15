@extends('adminlte::page')

@section('title', 'Nuevo Módulo')

@section('content')
    <div class="grobdi-header">
        <div class="grobdi-title">
            <div>
                <h2>Crear Nuevo Módulo</h2>
                <p>Completa el formulario para agregar un nuevo módulo</p>
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
    <form class="grobdi-form" action="{{ route('modules.store') }}" method="POST">
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
        <a href="{{ route('modules.index') }}" class="btn btn-grobdi btn-secondary-grobdi">Cancelar</a>
    </form>
@stop
