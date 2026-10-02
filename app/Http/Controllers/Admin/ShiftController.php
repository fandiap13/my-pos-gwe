<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ShiftResource;
use App\Models\Shift;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

// Riwayat shift semua kasir (read-only) — hanya admin yang boleh
// melihat milik kasir lain. Buka/tutup shift adalah tanggung jawab kasir
// (App\Http\Controllers\Kasir\ShiftController).
class ShiftController extends Controller
{
    public function index(Request $request): Response
    {
        $shifts = Shift::query()
            ->with('user')
            ->when(
                $request->string('status')->value(),
                fn ($query, $status) => $query->where('status', $status)
            )
            ->when(
                $request->string('search')->value(),
                fn ($query, $search) => $query->whereHas(
                    'user',
                    fn ($query) => $query->where('name', 'like', "%{$search}%")
                )
            )
            ->orderByDesc('opened_at')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Admin/Shifts/Index', [
            'shifts' => $shifts->through(fn (Shift $shift) => (new ShiftResource($shift))->resolve()),
            'filters' => $request->only(['status', 'search']),
        ]);
    }
}
