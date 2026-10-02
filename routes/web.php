<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Tidak ada halaman landing publik — aplikasi internal (POS), '/' langsung
// ke login. Lihat docs/UI.md: hanya ada halaman Login, Kasir, dan Admin.
// Tujuan setelah login ditentukan oleh role, lihat routes/admin.php,
// routes/kasir.php, dan DetermineLoginRedirectAction.
Route::redirect('/', '/login');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
});

// Dev tool, bukan fitur produk — sengaja tanpa middleware auth supaya
// bisa diakses langsung tanpa login. Hanya aktif di environment local
// (tidak pernah ter-register di production). Lihat docs/ROADMAP.md Fase 1.2.
if (app()->environment('local')) {
    Route::get('/dev/components', function () {
        return Inertia::render('ComponentShowcase');
    })->name('dev.components');
}

require __DIR__.'/auth.php';
