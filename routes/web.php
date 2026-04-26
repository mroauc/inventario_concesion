<?php

use Illuminate\Support\Facades\Route;

Route::get('/', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Auth::routes();
Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

// ─── SUPER ADMIN: Roles y Permisos ───────────────────────────────────────────
Route::middleware(['auth', 'role:super_admin'])->group(function () {
    Route::get('roles', [App\Http\Controllers\RolController::class, 'index'])->name('roles.index');
    Route::get('roles/{rol}', [App\Http\Controllers\RolController::class, 'show'])->name('roles.show');
    Route::put('roles/{rol}', [App\Http\Controllers\RolController::class, 'update'])->name('roles.update');
});

// ─── SUPER ADMIN: Concesiones y Representantes ───────────────────────────────
Route::middleware(['auth', 'permission:concesiones.ver'])->group(function () {
    Route::resource('concessions', App\Http\Controllers\ConcessionController::class);

    Route::get('/representative', [App\Http\Controllers\RepresentativeController::class, 'index'])->name('representative.index');
    Route::get('/representative/create', [App\Http\Controllers\RepresentativeController::class, 'create'])->name('representative.create');
    Route::post('/representative/store', [App\Http\Controllers\RepresentativeController::class, 'store'])->name('representative.store');
    Route::get('/representative/{id}/edit', [App\Http\Controllers\RepresentativeController::class, 'edit'])->name('representative.edit');
    Route::patch('/representative/{id}', [App\Http\Controllers\RepresentativeController::class, 'update'])->name('representative.update');
    Route::delete('/representative/{id}', [App\Http\Controllers\RepresentativeController::class, 'destroy'])->name('representative.destroy');
});

// ─── ADMINISTRACIÓN: Usuarios ─────────────────────────────────────────────────
Route::middleware(['auth', 'permission:usuarios.ver'])->group(function () {
    Route::resource('users', App\Http\Controllers\UserController::class);
});

// ─── HISTORIAL ────────────────────────────────────────────────────────────────
Route::middleware(['auth', 'permission:historial.ver'])->group(function () {
    Route::get('/historial', [App\Http\Controllers\LogsController::class, 'index'])->name('logs.index');
    Route::get('/historial/datatables', [App\Http\Controllers\LogsController::class, 'datatables'])->name('logs.datatables');
});

// ─── INVENTARIO: Productos ────────────────────────────────────────────────────
Route::middleware(['auth', 'permission:productos.ver'])->group(function () {
    Route::resource('products', App\Http\Controllers\ProductController::class);
    Route::get('/product/getInfo', [App\Http\Controllers\ProductController::class, 'getProduct'])->name('products.getInfo');
    Route::post('/product/storeModal', [App\Http\Controllers\ProductController::class, 'storeModal'])->name('representative.storeModal');
    Route::get('/product/importar', [App\Http\Controllers\ProductController::class, 'index_importar_product'])->name('products.index_importar');
    Route::post('/product/importar', [App\Http\Controllers\ProductController::class, 'import_products'])->name('products.import');
});

// ─── INVENTARIO: Bodegas ──────────────────────────────────────────────────────
Route::middleware(['auth', 'permission:bodegas.ver'])->group(function () {
    Route::resource('stores', App\Http\Controllers\StoreController::class);
});

// ─── INVENTARIO: Categorías ───────────────────────────────────────────────────
Route::middleware(['auth', 'permission:categorias.ver'])->group(function () {
    Route::resource('categoryProducts', App\Http\Controllers\Category_productController::class);
});

// ─── SERVICIO TÉCNICO: Clientes ───────────────────────────────────────────────
Route::middleware(['auth', 'permission:clientes.ver'])->group(function () {
    Route::post('clientes/{cliente}/coordenadas', [App\Http\Controllers\ClienteController::class, 'updateCoordenadas'])->name('clientes.updateCoordenadas');
    Route::get('clientes-datatables', [App\Http\Controllers\ClienteController::class, 'datatables'])->name('clientes.datatables');
    Route::resource('clientes', App\Http\Controllers\ClienteController::class);
});

// ─── SERVICIO TÉCNICO: Servicios (tipos) ─────────────────────────────────────
Route::middleware(['auth', 'permission:servicios.ver'])->group(function () {
    Route::resource('servicios', App\Http\Controllers\ServicioController::class);
});

// ─── SERVICIO TÉCNICO: Técnicos ───────────────────────────────────────────────
Route::middleware(['auth', 'permission:tecnicos.ver'])->group(function () {
    Route::resource('tecnicos', App\Http\Controllers\TecnicoController::class);
});

// ─── SERVICIO TÉCNICO: Órdenes de Servicio ───────────────────────────────────
Route::middleware(['auth', 'permission:ordenes.ver'])->group(function () {
    Route::get('clientes/{cliente}/datos', [App\Http\Controllers\OrdenServicioController::class, 'clienteDatos'])->name('clientes.datos');
    Route::get('ordenes-datatables', [App\Http\Controllers\OrdenServicioController::class, 'datatables'])->name('ordenes_servicio.datatables');
    Route::resource('ordenes_servicio', App\Http\Controllers\OrdenServicioController::class);
});

// ─── SERVICIO TÉCNICO: Artefactos ─────────────────────────────────────────────
Route::middleware(['auth', 'permission:artefactos.ver'])->group(function () {
    Route::get('artefactos-datatables', [App\Http\Controllers\ArtefactoController::class, 'datatables'])->name('artefactos.datatables');
    Route::get('artefactos/importar', [App\Http\Controllers\ArtefactoController::class, 'index_importar'])->name('artefactos.index_importar');
    Route::post('artefactos/importar', [App\Http\Controllers\ArtefactoController::class, 'importar'])->name('artefactos.importar');
    Route::get('artefactos/historial-importacion', [App\Http\Controllers\ArtefactoController::class, 'historial'])->name('artefactos.historial');
    Route::resource('artefactos', App\Http\Controllers\ArtefactoController::class);
});

// ─── SERVICIO TÉCNICO: Tipo Artefactos ───────────────────────────────────────
Route::middleware(['auth', 'permission:tipo_artefactos.ver'])->group(function () {
    Route::resource('tipo_artefactos', App\Http\Controllers\TipoArtefactoController::class)->except(['show']);
});

// ─── FLUJO DE CAJA ────────────────────────────────────────────────────────────
Route::middleware(['auth', 'permission:flujo_caja.ver'])->prefix('flujo-caja')->name('flujo_caja.')->group(function () {
    Route::get('/',                                    [App\Http\Controllers\FlujoCajaController::class, 'index'])->name('index');
    Route::get('/dia',                                 [App\Http\Controllers\FlujoCajaController::class, 'cargarDia'])->name('dia');
    Route::post('/movimiento',                         [App\Http\Controllers\FlujoCajaController::class, 'registrarMovimiento'])->name('movimiento');
    Route::post('/movimiento/{movimiento}/anular',     [App\Http\Controllers\FlujoCajaController::class, 'anularMovimiento'])->name('anular');
    Route::patch('/{caja}/apertura',                   [App\Http\Controllers\FlujoCajaController::class, 'actualizarAperturas'])->name('apertura');
    Route::post('/{caja}/cerrar',                      [App\Http\Controllers\FlujoCajaController::class, 'cerrarCaja'])->name('cerrar');
    Route::post('/{caja}/reabrir',                     [App\Http\Controllers\FlujoCajaController::class, 'reabrirCaja'])->name('reabrir');
});

// ─── PRUEBAS (solo desarrollo) ────────────────────────────────────────────────
Route::get('/pruebas', function () {
    $test = \App\Models\Product_Store::where('id_product', 1)->where('id_store', 2)->first();
    dd(implode(', ', $test->positions()->select('position')->get()->pluck('position')->toArray()));
});
