<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** Публичные данные товара. Денежные значения передаются в копейках. */
class ProductResource extends JsonResource
{
    /** Собирает ответ без служебных полей модели. */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'price' => $this->price,
            'stock' => $this->stock,
            'sku' => $this->sku,
            'brand' => $this->brand,
            'kind' => $this->kind,
            'badge' => $this->badge,
            'is_featured' => $this->is_featured,
            'specifications' => $this->specifications ?? [],
            'category' => new CategoryResource($this->whenLoaded('category')),
        ];
    }
}
