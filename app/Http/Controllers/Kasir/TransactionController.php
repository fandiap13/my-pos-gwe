<?php

namespace App\Http\Controllers\Kasir;

use App\Actions\Transaction\CreateTransactionAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Kasir\StoreTransactionRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Models\StoreSetting;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

// Checkout kasir: cari produk → keranjang → bayar → struk. Lihat
// docs/features/checkout.md & docs/UI.md "Alur Buka Shift → Transaksi".
class TransactionController extends Controller
{
    public function index(): Response
    {
        // Semua produk layak jual dimuat sekali sebagai props; pencarian
        // nama/SKU/barcode difilter client-side supaya scan barcode
        // terasa instan tanpa request tambahan (keputusan dicatat di
        // docs/DECISIONS.md). cost_price sengaja TIDAK dipilih —
        // kasir tidak boleh melihat harga modal.
        $products = Product::query()
            ->orderBy('name')
            ->get(['id', 'category_id', 'name', 'sku', 'barcode', 'price', 'stock', 'min_stock']);

        return Inertia::render('Kasir/Transaksi/Index', [
            'products' => ProductResource::collection($products)->resolve(),
        ]);
    }

    public function store(StoreTransactionRequest $request, CreateTransactionAction $createTransaction): RedirectResponse
    {
        $transaction = $createTransaction->handle($request->validated(), $request->user());

        return redirect()
            ->route('kasir.transaksi.selesai', $transaction)
            ->with('success', 'Transaksi berhasil disimpan.');
    }

    /**
     * Halaman struk setelah checkout sukses (checkout.md langkah 10).
     * Route ini DI LUAR middleware shift.active: transaksi sudah terlanjur
     * tersimpan, struk tetap harus bisa dilihat walau shift keburu
     * ditutup. Dilindungi cek kepemilikan — kasir hanya boleh melihat
     * struk transaksinya sendiri.
     */
    public function selesai(Request $request, Transaction $transaction): Response
    {
        abort_unless($transaction->user_id === $request->user()->id, 403);

        $transaction->load('items', 'user');
        $settings = StoreSetting::current();

        return Inertia::render('Kasir/Transaksi/Selesai', [
            'receipt' => $this->receipt($transaction, $settings),
            'storeName' => $settings->store_name,
            'storeAddress' => $settings->address,
        ]);
    }

    /**
     * Payload struk sesuai interface Receipt di
     * resources/js/types/models.ts (dipakai ReceiptPreview).
     *
     * @return array<string, mixed>
     */
    private function receipt(Transaction $transaction, StoreSetting $settings): array
    {
        return [
            'transaction_number' => $transaction->transaction_number,
            'created_at' => $transaction->created_at
                ->copy()
                ->setTimezone($settings->timezone)
                ->format('Y-m-d H:i'),
            'cashier_name' => $transaction->user->name,
            'items' => $transaction->items
                ->map(fn (TransactionItem $item): array => [
                    'product_name' => $item->product_name,
                    'price' => $item->price,
                    'quantity' => $item->quantity,
                    'subtotal' => $item->subtotal,
                ])
                ->values()
                ->all(),
            'subtotal' => $transaction->subtotal,
            'total' => $transaction->total,
            'paid_amount' => $transaction->paid_amount,
            'change_amount' => $transaction->change_amount,
            'payment_method' => $transaction->payment_method,
        ];
    }
}
