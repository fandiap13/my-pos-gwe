<?php

use App\Models\User;

test('category required message is in Indonesian', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->post(route('admin.categories.store'), [
        'name' => '',
    ]);

    $response->assertSessionHasErrors(['name' => 'Nama kategori wajib diisi.']);
});

test('product price message is in Indonesian', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->post(route('admin.products.store'), [
        'name' => 'Kopi Sachet',
        'price' => -1,
        'initial_stock' => 10,
        'min_stock' => 2,
    ]);

    $response->assertSessionHasErrors(['price' => 'Harga jual tidak boleh negatif.']);
});

test('login failure message is in Indonesian', function () {
    User::factory()->create();

    $response = $this->post('/login', [
        'email' => 'nobody@example.com',
        'password' => 'wrong-password',
    ]);

    $response->assertSessionHasErrors(['email' => 'Kredensial tidak cocok dengan data kami.']);
});
