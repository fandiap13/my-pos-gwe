<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Product\CreateProductAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductRequest;
use App\Http\Requests\Admin\UpdateProductRequest;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\ProductResource;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public function index(Request $request): Response
    {
        $products = Product::query()
            ->with('category')
            ->when(
                $request->string('search')->value(),
                fn ($query, $search) => $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%")
                        ->orWhere('barcode', 'like', "%{$search}%");
                })
            )
            ->when(
                $request->string('category_id')->value(),
                fn ($query, $categoryId) => $query->where('category_id', $categoryId)
            )
            ->orderBy('name')
            ->paginate($this->perPage($request))
            ->withQueryString();

        return Inertia::render('Admin/Products/Index', [
            // through() mempertahankan shape paginator standar Laravel
            // (data/links/current_page di level yang sama) sambil
            // mentransform tiap item lewat ProductResource. Dipakai
            // flat (bukan dibungkus ResourceCollection) supaya sesuai
            // tipe frontend Paginated<T> — lihat docs/DECISIONS.md.
            'products' => $products->through(fn (Product $product) => (new ProductResource($product))->resolve()),
            'categories' => CategoryResource::collection(Category::orderBy('name')->get()),
            'filters' => $request->only(['search', 'category_id']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Products/Create', [
            'categoryOptions' => $this->categoryOptions(),
        ]);
    }

    public function store(StoreProductRequest $request, CreateProductAction $createProduct): RedirectResponse
    {
        $createProduct->handle($request->validated(), $request->user());

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Produk berhasil ditambahkan.');
    }

    public function edit(Product $product): Response
    {
        return Inertia::render('Admin/Products/Edit', [
            // resolve() = props flat tanpa bungkusan {data: ...} — lihat
            // catatan serupa di Kasir\ShiftController@tutup &
            // docs/DECISIONS.md.
            'product' => (new ProductResource($product))->resolve(),
            'categoryOptions' => $this->categoryOptions(),
        ]);
    }

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $product->update($request->validated());

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $product->delete();

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Produk berhasil dihapus.');
    }

    /**
     * @return array<int, array{value: string, label: string}>
     */
    private function categoryOptions(): array
    {
        return Category::query()
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (Category $category) => ['value' => $category->id, 'label' => $category->name])
            ->all();
    }
}
