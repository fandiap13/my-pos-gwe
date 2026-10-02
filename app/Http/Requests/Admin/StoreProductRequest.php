<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'category_id' => ['nullable', 'uuid', Rule::exists('categories', 'id')->whereNull('deleted_at')],
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['nullable', 'string', 'max:255', Rule::unique('products', 'sku')],
            'barcode' => ['nullable', 'string', 'max:255', Rule::unique('products', 'barcode')],
            'price' => ['required', 'integer', 'min:0'],
            'cost_price' => ['nullable', 'integer', 'min:0'],
            // Stok awal diinput sebagai jumlah murni, BUKAN kolom stock
            // langsung — Action yang menerjemahkannya jadi stock_movements
            // type 'in'. Lihat AGENTS.md & docs/DATABASE.md.
            'initial_stock' => ['required', 'integer', 'min:0'],
            'min_stock' => ['required', 'integer', 'min:0'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'category_id.uuid' => 'Kategori tidak valid.',
            'category_id.exists' => 'Kategori tidak ditemukan.',
            'name.required' => 'Nama produk wajib diisi.',
            'name.string' => 'Nama produk harus berupa teks.',
            'name.max' => 'Nama produk maksimal 255 karakter.',
            'sku.string' => 'SKU harus berupa teks.',
            'sku.max' => 'SKU maksimal 255 karakter.',
            'sku.unique' => 'SKU sudah dipakai produk lain.',
            'barcode.string' => 'Barcode harus berupa teks.',
            'barcode.max' => 'Barcode maksimal 255 karakter.',
            'barcode.unique' => 'Barcode sudah dipakai produk lain.',
            'price.required' => 'Harga jual wajib diisi.',
            'price.integer' => 'Harga jual harus berupa angka.',
            'price.min' => 'Harga jual tidak boleh negatif.',
            'cost_price.integer' => 'Harga beli harus berupa angka.',
            'cost_price.min' => 'Harga beli tidak boleh negatif.',
            'initial_stock.required' => 'Stok awal wajib diisi.',
            'initial_stock.integer' => 'Stok awal harus berupa angka.',
            'initial_stock.min' => 'Stok awal tidak boleh negatif.',
            'min_stock.required' => 'Stok minimum wajib diisi.',
            'min_stock.integer' => 'Stok minimum harus berupa angka.',
            'min_stock.min' => 'Stok minimum tidak boleh negatif.',
        ];
    }
}
