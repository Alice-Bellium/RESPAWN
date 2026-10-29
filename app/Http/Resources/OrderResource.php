<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'number' => $this->number,
            'status' => $this->status,
            'customer_name' => $this->customer_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'delivery_method' => $this->delivery_method,
            'address' => $this->address,
            'note' => $this->note,
            'subtotal' => $this->subtotal,
            'delivery_total' => $this->delivery_total,
            'total' => $this->total,
            'payment_method' => $this->payment_method,
            'created_at' => $this->created_at->toIso8601String(),
            'items' => $this->whenLoaded('items', fn() => $this->items->map(fn($item) => [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'name' => $item->name,
                'kind' => $item->kind,
                'sku' => $item->sku,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'total' => $item->total,
            ])),
        ];
    }
}
