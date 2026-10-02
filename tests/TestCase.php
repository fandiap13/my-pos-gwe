<?php

namespace Tests;

use Database\Seeders\StoreSettingSeeder;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // store_settings adalah data wajib single-row (lihat
        // database/seeders/StoreSettingSeeder.php) — seeder jalan saat
        // migrate:fresh --seed, tapi test memakai RefreshDatabase tanpa
        // seed, sehingga StoreSetting::current() (firstOrFail) akan gagal
        // saat Resource mengonversi timestamp ke zona waktu toko.
        (new StoreSettingSeeder)->run();
    }
}
