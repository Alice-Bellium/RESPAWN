<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\Product;
use App\Services\CheckoutService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    /** Paginated orders with an optional fulfillment filter. */
    public function index(Request $request): AnonymousResourceCollection
    {
        $input = $request->validate([
            'page' => ['nullable', 'integer', 'min:1', 'max:10000'],
            'status' => [
                'nullable',
                Rule::in([
                    'processing',
                    'shipped',
                    'completed',
                    'cancelled',
                ]),
            ],
        ]);

        return OrderResource::collection(Order::query()
            ->with('items')
            ->when($input['status'] ?? null, fn($query, $status) => $query->where('status', $status))
            ->latest('id')
            ->paginate(12));
    }

    /** Only permitted transitions can change stock or fulfillment state. */
    public function update(Request $request, Order $order, CheckoutService $checkout): OrderResource
    {
        $input = $request->validate([
            'status' => [
                'required',
                Rule::in([
                    'processing',
                    'shipped',
                    'completed',
                    'cancelled',
                ]),
            ],
        ]);

        return new OrderResource($checkout->transition($order, $input['status']));
    }

    /** Demo revenue excludes cancelled orders and is never real payment revenue. */
    public function summary(): JsonResponse
    {
        return response()->json([
            'products' => Product::count(),
            'orders' => Order::count(),
            'processing' => Order::query()->where('status', Order::PROCESSING)->count(),
            'revenue' => (int)Order::query()->where('status', '!=', Order::CANCELLED)->sum('total'),
        ]);
    }
}
