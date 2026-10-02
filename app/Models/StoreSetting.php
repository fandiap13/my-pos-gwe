<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class StoreSetting extends Model
{
    use HasUuids;

    protected $fillable = [
        'store_name',
        'timezone',
        'address',
        'phone',
    ];

    /**
     * Ambil row pengaturan toko (single-row table).
     *
     * Dicache dalam request yang sama supaya tidak query berulang kali.
     * Lihat docs/DATABASE.md: akses lewat helper, bukan query manual
     * berulang di tiap controller.
     */
    public static function current(): self
    {
        return once(fn () => static::query()->firstOrFail());
    }
}
