<?php

use App\Models\Shift;
use App\Models\Transaction;
use App\Models\User;

test('kasir can open a shift with opening cash', function () {
    $kasir = User::factory()->kasir()->create();

    $response = $this->actingAs($kasir)->post(route('kasir.shift.store'), [
        'opening_cash' => 150000,
    ]);

    $response->assertRedirect(route('kasir.transaksi'));
    $response->assertSessionHas('success');

    $shift = Shift::where('user_id', $kasir->id)->firstOrFail();

    expect($shift->status)->toBe(Shift::STATUS_OPEN)
        ->and($shift->opening_cash)->toBe(150000)
        ->and($shift->opened_at)->not->toBeNull();
});

test('kasir without shift sees the buka shift page', function () {
    $kasir = User::factory()->kasir()->create();

    $response = $this->actingAs($kasir)->get(route('kasir.shift.buka'));

    $response->assertInertia(
        fn ($page) => $page
            ->component('Kasir/Shift/Buka')
            ->where('shiftIsActive', false)
    );
});

test('kasir with active shift is redirected away from the buka shift page', function () {
    $kasir = User::factory()->kasir()->create();
    Shift::factory()->create(['user_id' => $kasir->id]);

    $response = $this->actingAs($kasir)->get(route('kasir.shift.buka'));

    $response->assertRedirect(route('kasir.transaksi'));
});

test('opening cash is required and must be non-negative', function () {
    $kasir = User::factory()->kasir()->create();

    $this->actingAs($kasir)
        ->post(route('kasir.shift.store'), [])
        ->assertSessionHasErrors('opening_cash');

    $this->actingAs($kasir)
        ->post(route('kasir.shift.store'), ['opening_cash' => -1000])
        ->assertSessionHasErrors('opening_cash');
});

// PRD §6: satu kasir hanya bisa punya satu shift aktif — divalidasi di
// OpenShiftAction (bukan DB constraint, lihat docs/DATABASE.md).
test('kasir cannot open a second shift while one is still open', function () {
    $kasir = User::factory()->kasir()->create();
    Shift::factory()->create(['user_id' => $kasir->id]);

    $response = $this->actingAs($kasir)->post(route('kasir.shift.store'), [
        'opening_cash' => 50000,
    ]);

    $response->assertSessionHasErrors('opening_cash');
    expect(Shift::where('user_id', $kasir->id)->count())->toBe(1);
});

test('kasir can view the close shift rekap with expected cash preview', function () {
    $kasir = User::factory()->kasir()->create();
    $shift = Shift::factory()->create(['user_id' => $kasir->id, 'opening_cash' => 100000]);

    Transaction::create([
        'transaction_number' => 'TRX-20261002-0001',
        'user_id' => $kasir->id,
        'shift_id' => $shift->id,
        'subtotal' => 50000,
        'total' => 50000,
        'paid_amount' => 50000,
        'change_amount' => 0,
        'payment_method' => Transaction::PAYMENT_CASH,
        'status' => Transaction::STATUS_COMPLETED,
    ]);

    $response = $this->actingAs($kasir)->get(route('kasir.shift.tutup'));

    $response->assertInertia(
        fn ($page) => $page
            ->component('Kasir/Shift/Tutup')
            ->where('shiftIsActive', true)
            ->where('shift.opening_cash', 100000)
            ->where('expectedCash', 150000)
    );
});

test('kasir without active shift cannot open the close shift page', function () {
    $kasir = User::factory()->kasir()->create();

    $response = $this->actingAs($kasir)->get(route('kasir.shift.tutup'));

    $response->assertRedirect(route('kasir.shift.buka'));
});

