<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaveProductRequest;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /** Includes drafts so an administrator can manage the full catalog. */
    public function index(Request $request): JsonResponse
    {
        $input = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'page' => [
                'nullable',
                'integer',
                'min:1',
                'max:10000',
            ],
        ]);

        return response()->json(Product::query()
            ->with('category')
            ->when($input['q'] ?? null, fn($query, $term) => $query->where('name', 'like', '%' . $term . '%'))
            ->latest('id')
            ->paginate(12));
    }

    /** Create a product with explicitly validated fields. */
    public function store(SaveProductRequest $request): JsonResponse
    {
        return response()->json([
            'data' => Product::create($request->validated())->load('category'),
        ], 201);
    }

    /** Stock changes and checkout use the same row lock. */
    public function update(SaveProductRequest $request, Product $product): JsonResponse
    {
        $product->update($request->validated());

        return response()->json([
            'data' => $product->fresh()->load('category'),
        ]);
    }

    /** Archive a product while preserving historical orders and links. */
    public function destroy(Product $product): JsonResponse
    {
        $product->update([
            'is_active' => false,
            'is_featured' => false,
        ]);

        return response()->json([
            'message' => 'Товар скрыт из каталога.',
        ]);
    }
}
