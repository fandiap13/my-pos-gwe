<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Data dummy development: beberapa kategori root + subkategori,
     * mendemonstrasikan struktur self-referencing. Lihat docs/DATABASE.md.
     */
    public function run(): void
    {
        $makanan = Category::factory()->create(['name' => 'Makanan', 'slug' => 'makanan']);
        Category::factory()->childOf($makanan)->create(['name' => 'Makanan Ringan', 'slug' => 'makanan-ringan']);
        Category::factory()->childOf($makanan)->create(['name' => 'Makanan Berat', 'slug' => 'makanan-berat']);

        $minuman = Category::factory()->create(['name' => 'Minuman', 'slug' => 'minuman']);
        Category::factory()->childOf($minuman)->create(['name' => 'Minuman Dingin', 'slug' => 'minuman-dingin']);
        Category::factory()->childOf($minuman)->create(['name' => 'Minuman Panas', 'slug' => 'minuman-panas']);

        Category::factory()->create(['name' => 'Lainnya', 'slug' => 'lainnya']);
    }
}
