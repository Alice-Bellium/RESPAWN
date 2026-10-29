<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool)$this->user()?->is_admin;
    }

    public function rules(): array
    {
        return [
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:150'],
            'slug' => [
                'required',
                'string',
                'max:150',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('products', 'slug')->ignore($this->route('product')),
            ],
            'sku' => [
                'nullable',
                'string',
                'max:40',
                Rule::unique('products', 'sku')->ignore($this->route('product')),
            ],
            'brand' => ['required', 'string', 'max:80'],
            'kind' => ['required', Rule::in(['keyboard', 'mouse', 'chair', 'pc'])],
            'description' => ['nullable', 'string', 'max:10000'],
            'price' => ['required', 'integer', 'min:100', 'max:100000000'],
            'stock' => ['required', 'integer', 'min:0', 'max:100000'],
            'is_active' => ['required', 'boolean'],
            'is_featured' => ['required', 'boolean'],
            'badge' => ['nullable', 'string', 'max:40'],
            'specifications' => [
                'nullable',
                'array',
                'max:20',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (!is_array($value)) {
                        return;
                    }

                    foreach (array_keys($value) as $name) {
                        if (!is_string($name) || trim($name) === '' || mb_strlen($name) > 100) {
                            $fail('У каждой характеристики должно быть название длиной до 100 символов.');

                            return;
                        }
                    }
                },
            ],
            'specifications.*' => ['required', 'string', 'max:250'],
        ];
    }
}
