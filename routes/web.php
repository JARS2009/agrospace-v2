<?php

use App\Http\Controllers\PlayerProfileController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('granja', function () {
    return Inertia::render('Granja');
})->middleware(['auth', 'verified'])->name('granja');

Route::get('profile', [PlayerProfileController::class, 'show'])
    ->middleware(['auth', 'verified'])
    ->name('player.profile');

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
