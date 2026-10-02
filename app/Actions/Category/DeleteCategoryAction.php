<?php

namespace App\Actions\Category;

use App\Models\Category;
use Illuminate\Validation\ValidationException;

// Soft delete kategori, TAPI diblokir kalau masih ada produk atau
// subkategori anak yang terkait — FK nullOnDelete di database tidak
// terpicu untuk soft delete, jadi validasi ini wajib di Action.
class DeleteCategoryAction
{
    public function handle(Category $category): void
    {
        if ($category->products()->exists()) {
            throw ValidationException::withMessages([
                'category' => 'Kategori tidak bisa dihapus karena masih ada produk yang menggunakannya.',
            ]);
        }

        if ($category->children()->exists()) {
            throw ValidationException::withMessages([
                'category' => 'Kategori tidak bisa dihapus karena masih memiliki subkategori.',
            ]);
        }

        $category->delete();
    }
}
