<?php

namespace App\Http\Controllers;

use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CategoryController extends Controller
{
    /** Категории с количеством опубликованных товаров. */
    public function index(): AnonymousResourceCollection
    {
        return CategoryResource::collection(Category::query()
            ->withCount([
                'products' => fn(Builder $query) => $query->where('is_active', true),
            ])
            ->orderBy('id')
            ->get());
    }
}
