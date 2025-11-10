<?php

use App\Http\Controllers\ajustes\ConexionesController;
use App\Http\Controllers\ajustes\ModuleController;
use App\Http\Controllers\ajustes\RolesController;
use App\Http\Controllers\ajustes\UbigeoController;
use App\Http\Controllers\ajustes\UsuariosController;
use App\Http\Controllers\ajustes\ViewController;
use Illuminate\Support\Facades\Route;

Route::resource('usuarios', UsuariosController::class);
Route::put('/usuarios/changepass/{fecha}', UsuariosController::class . '@changepass')->name('usuarios.changepass');

Route::resource('roles', RolesController::class);
Route::get('roles/{role}/permissions', [RolesController::class, 'permissions'])->name('roles.permissions');
Route::put('roles/{role}/permissions', [RolesController::class, 'updatePermissions'])->name('roles.updatePermissions');

Route::resource('modules', ModuleController::class);
Route::resource('views', ViewController::class);

Route::get('conexiones', [ConexionesController::class, 'index'])->name('conexiones.index');

Route::get('/distritoslimacallao', UbigeoController::class . '@ObtenerDistritosLimayCallao')
    ->name('distritoslimacallao');
