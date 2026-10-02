<?php

use App\Http\Controllers\Kasir\ShiftController;
use App\Http\Controllers\Kasir\TransactionController;
use Illuminate\Support\Facades\Route;

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

        // Halaman struk setelah checkout sukses dikecualikan dari
        // shift.active: transaksi sudah tersimpan, struk tetap harus
        // bisa dibuka walau shift keburu ditutup — dilindungi cek
        // kepemilikan di TransactionController@selesai.
        Route::get('/transaksi/selesai/{transaction}', [TransactionController::class, 'selesai'])->name('transaksi.selesai');

        Route::middleware('shift.active')->group(function () {
            Route::get('/shift/tutup', [ShiftController::class, 'tutup'])->name('shift.tutup');
            Route::post('/shift/tutup', [ShiftController::class, 'close'])->name('shift.close');

            Route::get('/', [TransactionController::class, 'index'])->name('transaksi');
            Route::post('/', [TransactionController::class, 'store'])->name('transaksi.store');
        });
    });
