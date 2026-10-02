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
        // PENTING: tabel ini TIDAK BOLEH di-soft-delete atau dihapus sama
        // sekali. Lihat AGENTS.md & docs/DECISIONS.md.
        Schema::create('transaction_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            // restrictOnDelete, bukan cascade: transactions tidak pernah
            // dihapus (lihat AGENTS.md), jadi FK ini menegaskan larangan
            // tersebut juga di level database.
            $table->foreignUuid('transaction_id')->constrained('transactions')->restrictOnDelete();
            $table->foreignUuid('product_id')->constrained('products')->restrictOnDelete();
            // Snapshot nama & harga produk saat transaksi — sengaja
            // didupliksi, bukan selalu join ke products, supaya riwayat
            // transaksi lama tidak berubah kalau produk diedit kemudian.
            // Lihat docs/DATABASE.md.
            $table->string('product_name');
            $table->unsignedInteger('price');
            $table->unsignedInteger('quantity');
            $table->unsignedInteger('subtotal');
            $table->timestamps();

            $table->index('transaction_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction_items');
    }
};
