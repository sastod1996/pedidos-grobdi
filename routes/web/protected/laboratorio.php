<?php

use App\Http\Controllers\muestras\MuestrasController;
use App\Http\Controllers\pedidos\laboratorio\PedidoslabController;
use App\Http\Controllers\pedidos\laboratorio\PresentacionFarmaceuticaController;
use App\Http\Controllers\pedidos\produccion\OrdenesController;
use Illuminate\Support\Facades\Route;

Route::resource('pedidoslaboratorio', PedidoslabController::class);

Route::get('/get-unidades/{clasificacionId}', [MuestrasController::class, 'getUnidadesPorClasificacion']);

Route::get('/pedidoslaboratorio/{fecha}/downloadWord/{turno}', PedidoslabController::class . '@downloadWord')
    ->name('pedidoslaboratorio.downloadWord');
Route::post('/pedidoslaboratorio/cambio-masivo', [PedidoslabController::class, 'cambioMasivo'])
    ->name('pedidoslaboratorio.cambioMasivo');
Route::get('/pedidoslaboratoriodetalles', [PedidoslabController::class, 'pedidosDetalles'])
    ->name('pedidosLaboratorio.detalles');
Route::put('pedidoslaboratoriodetalles/asignar/{id}/', [PedidoslabController::class, 'asignarTecnicoProd'])
    ->name('pedidosLaboratorio.asignarTecnicoProd');
Route::post('/pedidoslaboratoriodetalles/asignarmultiple', [PedidoslabController::class, 'asignarmultipletecnico'])
    ->name('pedidosLaboratorio.asignarmultipletecnico');

Route::resource('presentacionfarmaceutica', PresentacionFarmaceuticaController::class);
Route::get('ingredientes/{base_id}', [PresentacionFarmaceuticaController::class, 'listaringredientes'])->name('ingredientes.index');
Route::post('base', [PresentacionFarmaceuticaController::class, 'guardarbases'])->name('base.store');
Route::post('ingredientes', [PresentacionFarmaceuticaController::class, 'guardaringredientes'])->name('ingredientes.store');
Route::put('ingredientes/{id}', [PresentacionFarmaceuticaController::class, 'actualizaringredientes'])->name('ingredientes.update');
Route::post('excipientes', [PresentacionFarmaceuticaController::class, 'guardarexcipientes'])->name('excipientes.store');
Route::delete('excipientes/{id}', [PresentacionFarmaceuticaController::class, 'eliminarexcipientes'])->name('excipientes.delete');

Route::get('pedidosproduccion', OrdenesController::class . '@index')->name('produccion.index');
Route::post('pedidosproduccion/{detalleId}/actualizarestado', [OrdenesController::class, 'actualizarEstado'])
    ->name('pedidosproduccion.actualizarEstado');
