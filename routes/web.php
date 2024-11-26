<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::get('/pruebas', function(){
    $test = \App\Models\Product_Store::where('id_product', 1)->where('id_store', 2)->first();
    dd(implode(', ', $test->positions()->select('position')->get()->pluck('position')->toArray()));
    dd($test->positions()->select('position')->get()->pluck('position')->toArray());
});
// REPRESENTANTES
Route::get('/representative', [App\Http\Controllers\RepresentativeController::class, 'index'])->name('representative.index');
// Route::get('/representative/{id}', [App\Http\Controllers\RepresentativeController::class, 'show'])->name('representative.show');
Route::get('/representative/create', [App\Http\Controllers\RepresentativeController::class, 'create'])->name('representative.create');
Route::post('/representative/store', [App\Http\Controllers\RepresentativeController::class, 'store'])->name('representative.store');
Route::get('/representative/{id}/edit', [App\Http\Controllers\RepresentativeController::class, 'edit'])->name('representative.edit');
Route::patch('/representative/{id}', [App\Http\Controllers\RepresentativeController::class, 'update'])->name('representative.update');
Route::delete('/representative/{id}', [App\Http\Controllers\RepresentativeController::class, 'destroy'])->name('representative.destroy');
// CONCESIONES
// Route::get('/concession', [App\Http\Controllers\ConcessionController::class, 'index'])->name('concession.index');
// Route::get('/concession/{id}', [App\Http\Controllers\ConcessionController::class, 'show'])->name('concession.show');
// Route::get('/concession/create', [App\Http\Controllers\ConcessionController::class, 'create'])->name('concession.create');
// Route::post('/concession/store', [App\Http\Controllers\ConcessionController::class, 'store'])->name('concession.store');
// Route::get('/concession/{id}/edit', [App\Http\Controllers\ConcessionController::class, 'edit'])->name('concession.edit');
// Route::patch('/concession/{id}', [App\Http\Controllers\ConcessionController::class, 'update'])->name('concession.update');
// Route::delete('/concession/{id}', [App\Http\Controllers\ConcessionController::class, 'destroy'])->name('concession.destroy');


Route::resource('concessions', App\Http\Controllers\ConcessionController::class);


Route::resource('categoryProducts', App\Http\Controllers\Category_productController::class);


Route::resource('products', App\Http\Controllers\ProductController::class);
Route::get('/product/getInfo', [App\Http\Controllers\ProductController::class, 'getProduct'])->name('products.getInfo');
Route::post('/product/storeModal', [App\Http\Controllers\ProductController::class, 'storeModal'])->name('representative.storeModal');
Route::get('/product/importar', [App\Http\Controllers\ProductController::class, 'index_importar_product'])->name('products.index_importar');
Route::post('/product/importar', [App\Http\Controllers\ProductController::class, 'import_products'])->name('products.import');

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');


Route::resource('stores', App\Http\Controllers\StoreController::class);

Route::resource('users', App\Http\Controllers\UserController::class)->middleware('auth');
