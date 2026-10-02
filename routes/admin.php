<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ShiftController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Route khusus Admin — lihat AGENTS.md (pemisahan route per role) &
// docs/DECISIONS.md. Semua route di sini wajib middleware auth + role:admin.
Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        // Placeholder — Dashboard sungguhan (StatCard, grafik, dll) di
        // Fase 1.11. Lihat docs/ROADMAP.md & docs/DECISIONS.md.
        Route::get('/', function () {
            return Inertia::render('Admin/Dashboard');
        })->name('dashboard');

        Route::resource('categories', CategoryController::class)
            ->except(['show'])
            ->parameters(['categories' => 'category']);

        Route::resource('products', ProductController::class)
            ->except(['show'])
            ->parameters(['products' => 'product']);

        // Riwayat shift semua kasir (read-only) — buka/tutup shift hanya
        // dari sisi kasir. Lihat docs/ROADMAP.md Fase 1.5.
        Route::get('/shifts', [ShiftController::class, 'index'])->name('shifts.index');
    });
