@php
    $showFrom = \Carbon\Carbon::createFromDate(\Carbon\Carbon::now()->year, 10, 24);
    $showUntil = \Carbon\Carbon::createFromDate(\Carbon\Carbon::now()->year, 11, 10)->endOfDay();
    $showDiaDeMuertos = \Carbon\Carbon::now()->between($showFrom, $showUntil);
@endphp

@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    @if (!$showDiaDeMuertos)
        <h1>Indicadores</h1>
    @endif
@stop

@section('content')
    @if ($showDiaDeMuertos)
        <div class="scary-banner position-relative">
            <div
                style="position: absolute; z-index: 1; inset: 0; background: radial-gradient(circle at 50% 50%, rgba(225, 225, 225, 0), rgba(0, 0, 0, 0.3) 100%); width: 100%; height: 100%">
            </div>
            <img src="/images/halloween_theme/contexto.png" alt="" width="100%" height="100%" class="bg-image">
            <div class='vector'>
                <svg viewBox='0 0 600 250' preserveAspectRatio='none'>
                    <line x1='1' y1='1' x2='450' y2='250' />
                    <line x1='1' y1='1' x2='175' y2='250' />
                    <path d='M 1,80 a 12,15 45 1,1 37,-26 a 10,12 0 1,1 14,-24 a 25,20 -45 1,1 40,-30' />
                    <path d='M 1,160 a 17,20 45 1,1 75,-52 a 17,20 0 1,1 30,-48 a 30,25 -45 1,1 60,-70' />
                    <path d='M 1,240 a 22,25 45 1,1 113,-78 a 23,26 0 1,1 46,-72 a 35,30 -45 1,1 90,-110' />
                </svg>
            </div>
            <div class="ofrenda-container text-center">
                <h2>¡El sistema fue raptado!</h2>
                <p>Por los Malditos del SysGrob</p>
                <img src="/images/halloween_theme/skull.png" class="skull">
            </div>

            <img src="/images/halloween_theme/veins_1.webp" class="vena">
            <img src="/images/halloween_theme/veins_2.webp" class="vena">
            <img src="/images/halloween_theme/veins_3.webp" class="vena">

            <img src="/images/halloween_theme/monstruo_rojo.png" class="red-monster">

            <div class="iris"></div>

            <img src="/images/halloween_theme/fantasma.png" alt="" class="ghost">

            <div class="calabaza-1">
                <img src="/images/halloween_theme/cara_01.png" class="face">
                <img src="/images/halloween_theme/calabaza_01.png" class="body">
            </div>
            <div class="calabaza-2">
                <img src="/images/halloween_theme/cara_02.png" class="face">
                <img src="/images/halloween_theme/calabaza_02.png" class="body">
            </div>
            <div class="calabaza-3">
                <img src="/images/halloween_theme/cara_03.png" class="face">
                <img src="/images/halloween_theme/calabaza_03.png" class="body">
            </div>
            <div class="calabaza-4">
                <img src="/images/halloween_theme/cara_04.png" class="face">
                <img src="/images/halloween_theme/calabaza_04.png" class="body">
            </div>

            <img src="/images/halloween_theme/arbusto.png" alt="" class="bush">
        </div>
    @else
        <p>Bienvenidos</p>
    @endif
@stop

