<?php

namespace App\Http\Requests\Kasir;

use Illuminate\Foundation\Http\FormRequest;

class StoreOpenShiftRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Authorization role sudah di middleware routes/kasir.php —
        // cukup pastikan pemilik shift adalah user yang login.
        return $this->user()?->isKasir() === true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'opening_cash' => ['required', 'integer', 'min:0'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'opening_cash.required' => 'Kas awal wajib diisi.',
            'opening_cash.integer' => 'Kas awal harus berupa angka.',
            'opening_cash.min' => 'Kas awal tidak boleh negatif.',
        ];
    }
}
