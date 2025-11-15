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
    <form action="{{ route('modules.update', $module) }}" method="POST" class="grobdi-form">
        @csrf @method('PUT')
        <x-grobdi.form.input
            label="Nombre"
            name="name"
            :value="old('name', $module->name)"
            required
        />
        <x-grobdi.form.input
            label="Descripción"
            name="description"
            :value="old('description', $module->description)"
        />
        <button class="btn btn-primary">Actualizar</button>
        <a href="{{ route('modules.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>
@stop
