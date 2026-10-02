<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User dibuat sebelum ProductSeeder: stok awal produk seed
        // dicatat sebagai stock_movements dengan admin sebagai
        // created_by (lihat ProductSeeder — products.stock adalah
        // cache agregat dari tabel itu).
        User::factory()->admin()->create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
        ]);

        User::factory()->kasir()->create([
            'name' => 'Kasir',
            'email' => 'kasir@example.com',
        ]);

        $this->call([
            StoreSettingSeeder::class,
            CategorySeeder::class,
            ProductSeeder::class,
        ]);
    }
}
