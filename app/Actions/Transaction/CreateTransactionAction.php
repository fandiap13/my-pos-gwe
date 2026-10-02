<?php

namespace App\Actions\Transaction;

use App\Models\Product;
use App\Models\Shift;
use App\Models\StockMovement;
use App\Models\StoreSetting;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Membuat transaksi checkout: insert transactions + transaction_items +
 * stock_movements type 'out' dalam satu DB::transaction() — seluruh
 * perubahan berhasil atau semua gagal (rollback). Cache products.stock
 * ikut ter-update lewat StockMovementObserver.
 * Lihat docs/features/checkout.md.
 */
class CreateTransactionAction
{
    /**
     * @param  array<string, mixed>  $data  hasil validasi StoreTransactionRequest
     */
    public function handle(array $data, User $kasir): Transaction
    {
        return DB::transaction(function () use ($data, $kasir) {
            $shift = $this->lockedActiveShift($kasir);
            // Row lock di tabel single-row store_settings dipakai untuk
            // dua hal sekaligus: zona waktu hari transaksi, dan
            // serialisasi generate transaction_number antar kasir
            // (lihat docs/DECISIONS.md) — menghindari race nomor urut
            // tanpa menambah tabel counter baru.
            $settings = $this->lockedStoreSettings();
            $products = $this->lockedProducts($data['items']);

            $this->validateItems($data['items'], $products);

            [$subtotal, $total] = $this->calculateTotals($data['items'], $products);
            [$paidAmount, $changeAmount] = $this->resolvePayment($data, $total);

            $transaction = Transaction::create([
                'transaction_number' => $this->generateTransactionNumber($settings),
                'user_id' => $kasir->id,
                'shift_id' => $shift->id,
                'subtotal' => $subtotal,
                'total' => $total,
                'paid_amount' => $paidAmount,
                'change_amount' => $changeAmount,
                'payment_method' => $data['payment_method'],
                'status' => Transaction::STATUS_COMPLETED,
            ]);

            $this->insertItems($transaction, $data['items'], $products, $kasir);

            return $transaction->load('items');
        });
    }

    private function lockedActiveShift(User $kasir): Shift
    {
        $shift = Shift::query()
            ->where('user_id', $kasir->id)
            ->where('status', Shift::STATUS_OPEN)
            ->lockForUpdate()
            ->first();

        // Kasus tepi: shift ditutup di tengah checkout — tolak, jangan
        // simpan transaksi ke shift yang sudah closed (checkout.md).
        if (! $shift) {
            throw ValidationException::withMessages([
                'checkout' => 'Shift sudah ditutup. Buka shift baru untuk melanjutkan transaksi.',
            ]);
        }

        return $shift;
    }

