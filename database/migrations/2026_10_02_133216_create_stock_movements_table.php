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
        // Sumber kebenaran untuk stok (lihat AGENTS.md). Kolom products.stock
        // hanya cache hasil agregat tabel ini. TIDAK BOLEH dihapus sama
        // sekali, sama seperti transactions — lihat docs/DECISIONS.md.
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('product_id')->constrained('products')->restrictOnDelete();
            // in, out, adjustment, void_return — lihat docs/DATABASE.md
            $table->string('type');
            // Selalu positif; arah perubahan ditentukan oleh `type`.
            $table->unsignedInteger('quantity');
            // Polymorphic ke sumber pergerakan (biasanya transaction_items),
            // nullable untuk input manual admin. Pola morphs() Laravel.
            $table->nullableUuidMorphs('reference');
            $table->text('note')->nullable();
            $table->foreignUuid('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();

            $table->index(['product_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
