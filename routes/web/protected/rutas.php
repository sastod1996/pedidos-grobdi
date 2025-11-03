<?php

use App\Http\Controllers\rutas\enrutamiento\EnrutamientoController;
use App\Http\Controllers\rutas\enrutamiento\ListaController;
use App\Http\Controllers\rutas\enrutamiento\RutasVisitadoraController;
use App\Http\Controllers\rutas\mantenimiento\CategoriaDoctorController;
use App\Http\Controllers\rutas\mantenimiento\CentroSaludController;
use App\Http\Controllers\rutas\mantenimiento\DoctorController;
use App\Http\Controllers\rutas\mantenimiento\EspecialidadController;
use App\Http\Controllers\rutas\visita\VisitaDoctorController;
use Illuminate\Support\Facades\Route;

Route::resource('centrosalud', CentroSaludController::class);
Route::post('centrosalud/creacionflotante', [CentroSaludController::class, 'creacionRapida'])->name('centrosalud.crearflorante');
Route::resource('especialidad', EspecialidadController::class);
Route::get('/doctor/export', [DoctorController::class, 'export'])->name('doctor.export');
Route::resource('doctor', DoctorController::class);
Route::post('/doctor/cargadata', [DoctorController::class, 'cargadata'])->name('doctor.cargadata');
Route::resource('lista', ListaController::class);

Route::get('/enrutamiento', [EnrutamientoController::class, 'index'])->name('enrutamiento.index');
Route::post('/enrutamiento/store', [EnrutamientoController::class, 'store'])->name('enrutamiento.store');
Route::post('/enrutamientolista/store', [EnrutamientoController::class, 'Enrutamientolistastore'])->name('enrutamientolista.store');
Route::get('/enrutamiento/{id}', [EnrutamientoController::class, 'agregarLista'])->name('enrutamiento.agregarlista');
Route::get('/enrutamientolista/{id}', [EnrutamientoController::class, 'DoctoresLista'])->name('enrutamientolista.doctores');
Route::post('/enrutamientolista/add-visita', [EnrutamientoController::class, 'addSpontaneousVisitaDoctor'])->name('visita.doctor.add.spontaneous');
Route::put('/enrutamientolista/doctor/{id}', [EnrutamientoController::class, 'DoctoresListaUpdate'])->name('enrutamientolista.doctoresupdate');
Route::delete('/enrutamientolista/doctor/{id}', [EnrutamientoController::class, 'destroyVisitaDoctor'])->name('enrutamientolista.doctoresdestroy');

Route::post('/visitadoctornuevo/{id}/aprobar', [VisitaDoctorController::class, 'aprobar'])->name('doctor.aprobarVisita');
Route::post('/visitadoctornuevo/{id}/rechazar', [VisitaDoctorController::class, 'rechazar'])->name('doctor.rechazarVisita');
Route::resource('categoriadoctor', CategoriaDoctorController::class);

Route::get('calendariovisitadora', [EnrutamientoController::class, 'calendariovisitadora'])->name('enrutamientolista.calendariovisitadora');
Route::get('/rutasdoctor/{id}', [EnrutamientoController::class, 'DetalleDoctorRutas'])->name('rutas.detalledoctor');
Route::get('/detalle-visita-doctor/{id}', [VisitaDoctorController::class, 'FindDetalleVisitaByID'])->name('rutasmapa.detallesdoctor');
Route::put('/update-visita-doctor/{id}', [VisitaDoctorController::class, 'UpdateVisitaDoctor'])->name('rutasmapa.guardarvisita');
Route::post('guardar-visita', [EnrutamientoController::class, 'GuardarVisita'])->name('rutas.guardarvisita');

Route::get('rutasvisitadora', [RutasVisitadoraController::class, 'ListarMisRutas'])->name('rutasvisitadora.ListarMisRutas');
Route::get('rutasvisitadora/{id}', [RutasVisitadoraController::class, 'listadoctores'])->name('rutasvisitadora.listadoctores');
Route::post('/rutasvisitadora/asignar', [RutasVisitadoraController::class, 'asignar'])->name('rutasvisitadora.asignar');
Route::get('/rutasvisitadora/buscardoctor/{cmp}', [DoctorController::class, 'buscarCMP'])->name('rutasvisitadora.buscarcmpdoctor');
Route::post('/rutasvisitadora/doctores', [DoctorController::class, 'guardarDoctorVisitador'])->name('rutasvisitadora.guardardoctor');

Route::get('centrosaludbuscar', CentroSaludController::class . '@buscar')->name('centrosalud.buscar');
Route::get('ruta-mapa', [VisitaDoctorController::class, 'mapa'])->name('ruta.mapa');
