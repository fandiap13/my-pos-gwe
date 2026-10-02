<?php

use App\Http\Controllers\Api\HealthController;
use Illuminate\Support\Facades\Route;

// Endpoint JSON murni untuk konsumen eksternal (lihat AGENTS.md).
// Setiap endpoint di sini WAJIB didokumentasikan dengan anotasi Swagger
// dan bisa diakses lewat Swagger UI di /api/documentation.

Route::get('/health', HealthController::class)->name('api.health');
