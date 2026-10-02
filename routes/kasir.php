<?php

use App\Http\Controllers\Kasir\ShiftController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Route khusus Kasir — lihat AGENTS.md (pemisahan route per role) &
// docs/DECISIONS.md. Semua route di sini wajib middleware auth + role:kasir.
Route::middleware(['auth', 'role:kasir'])
    ->prefix('kasir')
    ->name('kasir.')
    ->group(function () {
        // Buka Shift & Riwayat Shift TIDAK boleh pakai middleware
        // shift.active — kalau dipasang, kasir tanpa shift tidak akan
        // pernah bisa mengakses halaman untuk membuka shift (redirect
        // loop), dan riwayat tidak bisa dilihat setelah shift ditutup.
        // Lihat app/Http/Middleware/EnsureShiftActive.php.
        Route::get('/shift/buka', [ShiftController::class, 'buka'])->name('shift.buka');
        Route::post('/shift', [ShiftController::class, 'store'])->name('shift.store');
        Route::get('/shift/riwayat', [ShiftController::class, 'riwayat'])->name('shift.riwayat');

        Route::middleware('shift.active')->group(function () {
            Route::get('/shift/tutup', [ShiftController::class, 'tutup'])->name('shift.tutup');
            Route::post('/shift/tutup', [ShiftController::class, 'close'])->name('shift.close');

            // Placeholder — halaman Transaksi sungguhan di Fase 1.6.
            // Lihat docs/ROADMAP.md & docs/features/checkout.md.
            Route::get('/', function () {
                return Inertia::render('Kasir/Transaksi');
            })->name('transaksi');
        });
    });
