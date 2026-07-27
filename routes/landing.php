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
Route::get('/ofertas',   [LandingController::class, 'ofertas'])->middleware('track.landing:ofertas')->name('landing.ofertas');
Route::get('/repuestos', [LandingController::class, 'repuestos'])->middleware('track.landing:repuestos')->name('landing.repuestos');
Route::get('/conocenos', [LandingController::class, 'conocenos'])->middleware('track.landing:conocenos')->name('landing.conocenos');
Route::get('/contacto',  [LandingController::class, 'contacto'])->middleware('track.landing:contacto')->name('landing.contacto');
Route::post('/contacto', [LandingController::class, 'contactoEnviar'])->name('landing.contacto.enviar');
Route::post('/click/{tipo}', [LandingController::class, 'trackClick'])->name('landing.click');

// ponytail: sitemap inline — 5 URLs fijas. Mover a un controlador si algún día se agregan URLs dinámicas (ej. una por oferta).
Route::get('/sitemap.xml', function () {
    $urls = ['landing.home' => '1.0', 'landing.repuestos' => '0.9', 'landing.ofertas' => '0.8',
             'landing.conocenos' => '0.6', 'landing.contacto' => '0.6'];
    $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
         . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
    foreach ($urls as $name => $priority) {
        $xml .= '  <url><loc>' . route($name) . '</loc><priority>' . $priority . '</priority></url>' . "\n";
    }
    return response($xml . '</urlset>')->header('Content-Type', 'application/xml');
})->name('landing.sitemap');
