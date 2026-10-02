<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Tidak ada halaman landing publik — aplikasi internal (POS), '/' langsung
// ke login. Lihat docs/UI.md: hanya ada halaman Login, Kasir, dan Admin.
Route::redirect('/', '/login');

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
});

// Dev tool, bukan fitur produk — lihat docs/ROADMAP.md Fase 1.2.
if (app()->environment('local')) {
    Route::get('/dev/components', function () {
        return Inertia::render('ComponentShowcase');
    })->middleware('auth')->name('dev.components');
}

require __DIR__.'/auth.php';
