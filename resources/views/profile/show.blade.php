@extends('adminlte::page')

@section('title', 'Mi Perfil')

@section('content_header')
@stop

@section('content')
    <div class="grobdi-header mt-3">
        <div class="grobdi-title">
            <div>
                <h2 class="mb-0">Mi Perfil</h2>
                <p class="mb-0">Consulta tu información personal y mantén tu cuenta segura.</p>
            </div>
        </div>
    </div>

    @if (session('status'))
        <div class="alert-grobdi alert-success-grobdi">
            <strong><i class="fas fa-check-circle"></i> ¡Perfecto!</strong>
            <div>{{ session('status') }}</div>
        </div>
    @endif

    @if (session('password_status'))
        <div class="alert-grobdi alert-success-grobdi">
            <strong><i class="fas fa-lock"></i> Contraseña actualizada</strong>
            <div>{{ session('password_status') }}</div>
        </div>
    @endif

    <div class="form-grid grid-cols-2">
        <form action="{{ route('profile.update') }}" method="POST" class="grobdi-form">
            @csrf
            @method('PUT')

            <div class="form-section">
                <div class="section-header">
                    <div class="section-title">
                        <i class="fas fa-user"></i>
                        Datos personales
                    </div>
                    <div class="section-description">
                        Información básica que se muestra a otros usuarios dentro del sistema.
                    </div>
                </div>

                <div class="form-group-grobdi">
                    <label class="grobdi-label" for="name">
                        <i class="fas fa-id-card"></i> Nombre completo
                    </label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        value="{{ old('name', $user->name) }}"
                        class="grobdi-input @error('name') is-invalid @enderror"
                        placeholder="Ej: Juan Pérez"
                        required
                    >
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group-grobdi">
                    <label class="grobdi-label" for="email">
                        <i class="fas fa-envelope"></i> Correo electrónico
                    </label>
                    <input
                        type="email"
                        id="email"
                        value="{{ $user->email }}"
                        class="grobdi-input"
                        readonly
                        tabindex="-1"
                    >
                    <small class="field-description">Si necesitas actualizar el correo, contacta al administrador.</small>
                </div>

                <div class="form-group-grobdi">
                    <label class="grobdi-label" for="role-name">
                        <i class="fas fa-user-shield"></i> Rol asignado
                    </label>
                    <input
                        type="text"
                        id="role-name"
                        class="grobdi-input"
                        value="{{ $user->role->name ?? 'Sin rol asignado' }}"
                        readonly
                    >
                </div>

                <div class="form-group-grobdi">
                    <label class="grobdi-label" for="account-status">
                        <i class="fas fa-signal"></i> Estado de la cuenta
                    </label>
                    <input
                        type="text"
                        id="account-status"
                        class="grobdi-input"
                        value="{{ $user->active ? 'Activa' : 'Inactiva' }}"
                        readonly
                    >
                </div>

                <div class="form-group-grobdi">
                    <label class="grobdi-label">
                        <i class="fas fa-map-marker-alt"></i> Zonas asignadas
                    </label>
                    <div class="badge-stack">
                        @forelse ($user->zones as $zone)
                            <span class="badge-grobdi badge-blue">{{ $zone->name }}</span>
                        @empty
                            <span class="text-muted">Sin zonas asignadas</span>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="form-actions justify-end">
                <button type="submit" class="btn-grobdi btn-primary-grobdi">
                    <i class="fas fa-save"></i> Guardar cambios
                </button>
            </div>
        </form>

        <form action="{{ route('profile.password') }}" method="POST" class="grobdi-form">
            @csrf
            @method('PUT')

            <div class="form-section">
                <div class="section-header">
                    <div class="section-title">
                        <i class="fas fa-lock"></i>
                        Seguridad de la cuenta
                    </div>
                    <div class="section-description">
                        Usa una contraseña robusta para proteger el acceso a tu cuenta.
                    </div>
                </div>

                <div class="form-group-grobdi">
                    <label class="grobdi-label" for="current_password">
                        <i class="fas fa-key"></i> Contraseña actual
                    </label>
                    <input
                        type="password"
                        id="current_password"
                        name="current_password"
                        class="grobdi-input @error('current_password') is-invalid @enderror"
                        placeholder="Ingresa tu contraseña actual"
                        required
                    >
                    @error('current_password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group-grobdi">
                    <label class="grobdi-label" for="password">
                        <i class="fas fa-shield-alt"></i> Nueva contraseña
                    </label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="grobdi-input @error('password') is-invalid @enderror"
                        placeholder="Mínimo 8 caracteres, mayúsculas, números y símbolos"
                        required
                    >
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group-grobdi">
                    <label class="grobdi-label" for="password_confirmation">
                        <i class="fas fa-check-double"></i> Confirmar nueva contraseña
                    </label>
                    <input
                        type="password"
                        id="password_confirmation"
                        name="password_confirmation"
                        class="grobdi-input @error('password_confirmation') is-invalid @enderror"
                        placeholder="Repite la nueva contraseña"
                        required
                    >
                    @error('password_confirmation')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group-grobdi">
                    <small class="text-muted d-block">
                        <i class="fas fa-info-circle"></i> Consejos: evita contraseñas usadas en otros sitios y
                        no compartas tus credenciales.
                    </small>
                </div>
            </div>

            <div class="form-actions justify-end">
                <button type="submit" class="btn-grobdi btn-outline-primary-grobdi">
                    <i class="fas fa-sync-alt"></i> Actualizar contraseña
                </button>
            </div>
        </form>
    </div>
@stop
