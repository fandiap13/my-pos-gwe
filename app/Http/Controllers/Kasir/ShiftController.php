<?php

namespace App\Http\Controllers\Kasir;

use App\Actions\Shift\CalculateExpectedCashAction;
use App\Actions\Shift\CloseShiftAction;
use App\Actions\Shift\OpenShiftAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Kasir\StoreCloseShiftRequest;
use App\Http\Requests\Kasir\StoreOpenShiftRequest;
use App\Http\Resources\ShiftResource;
use App\Models\Shift;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

// Alur shift kasir: buka (modal awal kas) → transaksi → tutup (rekap kas
// sistem vs fisik). Lihat docs/PRD.md §6 & docs/UI.md "Alur Buka Shift".
class ShiftController extends Controller
{
    public function buka(Request $request): RedirectResponse|Response
    {
        // Sudah punya shift aktif → langsung ke halaman transaksi, jangan
        // tampilkan form buka shift lagi (juga mencegah loop halaman).
        if ($this->activeShift($request)) {
            return redirect()->route('kasir.transaksi');
        }

        return Inertia::render('Kasir/Shift/Buka');
    }

    public function store(StoreOpenShiftRequest $request, OpenShiftAction $openShift): RedirectResponse
    {
        $openShift->handle(
            ['opening_cash' => (int) $request->validated()['opening_cash']],
            $request->user()
        );

        return redirect()
            ->route('kasir.transaksi')
            ->with('success', 'Shift berhasil dibuka. Selamat bertugas!');
    }

    public function tutup(Request $request, CalculateExpectedCashAction $calculateExpectedCash): RedirectResponse|Response
    {
        $shift = $this->activeShift($request);

        if (! $shift) {
            return redirect()->route('kasir.shift.buka');
        }

        return Inertia::render('Kasir/Shift/Tutup', [
            // resolve() dipakai supaya props flat — Inertia me-resolve
            // JsonResource lewat jalur Responsable yang membungkusnya jadi
            // {data: {...}} (frontend mengharapkan shift.opening_cash
            // langsung). Lihat docs/DECISIONS.md.
            'shift' => (new ShiftResource($shift))->resolve(),
            // Preview rekap sebelum kasir input kas fisik. Nilai final
            // dihitung ulang oleh CloseShiftAction saat submit.
            'expectedCash' => $calculateExpectedCash->handle($shift),
        ]);
    }

    public function close(StoreCloseShiftRequest $request, CloseShiftAction $closeShift): RedirectResponse
    {
        $shift = $this->activeShift($request);

        if (! $shift) {
            return redirect()->route('kasir.shift.buka');
        }

        $closeShift->handle($shift, (int) $request->validated()['closing_cash']);

        return redirect()
            ->route('kasir.shift.riwayat')
            ->with('success', 'Shift berhasil ditutup. Periksa rekap kas Anda.');
    }

    public function riwayat(Request $request): Response
    {
        $shifts = Shift::query()
            ->where('user_id', $request->user()->id)
            ->orderByDesc('opened_at')
            ->paginate($this->perPage($request))
            ->withQueryString();

        return Inertia::render('Kasir/Shift/Riwayat', [
            // through() mempertahankan shape paginator standar Laravel
            // supaya cocok dengan Paginated<T> di frontend — jangan pakai
            // ShiftResource::collection(). Lihat docs/DECISIONS.md.
            'shifts' => $shifts->through(fn (Shift $shift) => (new ShiftResource($shift))->resolve()),
        ]);
    }

    private function activeShift(Request $request): ?Shift
    {
        return Shift::query()
            ->where('user_id', $request->user()->id)
            ->where('status', Shift::STATUS_OPEN)
            ->first();
    }
}
