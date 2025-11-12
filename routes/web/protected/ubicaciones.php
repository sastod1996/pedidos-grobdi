<?php

use App\Http\Controllers\Shared\LocationController;
use Illuminate\Support\Facades\Route;

//Endpoint para ubicaciones
Route::prefix('ubicaciones')->name('ubicaciones.')->group(function () {
	Route::get('departamentos', [LocationController::class, 'departamentos'])->name('departamentos.index');
	Route::get('departamentos/{departamento}/provincias', [LocationController::class, 'provincias'])->name('departamentos.provincias');
	Route::get('provincias/{provincia}/distritos', [LocationController::class, 'distritos'])->name('provincias.distritos');
	Route::get('distritos/{distrito}/chain', [LocationController::class, 'distritoChain'])->name('distritos.chain');
});
