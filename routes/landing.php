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

Route::get('/',          [LandingController::class, 'home'])->middleware('track.landing:home')->name('landing.home');
Route::redirect('/wpp', '/', 301);
Route::get('/repuestos', [LandingController::class, 'repuestos'])->middleware('track.landing:repuestos')->name('landing.repuestos');
Route::get('/conocenos', [LandingController::class, 'conocenos'])->middleware('track.landing:conocenos')->name('landing.conocenos');
Route::get('/contacto',  [LandingController::class, 'contacto'])->middleware('track.landing:contacto')->name('landing.contacto');
Route::post('/contacto', [LandingController::class, 'contactoEnviar'])->name('landing.contacto.enviar');
