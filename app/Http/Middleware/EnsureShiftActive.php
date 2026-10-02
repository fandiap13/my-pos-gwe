<?php

namespace App\Http\Middleware;

use App\Models\Shift;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

// Dicek ulang di tiap request ke route kasir (bukan cuma saat login) —
// kasir bisa logout di tengah shift lalu login lagi. Lihat
// docs/features/auth-login.md "Catatan Teknis".
//
// Route Buka/Tutup Shift sendiri HARUS dikecualikan dari middleware ini
// (lihat routes/kasir.php) — kalau tidak, kasir tanpa shift tidak akan
// pernah bisa mengakses halaman untuk membuka shift.
class EnsureShiftActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $hasActiveShift = Shift::query()
            ->where('user_id', $request->user()->id)
            ->where('status', Shift::STATUS_OPEN)
            ->exists();

        if (! $hasActiveShift) {
            return redirect()->route('kasir.shift.buka');
        }

        return $next($request);
    }
}
