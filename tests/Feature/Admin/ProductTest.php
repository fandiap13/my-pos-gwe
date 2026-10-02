<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;

function admin(): User
{
    return User::factory()->admin()->create();
}

test('admin can view product list', function () {
    $admin = admin();
    Product::factory()->create(['name' => 'Kopi Susu']);

    $response = $this->actingAs($admin)->get(route('admin.products.index'));

    $response->assertOk();
});

test('admin can create a product with initial stock recorded as stock movement', function () {
    $admin = admin();
    $category = Category::factory()->create();

    $response = $this->actingAs($admin)->post(route('admin.products.store'), [
        'category_id' => $category->id,
        'name' => 'Teh Botol',
        'sku' => 'SKU-TB01',
        'barcode' => null,
        'price' => 5000,
        'cost_price' => 3000,
        'initial_stock' => 20,
        'min_stock' => 5,
    ]);

    $response->assertRedirect(route('admin.products.index'));

    $product = Product::where('sku', 'SKU-TB01')->firstOrFail();

    expect($product->stock)->toBe(20);

    $this->assertDatabaseHas('stock_movements', [
        'product_id' => $product->id,
        'type' => StockMovement::TYPE_IN,
        'quantity' => 20,
    ]);
});

test('product with zero initial stock does not create a stock movement', function () {
    $admin = admin();

    $this->actingAs($admin)->post(route('admin.products.store'), [
        'name' => 'Produk Kosong',
        'price' => 1000,
        'initial_stock' => 0,
        'min_stock' => 0,
    ]);

    $product = Product::where('name', 'Produk Kosong')->firstOrFail();

    expect($product->stock)->toBe(0);
    $this->assertDatabaseMissing('stock_movements', ['product_id' => $product->id]);
});

test('product name and price are required', function () {
    $admin = admin();

    $response = $this->actingAs($admin)->post(route('admin.products.store'), [
        'initial_stock' => 0,
        'min_stock' => 0,
    ]);

    $response->assertSessionHasErrors(['name', 'price']);
});

test('duplicate sku is rejected', function () {
    $admin = admin();
    Product::factory()->create(['sku' => 'SKU-DUP']);

    $response = $this->actingAs($admin)->post(route('admin.products.store'), [
        'name' => 'Produk Lain',
        'sku' => 'SKU-DUP',
        'price' => 1000,
        'initial_stock' => 0,
        'min_stock' => 0,
    ]);

    $response->assertSessionHasErrors('sku');
});

test('duplicate barcode is rejected', function () {
    $admin = admin();
    Product::factory()->create(['barcode' => '1234567890123']);

    $response = $this->actingAs($admin)->post(route('admin.products.store'), [
        'name' => 'Produk Lain',
        'barcode' => '1234567890123',
        'price' => 1000,
        'initial_stock' => 0,
        'min_stock' => 0,
    ]);

    $response->assertSessionHasErrors('barcode');
});

test('admin can update a product without touching stock', function () {
    $admin = admin();
    $product = Product::factory()->create(['name' => 'Lama', 'stock' => 10]);

    $response = $this->actingAs($admin)->put(route('admin.products.update', $product), [
        'name' => 'Baru',
        'price' => $product->price,
        'min_stock' => $product->min_stock,
    ]);

    $response->assertRedirect(route('admin.products.index'));
    $product->refresh();

    expect($product->name)->toBe('Baru');
    expect($product->stock)->toBe(10);
});

test('updating sku to another products sku is rejected', function () {
    $admin = admin();
    Product::factory()->create(['sku' => 'SKU-A']);
    $productB = Product::factory()->create(['sku' => 'SKU-B']);

    $response = $this->actingAs($admin)->put(route('admin.products.update', $productB), [
        'name' => $productB->name,
        'sku' => 'SKU-A',
        'price' => $productB->price,
        'min_stock' => $productB->min_stock,
    ]);

    $response->assertSessionHasErrors('sku');
});

test('updating a product keeping its own sku is allowed', function () {
    $admin = admin();
    $product = Product::factory()->create(['sku' => 'SKU-KEEP']);

    $response = $this->actingAs($admin)->put(route('admin.products.update', $product), [
        'name' => $product->name,
        'sku' => 'SKU-KEEP',
        'price' => $product->price,
        'min_stock' => $product->min_stock,
    ]);

    $response->assertRedirect(route('admin.products.index'));
    $response->assertSessionDoesntHaveErrors();
});

test('admin can delete a product', function () {
    $admin = admin();
    $product = Product::factory()->create();

    $response = $this->actingAs($admin)->delete(route('admin.products.destroy', $product));

    $response->assertRedirect(route('admin.products.index'));
    $this->assertSoftDeleted($product);
});

test('product list can be filtered by search', function () {
    $admin = admin();
    Product::factory()->create(['name' => 'Kopi Hitam']);
    Product::factory()->create(['name' => 'Teh Manis']);

    $response = $this->actingAs($admin)->get(route('admin.products.index', ['search' => 'Kopi']));

    $response->assertOk();
});

test('product list can be filtered by category', function () {
    $admin = admin();
    $category = Category::factory()->create();
    Product::factory()->create(['category_id' => $category->id]);

    $response = $this->actingAs($admin)->get(route('admin.products.index', ['category_id' => $category->id]));

    $response->assertOk();
});

test('kasir cannot access product routes', function () {
    $kasir = User::factory()->kasir()->create();

    $response = $this->actingAs($kasir)->get(route('admin.products.index'));

    $response->assertForbidden();
});
