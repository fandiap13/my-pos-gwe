<?php

namespace App\Actions\Auth;

use App\Models\Shift;
use App\Models\User;

// Satu tempat untuk logic redirect setelah login, sesuai
// docs/features/auth-login.md "Catatan Teknis": admin ke dashboard,
// kasir ke transaksi (atau Buka Shift kalau belum ada shift aktif).
class DetermineLoginRedirectAction
{
    public function handle(User $user): string
    {
        if ($user->isAdmin()) {
            return route('admin.dashboard');
        }

        $hasActiveShift = Shift::query()
            ->where('user_id', $user->id)
            ->where('status', Shift::STATUS_OPEN)
            ->exists();

        return $hasActiveShift
            ? route('kasir.transaksi')
            : route('kasir.shift.buka');
    }
}
