@extends('adminlte::auth.auth-page', ['auth_type' => 'login'])

@include('vendor.adminlte.auth.partials.grobdi-auth-styles')

@php( $password_reset_url = View::getSection('password_reset_url') ?? config('adminlte.password_reset_url', 'password/reset') )
@php( $login_url = View::getSection('login_url') ?? config('adminlte.login_url', 'login') )

@if (config('adminlte.use_route_url', false))
    @php( $password_reset_url = $password_reset_url ? route($password_reset_url) : '' )
    @php( $login_url = $login_url ? route($login_url) : '' )
@else
    @php( $password_reset_url = $password_reset_url ? url($password_reset_url) : '' )
    @php( $login_url = $login_url ? url($login_url) : '' )
@endif

@section('auth_header')
    <div class="grobdi-auth-heading">
        <span class="grobdi-auth-title">Restablece tu contraseña</span>
        <span class="grobdi-auth-subtitle">Crea una nueva contraseña segura para volver a ingresar.</span>
    </div>
@stop

@section('auth_body')
    <div class="grobdi-auth-body">
        <form action="{{ $password_reset_url }}" method="post">
            @csrf

            <input type="hidden" name="token" value="{{ $token }}">

            <div class="grobdi-field">
                <label for="email">{{ __('adminlte::adminlte.email') }}</label>
                <div class="grobdi-input-wrapper">
                    <span class="grobdi-input-icon fas fa-envelope"></span>
                    <input
                        id="email"
                        type="email"
                        name="email"
                        value="{{ $email ?? old('email') }}"
                        class="grobdi-input form-control @error('email') is-invalid @enderror"
                        placeholder="correo@empresa.com"
                        autofocus
                    >
                </div>
                @error('email')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            <div class="grobdi-field">
                <label for="password">{{ __('adminlte::adminlte.password') }}</label>
                <div class="grobdi-input-wrapper">
                    <span class="grobdi-input-icon fas fa-lock"></span>
                    <input
                        id="password"
                        type="password"
                        name="password"
                        class="grobdi-input form-control @error('password') is-invalid @enderror"
                        placeholder="••••••••"
                    >
                    <button
                        type="button"
                        class="grobdi-toggle-password"
                        aria-label="Mostrar contraseña"
                        onclick="const input=this.previousElementSibling; const icon=this.querySelector('span'); if (input.type==='password') { input.type='text'; icon.classList.replace('fa-eye','fa-eye-slash'); } else { input.type='password'; icon.classList.replace('fa-eye-slash','fa-eye'); }"
                    >
                        <span class="fas fa-eye"></span>
                    </button>
                </div>
                @error('password')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            <div class="grobdi-field mb-4">
                <label for="password_confirmation">{{ trans('adminlte::adminlte.retype_password') }}</label>
                <div class="grobdi-input-wrapper">
                    <span class="grobdi-input-icon fas fa-lock"></span>
                    <input
                        id="password_confirmation"
                        type="password"
                        name="password_confirmation"
                        class="grobdi-input form-control @error('password_confirmation') is-invalid @enderror"
                        placeholder="••••••••"
                    >
                    <button
                        type="button"
                        class="grobdi-toggle-password"
                        aria-label="Mostrar confirmación"
                        onclick="const input=this.previousElementSibling; const icon=this.querySelector('span'); if (input.type==='password') { input.type='text'; icon.classList.replace('fa-eye','fa-eye-slash'); } else { input.type='password'; icon.classList.replace('fa-eye-slash','fa-eye'); }"
                    >
                        <span class="fas fa-eye"></span>
                    </button>
                </div>
                @error('password_confirmation')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                @enderror
            </div>

            <button type="submit" class="grobdi-primary-btn">
                <span class="fas fa-sync-alt"></span>
                {{ __('adminlte::adminlte.reset_password') }}
            </button>
        </form>
    </div>
@stop

@section('auth_footer')
    <div class="grobdi-auth-footer">
        <a href="{{ $login_url }}">Volver al inicio de sesión</a>
    </div>
@stop
