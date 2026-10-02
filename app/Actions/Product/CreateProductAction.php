<?php

namespace App\Actions\Product;

use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Support\Facades\DB;

// Membuat produk + insert stock_movements untuk stok awal (bukan isi
// kolom products.stock langsung) — lihat AGENTS.md & docs/DATABASE.md.
// StockMovementObserver otomatis menghitung ulang cache stock setelahnya.
class CreateProductAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(array $data, User $createdBy): Product
    {
        return DB::transaction(function () use ($data, $createdBy) {
            $product = Product::create([
                'category_id' => $data['category_id'] ?? null,
                'name' => $data['name'],
                'sku' => $data['sku'] ?? null,
                'barcode' => $data['barcode'] ?? null,
                'price' => $data['price'],
                'cost_price' => $data['cost_price'] ?? null,
                'min_stock' => $data['min_stock'],
            ]);

            if ($data['initial_stock'] > 0) {
                StockMovement::create([
                    'product_id' => $product->id,
                    'type' => StockMovement::TYPE_IN,
                    'quantity' => $data['initial_stock'],
                    'note' => 'Stok awal saat produk dibuat',
                    'created_by' => $createdBy->id,
                ]);
            }

            return $product;
        });
    }
}
