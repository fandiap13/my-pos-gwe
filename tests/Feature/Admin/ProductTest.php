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

// Memverifikasi SHAPE data Inertia props, bukan cuma status 200 — bug
// sebelumnya (products dibungkus ganda jadi products.data.data di
// frontend, padahal controller kirim products.data) lolos dari test
// assertOk() biasa tapi membuat halaman blank di browser. Lihat
// docs/DECISIONS.md.
test('product index returns flat paginator shape matching frontend Paginated<T>', function () {
    $admin = admin();
    Product::factory()->count(3)->create();

    $response = $this->actingAs($admin)->get(route('admin.products.index'));

    $response->assertInertia(
        fn ($page) => $page
            ->component('Admin/Products/Index')
            ->has('products.data', 3)
            ->has('products.current_page')
            ->has('products.last_page')
            ->has('products.total')
            ->has('products.links')
            ->has('products.data.0.stock_status')
            ->missing('products.data.data')
    );
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

// Regression: resource yang dilempar mentah ke Inertia dibungkus jadi
// {data: {...}} oleh jalur Responsable (bukan lewat ->resolve()), sehingga
// halaman Edit membaca props.product.name = undefined. Lihat
// docs/DECISIONS.md.
test('product edit page receives flat product props', function () {
    $admin = admin();
    $product = Product::factory()->create(['name' => 'Kopi Susu']);

    $response = $this->actingAs($admin)->get(route('admin.products.edit', $product));

    $response->assertInertia(
        fn ($page) => $page
            ->component('Admin/Products/Edit')
            ->where('product.name', 'Kopi Susu')
            ->missing('product.data')
    );
});
