<?php

use App\Http\Controllers\AnuncioController;
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

Route::get('/', function () {
    return view('welcome');
});

Route::get('/mis-anuncios', [AnuncioController::class, 'index'])->name('mis-anuncios');
Route::get('/anuncio/{id}', [AnuncioController::class, 'show'])->name('anuncio.show');