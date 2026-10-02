<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Single-row table: pengaturan toko yang bisa diubah lewat aplikasi,
        // bukan hardcode di .env/config. Lihat docs/DATABASE.md & docs/DECISIONS.md.
        Schema::create('store_settings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('store_name');
            // Zona waktu IANA, misal Asia/Jakarta (WIB). Dipakai hanya untuk
            // konversi tampilan — data timestamp tetap tersimpan UTC.
            $table->string('timezone');
            $table->text('address')->nullable();
            $table->string('phone')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('store_settings');
    }
};
