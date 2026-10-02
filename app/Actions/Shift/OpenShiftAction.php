<?php

namespace App\Actions\Shift;

use App\Models\Shift;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

// Buka shift kasir: modal awal kas + catat waktu buka. Satu kasir hanya
// boleh punya satu shift status 'open' (PRD §6) — divalidasi DI SINI,
// bukan lewat DB constraint (kondisinya bersyarat, lihat docs/DATABASE.md).
class OpenShiftAction
{
    /**
     * @param  array{opening_cash: int}  $data
     */
    public function handle(array $data, User $user): Shift
    {
        return DB::transaction(function () use ($data, $user) {
            // Lock baris user supaya dua request "buka shift" yang datang
            // bersamaan tidak lolos cek check-then-insert (dua shift open
            // untuk kasir yang sama). Query berikutnya menunggu sampai
            // transaksi pertama selesai.
            User::query()->whereKey($user->id)->lockForUpdate()->first();

            $hasOpenShift = Shift::query()
                ->where('user_id', $user->id)
                ->where('status', Shift::STATUS_OPEN)
                ->exists();

            if ($hasOpenShift) {
                throw ValidationException::withMessages([
                    'opening_cash' => 'Anda masih memiliki shift aktif. Tutup shift terlebih dahulu.',
                ]);
            }

            return Shift::create([
                'user_id' => $user->id,
                'opening_cash' => $data['opening_cash'],
                'status' => Shift::STATUS_OPEN,
                'opened_at' => now(),
            ]);
        });
    }
}
