<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Harga dalam rupiah (integer, tanpa desimal) — lihat AGENTS.md.
        $price = fake()->numberBetween(5, 500) * 1000;

        // Catatan: stock di sini diisi langsung lewat factory untuk
        // kebutuhan seed/testing, BYPASS StockMovementObserver. Ini
        // pengecualian yang disengaja untuk data dummy — Action
        // sungguhan (checkout, adjustment) WAJIB tetap lewat
        // StockMovement::create(), bukan update langsung. Lihat AGENTS.md.

        return [
            'category_id' => Category::factory(),
            'name' => ucfirst(fake()->unique()->words(3, true)),
            'sku' => fake()->unique()->bothify('SKU-####??'),
            'barcode' => fake()->unique()->ean13(),
            'price' => $price,
            'cost_price' => (int) ($price * 0.7),
            'stock' => fake()->numberBetween(0, 100),
            'min_stock' => 10,
        ];
    }

    /**
     * Produk dengan stok habis (dipakai untuk test blokir checkout).
     */
    public function outOfStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'stock' => 0,
        ]);
    }

    /**
     * Produk dengan stok menipis (di bawah/sama dengan min_stock).
     */
    public function lowStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'stock' => 5,
            'min_stock' => 10,
        ]);
    }
}
