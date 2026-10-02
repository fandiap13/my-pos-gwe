<?php

namespace App\Observers;

use App\Models\StockMovement;

/**
 * Menjaga kolom cache products.stock tetap sinkron setiap kali ada
 * stock_movements baru. Lihat docs/DATABASE.md: "Hal yang Masih Perlu
 * Diputuskan" — kalkulasi dipilih lewat Observer (bukan on-the-fly),
 * supaya laporan/listing produk tetap cepat tanpa agregasi berulang.
 *
 * stock_movements tetap jadi sumber kebenaran; kolom ini murni cache.
 */
class StockMovementObserver
{
    public function created(StockMovement $stockMovement): void
    {
        $this->recalculate($stockMovement);
    }

    private function recalculate(StockMovement $stockMovement): void
    {
        $product = $stockMovement->product;

        $incoming = $product->stockMovements()
            ->whereIn('type', [
                StockMovement::TYPE_IN,
                StockMovement::TYPE_ADJUSTMENT,
                StockMovement::TYPE_VOID_RETURN,
            ])
            ->sum('quantity');

        $outgoing = $product->stockMovements()
            ->where('type', StockMovement::TYPE_OUT)
            ->sum('quantity');

        $product->update(['stock' => $incoming - $outgoing]);
    }
}
