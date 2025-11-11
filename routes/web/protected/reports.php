<?php

use App\Http\Controllers\ReportsController;
use Illuminate\Support\Facades\Route;

Route::prefix('reports')->group(function () {
    Route::prefix('rutas')->group(function () {
        Route::get('/', [ReportsController::class, 'rutasView'])->name('reports.rutas');

        Route::prefix('api/v1')->group(function () {
            Route::get('zones', [ReportsController::class, 'getZonesReport'])->name('reports.rutas.zones');
            Route::get('/distritos/{zoneId}', [ReportsController::class, 'getDistritosByZone'])->name('rutas.zones.distritos');
        });
    });

    Route::prefix('ventas')->group(function () {
        Route::get('/', [ReportsController::class, 'ventasView'])->name('reports.ventas');

        Route::prefix('api/v1')->group(function () {
            Route::get('general', [ReportsController::class, 'getVentasGeneralReport'])->name('reports.ventas.general');
            Route::get('visitadoras', [ReportsController::class, 'getVisitadorasReport'])->name('reports.ventas.visitadoras');
            Route::get('productos', [ReportsController::class, 'getProductosReport'])->name('reports.ventas.productos');
            Route::get('provincias', [ReportsController::class, 'getProvinciasReport'])->name('reports.ventas.provincias');
            Route::get('detail-pedidos-by-departamento', [ReportsController::class, 'getPedidosDetailsByProvincia'])->name('reports.ventas.provincias.departamento');
        });
    });

    Route::prefix('doctores')->group(function () {
        Route::get('/', [ReportsController::class, 'doctorsView'])->name('reports.doctors');

        Route::prefix('api/v1')->group(function () {
            Route::get('doctors', [ReportsController::class, 'getDoctorReport'])->name('reports.doctores.doctores');
            Route::get('tipo-doctor', [ReportsController::class, 'getTipoDoctorReport'])->name('reports.doctores.tipo-doctor');
            Route::get('seguimiento', [ReportsController::class, 'getDoctorSeguimientoReport'])->name('reports.doctores.seguimiento');
        });
    });

    Route::prefix('muestras')->group(function () {
        Route::get('/', [ReportsController::class, 'muestrasView'])->name('reports.muestras');

        Route::prefix('api/v1')->group(function () {
            Route::get('general', [ReportsController::class, 'getMuestrasGeneralReport'])->name('reports.muestras.general');
            Route::get('doctors', [ReportsController::class, 'getMuestrasDoctorReport'])->name('reports.muestras.doctores');
        });
    });

    Route::prefix('motorizados')->group(function () {
        Route::get('/', [ReportsController::class, 'muestrasView'])->name('reports.motorizados');
    });
});
