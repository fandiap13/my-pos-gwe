<?php

namespace App\Http\Resources;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Product
 */
class ProductResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'category_id' => $this->category_id,
            'category_name' => $this->whenLoaded('category', fn () => $this->category?->name),
            'name' => $this->name,
            'sku' => $this->sku,
            'barcode' => $this->barcode,
            'price' => $this->price,
            'cost_price' => $this->cost_price,
            'stock' => $this->stock,
            'min_stock' => $this->min_stock,
            // Dihitung di accessor Resource, bukan disimpan sebagai kolom
            // — lihat Product::isOutOfStock()/isLowStock() (Fase 1.1).
            'stock_status' => match (true) {
                $this->isOutOfStock() => 'out_of_stock',
                $this->isLowStock() => 'low_stock',
                default => 'in_stock',
            },
        ];
    }
}
