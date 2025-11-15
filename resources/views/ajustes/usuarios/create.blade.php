@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content')

    <div class="card card-grobdi">
        <h2 class="card-header-grobdi">Crear Nuevo Usuario</h2>
        <div class="card-body-grobdi">

            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <a class="btn-grobdi btn-outline-grobdi btn-sm" href="{{ url()->previous() }}"><i class="fa fa-arrow-left"></i> Atrás</a>
            </div>

            @php
                $roleOptions = $roles->map(fn($rol) => ['value' => $rol->id, 'label' => $rol->name])->toArray();
                $zonaOptions = $zonas->map(fn($zona) => ['value' => $zona->id, 'label' => $zona->name])->toArray();
            @endphp

            <form action="{{ route('usuarios.store') }}" method="POST" class="grobdi-form mt-3">
                @csrf

                <x-grobdi.form.input
                    label="Nombre de usuario <span class='required-mark'>*</span>"
                    name="name"
                    id="inputName"
                    placeholder="Ingresar el nombre del usuario"
                    required
                />
                <x-grobdi.form.input
                    type="email"
                    label="Email <span class='required-mark'>*</span>"
                    name="email"
                    id="inputEmail"
                    placeholder="Ingresar correo electrónico"
                    required
                />
                <x-grobdi.form.input
                    type="password"
                    label="Contraseña <span class='required-mark'>*</span>"
                    name="password"
                    id="passName"
                    placeholder="Ingresar contraseña"
                    required
                />
                <x-grobdi.form.input
                    type="password"
                    label="Confirmar Contraseña <span class='required-mark'>*</span>"
                    name="password_confirmation"
                    id="passNameConfirm"
                    placeholder="Repetir la contraseña"
                    required
                />
                <x-grobdi.form.checkbox-group
                    label="Zonas <span class='required-mark'>*</span>"
                    name="zonas[]"
                    :options="$zonaOptions"
                    required
                />
                <x-grobdi.form.select
                    label="Rol <span class='required-mark'>*</span>"
                    name="role_id"
                    id="role_id"
                    :options="$roleOptions"
                    placeholder="Selecciona un rol"
                    required
                />

                <div class="form-actions justify-end">
                    <button type="submit" class="btn-grobdi btn-success-grobdi">
                        <i class="fa-solid fa-floppy-disk"></i> Crear
                    </button>
                </div>
            </form>

        </div>
    </div>

@stop

@section('css')
    {{-- Add here extra stylesheets --}}
    {{-- <link rel="stylesheet" href="/css/admin_custom.css"> --}}
@stop

@section('js')
    <script>
        console.log("Hi, I'm using the Laravel-AdminLTE package!");
    </script>
@stop
