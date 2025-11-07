<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/perfil', [ProfileController::class, 'show'])->name('profile.show');
Route::put('/perfil', [ProfileController::class, 'update'])->name('profile.update');
Route::put('/perfil/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
