<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Sumber kebenaran untuk stok. Kolom products.stock hanya cache hasil
 * agregat tabel ini — lihat AGENTS.md. TIDAK BOLEH dihapus sama sekali.
 */
class StockMovement extends Model
{
    use HasUuids;

    public const TYPE_IN = 'in';

    public const TYPE_OUT = 'out';

    public const TYPE_ADJUSTMENT = 'adjustment';

    public const TYPE_VOID_RETURN = 'void_return';

    protected $fillable = [
        'product_id',
        'type',
        'quantity',
        'reference_type',
        'reference_id',
        'note',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Apakah type ini menambah stok (in) atau mengurangi (out).
     * Dipakai saat menghitung ulang cache products.stock.
     */
    public function isIncoming(): bool
    {
        return in_array($this->type, [self::TYPE_IN, self::TYPE_ADJUSTMENT, self::TYPE_VOID_RETURN], true);
    }
}
