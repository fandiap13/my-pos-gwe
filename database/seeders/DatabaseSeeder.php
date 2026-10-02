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
        $this->call([
            StoreSettingSeeder::class,
            CategorySeeder::class,
            ProductSeeder::class,
        ]);

        User::factory()->admin()->create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
        ]);

        User::factory()->kasir()->create([
            'name' => 'Kasir',
            'email' => 'kasir@example.com',
        ]);
    }
}
