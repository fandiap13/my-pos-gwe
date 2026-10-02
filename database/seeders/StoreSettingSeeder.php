<?php

namespace Database\Seeders;

use App\Models\StoreSetting;
use Illuminate\Database\Seeder;

class StoreSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Wajib ada 1 row default — aplikasi akan error tanpa ini, karena
     * StoreSetting::current() memakai firstOrFail(). Lihat docs/DATABASE.md.
     */
    public function run(): void
    {
        StoreSetting::firstOrCreate([], [
            'store_name' => 'Toko DiPosKan',
            'timezone' => 'Asia/Jakarta',
            'address' => null,
            'phone' => null,
        ]);
    }
}
