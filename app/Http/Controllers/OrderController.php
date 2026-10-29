<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckoutRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Services\CheckoutService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class OrderController extends Controller
{
    /** Paginated history belonging only to the authenticated buyer. */
    public function index(Request $request): AnonymousResourceCollection
    {
        $request->validate([
            'page' => ['nullable', 'integer', 'min:1', 'max:10000'],
        ]);

        return OrderResource::collection(Order::query()
            ->where('user_id', $request->user()->id)
            ->with('items')
            ->latest('id')
            ->paginate(10));
    }

    /** Checkout uses trusted server prices and never charges real money. */
    public function store(CheckoutRequest $request, CheckoutService $checkout): OrderResource
    {
        return new OrderResource($checkout->place($request->user(), $request->validated()));
    }

    /** Return 404 rather than disclosing another buyer's order. */
    public function show(Request $request, string $number): OrderResource
    {
        return new OrderResource(Order::query()
            ->where('user_id', $request->user()->id)
            ->where('number', $number)
            ->with('items')
            ->firstOrFail());
    }

    /** A buyer may cancel an order before it is shipped. */
    public function cancel(Request $request, string $number, CheckoutService $checkout): OrderResource
    {
        $order = Order::query()
            ->where('user_id', $request->user()->id)
            ->where('number', $number)
            ->firstOrFail();

        return new OrderResource($checkout->transition($order, Order::CANCELLED));
    }
}
