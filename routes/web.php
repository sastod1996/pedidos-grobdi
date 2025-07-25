<?php

use App\Http\Controllers\ajustes\UbigeoController;
use App\Http\Controllers\ajustes\UsuariosController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
// Route::get('/', function () {
    //     return view('home');
    // });
use App\Http\Controllers\pedidos\laboratorio\PedidoslabController;
use App\Http\Controllers\pedidos\contabilidad\PedidosContaController;
use App\Http\Controllers\pedidos\counter\AsignarPedidoController;
use App\Http\Controllers\pedidos\counter\CargarPedidosController;
use App\Http\Controllers\pedidos\counter\HistorialPedidosController;
use App\Http\Controllers\pedidos\Motorizado\PedidosMotoController;
use App\Http\Controllers\rutas\enrutamiento\AsignacionSemanal;
use App\Http\Controllers\rutas\enrutamiento\ListaController;
use App\Http\Controllers\rutas\mantenimiento\CentroSaludController;
use App\Http\Controllers\rutas\mantenimiento\DoctorController;
use App\Http\Controllers\rutas\mantenimiento\EspecialidadController;

//modulo muestras
use App\Http\Controllers\muestras\coordinadoraController;
use App\Http\Controllers\muestras\gerenciaController;
use App\Http\Controllers\muestras\JcomercialController;
use App\Http\Controllers\muestras\jefe_proyectosController;
use App\Http\Controllers\muestras\laboratorioController;
use App\Http\Controllers\muestras\MuestrasController;
use App\Http\Controllers\pedidos\laboratorio\PresentacionFarmaceuticaController;
use App\Http\Controllers\pedidos\produccion\OrdenesController;
use App\Http\Controllers\pedidos\reportes\FormatosController;
use App\Http\Controllers\rutas\enrutamiento\EnrutamientoController;
use App\Http\Controllers\rutas\visita\VisitaDoctorController;
//COTIZADOR GENERAL
use App\Http\Controllers\cotizador\ProductoFinalController;
use App\Http\Controllers\cotizador\BaseController;
use App\Http\Controllers\cotizador\InsumoEmpaqueController;
use App\Http\Controllers\PedidosController;
use App\Http\Controllers\rutas\mantenimiento\CategoriaDoctorController;
//cruds separados de envase, material e insumo
use App\Http\Controllers\cotizador\EnvaseController;
use App\Http\Controllers\cotizador\MaterialController;
use App\Http\Controllers\cotizador\InsumoController;
//softlyn modulos
use App\Http\Controllers\softlyn\VolumenController;
use App\Http\Controllers\softlyn\ProveedorController;
use App\Http\Controllers\softlyn\TipoCambioController;
use App\Http\Controllers\softlyn\MerchandiseController;
use App\Http\Controllers\softlyn\CompraController;
use App\Http\Controllers\softlyn\UtilController;

// use App\Http\Middleware\RoleMiddleware;

// use Auth;
Auth::routes();


