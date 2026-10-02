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
        // Kategori & subkategori pakai 1 tabel self-referencing (parent_id).
        // Lihat docs/DATABASE.md & docs/DECISIONS.md untuk alasan.
        Schema::create('categories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('parent_id')->nullable();
            $table->string('name');
            $table->string('slug')->unique();
            $table->softDeletes();
            $table->timestamps();

            $table->index('parent_id');
        });

        // FK self-referencing ditambahkan setelah tabel dibuat: PostgreSQL
        // tidak bisa merujuk primary key tabel yang sama di dalam satu
        // Schema::create() (constraint belum ter-commit saat FK dievaluasi).
        Schema::table('categories', function (Blueprint $table) {
            $table->foreign('parent_id')->references('id')->on('categories')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
