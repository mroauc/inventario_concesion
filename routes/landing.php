<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LandingController;

/*
|--------------------------------------------------------------------------
| Rutas de la landing pública — serviciotecnicoroaval.com
|--------------------------------------------------------------------------
| Este archivo es incluido por web.php dentro de Route::domain() para
| ambos dominios: serviciotecnicoroaval.com y www.serviciotecnicoroaval.com
|
| NO modificar rutas del portal (roait.dev) aquí.
|--------------------------------------------------------------------------
*/

Route::get('/',          [LandingController::class, 'home'])->name('landing.home');
Route::get('/wpp',       fn() => redirect('/'));
Route::get('/repuestos', [LandingController::class, 'repuestos'])->name('landing.repuestos');
Route::get('/conocenos', [LandingController::class, 'conocenos'])->name('landing.conocenos');
Route::get('/contacto',  [LandingController::class, 'contacto'])->name('landing.contacto');
Route::post('/contacto', [LandingController::class, 'contactoEnviar'])->name('landing.contacto.enviar');
