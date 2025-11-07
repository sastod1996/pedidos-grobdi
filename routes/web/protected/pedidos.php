<?php

use App\Http\Controllers\PedidosController;
use App\Http\Controllers\pedidos\comercial\PedidosComercialController;
use App\Http\Controllers\pedidos\contabilidad\PedidosContaController;
use App\Http\Controllers\pedidos\counter\AsignarPedidoController;
use App\Http\Controllers\pedidos\counter\CargarPedidosController;
use App\Http\Controllers\pedidos\counter\HistorialPedidosController;
use App\Http\Controllers\pedidos\Motorizado\PedidosMotoController;
use App\Http\Controllers\pedidos\reportes\FormatosController;
use App\Http\Controllers\rutas\mantenimiento\DoctorController;
use Illuminate\Support\Facades\Route;

Route::get('pedidoscomercial', [PedidosComercialController::class, 'index'])->name('pedidoscomercial.index');
Route::get('pedidoscomercial/export', [PedidosComercialController::class, 'export'])->name('pedidoscomercial.export');

Route::get('/doctors/search', [DoctorController::class, 'showByNameLike'])->name('doctors.search');
Route::get('pedido/{id}/state', [PedidosController::class, 'showDeliveryStates'])->name('pedidos.showDeliveryStates');

Route::resource('cargarpedidos', CargarPedidosController::class);
Route::post('/cargarpedidosdetail', CargarPedidosController::class . '@cargarExcelArticulos')->name('cargarpedidos.excelarticulos');
Route::post('/cargarpedidos/articulos/store', CargarPedidosController::class . '@storeArticulos')->name('cargarpedidos.articulos.store');
Route::get('/cargarpedidos/{pedido}/uploadfile', [CargarPedidosController::class, 'uploadfile'])->name('cargarpedidos.uploadfile');
Route::put('/cargarpedidos/cargarImagen/{id}', CargarPedidosController::class . '@cargarImagen')->name('cargarpedidos.cargarImagen');
Route::put('/cargarpedidos/actualizarPago/{id}', CargarPedidosController::class . '@actualizarPago')->name('cargarpedidos.actualizarPago');
Route::put('/cargarpedidos/cargarImagenReceta/{id}', CargarPedidosController::class . '@cargarImagenReceta')->name('cargarpedidos.cargarImagenReceta');
Route::delete('cargarpedidos/eliminarFotoVoucher/{id}', CargarPedidosController::class . '@eliminarFotoVoucher')->name('cargarpedidos.eliminarFotoVoucher');
Route::delete('cargarpedidos/eliminarFotoReceta/{id}', CargarPedidosController::class . '@eliminarFotoReceta')->name('cargarpedidos.eliminarFotoReceta');
Route::put('/cargarpedidos/actualizarTurno/{id}', CargarPedidosController::class . '@actualizarTurno')->name('cargarpedidos.actualizarTurno');

Route::get('/cargarpedidos/preview/changes', CargarPedidosController::class . '@preview')->name('cargarpedidos.preview');
Route::post('/cargarpedidos/confirm/changes', CargarPedidosController::class . '@confirmChanges')->name('cargarpedidos.confirm');
Route::post('/cargarpedidos/cancel/changes', CargarPedidosController::class . '@cancelChanges')->name('cargarpedidos.cancel');

Route::get('/cargarpedidos/preview/articulos', CargarPedidosController::class . '@previewArticulos')->name('cargarpedidos.preview-articulos');
Route::post('/cargarpedidos/confirm/articulos', CargarPedidosController::class . '@confirmArticulos')->name('cargarpedidos.confirm-articulos');
Route::post('/cargarpedidos/cancel/articulos', CargarPedidosController::class . '@cancelArticulos')->name('cargarpedidos.cancel-articulos');

Route::get('/pedidos/sincronizar', CargarPedidosController::class . '@sincronizarDoctoresPedidos')->name('pedidos.sincronizar');
Route::get('/api/doctores/search', CargarPedidosController::class . '@searchDoctores')->name('api.doctores.search');

Route::resource('asignarpedidos', AsignarPedidoController::class);
Route::post('/cargarpedidos/downloadWord', CargarPedidosController::class . '@downloadWord')->name('cargarpedidos.downloadWord');

Route::get('historialpedidos', HistorialPedidosController::class . '@index')->name('historialpedidos.index');
Route::get('historialpedidos/{historialpedido}', HistorialPedidosController::class . '@show')->name('historialpedidos.show');
Route::delete('historialpedidos/{historialpedido}', HistorialPedidosController::class . '@destroy')->name('historialpedidos.destroy');
Route::put('historial/{historialpedido}/actualizar', HistorialPedidosController::class . '@update')->name('historialpedidos.update');

Route::resource('pedidoscontabilidad', PedidosContaController::class);
Route::get('/pedidoscontabilidad/downloadExcel/{fechainicio}/{fechafin}', PedidosContaController::class . '@downloadExcel')->name('pedidoscontabilidad.downloadExcel');

Route::get('hoja-ruta-motorizado', [PedidosController::class, 'exportHojaDeRutaByMotorizadoForm'])->name('motorizado.viewFormHojaDeRuta');
Route::post('export-hoja-ruta-motorizado', [PedidosController::class, 'exportHojaDeRutaByMotorizadoExcel'])->name('motorizado.exportHojaDeRuta');
Route::post('excelhojaruta', FormatosController::class . '@excelhojaruta')->name('formatos.excelhojaruta');
Route::post('plantillaenvioolva', [PedidosController::class, 'exportPlantillaEnvioOlva'])->name('pedidos.plantillaenvioolva');
Route::post('wordrotuladoenvio', [PedidosController::class, 'exportPlantillaEnvioOlvaWord'])->name('pedidos.wordrotuladoenvio');

Route::resource('pedidosmotorizado', PedidosMotoController::class);
Route::put('/pedidosmotorizado/fotos/{id}', [PedidosMotoController::class, 'cargarFotos'])->name('pedidosmotorizado.cargarfotos');
Route::put('/pedidos-motorizado/{id}', [PedidosMotoController::class, 'updatePedidoByMotorizado'])->name('pedidosmotorizado.updatePedidoByMotorizado');
