<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Route khusus Kasir — lihat AGENTS.md (pemisahan route per role) &
// docs/DECISIONS.md. Semua route di sini wajib middleware auth + role:kasir.
Route::middleware(['auth', 'role:kasir'])
    ->prefix('kasir')
    ->name('kasir.')
    ->group(function () {
        // Buka Shift TIDAK boleh pakai middleware shift.active — kalau
        // dipasang, kasir tanpa shift tidak akan pernah bisa mengakses
        // halaman untuk membuka shift (redirect loop). Lihat
        // app/Http/Middleware/EnsureShiftActive.php.
        Route::get('/shift/buka', function () {
            return Inertia::render('Kasir/Shift/Buka');
        })->name('shift.buka');

        Route::middleware('shift.active')->group(function () {
            // Placeholder — halaman Transaksi sungguhan di Fase 1.6.
            // Lihat docs/ROADMAP.md & docs/features/checkout.md.
            Route::get('/', function () {
                return Inertia::render('Kasir/Transaksi');
            })->name('transaksi');
        });
    });
