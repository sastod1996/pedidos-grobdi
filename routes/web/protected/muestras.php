<?php

use App\Http\Controllers\muestras\MuestrasController;
use Illuminate\Support\Facades\Route;

Route::prefix('muestras')->group(function () {
    Route::get('/', [MuestrasController::class, 'index'])->name('muestras.index');
    Route::get('/export', [MuestrasController::class, 'exportExcel'])->name('muestras.exportExcel');
    Route::delete('/disable/{muestra}', [MuestrasController::class, 'disableMuestra'])->name('muestras.disable');
    Route::get('/{id}', [MuestrasController::class, 'show'])->name('muestras.show');
    Route::get('create/form', [MuestrasController::class, 'create'])->name('muestras.create');
    Route::post('create/', [MuestrasController::class, 'store'])->name('muestras.store');
    Route::get('edit/{muestra}', [MuestrasController::class, 'edit'])->name('muestras.edit');
    Route::put('edit/{muestra}', [MuestrasController::class, 'update'])->name('muestras.update');
    Route::put('edit/{muestra}/update-tipo-muestra', [MuestrasController::class, 'updateTipoMuestra'])->name('muestras.updateTipoMuestra');
    Route::put('edit/{muestra}/update-fecha-hora-entrega', [MuestrasController::class, 'updateDateTimeScheduled'])->name('muestras.updateDateTimeScheduled');

    Route::put('laboratorio/{muestra}/comentario', [MuestrasController::class, 'updateComentarioLab'])->name('muestras.updateComentarioLab');
    Route::put('laboratorio/{muestra}/state', [MuestrasController::class, 'markAsElaborated'])->name('muestras.markAsElaborated');
    Route::put('/{muestra}/update-price', [MuestrasController::class, 'updatePrice'])->name('muestras.updatePrice');
    Route::put('/aprove-coordinador/{muestra}', [MuestrasController::class, 'aproveMuestraByCoordinadora'])->name('muestras.aproveCoordinadora');
    Route::put('/aprove-jcomercial/{muestra}', [MuestrasController::class, 'aproveMuestraByJefeComercial'])->name('muestras.aproveJefeComercial');
    Route::put('/aprove-joperaciones/{muestra}', [MuestrasController::class, 'aproveMuestraByJefeOperaciones'])->name('muestras.aproveJefeOperaciones');
});
