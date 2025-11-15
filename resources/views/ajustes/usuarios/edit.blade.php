@extends('adminlte::page')

@section('title', 'Dashboard')


@section('content')

<div class="card card-grobdi">
  <h2 class="card-header-grobdi">Actualizar Usuario</h2>
  <div class="card-body-grobdi">

    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
        <a class="btn-grobdi btn-outline-grobdi btn-sm" href="{{ url()->previous() }}"><i class="fa fa-arrow-left"></i> Atrás</a>
    </div>
    @php
        $selectedZones = old('zonas', $usuario->zones->pluck('id')->toArray());
        $selectedRole = old('role_id', $usuario->role_id);
        $roleOptions = $roles->map(fn($rol) => ['value' => $rol->id, 'label' => $rol->name])->toArray();
        $zonaOptions = $zonas->map(fn($zona) => ['value' => $zona->id, 'label' => $zona->name])->toArray();
    @endphp

    <form action="{{ route('usuarios.update',$usuario) }}" method="POST" class="grobdi-form mt-3">
        @csrf
        @method('PUT')
        <x-grobdi.form.input
            label="Nombre de usuario <span class='required-mark'>*</span>"
            name="name"
            id="inputName"
            placeholder="Ingresar el nombre del usuario"
            :value="$usuario->name"
            required
        />
        <x-grobdi.form.input
            type="email"
            label="Email <span class='required-mark'>*</span>"
            name="email"
            id="inputEmail"
            placeholder="Ingresar correo electrónico"
            :value="$usuario->email"
            required
        />
        <x-grobdi.form.checkbox-group
            label="Zonas <span class='required-mark'>*</span>"
            name="zonas[]"
            :options="$zonaOptions"
            :value="$selectedZones"
            required
        />
        <x-grobdi.form.select
            label="Rol <span class='required-mark'>*</span>"
            name="role_id"
            id="role_id"
            :options="$roleOptions"
            :value="$selectedRole"
            placeholder="Selecciona un rol"
            required
        />

        <div class="form-actions justify-end">
            <button type="submit" class="btn-grobdi btn-success-grobdi"><i class="fa-solid fa-floppy-disk"></i> Actualizar</button>
        </div>
    </form>

    @session('success')
        <br>
        <div class="alert-grobdi alert-success-grobdi" role="alert"> {{ $value }} </div>
    @endsession
    <br>
    <button type="button" class="btn-grobdi btn-primary-grobdi" data-bs-toggle="modal" data-bs-target="#exampleModal">
    Cambiar Contraseña
    </button>
    @error('password')
        <div class="invalid-feedback d-block">No se pudo actualizar la contraseña, ingresar al formulario para detectar los errores</div>
    @enderror
    <!-- Modal -->
    <div class="modal fade modal-grobdi" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
            <form action="{{ route('usuarios.changepass',$usuario) }}" method="POST" class="grobdi-form">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Cambiar de contraseña</h5>
                </div>
                <div class="modal-body">
                    <x-grobdi.form.input
                        type="password"
                        label="Ingresar Contraseña <span class='required-mark'>*</span>"
                        name="password"
                        id="inputPass"
                        placeholder="Ingresar la contraseña del usuario"
                        required
                    />
                    <x-grobdi.form.input
                        type="password"
                        label="Repetir Contraseña <span class='required-mark'>*</span>"
                        name="password_confirmation"
                        id="inputConfirmedPass"
                        placeholder="Repetir la contraseña ingresada"
                        required
                    />
                </div>
                <div class="modal-footer form-actions justify-end">
                    <button type="button" class="btn-grobdi btn-outline-grobdi" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn-grobdi btn-primary-grobdi" id="liveToastBtn">Actualizar</button>
                </div>
            </form>
            </div>
        </div>
    </div>
</div>

@stop

@section('css')
    {{-- Add here extra stylesheets --}}
    {{-- <link rel="stylesheet" href="/css/admin_custom.css"> --}}
@stop

@section('js')
<!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous"> -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
@stop
