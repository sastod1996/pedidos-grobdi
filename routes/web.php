<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Auth::routes();

require __DIR__ . '/web/public/softlyn.php';

Route::middleware(['check.permission'])->group(function () {
    require __DIR__ . '/web/protected/home.php';
    require __DIR__ . '/web/protected/muestras.php';
    require __DIR__ . '/web/protected/bonificaciones.php';
    require __DIR__ . '/web/protected/pedidos.php';
    require __DIR__ . '/web/protected/rutas.php';
    require __DIR__ . '/web/protected/reports.php';
    require __DIR__ . '/web/protected/laboratorio.php';
    require __DIR__ . '/web/protected/ajustes.php';
});
