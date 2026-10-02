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
        // sekali (hard delete maupun soft delete). "Batalkan transaksi"
        // berarti update kolom status jadi 'voided', bukan menghapus row.
        // Lihat AGENTS.md & docs/DECISIONS.md untuk alasan audit trail.
        Schema::create('transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            // Nomor struk human-readable, terpisah dari id (UUID tidak
            // praktis dicetak di struk fisik). Format: TRX-YYYYMMDD-XXXX.
            $table->string('transaction_number')->unique();
            $table->foreignUuid('user_id')->constrained('users')->restrictOnDelete();
            $table->foreignUuid('shift_id')->constrained('shifts')->restrictOnDelete();
            $table->unsignedInteger('subtotal');
            $table->unsignedInteger('total');
            $table->unsignedInteger('paid_amount');
            $table->unsignedInteger('change_amount');
            $table->string('payment_method');
            $table->string('status')->default('completed');
            $table->foreignUuid('voided_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('voided_at')->nullable();
            $table->text('void_reason')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index('shift_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
