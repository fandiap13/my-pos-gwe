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
        // Satu row = satu sesi kerja kasir, dari buka sampai tutup.
        // Constraint "satu kasir satu shift aktif" divalidasi di Action,
        // bukan di database (kondisinya bersyarat) — lihat docs/DATABASE.md.
        Schema::create('shifts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->restrictOnDelete();
            $table->unsignedInteger('opening_cash');
            $table->unsignedInteger('closing_cash')->nullable();
            $table->unsignedInteger('expected_cash')->nullable();
            $table->string('status');
            $table->timestamp('opened_at');
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shifts');
    }
};
