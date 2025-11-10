@extends('adminlte::auth.auth-page', ['auth_type' => 'login'])

@include('vendor.adminlte.auth.partials.grobdi-auth-styles')

@php( $login_url = View::getSection('login_url') ?? config('adminlte.login_url', 'login') )
@php( $register_url = View::getSection('register_url') ?? config('adminlte.register_url', 'register') )
@php( $password_reset_url = View::getSection('password_reset_url') ?? config('adminlte.password_reset_url', 'password/reset') )

@if (config('adminlte.use_route_url', false))
    @php( $login_url = $login_url ? route($login_url) : '' )
    @php( $register_url = $register_url ? route($register_url) : '' )
    @php( $password_reset_url = $password_reset_url ? route($password_reset_url) : '' )
@else
    @php( $login_url = $login_url ? url($login_url) : '' )
    @php( $register_url = $register_url ? url($register_url) : '' )
    @php( $password_reset_url = $password_reset_url ? url($password_reset_url) : '' )
@endif

@section('auth_header')
    <div class="grobdi-auth-heading">
        <span class="grobdi-auth-title">Bienvenido a Grobdi</span>
        <span class="grobdi-auth-subtitle">Ingresa tus credenciales para continuar</span>
    </div>
@stop

@section('auth_body')
    <div class="grobdi-auth-body">
        <form action="{{ $login_url }}" method="post">
            @csrf

            <div class="grobdi-field">
                <label for="email">{{ __('adminlte::adminlte.email') }}</label>
                <div class="grobdi-input-wrapper">
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
                        style="position:absolute; inset-inline-end:1rem; inset-block-start:50%; transform:translateY(-50%); background:none; border:none; color:#475569; font-size:1rem; display:flex; align-items:center; justify-content:center;"
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

            <div class="grobdi-remember">
                <label class="grobdi-checkbox" for="remember" title="{{ __('adminlte::adminlte.remember_me_hint') }}">
                    <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                    <span>{{ __('adminlte::adminlte.remember_me') }}</span>
                </label>

                <button type="submit" class="grobdi-primary-btn">
                    <span class="fas fa-sign-in-alt"></span>
                    {{ __('adminlte::adminlte.sign_in') }}
                </button>
            </div>
        </form>
    </div>
@stop

@section('auth_footer')
    @if($password_reset_url)
        <div class="grobdi-auth-footer">
            <a href="{{ $password_reset_url }}">
                {{ __('adminlte::adminlte.i_forgot_my_password') }}
            </a>
        </div>
    @endif
@stop
