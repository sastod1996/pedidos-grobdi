@extends('adminlte::page')

@section('title', 'Asignar Permisos')

@section('content_header')
<h2>Asignar permisos a {{ $role->name }}</h2>
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
    <form action="{{ route('roles.updatePermissions', $role->id) }}" method="POST">
        @csrf
        @method('PUT')

        @foreach($modules as $module)
            <div class="card mb-3">
                <div class="card-header">
                    <label>
                        <input type="checkbox" name="modules[]" value="{{ $module->id }}"
                            {{ $role->modules->contains($module->id) ? 'checked' : '' }}>
                        {{ $module->name }}
                    </label>
                </div>
                <div class="card-body">
                    @foreach($module->views as $view)
                        <label class="d-block">
                            <input type="checkbox" name="views[]" value="{{ $view->id }}"
                                {{ $role->views->contains($view->id) ? 'checked' : '' }}>
                            {{ $view->description }}
                        </label>
                    @endforeach
                </div>
            </div>
        @endforeach

        <button type="submit" class="btn btn-primary">Guardar permisos</button>
    </form>
@endsection
