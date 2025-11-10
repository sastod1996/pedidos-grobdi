@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content')
    <section class="dashboard-hero">
        <div class="hero-content">
            <div class="hero-header">
                <img src="/images/logo_solo.png" alt="Logo Grobdi" class="hero-icon">
                <div class="hero-identity">
                    <h1 class="hero-title">Especialistas en fórmulas magistrales pediátricas con altos estándares de calidad.</h1>
                </div>
            </div>

            <div class="hero-message">
                    <p class="quote-text">
                        "La excelencia en cada fórmula, el cuidado en cada detalle. Innovamos para la salud,
                        trabajamos con precisión y entregamos con compromiso."
                    </p>
                    <cite class="quote-author">— Grobdi, tu aliado en salud</cite>

            </div>

            <div class="hero-features">
                <div class="feature-item">
                    <div class="feature-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M9 11l3 3L22 4"></path>
                            <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path>
                        </svg>
                    </div>
                    <span><strong>Calidad Farmacéutica</strong></span>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <path d="M12 6v6l4 2"></path>
                        </svg>
                    </div>
                    <span><strong>Entrega Oportuna</strong></span>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                            <circle cx="9" cy="7" r="4"></circle>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                        </svg>
                    </div>
                    <span><strong>Equipo Especializado</strong></span>
                </div>
            </div>
        </div>
    </section>
@stop

@section('css')
    <style>
        .dashboard-hero {
            position: relative;
            display: flex;
            flex-direction: column;
            padding: 3rem 2.5rem;
            border: 1px solid #cbd5e1;
            border-radius: 1rem;
            background-color: #ffffff;
            box-shadow: 0 12px 24px rgba(15, 23, 42, 0.08);
            margin-bottom: 2rem;
            overflow: hidden;
        }

        .hero-background {
            position: absolute;
            top: 0;
            right: 0;
            bottom: 0;
            left: 0;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            padding-right: 3rem;
            pointer-events: none;
            overflow: hidden;
        }

        .hero-content {
            position: relative;
            z-index: 1;
            display: flex;
            flex-direction: column;
            gap: 2rem;
        }

        .hero-header {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .hero-icon {
            width: 80px;
            height: 80px;
            flex-shrink: 0;
            object-fit: contain;
        }

        .hero-identity {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .hero-title {
            margin: 0;
            font-size: 2.25rem;
            font-weight: 700;
            color: #1f2937;
            line-height: 1.2;
        }

        .hero-badge {
            display: inline-flex;
            align-self: flex-start;
            padding: 0.35rem 0.85rem;
            border: 2px solid #ef4444;
            border-radius: 9999px;
            background-color: #fef2f2;
            color: #b91c1c;
            font-size: 0.85rem;
            font-weight: 700;
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }

        .hero-message {
            background-color: #f8fafc;
            border-left: 4px solid #ef4444;
            border-radius: 0.5rem;
            padding: 1.75rem 2rem;
            margin: 0.5rem 0;
        }

        .hero-quote {
            margin: 0;
        }

        .quote-text {
            margin: 0 0 1rem 0;
            color: #334155;
            font-size: 1.05rem;
            line-height: 1.7;
            font-style: italic;
        }

        .quote-author {
            display: block;
            color: #64748b;
            font-size: 0.9rem;
            font-style: normal;
            font-weight: 600;
            text-align: left;
        }

        .hero-features {
            display: flex;
            align-items: center;
            gap: 2rem;
            flex-wrap: wrap;
            padding-top: 0.5rem;
        }

        .feature-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            color: #334155;
            font-size: 0.95rem;
        }

        .feature-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 0.5rem;
            background-color: #fef2f2;
            color: #ef4444;
            flex-shrink: 0;
        }

        .feature-item strong {
            font-weight: 600;
            color: #0f172a;
        }

        @media (max-width: 992px) {
            .dashboard-hero {
                padding: 2.5rem 2rem;
            }

            .hero-title {
                font-size: 1.9rem;
            }

            .background-logo {
                width: 320px;
            }

            .hero-features {
                gap: 1.5rem;
            }
        }

        @media (max-width: 768px) {
            .dashboard-hero {
                padding: 2rem 1.5rem;
            }

            .hero-header {
                flex-direction: column;
                align-items: center;
                text-align: center;
            }

            .hero-icon {
                width: 64px;
                height: 64px;
            }

            .hero-title {
                font-size: 1.6rem;
            }

            .background-logo {
                width: 280px;
                opacity: 0.03;
            }

            .hero-message {
                padding: 1.5rem;
            }

            .quote-text {
                font-size: 1rem;
            }

            .hero-features {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }
        }

        @media (max-width: 576px) {
            .dashboard-hero {
                padding: 1.5rem;
            }

            .hero-header {
                flex-direction: column;
                align-items: center;
                text-align: center;
                gap: 1rem;
            }

            .hero-icon {
                width: 56px;
                height: 56px;
            }

            .hero-title {
                font-size: 1.4rem;
            }

            .hero-badge {
                font-size: 0.75rem;
                padding: 0.3rem 0.7rem;
            }

            .background-logo {
                display: none;
            }

            .hero-message {
                padding: 1.25rem;
            }

            .quote-text {
                font-size: 0.95rem;
            }
        }
    </style>
@stop
