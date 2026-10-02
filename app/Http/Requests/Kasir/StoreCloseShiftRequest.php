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
}