@section('css')
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Creepster&family=Montserrat:wght@400;600&display=swap');

        /* .red-monster {
                                                position: absolute;
                                                max-width: 120px;
                                                height: auto;
                                                transform: translate(-50%, -50%);
                                                bottom: -10px;
                                            } */

        @keyframes scaredMonster {

            0%,
            100% {
                transform: translate(-50%, -50%) scale(1);
                filter: brightness(1);
            }

            25% {
                transform: translate(-50%, -55%) scale(0.98, 1.02);
                /* se encoge y estira verticalmente (ojos abiertos) */
                filter: brightness(1.1);
            }

            50% {
                transform: translate(-50%, -50%) scale(1);
            }

            75% {
                transform: translate(-50%, -52%) scale(0.99, 1.01);
                filter: brightness(1.05);
            }
        }

        .red-monster {
            position: absolute;
            max-width: 120px;
            height: auto;
            bottom: -10px;
            z-index: 4;
            transform: translate(-50%, -50%);
            animation: scaredMonster 4s infinite ease-in-out;
            filter: drop-shadow(0 2px 6px rgba(0, 0, 0, 0.4));
        }

        .scary-banner {
            position: relative;
            color: white;
            padding: 2rem;
            width: 100%;
            min-height: 90dvh;
            border-radius: 12px;
            text-align: center;
            overflow: hidden;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.3);
            background: #1067A4;
        }

        .scary-banner .bg-image {
            object-fit: cover;
            object-position: bottom;
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
        }

        .skull {
            filter: drop-shadow(0 0 10px #fff);
            position: absolute;
            z-index: 0;
            height: auto;
            width: 7%;
            left: 20%;
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0) rotate(0deg);
            }

            50% {
                transform: translateY(-12px) rotate(5deg);
            }
        }

        @keyframes windSway {

            0%,
            100% {
                transform: skewX(0deg);
            }

            20% {
                transform: skewX(10deg);
            }

            50% {
                transform: skewX(5deg);
            }

            80% {
                transform: skewX(-10deg);
            }
        }

        .bush {
            position: absolute;
            bottom: -50px;
            right: -20px;
            max-width: 350px;
            height: auto;
            z-index: 5;
            transform-origin: bottom;
            animation: windSway 5s infinite ease-in-out;
            filter: drop-shadow(10px 5px 20px #15365D);
        }

        @keyframes floatGhost {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-15px);
            }
        }

        .ghost {
            position: absolute;
            top: 10%;
            right: 20%;
            max-width: 100px;
            height: auto;
            z-index: 5;
            animation: floatGhost 6s infinite ease-in-out;
            filter: drop-shadow(5px 1px 5px #fff);
        }

        @keyframes reptileLook {

            /* === 1. Centro (reposo inicial) === */
            0%,
            3% {
                transform: translateX(0) translateY(0);
            }

            /* === 2. Mira arriba (posición 2 del centro) === */
            6%,
            9% {
                transform: translateX(0) translateY(-20px);
            }

            /* === 3. Vuelve al centro === */
            12%,
            15% {
                transform: translateX(0) translateY(0);
            }

            /* === 4. Va al centro izquierdo === */
            18%,
            21% {
                transform: translateX(-50px) translateY(0);
            }

            /* === 5. Arriba del centro izquierdo (posición 2) === */
            24%,
            27% {
                transform: translateX(-50px) translateY(-20px);
            }

            /* === 6. Abajo-izquierda del centro izquierdo (posición 7) === */
            30%,
            33% {
                transform: translateX(-58px) translateY(20px);
            }

            /* === 7. Vuelve al centro izquierdo === */
            36%,
            39% {
                transform: translateX(-50px) translateY(0);
            }

            /* === 8. Regresa al centro === */
            42%,
            45% {
                transform: translateX(0) translateY(0);
            }

            /* === 9. Va al centro derecho === */
            48%,
            51% {
                transform: translateX(50px) translateY(0);
            }

            /* === 10. Arriba-derecha del centro derecho (posición 3) === */
            54%,
            57% {
                transform: translateX(58px) translateY(-20px);
            }

            /* === 11. Abajo del centro derecho (posición 8) === */
            60%,
            63% {
                transform: translateX(50px) translateY(20px);
            }

            /* === 12. Vuelve al centro derecho === */
            66%,
            69% {
                transform: translateX(50px) translateY(0);
            }

            /* === 13. Regresa al centro === */
            72%,
            75% {
                transform: translateX(0) translateY(0);
            }

            /* === 14. Mira abajo (posición 8 del centro) — momento raro === */
            78%,
            81% {
                transform: translateX(0) translateY(20px);
            }

            /* === 15. Vuelve al centro y termina suavemente === */
            84%,
            100% {
                transform: translateX(0) translateY(0);
            }
        }

        .iris {
            background: #000;
            width: 10px;
            height: 80px;
            border-radius: 100%;
            position: absolute;
            z-index: 20;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            animation: reptileLook 20s infinite;
        }

        .vena {
            position: absolute;
            z-index: 19;
            transform: translate(-50%, -50%);
            width: auto;
            height: 105px;
        }

        @keyframes pulseVena2 {

            0%,
            100% {
                transform: rotate(-120deg) scale(1);
            }

            50% {
                transform: rotate(-120deg) scale(1.05);
            }
        }

        @keyframes pulseVena3 {

            0%,
            100% {
                transform: rotate(120deg) scale(1);
            }

            50% {
                transform: rotate(120deg) scale(1.05);
            }
        }

        /* Si hay cuarta */
        @keyframes pulseVena4 {

            0%,
            100% {
                transform: rotate(180deg) scale(1);
            }

            50% {
                transform: rotate(180deg) scale(1.05);
            }
        }

        /* Segundo .vena */
        .vena:nth-of-type(2) {
            top: 35%;
            right: 40.5%;
            transform: rotate(-120deg);
            transform-origin: center;
            animation: pulseVena2 4s infinite;
        }

        /* Tercero */
        .vena:nth-of-type(3) {
            top: 35%;
            left: 38%;
            transform: rotate(120deg);
            transform-origin: center;
            animation: pulseVena3 4s infinite;
        }

        /* Cuarto */
        .vena:nth-of-type(4) {
            top: 30.5%;
            left: 48%;
            transform: rotate(180deg);
            transform-origin: center;
            animation: pulseVena4 4s infinite;
        }

        @keyframes pumpkinPulse {

            0%,
            100% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.03);
            }
        }

        .face {
            position: absolute;
            filter: drop-shadow(2px 2px 5px #E07032);
            z-index: 2;
            transform-origin: center;
            animation: pumpkinPulse 3.5s infinite;
        }

        .calabaza-1 {
            position: absolute;
            bottom: 2%;
            left: 5%;
            z-index: 2;
        }

        .calabaza-1 .face {
            max-width: 60px;
            bottom: 15%;
            left: 22%;
        }

        .calabaza-1 .body {
            max-width: 100px;
        }

        .calabaza-2 {
            position: absolute;
            bottom: 13%;
            left: 25%;
            z-index: 2;
        }

        .calabaza-2 .face {
            max-width: 60px;
            bottom: 13%;
            left: 28%;
        }

        .calabaza-2 .body {
            max-width: 110px;
        }

        .calabaza-3 {
            position: absolute;
            bottom: 9%;
            right: 30%;
            z-index: 2;
        }

        .calabaza-3 .face {
            max-width: 60px;
            bottom: 13%;
            left: 23%;
        }

        .calabaza-3 .body {
            max-width: 100px;
        }

        .calabaza-4 {
            position: absolute;
            bottom: 8%;
            right: 8%;
            z-index: 2;
        }

        .calabaza-4 .face {
            max-width: 60px;
            bottom: 15%;
            left: 22%;
        }

        .calabaza-4 .body {
            max-width: 120px;
        }

        .ofrenda-container {
            position: relative;
            font-family: 'Creepster', cursive;
            text-shadow: 2px 8px 2px rgba(0, 0, 0, 0.6);
            display: flex;
            flex-direction: column;
            flex justify-items: center;
            justify-content: center;
            gap: 5px;
            z-index: 10;
            animation: pumpkinPulse 6s ease-in-out infinite;
        }

        .ofrenda-container h2 {
            font-size: 2.4rem;
            word-spacing: 10px;
            margin: 0;
        }

        .ofrenda-container p {
            font-size: 1.5rem;
            word-spacing: 5px;

        }

        div.vector {
            top: 0;
            left: 0;
            height: 250px;
            width: 600px;
            overflow: hidden;
            position: absolute;
            z-index: 2;
        }

        svg {
            height: 100%;
            width: 100%;
        }

        line,
        path {
            stroke: #E2E4E3;
            stroke-width: 2px;
            filter: drop-shadow(0 0 4px #E2E4E3);
            fill: none;
        }


        @media (max-width: 1200px) {
            .vena {
                height: 100px;
            }

            .calabaza-1 .body {
                max-width: 120px;
            }

            .calabaza-1 .face {
                max-width: 65px;
            }

            .calabaza-2 .body {
                max-width: 130px;
            }

            .calabaza-2 .face {
                max-width: 68px;
            }

            .calabaza-3 .body {
                max-width: 105px;
            }

            .calabaza-3 .face {
                max-width: 65px;
            }

            .calabaza-4 .body {
                max-width: 150px;
            }

            .calabaza-4 .face {
                max-width: 80px;
            }

            .calabaza-1 {
                left: 10%;
                bottom: 5%
            }

            .calabaza-2 {
                left: 21%;
                bottom: 5%;
            }

            .calabaza-3 {
                right: 20%;
                bottom: 9%;
            }

            .calabaza-4 {
                right: 3%;
                bottom: 5%
            }

            .bush {
                bottom: -20px;
                right: -30px;
                max-width: 200px;
            }
        }

        /* Ajuste para pantallas pequeñas */
        @media (max-width: 768px) {
            .vena {
                height: 60px;
            }

            .calabaza-2 {
                display: none;
            }

            .calabaza-3 {
                display: none;
            }

            .skull {
                display: none;
            }

            .calabaza-1 .body {
                max-width: 95px;
            }

            .calabaza-1 .face {
                max-width: 50px;
            }

            .calabaza-4 .body {
                max-width: 115px;
            }

            .calabaza-4 .face {
                max-width: 60px;
            }

            .calabaza-1 {
                left: 10%;
            }

            .calabaza-4 {
                right: 3%;
                bottom: 5%
            }

            .vector {
                display: none;
            }

            .bush {
                display: none;
            }
        }
    </style>
@stop
