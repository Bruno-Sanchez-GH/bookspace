<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\BookCatalogController;
use App\Http\Controllers\BookRecommendationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\SavedBookController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/dashboard', DashboardController::class)->name('dashboard');
Route::get('/catalogo', [BookCatalogController::class, 'index'])->name('catalog.index');

Route::middleware('guest')->group(function (): void {
    Route::get('/registro', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/registro', [RegisteredUserController::class, 'store'])->name('register.store');
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('login.store');
});

Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
    Route::get('/mi-biblioteca', [SavedBookController::class, 'index'])->name('library.index');
    Route::post('/mi-biblioteca/recomendaciones', [BookRecommendationController::class, 'store'])
        ->middleware('throttle:book-recommendations')
        ->name('recommendations.store');
    Route::post('/mi-biblioteca/{externalId}', [SavedBookController::class, 'store'])
        ->where('externalId', '[A-Za-z0-9_-]+')
        ->name('library.store');
    Route::patch('/mi-biblioteca/{book}', [SavedBookController::class, 'update'])->name('library.update');
    Route::delete('/mi-biblioteca/{book}', [SavedBookController::class, 'destroy'])->name('library.destroy');
});
