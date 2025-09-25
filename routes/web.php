<?php

use App\Http\Controllers\AnuncioController;
use App\Http\Controllers\PerfilController;
use App\Http\Controllers\DashboardController;
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

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

Route::get('/mis-anuncios', [AnuncioController::class, 'index'])->name('mis-anuncios');
Route::get('/anuncio/{id}', [AnuncioController::class, 'show'])->name('anuncio.show');
Route::get('/anuncio/{id}/edit', [AnuncioController::class, 'edit'])->name('anuncio.edit');
Route::put('/anuncio/{id}', [AnuncioController::class, 'update'])->name('anuncio.update');

// Perfil de usuario
Route::get('/perfil', [PerfilController::class, 'edit'])->name('perfil');
Route::post('/perfil', [PerfilController::class, 'update'])->name('perfil.update');

// Logout simulado: limpia la sesión y redirige al inicio
Route::get('/logout', function () {
    try {
        \Illuminate\Support\Facades\Auth::logout();
    } catch (\Throwable $e) {
        // ignorar si no hay auth configurado
    }
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/');
})->name('logout');