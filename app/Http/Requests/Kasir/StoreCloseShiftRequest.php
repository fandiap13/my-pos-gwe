<?php

namespace App\Http\Requests\Kasir;

use Illuminate\Foundation\Http\FormRequest;

class StoreCloseShiftRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isKasir() === true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            // Kas fisik aktual di laci saat tutup shift. Selisih terhadap
            // expected_cash dihitung di backend (CloseShiftAction), bukan
            // dikirim dari frontend.
            'closing_cash' => ['required', 'integer', 'min:0'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'closing_cash.required' => 'Kas akhir wajib diisi.',
            'closing_cash.integer' => 'Kas akhir harus berupa angka.',
            'closing_cash.min' => 'Kas akhir tidak boleh negatif.',
        ];
    }
}
