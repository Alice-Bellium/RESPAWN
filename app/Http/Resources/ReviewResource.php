<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class ReviewResource extends JsonResource
{
    /** Контакты покупателя не публикуются; причина модерации видна только автору и администратору. */
    public function toArray(Request $request): array
    {
        $private = $request->user()
            && ($request->user()->id === $this->user_id || $request->user()->is_admin);

        return [
            'id' => $this->id,
            'author' => Str::before(trim($this->user->name), ' ') ?: 'Покупатель',
            'rating' => $this->rating,
            'body' => $this->body,
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
            'verified_purchase' => true,
            $this->mergeWhen($private, [
                'status' => $this->status,
                'revision' => $this->revision,
                'moderation_note' => $this->moderation_note,
            ]),
            'product' => $this->whenLoaded('product', fn() => [
                'name' => $this->product->name,
                'slug' => $this->product->slug,
                'is_active' => $this->product->is_active,
            ]),
        ];
    }
}
