<?php

use App\Models\Product;
use App\Models\Shift;
use App\Models\StockMovement;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

// Setup umum: kasir dengan shift aktif + produk harga Rp 10.000 stok 10.
function checkoutSetup(array $productAttributes = []): array
{
    $kasir = User::factory()->kasir()->create();
    $shift = Shift::factory()->create(['user_id' => $kasir->id]);
    $product = Product::factory()->create(array_merge([
        'price' => 10000,
        'stock' => 10,
        'min_stock' => 2,
    ], $productAttributes));

    // products.stock adalah cache agregat dari stock_movements (AGENTS.md).
    // Factory mengisi kolom stok langsung tanpa riwayat — pasang stok awal
    // berupa movement 'in' supaya konsisten, kalau tidak observer akan
    // menghitung ulang stok jadi negatif saat transaksi pertama.
    StockMovement::create([
        'product_id' => $product->id,
        'type' => StockMovement::TYPE_IN,
        'quantity' => $product->stock,
        'created_by' => $kasir->id,
    ]);

    return [$kasir, $shift, $product];
}

test('halaman transaksi menampilkan produk tanpa harga modal', function () {
    [$kasir, , $product] = checkoutSetup();

    $response = $this->actingAs($kasir)->get(route('kasir.transaksi'));

    $response->assertInertia(
        fn (Assert $page) => $page
            ->component('Kasir/Transaksi/Index')
            ->has('products', 1)
            ->where('products.0.id', $product->id)
            // cost_price sengaja tidak dipilih dari DB — kasir tidak
            // boleh melihat harga modal (lihat TransactionController).
            ->where('products.0.cost_price', null)
    );
});

test('kasir can checkout and everything is recorded in one transaction', function () {
    [$kasir, $shift, $product] = checkoutSetup();

    $response = $this->actingAs($kasir)->post(route('kasir.transaksi.store'), [
        'items' => [
            ['product_id' => $product->id, 'quantity' => 2, 'price' => 10000],
        ],
        'payment_method' => Transaction::PAYMENT_CASH,
        'paid_amount' => 25000,
    ]);

    $transaction = Transaction::firstOrFail();

    $response->assertRedirect(route('kasir.transaksi.selesai', $transaction));
    $response->assertSessionHas('success');

    expect($transaction->transaction_number)->toMatch('/^TRX-\d{8}-\d{4}$/')
        ->and($transaction->status)->toBe(Transaction::STATUS_COMPLETED)
        ->and($transaction->user_id)->toBe($kasir->id)
        ->and($transaction->shift_id)->toBe($shift->id)
        ->and($transaction->subtotal)->toBe(20000)
        ->and($transaction->total)->toBe(20000)
        ->and($transaction->paid_amount)->toBe(25000)
        ->and($transaction->change_amount)->toBe(5000)
        ->and($transaction->payment_method)->toBe(Transaction::PAYMENT_CASH);

    $item = $transaction->items()->firstOrFail();

    expect($item->product_id)->toBe($product->id)
        ->and($item->product_name)->toBe($product->name)
        ->and($item->price)->toBe(10000)
        ->and($item->quantity)->toBe(2)
        ->and($item->subtotal)->toBe(20000);

    $movement = StockMovement::where('type', StockMovement::TYPE_OUT)
        ->firstOrFail();

    expect($movement->type)->toBe(StockMovement::TYPE_OUT)
        ->and($movement->product_id)->toBe($product->id)
        ->and($movement->quantity)->toBe(2)
        ->and($movement->reference_type)->toBe(TransactionItem::class)
        ->and($movement->reference_id)->toBe($item->id)
        ->and($movement->created_by)->toBe($kasir->id);

    // Cache products.stock ikut berkurang lewat StockMovementObserver.
    expect($product->refresh()->stock)->toBe(8);
});

test('checkout is rejected when stock is insufficient and nothing is inserted', function () {
    [$kasir, , $product] = checkoutSetup(['stock' => 1]);

    $response = $this->actingAs($kasir)->post(route('kasir.transaksi.store'), [
        'items' => [
            ['product_id' => $product->id, 'quantity' => 3, 'price' => 10000],
        ],
        'payment_method' => Transaction::PAYMENT_CASH,
        'paid_amount' => 30000,
    ]);

    $response->assertSessionHasErrors('checkout');

    expect(Transaction::count())->toBe(0)
        ->and(StockMovement::where('type', StockMovement::TYPE_OUT)->count())->toBe(0)
        ->and($product->refresh()->stock)->toBe(1);
});

test('checkout is rejected when the cart price no longer matches the product', function () {
    [$kasir, , $product] = checkoutSetup();

    $response = $this->actingAs($kasir)->post(route('kasir.transaksi.store'), [
        'items' => [
            ['product_id' => $product->id, 'quantity' => 1, 'price' => 9000],
        ],
        'payment_method' => Transaction::PAYMENT_CASH,
        'paid_amount' => 9000,
    ]);

    $response->assertSessionHasErrors('checkout');

    expect(Transaction::count())->toBe(0)
        ->and(StockMovement::where('type', StockMovement::TYPE_OUT)->count())->toBe(0);
});

