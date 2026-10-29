<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'idempotency_key' => ['required', 'uuid'],
            'items' => ['required', 'array', 'min:1', 'max:50'],
            'items.*.product_id' => ['required', 'integer', 'min:1', 'distinct'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:' . config('shop.max_quantity')],
            'customer_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:190'],
            'phone' => ['required', 'string', 'regex:/^[+0-9() .-]{7,30}$/'],
            'delivery_method' => ['required', Rule::in(['delivery', 'pickup'])],
            'address' => ['required_if:delivery_method,delivery', 'nullable', 'string', 'max:500'],
            'note' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'address.required_if' => 'Укажите адрес доставки.',
            'phone.regex' => 'Проверьте номер телефона.',
            'items.required' => 'Добавьте товары в корзину.',
        ];
    }
}
