<?php

namespace App\Http\Requests\Admin;

use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateCategoryRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'parent_id' => ['nullable', 'uuid', Rule::exists('categories', 'id')->whereNull('deleted_at')],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama kategori wajib diisi.',
            'name.string' => 'Nama kategori harus berupa teks.',
            'name.max' => 'Nama kategori maksimal 255 karakter.',
            'parent_id.uuid' => 'Kategori induk tidak valid.',
            'parent_id.exists' => 'Kategori induk tidak ditemukan.',
        ];
    }

    /**
     * Cegah siklus: kategori tidak boleh jadi parent dirinya sendiri
     * (langsung maupun tidak langsung lewat salah satu descendant-nya).
     * Lihat docs/DATABASE.md "Catatan implementasi" untuk categories.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $parentId = $this->input('parent_id');

            if ($parentId === null) {
                return;
            }

            /** @var Category $category */
            $category = $this->route('category');

            if ($parentId === $category->id) {
                $validator->errors()->add('parent_id', 'Kategori tidak boleh menjadi parent dirinya sendiri.');

                return;
            }

            if ($this->isDescendant($category, $parentId)) {
                $validator->errors()->add('parent_id', 'Parent tidak boleh salah satu subkategori dari kategori ini.');
            }
        });
    }

    private function isDescendant(Category $category, string $candidateParentId): bool
    {
        $descendantIds = $category->children()->pluck('id');

        foreach ($descendantIds as $descendantId) {
            if ($descendantId === $candidateParentId) {
                return true;
            }

            if ($this->isDescendant(Category::findOrFail($descendantId), $candidateParentId)) {
                return true;
            }
        }

        return false;
    }
}
