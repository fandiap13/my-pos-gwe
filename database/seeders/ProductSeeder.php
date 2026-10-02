<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Data dummy development, termasuk beberapa skenario khusus
     * (stok habis, stok menipis) untuk memudahkan testing manual UI.
     */
    public function run(): void
    {
        $categories = Category::query()->whereNotNull('parent_id')->get();

        $categories->each(function (Category $category) {
            Product::factory()
                ->count(3)
                ->create(['category_id' => $category->id]);
        });

        // Skenario khusus untuk testing UI stok menipis/habis.
        $sample = $categories->first();

        if ($sample !== null) {
            Product::factory()->outOfStock()->create([
                'category_id' => $sample->id,
                'name' => 'Produk Stok Habis (contoh)',
            ]);

            Product::factory()->lowStock()->create([
                'category_id' => $sample->id,
                'name' => 'Produk Stok Menipis (contoh)',
            ]);
        }

        $this->recordInitialStockMovements();
    }

    /**
     * Factory mengisi kolom products.stock langsung tanpa riwayat.
     * Karena products.stock hanyalah cache agregat stock_movements
     * (AGENTS.md), tanpa baris 'in' ini transaksi pertama pada produk
     * seed akan membuat observer menghitung ulang stok menjadi negatif.
     */
    private function recordInitialStockMovements(): void
    {
        $admin = User::query()->where('email', 'admin@example.com')->firstOrFail();

        Product::query()
            ->where('stock', '>', 0)
            ->whereDoesntHave('stockMovements')
            ->each(function (Product $product) use ($admin) {
                StockMovement::create([
                    'product_id' => $product->id,
                    'type' => StockMovement::TYPE_IN,
                    'quantity' => $product->stock,
                    'created_by' => $admin->id,
                ]);
            });
    }
}
