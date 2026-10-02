<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

// Alias route: 'role:admin' atau 'role:kasir' — lihat AGENTS.md &
// bootstrap/app.php. Dipasang di routes/admin.php dan routes/kasir.php,
// bukan di routes/web.php (route shared tidak butuh pembatasan role).
class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (! $request->user() || $request->user()->role !== $role) {
            abort(403);
        }

        return $next($request);
    }
}
