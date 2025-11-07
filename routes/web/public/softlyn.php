<?php

use App\Http\Controllers\cotizador\BaseController;
use App\Http\Controllers\cotizador\EnvaseController;
use App\Http\Controllers\cotizador\InsumoController;
use App\Http\Controllers\cotizador\InsumoEmpaqueController;
use App\Http\Controllers\cotizador\MaterialController;
use App\Http\Controllers\cotizador\ProductoFinalController;
use App\Http\Controllers\softlyn\CompraController;
use App\Http\Controllers\softlyn\GuiaIngresoController;
use App\Http\Controllers\softlyn\MerchandiseController;
use App\Http\Controllers\softlyn\ProveedorController;
use App\Http\Controllers\softlyn\TipoCambioController;
use App\Http\Controllers\softlyn\UtilController;
use App\Http\Controllers\softlyn\VolumenController;
use Illuminate\Support\Facades\Route;

Route::resource('insumo_empaque', InsumoEmpaqueController::class);

Route::resource('envases', EnvaseController::class);
Route::resource('material', MaterialController::class);
Route::resource('insumos', InsumoController::class);

Route::resource('proveedores', ProveedorController::class)->parameters([
    'proveedores' => 'proveedor',
]);

Route::resource('tipo_cambio', TipoCambioController::class);
Route::get('/resumen-tipo-cambio', [TipoCambioController::class, 'resumenTipoCambio'])->name('tipo_cambio.resumen');

Route::resource('merchandise', MerchandiseController::class);
Route::resource('util', UtilController::class);
Route::resource('compras', CompraController::class);
Route::resource('guia_ingreso', GuiaIngresoController::class);
Route::get('lotes/por-articulo/{articulo_id}', [GuiaIngresoController::class, 'getLotesPorArticulo'])->name('lotes.por_articulo');
Route::get('guia_ingreso/detalles-compra/{compra_id}', [GuiaIngresoController::class, 'getDetallesCompra'])->name('guia_ingreso.detalles_compra');

Route::resource('producto_final', ProductoFinalController::class);
Route::resource('volumen', VolumenController::class);

Route::resource('bases', BaseController::class);

Route::get('/insumo/marcar-caro', [InsumoController::class, 'marcarCaro'])->name('insumos.marcar-caro');
Route::post('/insumo/marcar-caro', [InsumoController::class, 'actualizarEsCaro'])->name('insumos.actualizar-es-caro');
