@extends('adminlte::auth.auth-page', ['auth_type' => 'login'])

@push('css')
    <style>
        .login-page {
            background-color: #f8fafc;
            color: #0f172a;
            font-family: 'Nunito', sans-serif;
        }

        .login-page .login-box {
            width: min(420px, 92vw);
        }

        .login-page .card {
            border: 1px solid #cbd5e1;
            border-radius: 1rem;
            box-shadow: 0 24px 48px rgba(15, 23, 42, 0.14);
            background-color: #ffffff;
        }

        .login-page .card-header {
            border-bottom: none;
            background-color: transparent;
            padding: 2rem 2rem 0;
        }

        .login-page .card-body {
            padding: 2.5rem 2.25rem;
        }

        .login-page .card-footer {
            background-color: transparent;
            border-top: none;
            padding-bottom: 2rem;
        }

        .grobdi-auth-heading {
            display: flex;
            flex-direction: column;
            gap: 0.35rem;
            text-align: center;
        }

        .grobdi-auth-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1f2937;
        }

        .grobdi-auth-subtitle {
            font-size: 0.95rem;
            color: #64748b;
        }

        .grobdi-auth-body {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .grobdi-field {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .grobdi-field label {
            font-size: 0.95rem;
            font-weight: 600;
            color: #334155;
        }

        .grobdi-input-wrapper {
            position: relative;
        }

        .grobdi-input-icon {
            position: absolute;
            inset-inline-start: 1rem;
            inset-block-start: 50%;
            transform: translateY(-50%);
            color: #475569;
            font-size: 1rem;
            pointer-events: none;
        }

        .grobdi-input {
            width: 100%;
            border: 2px solid #cbd5e1;
            border-radius: 0.65rem;
            padding: 0.75rem 1rem 0.75rem 2.75rem;
            background-color: #ffffff;
            color: #0f172a;
            font-size: 0.95rem;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .grobdi-input:hover {
            border-color: #94a3b8;
        }

        .grobdi-input:focus {
            border-color: #ef4444;
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.14);
            outline: none;
        }

        .grobdi-input.is-invalid {
            border-color: #ef4444;
        }

        .invalid-feedback {
            display: block;
            color: #b91c1c;
            font-size: 0.85rem;
            margin-top: 0.25rem;
        }

        .grobdi-remember {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .grobdi-checkbox {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
            color: #334155;
        }

        .grobdi-checkbox input[type="checkbox"] {
            width: 1.05rem;
            height: 1.05rem;
            border-radius: 0.25rem;
            border: 2px solid #cbd5e1;
            accent-color: #ef4444;
        }

        .grobdi-primary-btn {
            width: 100%;
            background-color: #ef4444;
            color: #ffffff;
            border: none;
            border-radius: 0.65rem;
            padding: 0.8rem 1rem;
            font-size: 0.95rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            transition: background-color 0.2s ease, box-shadow 0.2s ease;
        }

        .grobdi-primary-btn:hover {
            background-color: #dc2626;
            box-shadow: 0 12px 28px rgba(239, 68, 68, 0.22);
        }

        .grobdi-primary-btn:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.18);
        }

        .grobdi-auth-footer {
            text-align: center;
            font-size: 0.9rem;
        }

        .grobdi-auth-footer a {
            color: #1d4ed8;
            font-weight: 600;
            text-decoration: none;
            transition: color 0.2s ease;
        }

        .grobdi-auth-footer a:hover {
            color: #2563eb;
        }

        @media (max-width: 480px) {
            .login-page .card {
                border-radius: 0.75rem;
            }

            .login-page .card-body {
                padding: 2rem 1.5rem;
            }

            .login-page .card-header {
                padding: 1.5rem 1.5rem 0;
            }

            .grobdi-auth-title {
                font-size: 1.35rem;
            }

            .grobdi-primary-btn {
                padding: 0.75rem;
            }
        }
    </style>
@endpush

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
