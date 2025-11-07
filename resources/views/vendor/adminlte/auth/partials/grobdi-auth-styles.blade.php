@push('css')
    @once
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

            .grobdi-toggle-password {
                position: absolute;
                inset-inline-end: 1rem;
                inset-block-start: 50%;
                transform: translateY(-50%);
                background: none;
                border: none;
                color: #475569;
                font-size: 1rem;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 0;
                cursor: pointer;
            }

            .grobdi-toggle-password:focus {
                outline: none;
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

            .grobdi-auth-alert {
                border-radius: 0.65rem;
                border: 1px solid #bbf7d0;
                background-color: #ecfdf5;
                color: #047857;
                font-size: 0.9rem;
                padding: 0.75rem 1rem;
            }

            .grobdi-auth-alert.grobdi-alert-error {
                border-color: #fecaca;
                background-color: #fef2f2;
                color: #b91c1c;
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
    @endonce
@endpush
