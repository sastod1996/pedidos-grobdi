@extends('adminlte::page')

@section('title', 'Dashboard')


@section('content')

<div class="card card-grobdi">
  <h2 class="card-header-grobdi">Actualizar Usuario</h2>
  <div class="card-body-grobdi">

    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
        <a class="btn-grobdi btn-outline-grobdi btn-sm" href="{{ url()->previous() }}"><i class="fa fa-arrow-left"></i> Atrás</a>
    </div>
    <form action="{{ route('usuarios.update',$usuario) }}" method="POST" class="grobdi-form mt-3">
        @csrf
        @method('PUT')
        <div class="form-group-grobdi">
            <label for="inputName" class="grobdi-label">
                Nombre de usuario
                <span class="required-mark">*</span>
            </label>
            <input
                type="text"
                name="name"
                value="{{ old('name', $usuario->name) }}"
                class="form-control-grobdi @error('name') is-invalid @enderror"
                id="inputName"
                placeholder="Ingresar el nombre del usuario" required>
            @error('name')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>
        <div class="form-group-grobdi">
            <label for="inputEmail" class="grobdi-label">
                Email
                <span class="required-mark">*</span>
            </label>
            <input
                type="email"
                name="email"
                value="{{ old('email', $usuario->email) }}"
                class="form-control-grobdi @error('email') is-invalid @enderror"
                id="inputEmail"
                placeholder="Ingresar correo electrónico" required>
            @error('email')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>
        @php
            $selectedZones = old('zonas', $usuario->zones->pluck('id')->toArray());
            $selectedRole = old('role_id', $usuario->role_id);
        @endphp
        <div class="form-group-grobdi">
            <span class="grobdi-label">
                Zonas
                <span class="required-mark">*</span>
            </span>
            <div class="grobdi-checkbox-group">
                @foreach ($zonas as $zona)
                    <label class="grobdi-checkbox" for="zona-{{ $zona->id }}">
                        <input
                            type="checkbox"
                            value="{{ $zona->id }}"
                            id="zona-{{ $zona->id }}"
                            name="zonas[]"
                            @checked(in_array($zona->id, $selectedZones))
                        >
                        <span class="checkbox-custom"></span>
                        <span class="checkbox-label">{{ $zona->name }}</span>
                    </label>
                @endforeach
            </div>
            @error('zonas')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>
        <div class="form-group-grobdi">
            <label for="role_id" class="grobdi-label">
                Rol
                <span class="required-mark">*</span>
            </label>
            <select class="form-control-grobdi @error('role_id') is-invalid @enderror" name="role_id" id="role_id" required>
                <option value="" disabled {{ $selectedRole ? '' : 'selected' }}>Selecciona un rol</option>
                @foreach ($roles as $rol )
                    <option value="{{ $rol->id }}" {{ (string) $rol->id === (string) $selectedRole ? 'selected' : '' }}> {{ $rol->name }}</option>
                @endforeach
            </select>
            @error('role_id')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

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
                    <div class="form-group-grobdi">
                        <label for="inputPass" class="grobdi-label">
                            Ingresar Contraseña
                            <span class="required-mark">*</span>
                        </label>
                        <input
                            type="password"
                            name="password"
                            class="form-control-grobdi @error('password') is-invalid @enderror"
                            id="inputPass"
                            placeholder="Ingresar la contraseña del usuario" required>
                        @error('password')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group-grobdi">
                        <label for="inputConfirmedPass" class="grobdi-label">
                            Repetir Contraseña
                            <span class="required-mark">*</span>
                        </label>
                        <input
                            type="password"
                            name="password_confirmation"
                            class="form-control-grobdi @error('password_confirmation') is-invalid @enderror"
                            id="inputConfirmedPass"
                            placeholder="Repetir la contraseña ingresada" required>
                        @error('password_confirmation')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
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
