<?php

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
    });