    private function lockedStoreSettings(): StoreSetting
    {
        return StoreSetting::query()->lockForUpdate()->firstOrFail();
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @return Collection<string, Product>
     */
    private function lockedProducts(array $items): Collection
    {
        $productIds = collect($items)->pluck('product_id')->unique()->values();

        return Product::query()
            ->whereIn('id', $productIds)
            ->lockForUpdate()
            ->get()
            ->keyBy('id');
    }

    /**
     * Validasi bisnis per item: produk masih ada dan stok cukup,
     * harga tidak berubah sejak kasir memasukkan ke keranjang.
     * Pesan menyebut produk yang bermasalah (checkout.md langkah 9a).
     *
     * @param  array<int, array<string, mixed>>  $items
     * @param  Collection<string, Product>  $products
     */
    private function validateItems(array $items, Collection $products): void
    {
        foreach ($items as $item) {
            $product = $products->get($item['product_id']);

            if (! $product) {
                throw ValidationException::withMessages([
                    'checkout' => 'Ada produk yang sudah dihapus. Muat ulang halaman dan periksa kembali keranjang.',
                ]);
            }

            $quantity = (int) $item['quantity'];

            if ($product->stock < $quantity) {
                throw ValidationException::withMessages([
                    'checkout' => sprintf(
                        'Stok %s tidak cukup (tersisa %d, di keranjang %d). Kurangi quantity atau muat ulang halaman.',
                        $product->name,
                        $product->stock,
                        $quantity,
                    ),
                ]);
            }

            if ($product->price !== (int) $item['price']) {
                throw ValidationException::withMessages([
                    'checkout' => sprintf(
                        'Harga %s berubah (Rp %s). Muat ulang halaman sebelum membayar.',
                        $product->name,
                        number_format($product->price, 0, ',', '.'),
                    ),
                ]);
            }
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @param  Collection<string, Product>  $products
     * @return array{0: int, 1: int}
     */
    private function calculateTotals(array $items, Collection $products): array
    {
        $subtotal = 0;

        foreach ($items as $item) {
            $product = $products->get($item['product_id']);
            $subtotal += $product->price * (int) $item['quantity'];
        }

        // Belum ada diskon/pajak (PRD §6 "perlu diisi") — subtotal = total.
        return [$subtotal, $subtotal];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{0: int, 1: int}
     */
    private function resolvePayment(array $data, int $total): array
    {
        if ($data['payment_method'] !== Transaction::PAYMENT_CASH) {
            // Transfer/debit: nominal dibayar = total, tanpa kembalian
            // (checkout.md Aturan Bisnis — asumsi dikonfirmasi di PRD §3).
            return [$total, 0];
        }

        $paidAmount = (int) ($data['paid_amount'] ?? 0);

        if ($paidAmount < $total) {
            throw ValidationException::withMessages([
                'checkout' => sprintf(
                    'Nominal dibayar kurang dari total (dibayar Rp %s, total Rp %s).',
                    number_format($paidAmount, 0, ',', '.'),
                    number_format($total, 0, ',', '.'),
                ),
            ]);
        }

        return [$paidAmount, $paidAmount - $total];
    }

    /**
     * Format TRX-YYYYMMDD-XXXX, sekuensial per hari toko. Panggil hanya
     * setelah row store_settings terkunci — lock itulah yang membuat
     * generate nomor aman dari race condition antar kasir
     * (lihat docs/DECISIONS.md). Hari transaksi memakai zona waktu
     * store_settings, bukan UTC, supaya tanggal di nomor cocok dengan
     * tanggal yang terlihat di struk.
     */
    private function generateTransactionNumber(StoreSetting $settings): string
    {
        $now = Carbon::now($settings->timezone);
        $dayStart = $now->copy()->startOfDay()->setTimezone('UTC');
        $dayEnd = $now->copy()->addDay()->startOfDay()->setTimezone('UTC');

        $sequence = Transaction::query()
            ->where('created_at', '>=', $dayStart)
            ->where('created_at', '<', $dayEnd)
            ->count() + 1;

        return sprintf('TRX-%s-%04d', $now->format('Ymd'), $sequence);
    }

    /**
     * Snapshot nama & harga produk ke transaction_items + catat
     * stock_movements type 'out' per item (lihat checkout.md langkah 9d-e).
     *
     * @param  array<int, array<string, mixed>>  $items
     * @param  Collection<string, Product>  $products
     */
    private function insertItems(Transaction $transaction, array $items, Collection $products, User $kasir): void
    {
        foreach ($items as $item) {
            $product = $products->get($item['product_id']);
            $quantity = (int) $item['quantity'];

            $transactionItem = TransactionItem::create([
                'transaction_id' => $transaction->id,
                'product_id' => $product->id,
                'product_name' => $product->name,
                'price' => $product->price,
                'quantity' => $quantity,
                'subtotal' => $product->price * $quantity,
            ]);

            StockMovement::create([
                'product_id' => $product->id,
                'type' => StockMovement::TYPE_OUT,
                'quantity' => $quantity,
                'reference_type' => TransactionItem::class,
                'reference_id' => $transactionItem->id,
                'created_by' => $kasir->id,
            ]);
        }
    }
}
