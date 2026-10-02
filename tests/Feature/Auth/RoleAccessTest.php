<?php

use App\Models\Shift;
use App\Models\User;

// Acceptance criteria docs/features/auth-login.md: user yang belum login
// tidak bisa akses route /admin/* atau /kasir/* manapun.
test('guest cannot access admin routes', function () {
    $response = $this->get(route('admin.dashboard'));

    $response->assertRedirect(route('login'));
});

test('guest cannot access kasir routes', function () {
    $response = $this->get(route('kasir.transaksi'));

    $response->assertRedirect(route('login'));
});

// role:admin / role:kasir middleware — lihat AGENTS.md pemisahan route
// per role dan app/Http/Middleware/EnsureUserHasRole.php.
test('kasir cannot access admin routes', function () {
    $user = User::factory()->kasir()->create();

    $response = $this->actingAs($user)->get(route('admin.dashboard'));

    $response->assertForbidden();
});

test('admin cannot access kasir routes', function () {
    $user = User::factory()->admin()->create();

    $response = $this->actingAs($user)->get(route('kasir.transaksi'));

    $response->assertForbidden();
});

test('admin can access admin dashboard', function () {
    $user = User::factory()->admin()->create();

    $response = $this->actingAs($user)->get(route('admin.dashboard'));

    $response->assertOk();
});

// shift.active middleware — lihat app/Http/Middleware/EnsureShiftActive.php.
test('kasir without active shift is redirected to buka shift page', function () {
    $user = User::factory()->kasir()->create();

    $response = $this->actingAs($user)->get(route('kasir.transaksi'));

    $response->assertRedirect(route('kasir.shift.buka'));
});

test('kasir with active shift can access transaksi page', function () {
    $user = User::factory()->kasir()->create();

    Shift::create([
        'user_id' => $user->id,
        'opening_cash' => 100000,
        'status' => Shift::STATUS_OPEN,
        'opened_at' => now(),
    ]);

    $response = $this->actingAs($user)->get(route('kasir.transaksi'));

    $response->assertOk();
});

test('kasir without active shift can still access buka shift page', function () {
    $user = User::factory()->kasir()->create();

    $response = $this->actingAs($user)->get(route('kasir.shift.buka'));

    $response->assertOk();
});
