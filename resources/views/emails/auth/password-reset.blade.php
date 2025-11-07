<!DOCTYPE html>
<html lang="es" xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $appName }} - Restablece tu contraseña</title>
    <style>
        :root {
            color-scheme: light;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: 'Nunito', Arial, sans-serif;
            background-color: #f8fafc;
            color: #0f172a;
        }

        a {
            color: inherit;
        }

        .wrapper {
            width: 100%;
            background-color: #f8fafc;
            padding: 32px 16px;
        }

        .mail-container {
            max-width: 560px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 20px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 24px 48px rgba(15, 23, 42, 0.08);
            overflow: hidden;
        }

        .mail-body {
            padding: 32px;
        }

        .mail-body p {
            margin: 0 0 18px;
            line-height: 1.55;
            font-size: 15px;
            color: #1f2937;
        }

        .highlight {
            background-color: #fee2e2;
            color: #b91c1c;
            padding: 12px 18px;
            border-radius: 12px;
            font-weight: 600;
            margin-bottom: 24px;
        }

        .action-button {
            display: inline-block;
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: #ffffff !important;
            text-decoration: none;
            font-weight: 700;
            padding: 14px 28px;
            border-radius: 12px;
            font-size: 15px;
            box-shadow: 0 18px 36px rgba(239, 68, 68, 0.25);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .action-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 18px 36px rgba(220, 38, 38, 0.3);
        }

        .action-container {
            text-align: center;
            margin: 32px 0;
        }

        .mail-footer {
            border-top: 1px solid #e2e8f0;
            padding: 24px 32px 32px;
            background-color: #f1f5f9;
        }

        .mail-footer p {
            margin: 0 0 12px;
            font-size: 13px;
            color: #475569;
        }

        .mail-footer a {
            color: #ef4444;
            font-weight: 600;
            text-decoration: none;
        }

        .mail-footer small {
            display: block;
            margin-top: 20px;
            font-size: 12px;
            color: #94a3b8;
        }

        @media (max-width: 600px) {
            .mail-container {
                border-radius: 16px;
            }

            .mail-body {
                padding: 24px;
            }

            .mail-header {
                padding: 32px 24px 16px;
            }
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="mail-container">
            <div class="mail-body">
                <h1 style="margin: 0 0 12px; font-size: 24px; color: #ef4444; font-weight: 700;">Restablece tu contraseña</h1>
                <p style="margin-top: -4px; color: #64748b;">Mantenemos seguro tu acceso al sistema sysgrob.</p>

                <p>Hola,</p>
                <p>
                    Recibimos una solicitud para la contraseña de tu cuenta de <strong>sysgrob</strong>.
                    Si tú realizaste esta petición, haz clic en el botón para continuar con el proceso.
                </p>

                <div class="highlight">
                    Este enlace vence en {{ $expiration }} minutos por seguridad.
                </div>

                <div class="action-container">
                    <a href="{{ $actionUrl }}" class="action-button">
                        Restablecer contraseña
                    </a>
                </div>

                <p>
                    Si el botón no funciona, copia y pega este enlace en tu navegador:
                    <br>
                    <a href="{{ $actionUrl }}" style="color: #ef4444;">{{ $actionUrl }}</a>
                </p>

                <p>
                    ¿No solicitaste este cambio? Ignora este correo y tu contraseña seguirá siendo la misma.
                </p>

                <p>
                    Gracias por confiar en nosotros.<br>
                    Equipo de sistemas de <strong>sysgrob</strong>
                </p>
            </div>

            <div class="mail-footer">
                <p>
                    Si tienes alguna duda o necesitas ayuda adicional, escríbenos a
                    <a href="mailto:{{ $supportEmail }}">{{ $supportEmail }}</a>.
                </p>
                <small>&copy; {{ date('Y') }} sysgrob. Todos los derechos reservados.</small>
            </div>
        </div>
    </div>
</body>
</html>
