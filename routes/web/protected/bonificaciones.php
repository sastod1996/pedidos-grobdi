<?php

use App\Http\Controllers\Visitadoras\Metas\GoalNotReachedConfigController;
use App\Http\Controllers\Visitadoras\Metas\MetasController;
use App\Http\Controllers\Visitadoras\Metas\VisitorGoalController;
use Illuminate\Support\Facades\Route;

Route::prefix('bonificaciones')->group(function () {
    Route::get('/', [MetasController::class, 'index'])->name('bonificaciones.index');
    // Route::get('/metas/form', [MetasController::class, 'form'])->name('visitadoras.metas.form');

    Route::post('/metas/store', [MetasController::class, 'store'])->name('visitadoras.metas.store');
    Route::post('/metas/details/{visitorGoalId}', [MetasController::class, 'getDataForChartByVisitorGoal'])->name('visitadoras.metas.details');

    Route::post('/metas/not-reached-config/store', [GoalNotReachedConfigController::class, 'store'])->name('visitadoras.metas.not-reached-config.store');
    Route::get('/metas/not-reached-config', [GoalNotReachedConfigController::class, 'showActive'])->name('visitadoras.metas.not-reached-config.index');

    Route::put('/metas/update-debited-amount/{visitorGoal}', [VisitorGoalController::class, 'updateDebitedAmount'])->name('visitadoras.metas.update.debited-amount');
    Route::get('/metas/{id}', [MetasController::class, 'show'])->name('visitadoras.metas.show');
});
