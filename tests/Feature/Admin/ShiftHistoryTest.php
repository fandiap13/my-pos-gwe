<?php

use App\Models\Shift;
use App\Models\User;

test('admin can view all shifts from every cashier', function () {
    $admin = User::factory()->admin()->create();
    $kasirA = User::factory()->kasir()->create();
    $kasirB = User::factory()->kasir()->create();
    Shift::factory()->create(['user_id' => $kasirA->id]);
    Shift::factory()->closed()->create(['user_id' => $kasirB->id]);

    $response = $this->actingAs($admin)->get(route('admin.shifts.index'));

    // Shape paginator wajib flat — lihat docs/DECISIONS.md.
    $response->assertInertia(
        fn ($page) => $page
            ->component('Admin/Shifts/Index')
            ->has('shifts.data', 2)
            ->has('shifts.current_page')
            ->has('shifts.links')
            ->has('shifts.data.0.user_name')
            ->missing('shifts.data.data')
    );
});

test('admin shift history can be filtered by status', function () {
    $admin = User::factory()->admin()->create();
    $kasir = User::factory()->kasir()->create();
    Shift::factory()->create(['user_id' => $kasir->id]);
    Shift::factory()->closed()->create(['user_id' => $kasir->id]);

    $response = $this->actingAs($admin)->get(route('admin.shifts.index', [
        'status' => Shift::STATUS_CLOSED,
    ]));

    $response->assertInertia(
        fn ($page) => $page
            ->has('shifts.data', 1)
            ->where('shifts.data.0.status', Shift::STATUS_CLOSED)
            ->where('filters.status', Shift::STATUS_CLOSED)
    );
});

test('admin shift history can be filtered by cashier name', function () {
    $admin = User::factory()->admin()->create();
    $target = User::factory()->kasir()->create(['name' => 'Siti Aminah']);
    $other = User::factory()->kasir()->create(['name' => 'Budi Santoso']);
    Shift::factory()->create(['user_id' => $target->id]);
    Shift::factory()->create(['user_id' => $other->id]);

    $response = $this->actingAs($admin)->get(route('admin.shifts.index', [
        'search' => 'Siti',
    ]));

    $response->assertInertia(
        fn ($page) => $page
            ->has('shifts.data', 1)
            ->where('shifts.data.0.user_name', 'Siti Aminah')
    );
});

test('kasir cannot access admin shift history', function () {
    $kasir = User::factory()->kasir()->create();

    $response = $this->actingAs($kasir)->get(route('admin.shifts.index'));

    $response->assertForbidden();
});