test('checkout is rejected when cash paid is less than total', function () {
    [$kasir, , $product] = checkoutSetup();

    $response = $this->actingAs($kasir)->post(route('kasir.transaksi.store'), [
        'items' => [
            ['product_id' => $product->id, 'quantity' => 2, 'price' => 10000],
        ],
        'payment_method' => Transaction::PAYMENT_CASH,
        'paid_amount' => 15000,
    ]);

    $response->assertSessionHasErrors('checkout');

    expect(Transaction::count())->toBe(0);
});

test('kasir without active shift cannot checkout', function () {
    $kasir = User::factory()->kasir()->create();
    $product = Product::factory()->create(['price' => 10000, 'stock' => 10]);

    $response = $this->actingAs($kasir)->post(route('kasir.transaksi.store'), [
        'items' => [
            ['product_id' => $product->id, 'quantity' => 1, 'price' => 10000],
        ],
        'payment_method' => Transaction::PAYMENT_CASH,
        'paid_amount' => 10000,
    ]);

    $response->assertRedirect(route('kasir.shift.buka'));

    expect(Transaction::count())->toBe(0);
});

test('transfer and debit use the total as paid amount with no change', function () {
    [$kasir, , $product] = checkoutSetup();

    $this->actingAs($kasir)->post(route('kasir.transaksi.store'), [
        'items' => [
            ['product_id' => $product->id, 'quantity' => 2, 'price' => 10000],
        ],
        'payment_method' => Transaction::PAYMENT_TRANSFER,
        // Nominal yang dikirim client diabaikan untuk non-cash.
        'paid_amount' => 999999,
    ])->assertSessionHasNoErrors();

    $transaction = Transaction::firstOrFail();

    expect($transaction->paid_amount)->toBe(20000)
        ->and($transaction->change_amount)->toBe(0)
        ->and($transaction->payment_method)->toBe(Transaction::PAYMENT_TRANSFER);
});

test('transaction numbers increment sequentially per day', function () {
    [$kasir, , $product] = checkoutSetup();

    $payload = [
        'items' => [
            ['product_id' => $product->id, 'quantity' => 1, 'price' => 10000],
        ],
        'payment_method' => Transaction::PAYMENT_CASH,
        'paid_amount' => 10000,
    ];

    $this->actingAs($kasir)->post(route('kasir.transaksi.store'), $payload);
    $this->actingAs($kasir)->post(route('kasir.transaksi.store'), $payload);

    $numbers = Transaction::query()
        ->orderBy('created_at')
        ->pluck('transaction_number')
        ->all();

    expect($numbers)->toHaveCount(2)
        ->and($numbers[0])->toMatch('/^TRX-\d{8}-0001$/')
        ->and($numbers[1])->toMatch('/^TRX-\d{8}-0002$/')
        ->and($numbers[0])->not->toBe($numbers[1]);
});

test('item name and price snapshots do not change when the product is edited', function () {
    [$kasir, , $product] = checkoutSetup();

    $this->actingAs($kasir)->post(route('kasir.transaksi.store'), [
        'items' => [
            ['product_id' => $product->id, 'quantity' => 1, 'price' => 10000],
        ],
        'payment_method' => Transaction::PAYMENT_CASH,
        'paid_amount' => 10000,
    ]);

    $originalName = $product->name;
    $product->update(['name' => 'Nama Baru', 'price' => 99999]);

    $item = TransactionItem::firstOrFail();

    expect($item->product_name)->toBe($originalName)
        ->and($item->product_name)->not->toBe('Nama Baru')
        ->and($item->price)->toBe(10000);
});

test('kasir can only open their own receipt', function () {
    [$kasir, , $product] = checkoutSetup();

    $this->actingAs($kasir)->post(route('kasir.transaksi.store'), [
        'items' => [
            ['product_id' => $product->id, 'quantity' => 1, 'price' => 10000],
        ],
        'payment_method' => Transaction::PAYMENT_CASH,
        'paid_amount' => 10000,
    ]);

    $transaction = Transaction::firstOrFail();
    $otherKasir = User::factory()->kasir()->create();

    $this->actingAs($otherKasir)
        ->get(route('kasir.transaksi.selesai', $transaction))
        ->assertForbidden();

    $response = $this->actingAs($kasir)
        ->get(route('kasir.transaksi.selesai', $transaction));

    $response->assertOk();
    $response->assertInertia(
        fn (Assert $page) => $page
            ->component('Kasir/Transaksi/Selesai')
            ->where('receipt.transaction_number', $transaction->transaction_number)
            ->where('receipt.total', 10000)
            ->where('receipt.change_amount', 0)
            ->where('receipt.payment_method', Transaction::PAYMENT_CASH)
            ->has('receipt.items', 1)
            ->where('receipt.items.0.product_name', $product->name)
    );
});

test('deleted products are rejected by validation', function () {
    [$kasir, , $product] = checkoutSetup();
    $product->delete();

    $response = $this->actingAs($kasir)->post(route('kasir.transaksi.store'), [
        'items' => [
            ['product_id' => $product->id, 'quantity' => 1, 'price' => 10000],
        ],
        'payment_method' => Transaction::PAYMENT_CASH,
        'paid_amount' => 10000,
    ]);

    $response->assertSessionHasErrors('items.0.product_id');

    expect(Transaction::count())->toBe(0);
});
