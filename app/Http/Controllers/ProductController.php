<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductIndexRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProductController extends Controller
{
    /** Возвращает опубликованные товары с фильтрами и стабильной пагинацией. */
    public function index(ProductIndexRequest $request): AnonymousResourceCollection
    {
        $filters = $request->validated();
        $query = Product::query()
            ->withRating()
            ->with('category')
            ->where('is_active', true);

        if (isset($filters['category'])) {
            $query->whereHas('category', fn(Builder $category) => $category->where('slug', $filters['category']));
        }

        if (isset($filters['q'])) {
            $search = '%' . trim($filters['q']) . '%';
            $query->where(fn(Builder $q) => $q
                ->where('name', 'like', $search)
                ->orWhere('brand', 'like', $search)
                ->orWhere('sku', 'like', $search));
        }

        if (!empty($filters['in_stock'])) {
            $query->where('stock', '>', 0);
        }

        if (!empty($filters['featured'])) {
            $query->where('is_featured', true);
        }

        if (isset($filters['max_price'])) {
            $query->where('price', '<=', $filters['max_price']);
        }

        if (array_key_exists('ids', $filters)) {
            $query->whereIn('id', $filters['ids']);
        }

        match ($filters['sort'] ?? 'newest') {
            'price_asc' => $query->orderBy('price')->orderBy('id'),
            'price_desc' => $query->orderByDesc('price')->orderBy('id'),
            default => $query->orderByDesc('created_at')->orderByDesc('id'),
        };

        return ProductResource::collection($query->paginate($filters['per_page'] ?? 12)->withQueryString());
    }

    /** Возвращает один опубликованный товар по slug. */
    public function show(Product $product): ProductResource
    {
        abort_unless($product->is_active, 404);

        return new ProductResource($product
            ->load('category')
            ->loadCount('approvedReviews')
            ->loadAvg('approvedReviews', 'rating'));
    }
}
