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
            {{-- <div class="ofrenda-container text-center">
                <h2>¡El sistema fue raptado! <span class="ms-2 scary-span" style="text-shadow: none">👹</span></h2>
                <p>Por los Malditos del SysGrob <span class="ms-2 scary-span text-xl" style="text-shadow: none">😈</span>
                </p>
            </div> --}}

            <div class="calabaza-1">
                <img src="/images/halloween_theme/cara_01.png" class="face" width="85">
                <img src="/images/halloween_theme/calabaza_01.png" width="150">
            </div>
            <div class="calabaza-2">
                <img src="/images/halloween_theme/cara_02.png" class="face" width="85">
                <img src="/images/halloween_theme/calabaza_02.png" width="150">
            </div>
            <div class="calabaza-3">
                <img src="/images/halloween_theme/cara_03.png" class="face" width="85">
                <img src="/images/halloween_theme/calabaza_03.png" width="150">
            </div>
            <div class="calabaza-4">
                <img src="/images/halloween_theme/cara_04.png" class="face" width="85">
                <img src="/images/halloween_theme/calabaza_04.png" width="180">
            </div>

            {{-- <span class="scary-span" style="top: 10%; left: 5%; filter: drop-shadow(0 0 10px #ffffffff);">🎃</span>
            <span class="scary-span" style="top: 20%; right: 8%;">🕸️</span>
            <span class="scary-span" style="top: 40%; left: 10%;">💀</span>
            <span class="scary-span" style="top: 45%; right: 12%;">🩻</span>
            <span class="scary-span" style="bottom: 20%; right: 15%;">💀</span>
            <span class="scary-span" style="top: 65%; left: 15%;">🕸️</span>
            <span class="scary-span" style="bottom: 10%; left: 50%;">🎃</span> --}}
        </div>
    @else
        <p>Bienvenidos</p>
    @endif
@stop

@section('css')
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Creepster&family=Montserrat:wght@400;600&display=swap');

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
            /* background: radial-gradient(circle at 30% 30%, #320052, #000000 80%); background: linear-gradient(180deg, #1a1a1a 20%, #4a1c6b 80%, #d95700 120%); */
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

        .calabaza-1 {
            position: absolute;
            bottom: 13%;
            left: 20%;
            z-index: 2;
        }

        .calabaza-1 .face {
            position: absolute;
            bottom: 13%;
            left: 22%;
            z-index: 3;
            filter: drop-shadow(2px 2px 5px #E07032);
        }

        .calabaza-2 {
            position: absolute;
            bottom: 13%;
            left: 20%;
            z-index: 2;
        }

        .calabaza-2 .face {
            position: absolute;
            bottom: 13%;
            left: 22%;
            z-index: 3;
            filter: drop-shadow(2px 2px 5px #E07032);
        }

        .calabaza-3 {
            position: absolute;
            bottom: 13%;
            left: 20%;
            z-index: 2;
        }

        .calabaza-3 .face {
            position: absolute;
            bottom: 13%;
            left: 22%;
            z-index: 3;
            filter: drop-shadow(2px 2px 5px #E07032);
        }

        .calabaza-4 {
            position: absolute;
            bottom: 13%;
            right: 10%;
            z-index: 2;
            /* filter: drop-shadow(5px -12px 30px); */
        }

        .calabaza-4 .face {
            position: absolute;
            bottom: 13%;
            left: 22%;
            z-index: 3;
            filter: drop-shadow(2px 2px 5px #E07032);
        }

        .ofrenda-container {
            position: relative;
            font-family: 'Creepster', cursive;
            text-shadow: 2px 10px 5px rgba(0, 0, 0, 0.6);
            display: flex;
            flex-direction: column;
            flex justify-items: center;
            justify-content: center;
            gap: 5px;
            z-index: 10;
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

        .scary-span {
            position: absolute;
            font-size: 2.8rem;
            filter: drop-shadow(0 0 10px #ffae00);
            animation: float 4s ease-in-out infinite;
            z-index: 0;
        }

        /* Diferentes retrasos para que floten de forma orgánica */
        .scary-span:nth-of-type(1) {
            animation-delay: 0s;
        }

        .scary-span:nth-of-type(2) {
            animation-delay: 1.2s;
        }

        .scary-span:nth-of-type(3) {
            animation-delay: 2.1s;
        }

        .scary-span:nth-of-type(4) {
            animation-delay: 0.7s;
        }

        .scary-span:nth-of-type(5) {
            animation-delay: 1.8s;
        }

        .scary-span:nth-of-type(6) {
            animation-delay: 0.3s;
        }

        .scary-span:nth-of-type(7) {
            animation-delay: 2.5s;
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

        div.vector {
            top: 0;
            left: 0;
            height: 250px;
            width: 600px;
            overflow: hidden;
            position: absolute;
            z-index: 0;
        }

        svg {
            height: 100%;
            width: 100%;
        }

        line,
        path {
            stroke: #c98f11ff;
            stroke-width: 2px;
            filter: drop-shadow(0 0 4px #fff);
            fill: none;
        }
    </style>
@stop
