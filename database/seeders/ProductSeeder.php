<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
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
    }
}