Route::get('/', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');


//COUNTER
Route::middleware(['checkRole:counter,admin,Administracion'])->group(function () {
    
    // Route::resource('cargarpedidos', PedidosController::class);
    Route::resource('cargarpedidos', CargarPedidosController::class);
    Route::post('/cargarpedidosdetail',CargarPedidosController::class.'@cargarExcelArticulos')->name('cargarpedidos.excelarticulos');
    Route::get('/cargarpedidos/{pedido}/uploadfile', CargarPedidosController::class .'@uploadfile')->name('cargarpedidos.uploadfile');
    Route::put('/cargarpedidos/cargarImagen/{post}', CargarPedidosController::class .'@cargarImagen')->name('cargarpedidos.cargarImagen');
    Route::put('/cargarpedidos/actualizarPago/{post}', CargarPedidosController::class .'@actualizarPago')->name('cargarpedidos.actualizarPago');
    Route::put('/cargarpedidos/cargarImagenReceta/{post}', CargarPedidosController::class .'@cargarImagenReceta')->name('cargarpedidos.cargarImagenReceta');
    Route::delete('cargarpedidos/eliminarFotoVoucher/{id}',CargarPedidosController::class.'@eliminarFotoVoucher')->name('cargarpedidos.eliminarFotoVoucher');
    Route::put('/cargarpedidos/actualizarTurno/{id}',CargarPedidosController::class.'@actualizarTurno')->name('cargarpedidos.actualizarTurno');
    Route::resource('asignarpedidos', AsignarPedidoController::class);
    Route::post('/cargarpedidos/downloadWord', CargarPedidosController::class .'@downloadWord')
    ->name('cargarpedidos.downloadWord');
});
//counter - jefe de operaciones -laboratorio
Route::get('historialpedidos', HistorialPedidosController::class.'@index')
->name('historialpedidos.index')
->middleware(['checkRole:counter,admin,jefe-operaciones,laboratorio,Administracion']);
Route::get('historialpedidos/{historialpedido}', HistorialPedidosController::class.'@show')
->name('historialpedidos.show')
->middleware(['checkRole:counter,admin,jefe-operaciones,laboratorio,Administracion']);
//Jefe de operaciones
Route::delete('historialpedidos/{historialpedido}', HistorialPedidosController::class.'@destroy')
->name('historialpedidos.destroy')
->middleware(['checkRole:admin,jefe-operaciones,Administracion']);
Route::put('historial/{historialpedido}/actualizar', HistorialPedidosController::class.'@update')
->name('historialpedidos.update')
->middleware(['checkRole:admin,jefe-operaciones,Administracion']);
Route::resource('usuarios', UsuariosController::class)->middleware(['checkRole:admin,jefe-operaciones']);
Route::put('/usuarios/changepass/{fecha}', UsuariosController::class .'@changepass')
    ->name('usuarios.changepass')
    ->middleware(['checkRole:admin,jefe-operaciones']);
Route::get('sincronizarpedidos',[CargarPedidosController::class,'sincronizarDoctoresPedidos'])
    ->name('pedidos.sincronizar')
    ->middleware(['checkRole:admin,jefe-operaciones']);

Route::resource('pedidoscontabilidad', PedidosContaController::class)->middleware(['checkRole:contabilidad,admin']);
Route::get('/pedidoscontabilidad/downloadExcel/{fechainicio}/{fechafin}', PedidosContaController::class .'@downloadExcel')
    ->name('pedidoscontabilidad.downloadExcel')
    ->middleware(['checkRole:contabilidad,admin']);

//ADMINISTRACION
Route::get('formatos',FormatosController::class.'@index')->name('formatos.index');
Route::post('excelhojaruta',FormatosController::class.'@excelhojaruta')->name('formatos.excelhojaruta');

//MOTORIZADO
Route::resource('pedidosmotorizado', PedidosMotoController::class)->middleware(['checkRole:motorizado,admin']);
Route::put('/pedidosmotorizado/fotos/{id}', [PedidosMotoController::class, 'cargarFotos'])->name('pedidosmotorizado.cargarfotos')->middleware(['checkRole:motorizado,admin']);

//SUPERVISOR
Route::middleware(['checkRole:supervisor,admin'])->group(function () {
    Route::resource('centrosalud', CentroSaludController::class);
    Route::get('centrosaludbuscar', CentroSaludController::class.'@buscar');
    Route::resource('especialidad', EspecialidadController::class);
    Route::resource('doctor', DoctorController::class);
    Route::post('/doctor/cargadata', [DoctorController::class, 'cargadata'])->name('doctor.cargadata');
    Route::resource('lista', ListaController::class);
    Route::get('/enrutamiento', [EnrutamientoController::class, 'index'])->name('enrutamiento.index');
    Route::post('/enrutamiento/store', [EnrutamientoController::class, 'store'])->name('enrutamiento.store');
    Route::post('/enrutamientolista/store', [EnrutamientoController::class, 'Enrutamientolistastore'])->name('enrutamientolista.store');
    Route::get('/enrutamiento/{id}', [EnrutamientoController::class, 'agregarLista'])->name('enrutamiento.agregarlista');
    Route::get('/enrutamientolista/{id}', [EnrutamientoController::class, 'DoctoresLista'])->name('enrutamientolista.doctores');
    Route::put('/enrutamientolista/doctor/{id}', [EnrutamientoController::class, 'DoctoresListaUpdate'])->name('enrutamientolista.doctoresupdate');
});
//VISITADOR
Route::middleware(['checkRole:visitador,admin'])->group(function () {

    Route::resource('visitadoctor', VisitaDoctorController::class);
    //=============================Muestras - Modulo
    // Ruta principal que muestra todas las muestras
    Route::resource('muestras', MuestrasController::class);
    Route::get('misrutas',[EnrutamientoController::class,'MisRutas'])->name('enrutamientolista.misrutas');
    Route::get('/rutasdoctor/{id}',[EnrutamientoController::class,'DetalleDoctorRutas']);
    Route::post('guardar-visita',[EnrutamientoController::class,'GuardarVisita'])->name('rutas.guardarvisita');
});


Route::get('/diasdelmes', AsignacionSemanal::class .'@mostrarDiasDelMes')
    ->name('mostrarDiasDelMes');
Route::get('/prueba', function () {
    return view('pedidos.counter.cargar_pedido.prueba');
});

Route::get('/distritoslimacallao', UbigeoController::class .'@ObtenerDistritosLimayCallao')
    ->name('distritoslimacallao');
// Route::middleware(['checkRole:contabilidad'])->group(function () {
//     Route::resource('pedidoscontabilidad', PedidosContaController::class);
// });



//laboratorio
Route::middleware(['checkRole:laboratorio,admin'])->group(function () {
    Route::resource('pedidoslaboratorio', PedidoslabController::class);
    Route::get('/laboratorio', [laboratorioController::class, 'estado'])->name('muestras.estado');
    Route::put('/laboratorio/{id}/actualizar-estado', [laboratorioController::class, 'actualizarEstado'])
        ->name('muestras.actualizarEstado');
    Route::get('/laboratorio/{id}', [laboratorioController::class, 'showLab'])->name('muestras.showLab');
    Route::put('/laboratorio/{id}/actualizar-fecha', [laboratorioController::class, 'actualizarFechaEntrega'])->name('muestras.actualizarFechaEntrega');
    Route::get('/get-unidades/{clasificacionId}', [MuestrasController::class, 'getUnidadesPorClasificacion']);
    Route::put('/muestras/{id}/comentario', [laboratorioController::class, 'actualizarComentario'])->name('muestras.actualizarComentario');
    Route::get('/pedidoslaboratorio/{fecha}/downloadWord/{turno}', PedidoslabController::class .'@downloadWord')
    ->name('pedidoslaboratorio.downloadWord');
    Route::get('/pedidoslaboratoriodetalles',[PedidoslabController::class,'pedidosDetalles'])->name('pedidosLaboratorio.detalles');
    Route::put('pedidoslaboratoriodetalles/asignar/{id}/',[PedidoslabController::class,'asignarTecnicoProd'])->name('pedidosLaboratorio.asignarTecnicoProd');
    Route::post('/pedidoslaboratoriodetalles/asignarmultiple',[PedidoslabController::class,'asignarmultipletecnico'])->name('pedidosLaboratorio.asignarmultipletecnico');

    Route::resource('presentacionfarmaceutica', PresentacionFarmaceuticaController::class);
    Route::get('ingredientes/{base_id}',[PresentacionFarmaceuticaController::class,'listaringredientes'])->name('ingredientes.index');
    Route::post('base',[PresentacionFarmaceuticaController::class,'guardarbases'])->name('base.store');
    Route::post('ingredientes',[PresentacionFarmaceuticaController::class,'guardaringredientes'])->name('ingredientes.store');
    Route::put('ingredientes/{id}',[PresentacionFarmaceuticaController::class,'actualizaringredientes'])->name('ingredientes.update');
    Route::post('excipientes',[PresentacionFarmaceuticaController::class,'guardarexcipientes'])->name('excipientes.store');
    Route::delete('excipientes/{id}',[PresentacionFarmaceuticaController::class,'eliminarexcipientes'])->name('excipientes.delete');
});
    //ROL DE TECNICA DE PRODUCCION
    Route::get('pedidosproduccion',OrdenesController::class.'@index')->name('produccion.index')->middleware(['checkRole:tecnico_produccion,admin']);
    Route::post('pedidosproduccion/{detalleId}/actualizarestado',[OrdenesController::class,'actualizarEstado'])->name('pedidosproduccion.actualizarEstado');
// Ruta para actualizar el precio de una muestra
// Ruta para la gestión de precios en la vista de jefe de proyectos
Route::middleware(['checkRole:jefe-operaciones,admin'])->group(function () {
    Route::get('/jefe-operaciones', [jefe_proyectosController::class, 'precio'])->name('muestras.precio');
    Route::get('/jefe-operaciones/{id}', [jefe_proyectosController::class, 'showJO'])->name('muestras.showJO');
    Route::put('/muestras/{id}/actualizar-precio', [jefe_proyectosController::class, 'actualizarPrecio'])->name('muestras.actualizarPrecio');
    Route::get('/pedidos/jefe_proyectos'.jefe_proyectosController::class,);
});

//coordinadora 
//Aprobaciones

Route::middleware(['checkRole:coordinador-lineas,admin'])->group(function () {
    Route::get('/Coordinadora', [coordinadoraController::class, 'aprobacionCoordinadora'])->name('muestras.aprobacion.coordinadora');
    Route::put('/muestras/{id}/actualizar-fecha', [coordinadoraController::class, 'actualizarFechaEntrega'])->name('muestras.actualizarFechaEntrega');
    //crud
    Route::get('/Coordinadora/{id}', [coordinadoraController::class, 'showCo'])->name('muestras.showCo');
    Route::get('/coordinadora/agregar', [coordinadoraController::class, 'createCO'])->name('muestras.createCO');
    Route::post('/Coordinadora/agregar', [coordinadoraController::class, 'storeCO'])->name('muestras.storeCO');
    Route::get('/Coordinadora/{id}/edit', [coordinadoraController::class, 'editCO'])->name('muestras.editCO');
    Route::put('/Coordinadora/{id}/actualizar', [coordinadoraController::class, 'updateCO'])->name('muestras.updateCO');
    Route::delete('/Coordinadora/elimi/{id}', [coordinadoraController::class, 'destroyCO'])->name('muestras.destroyCO');
});
//JEFE COMERCIAL
Route::middleware(['checkRole:jefe-comercial,admin'])->group(function () {
    Route::resource('categoriadoctor',CategoriaDoctorController::class);
    Route::get('/jefe-comercial', [JcomercialController::class, 'confirmar'])->name('muestras.confirmar');
    Route::get('/jefe-comercial/{id}', [JcomercialController::class, 'showJC'])->name('muestras.showJC');
    Route::get('/ventascliente',[PedidosController::class,'listPedCliente'])->name('pedidosxcliente.listar');
});
//Jcomercial - coordonadordelineas
Route::put('/muestras/{id}/actualizar-aprobacion', [coordinadoraController::class, 'actualizarAprobacion'])->name('muestras.actualizarAprobacion')->middleware(['checkRole:jefe-comercial,coordinador-lineas,admin']);
//GERENCIACONTROLLER
Route::middleware(['checkRole:gerencia-general,admin'])->group(function () {
    //Reporte gerencia - Clasificaciones
    Route::get('/reporte', [gerenciaController::class, 'mostrarReporte'])->name('muestras.reporte');
    //Reporte Gerencia frasco original
    Route::get('/reporte/frasco-original', [gerenciaController::class, 'mostrarReporteFrascoOriginal'])->name('muestras.reporte.frasco-original');
    //Reporte Gerencia Frasco Muestra
    Route::get('/reporte/frasco-muestra', [gerenciaController::class, 'mostrarReporteFrascoMuestra'])->name('muestras.reporte.frasco-muestra');
    //exportar pdf en Reportes
    Route::get('reporte/PDF-frascoMuestra', [gerenciaController::class, 'exportarPDF'])->name('muestras.exportarPDF');
    Route::get('reporte/PDF-frascoOriginal', [gerenciaController::class, 'exportarPDFFrascoOriginal'])->name('muestras.frasco.original.pdf');
});

//COTIZADOR GENERAL----------
//modulos del softlyn
Route::middleware(['checkRole:Administracion,admin'])->group(function () {
    //Administración
    Route::resource('insumo_empaque', InsumoEmpaqueController::class);
    // CRUDs separados para envases, material e insumos
    Route::resource('envases', EnvaseController::class);
    Route::resource('material', MaterialController::class);
    Route::resource('insumos', InsumoController::class);
    //Crud proveedores
    Route::resource('proveedores', ProveedorController::class)->parameters([
            'proveedores' => 'proveedor']); 
    //Crud tipo de cambio- EL PRINCIPAL ES RESUMEN-TIPO-CAMBIO!!!
    Route::resource('tipo_cambio', TipoCambioController::class);
    Route::get('/resumen-tipo-cambio', [TipoCambioController::class, 'resumenTipoCambio'])->name('tipo_cambio.resumen');
    Route::delete('/tipo-cambio/{id}', [TipoCambioController::class, 'destroy'])->name('tipo_cambio.destroy');

    //crud para merchandise
    Route::resource('merchandise', MerchandiseController::class);
    //Ruta para utiles
    Route::resource('util', UtilController::class);
    //crud compras
    Route::resource('compras', CompraController::class);
    // CRUD Guía de Ingreso
    Route::resource('guia_ingreso', \App\Http\Controllers\softlyn\GuiaIngresoController::class);
    // Ruta AJAX para obtener detalles de compra
    Route::get('lotes/por-articulo/{articulo_id}', [\App\Http\Controllers\softlyn\GuiaIngresoController::class, 'getLotesPorArticulo'])->name('lotes.por_articulo');
    Route::get('guia_ingreso/detalles-compra/{compra_id}', [\App\Http\Controllers\softlyn\GuiaIngresoController::class, 'getDetallesCompra'])->name('guia_ingreso.detalles_compra');
});

Route::middleware(['checkRole:Administracion,admin'])->group(function () {
    // Rutas estándar del CRUD
    Route::resource('producto_final', ProductoFinalController::class);

    //crud volumen
    Route::resource('volumen', VolumenController::class);

    //Laboratorio 
    Route::resource('bases', BaseController::class);
    // Rutas adicionales para AJAX
    /* Route::get('articulos/por-tipo', [CompraController::class, 'getArticulosByTipo'])
        ->name('articulos.por-tipo');
    */
});

// Ruta principal que muestra todas las muestras
    Route::post('muestras/exportar-excel-jc', [App\Http\Controllers\muestras\JcomercialController::class, 'exportarExcel'])->name('muestras.exportarExcelJC');
    Route::post('muestras/exportar-excel-co', [App\Http\Controllers\muestras\coordinadoraController::class, 'exportarExcel'])->name('muestras.exportarExcelCO');
    Route::post('muestras/exportar-excel-lab', [App\Http\Controllers\muestras\laboratorioController::class, 'exportarExcel'])->name('muestras.exportarExcelLAB');

Route::middleware(['checkRole:contabilidad,admin'])->group(function () {
//contabilidad  marcará si el insumo es caro o no
Route::get('/insumo/marcar-caro', [InsumoController::class, 'marcarCaro'])->name('insumos.marcar-caro');
Route::post('/insumo/marcar-caro', [InsumoController::class, 'actualizarEsCaro'])->name('insumos.actualizar-es-caro');
});
