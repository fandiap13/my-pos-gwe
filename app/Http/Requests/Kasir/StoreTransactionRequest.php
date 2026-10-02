<?php

namespace App\Http\Requests\Kasir;

use App\Models\Transaction;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Authorization role sudah di middleware routes/kasir.php —
        // cukup pastikan hanya kasir yang boleh membuat transaksi.
        return $this->user()?->isKasir() === true;
    }

    /**
     * Validasi struktur input checkout. Validasi bisnis (stok cukup,
     * harga belum berubah, nominal bayar cukup, shift masih open)
     * dilakukan di CreateTransactionAction karena butuh data terkunci
     * di dalam DB::transaction(). Lihat docs/features/checkout.md.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'items' => ['required', 'array', 'min:1', 'max:100'],
            'items.*.product_id' => [
                'required',
                'uuid',
                // distinct: satu produk cukup satu baris per transaksi —
                // cek stok per baris bisa dilewati kalau duplikat diizinkan.
                'distinct',
                Rule::exists('products', 'id')->whereNull('deleted_at'),
            ],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.price' => ['required', 'integer', 'min:0'],
            'payment_method' => [
                'required',
                Rule::in([
                    Transaction::PAYMENT_CASH,
                    Transaction::PAYMENT_TRANSFER,
                    Transaction::PAYMENT_DEBIT,
                ]),
            ],
            // Untuk transfer/debit nominal diabaikan (pakai total di Action).
            'paid_amount' => ['nullable', 'integer', 'min:0'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'items.required' => 'Item transaksi wajib diisi.',
            'items.array' => 'Item transaksi tidak valid.',
            'items.min' => 'Transaksi minimal berisi 1 item.',
            'items.max' => 'Transaksi maksimal 100 item.',
            'items.*.product_id.required' => 'Produk pada item wajib dipilih.',
            'items.*.product_id.uuid' => 'Produk pada item tidak valid.',
            'items.*.product_id.distinct' => 'Produk tidak boleh dobel — gabungkan jumlahnya jadi satu item.',
            'items.*.product_id.exists' => 'Produk pada item tidak ditemukan.',
            'items.*.quantity.required' => 'Jumlah pada item wajib diisi.',
            'items.*.quantity.integer' => 'Jumlah pada item harus berupa angka.',
            'items.*.quantity.min' => 'Jumlah pada item minimal 1.',
            'items.*.price.required' => 'Harga pada item wajib diisi.',
            'items.*.price.integer' => 'Harga pada item harus berupa angka.',
            'items.*.price.min' => 'Harga pada item tidak boleh negatif.',
            'payment_method.required' => 'Metode pembayaran wajib dipilih.',
            'payment_method.in' => 'Metode pembayaran tidak valid.',
            'paid_amount.integer' => 'Nominal bayar harus berupa angka.',
            'paid_amount.min' => 'Nominal bayar tidak boleh negatif.',
        ];
    }
}
