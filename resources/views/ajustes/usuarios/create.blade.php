@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content')

    <div class="card card-grobdi">
        <h2 class="card-header-grobdi">Crear Nuevo Usuario</h2>
        <div class="card-body-grobdi">

            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <a class="btn-grobdi btn-outline-grobdi btn-sm" href="{{ url()->previous() }}"><i class="fa fa-arrow-left"></i> Atrás</a>
            </div>

            <form action="{{ route('usuarios.store') }}" method="POST" class="grobdi-form mt-3">
                @csrf

                <div class="form-group-grobdi">
                    <label for="inputName" class="grobdi-label">
                        Nombre de usuario
                        <span class="required-mark">*</span>
                    </label>
                    <input type="text" name="name" value="{{ old('name') }}"
                        class="form-control-grobdi @error('name') is-invalid @enderror" id="inputName"
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
                    <input type="email" name="email" value="{{ old('email') }}"
                        class="form-control-grobdi @error('email') is-invalid @enderror" id="inputEmail"
                        placeholder="Ingresar correo electrónico" required>
                    @error('email')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group-grobdi">
                    <label for="passName" class="grobdi-label">
                        Contraseña
                        <span class="required-mark">*</span>
                    </label>
                    <input type="password" name="password"
                        class="form-control-grobdi @error('password') is-invalid @enderror" id="passName"
                        placeholder="Ingresar contraseña" required>
                    @error('password')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group-grobdi">
                    <label for="passNameConfirm" class="grobdi-label">
                        Confirmar Contraseña
                        <span class="required-mark">*</span>
                    </label>
                    <input type="password" name="password_confirmation"
                        class="form-control-grobdi @error('password_confirmation') is-invalid @enderror" id="passNameConfirm"
                        placeholder="Repetir la contraseña" required>
                    @error('password_confirmation')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group-grobdi">
                    <span class="grobdi-label">
                        Zonas
                        <span class="required-mark">*</span>
                    </span>
                    <div class="grobdi-checkbox-group">
                        @foreach ($zonas as $zona)
                            <label class="grobdi-checkbox" for="zona-{{ $zona->id }}">
                                <input type="checkbox" value="{{ $zona->id }}" id="zona-{{ $zona->id }}" name="zonas[]"
                                    @checked(in_array($zona->id, old('zonas', [])))>
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
                        <option value="" disabled {{ old('role_id') ? '' : 'selected' }}>Selecciona un rol</option>
                        @foreach ($roles as $rol)
                            <option value="{{ $rol->id }}" {{ (string) $rol->id === (string) old('role_id') ? 'selected' : '' }}>{{ $rol->name }}</option>
                        @endforeach
                    </select>
                    @error('role_id')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

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
