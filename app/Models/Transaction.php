<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * PENTING: model ini TIDAK memakai trait SoftDeletes dan tidak boleh
 * memiliki method/call delete() di manapun. Data transaksi tidak boleh
 * dihapus sama sekali — lihat AGENTS.md & docs/DECISIONS.md.
 * "Batalkan transaksi" berarti update status jadi self::STATUS_VOIDED.
 */
class Transaction extends Model
{
    use HasUuids;

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_VOIDED = 'voided';

    public const PAYMENT_CASH = 'cash';

    public const PAYMENT_TRANSFER = 'transfer';

    public const PAYMENT_DEBIT = 'debit';

    protected $fillable = [
        'transaction_number',
        'user_id',
        'shift_id',
        'subtotal',
        'total',
        'paid_amount',
        'change_amount',
        'payment_method',
        'status',
        'voided_by',
        'voided_at',
        'void_reason',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'integer',
            'total' => 'integer',
            'paid_amount' => 'integer',
            'change_amount' => 'integer',
            'voided_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Shift, $this>
     */
    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function voidedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'voided_by');
    }

    /**
     * @return HasMany<TransactionItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(TransactionItem::class);
    }

    public function isVoided(): bool
    {
        return $this->status === self::STATUS_VOIDED;
    }
}
