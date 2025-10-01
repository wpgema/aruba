<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'slug'        => $this->slug,
            'name'        => $this->name,
            'price_buy'   => $this->price_buy,
            'price_sale'  => $this->price_sale,
            'stock'       => $this->stock,
            'image'       => $this->image ? url('storage/' . $this->image) : null,
            'description' => $this->description,
            'category_id' => $this->category_id,
            'supplier_id' => $this->supplier_id,
            'category'    => $this->whenLoaded('category'),
            'supplier'    => $this->whenLoaded('supplier'),
            'status'      => $this->is_active,
            'created_at'  => $this->created_at?->toDateTimeString(),
            'updated_at'  => $this->updated_at?->toDateTimeString(),
        ];
    }
}