// expected_cash = opening_cash + penjualan tunai 'completed' selama shift;
// transfer & transaksi voided tidak dihitung — lihat
// app/Actions/Shift/CalculateExpectedCashAction.php.
test('kasir can close a shift and expected cash counts only completed cash sales', function () {
    $kasir = User::factory()->kasir()->create();
    $shift = Shift::factory()->create(['user_id' => $kasir->id, 'opening_cash' => 100000]);

    Transaction::create([
        'transaction_number' => 'TRX-20261002-0001',
        'user_id' => $kasir->id,
        'shift_id' => $shift->id,
        'subtotal' => 50000,
        'total' => 50000,
        'paid_amount' => 50000,
        'change_amount' => 0,
        'payment_method' => Transaction::PAYMENT_CASH,
        'status' => Transaction::STATUS_COMPLETED,
    ]);
    Transaction::create([
        'transaction_number' => 'TRX-20261002-0002',
        'user_id' => $kasir->id,
        'shift_id' => $shift->id,
        'subtotal' => 30000,
        'total' => 30000,
        'paid_amount' => 30000,
        'change_amount' => 0,
        'payment_method' => Transaction::PAYMENT_TRANSFER,
        'status' => Transaction::STATUS_COMPLETED,
    ]);
    Transaction::create([
        'transaction_number' => 'TRX-20261002-0003',
        'user_id' => $kasir->id,
        'shift_id' => $shift->id,
        'subtotal' => 20000,
        'total' => 20000,
        'paid_amount' => 20000,
        'change_amount' => 0,
        'payment_method' => Transaction::PAYMENT_CASH,
        'status' => Transaction::STATUS_VOIDED,
    ]);

    $response = $this->actingAs($kasir)->post(route('kasir.shift.close'), [
        'closing_cash' => 145000,
    ]);

    $response->assertRedirect(route('kasir.shift.riwayat'));
    $response->assertSessionHas('success');

    $shift->refresh();

    expect($shift->status)->toBe(Shift::STATUS_CLOSED)
        ->and($shift->closing_cash)->toBe(145000)
        ->and($shift->expected_cash)->toBe(150000)
        ->and($shift->closed_at)->not->toBeNull()
        ->and($shift->cashDifference())->toBe(-5000);
});

test('closing cash is required to close a shift', function () {
    $kasir = User::factory()->kasir()->create();
    $shift = Shift::factory()->create(['user_id' => $kasir->id]);

    $response = $this->actingAs($kasir)->post(route('kasir.shift.close'), []);

    $response->assertSessionHasErrors('closing_cash');
    expect($shift->fresh()->status)->toBe(Shift::STATUS_OPEN);
});

test('kasir without active shift cannot close a shift', function () {
    $kasir = User::factory()->kasir()->create();

    $response = $this->actingAs($kasir)->post(route('kasir.shift.close'), [
        'closing_cash' => 100000,
    ]);

    $response->assertRedirect(route('kasir.shift.buka'));
    expect(Shift::where('user_id', $kasir->id)->count())->toBe(0);
});

// Shape paginator wajib flat (bukan dibungkus ResourceCollection) —
// lihat docs/DECISIONS.md.
test('kasir shift history returns flat paginator shape with only own shifts', function () {
    $kasir = User::factory()->kasir()->create();
    $otherKasir = User::factory()->kasir()->create();
    Shift::factory()->count(2)->create(['user_id' => $kasir->id]);
    Shift::factory()->create(['user_id' => $otherKasir->id]);

    $response = $this->actingAs($kasir)->get(route('kasir.shift.riwayat'));

    $response->assertInertia(
        fn ($page) => $page
            ->component('Kasir/Shift/Riwayat')
            ->has('shifts.data', 2)
            ->has('shifts.current_page')
            ->has('shifts.last_page')
            ->has('shifts.links')
            ->missing('shifts.data.data')
    );
});

test('kasir shift history renders store timezone timestamps', function () {
    $kasir = User::factory()->kasir()->create();
    $shift = Shift::factory()->create(['user_id' => $kasir->id]);

    $response = $this->actingAs($kasir)->get(route('kasir.shift.riwayat'));

    // store_settings.timezone default 'Asia/Jakarta' (UTC+7) — konversi
    // terjadi di ShiftResource, bukan di query (docs/DECISIONS.md).
    $expected = $shift->opened_at->copy()->timezone('Asia/Jakarta')->format('Y-m-d H:i');

    $response->assertInertia(
        fn ($page) => $page->where('shifts.data.0.opened_at', $expected)
    );
});
