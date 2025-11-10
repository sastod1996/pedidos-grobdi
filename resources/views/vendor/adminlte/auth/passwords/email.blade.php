@extends('adminlte::auth.auth-page', ['auth_type' => 'login'])

@include('vendor.adminlte.auth.partials.grobdi-auth-styles')

@php( $password_email_url = View::getSection('password_email_url') ?? config('adminlte.password_email_url', 'password/email') )
@php( $login_url = View::getSection('login_url') ?? config('adminlte.login_url', 'login') )

@if (config('adminlte.use_route_url', false))
    @php( $password_email_url = $password_email_url ? route($password_email_url) : '' )
    @php( $login_url = $login_url ? route($login_url) : '' )
@else
    @php( $password_email_url = $password_email_url ? url($password_email_url) : '' )
    @php( $login_url = $login_url ? url($login_url) : '' )
@endif

@section('auth_header')
    <div class="grobdi-auth-heading">
        <span class="grobdi-auth-title">¿Olvidaste tu contraseña?</span>
        <span class="grobdi-auth-subtitle">Ingresa tu correo y te enviaremos un enlace para restablecerla.</span>
    </div>
@stop

@section('auth_body')
    <div class="grobdi-auth-body">
        @if(session('status'))
            <div class="grobdi-auth-alert">
                {{ session('status') }}
            </div>
        @endif

        <form action="{{ $password_email_url }}" method="post">
            @csrf

            <div class="grobdi-field">
                <label for="email">{{ __('adminlte::adminlte.email') }}</label>
                <div class="grobdi-input-wrapper mb-4">
                    <span class="grobdi-input-icon fas fa-envelope"></span>
                    <input
                        id="email"
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
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

            <button type="submit" class="grobdi-primary-btn">
                <span class="fas fa-share-square"></span>
                {{ __('adminlte::adminlte.send_password_reset_link') }}
            </button>
        </form>
    </div>
@stop

@section('auth_footer')
    <div class="grobdi-auth-footer">
        <a href="{{ $login_url }}">Volver al inicio de sesión</a>
    </div>
@stop
