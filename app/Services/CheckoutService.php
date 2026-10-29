<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CheckoutService
{
    /** Atomically reserve stock and create a demo order, once per checkout key. */
    public function place(User $buyer, array $input): Order
    {
        $items = collect($input['items'])
            ->map(fn(array $item) => [
                'product_id' => (int)$item['product_id'],
                'quantity' => (int)$item['quantity'],
            ])
            ->sortBy('product_id')
            ->values()
            ->all();
        $input['items'] = $items;
        $input['address'] = $input['delivery_method'] === 'pickup' ? null : ($input['address'] ?? null);
        $input['note'] = $input['note'] ?? null;
        $fingerprint = hash('sha256', json_encode($input, JSON_THROW_ON_ERROR));

        return DB::transaction(function () use ($buyer, $input, $items, $fingerprint) {
            User::query()
                ->whereKey($buyer->id)
                ->lockForUpdate()
                ->firstOrFail();
            $existing = Order::query()
                ->where('user_id', $buyer->id)
                ->where('idempotency_key', $input['idempotency_key'])
                ->first();

            if ($existing) {
                abort_unless(
                    hash_equals($existing->request_hash, $fingerprint),
                    409,
                    'Состав заказа изменился. Обновите оформление заказа.'
                );

                return $existing->load('items');
            }

            $products = Product::query()
                ->whereIn('id', array_column($items, 'product_id'))
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $subtotal = 0;

            foreach ($items as $item) {
                $product = $products->get($item['product_id']);

                if (!$product || !$product->is_active) {
                    throw ValidationException::withMessages([
                        'items' => 'Один из товаров больше недоступен. Обновите корзину.',
                    ]);
                }

                if ($product->stock < $item['quantity']) {
                    throw ValidationException::withMessages([
                        'items' => 'Недостаточно товара «' . $product->name . '». Доступно: ' . $product->stock . '.',
                    ]);
                }

                $subtotal += $product->price * $item['quantity'];
            }

            $delivery = $input['delivery_method'] === 'pickup' || $subtotal >= config('shop.free_delivery_from')
                ? 0 : config('shop.delivery_price');
            $order = Order::create([
                'user_id' => $buyer->id,
                'number' => 'RSP-' . Str::ulid(),
                'idempotency_key' => $input['idempotency_key'],
                'request_hash' => $fingerprint,
                'customer_name' => $input['customer_name'],
                'email' => $input['email'],
                'phone' => $input['phone'],
                'delivery_method' => $input['delivery_method'],
                'address' => $input['address'],
                'note' => $input['note'],
                'subtotal' => $subtotal,
                'delivery_total' => $delivery,
                'total' => $subtotal + $delivery,
                'payment_method' => 'demo',
                'status' => Order::PROCESSING,
            ]);

            foreach ($items as $item) {
                $product = $products->get($item['product_id']);
                $order->items()->create([
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'kind' => $product->kind,
                    'quantity' => $item['quantity'],
                    'unit_price' => $product->price,
                    'total' => $product->price * $item['quantity'],
                ]);
                $product->decrement('stock', $item['quantity']);
            }

            return $order->load('items');
        }, 3);
    }

    /** Enforce order transitions and return cancelled stock exactly once. */
    public function transition(Order $order, string $status): Order
    {
        return DB::transaction(function () use ($order, $status) {
            $order = Order::query()
                ->whereKey($order->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($order->status === $status) {
                return $order->load('items');
            }

            $allowed = [
                Order::PROCESSING => [Order::SHIPPED, Order::CANCELLED],
                Order::SHIPPED => [Order::COMPLETED],
            ];

            if (!in_array($status, $allowed[$order->status] ?? [], true)) {
                throw ValidationException::withMessages([
                    'status' => 'Этот статус заказа уже нельзя изменить выбранным способом.',
                ]);
            }

            if ($status === Order::CANCELLED) {
                $order->load('items');
                $products = Product::query()
                    ->whereIn('id', $order->items->pluck('product_id')->filter())
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                foreach ($order->items as $item) {
                    $products->get($item->product_id)?->increment('stock', $item->quantity);
                }

                $order->cancelled_at = now();
            }

            $order->status = $status;
            $order->save();

            return $order->load('items');
        }, 3);
    }
}
