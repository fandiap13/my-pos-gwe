<?php

namespace App\Actions\Shift;

use App\Models\Shift;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

// Tutup shift: simpan kas fisik yang diinput kasir, hitung ulang kas
// seharusnya (expected_cash) di detik itu juga, lalu tandai shift closed.
// expected_cash SELALU dihitung ulang di backend — nilai preview dari
// frontend tidak dipercaya (prinsip: aturan bisnis ada di backend).
class CloseShiftAction
{
    public function __construct(private readonly CalculateExpectedCashAction $calculateExpectedCash) {}

    public function handle(Shift $shift, int $closingCash): Shift
    {
        return DB::transaction(function () use ($shift, $closingCash) {
            if (! $shift->isOpen()) {
                throw ValidationException::withMessages([
                    'closing_cash' => 'Shift ini sudah ditutup.',
                ]);
            }

            $shift->update([
                'closing_cash' => $closingCash,
                'expected_cash' => $this->calculateExpectedCash->handle($shift),
                'status' => Shift::STATUS_CLOSED,
                'closed_at' => now(),
            ]);

            return $shift;
        });
    }
}
