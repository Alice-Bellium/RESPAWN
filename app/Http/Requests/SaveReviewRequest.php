<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaveReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'rating' => ['required', 'integer', 'between:1,5'],
            'body' => ['required', 'string', 'min:10', 'max:2000'],
            'revision' => ['required', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'rating.required' => 'Выберите оценку от 1 до 5.',
            'rating.between' => 'Оценка должна быть от 1 до 5.',
            'body.required' => 'Расскажите о своём опыте использования товара.',
            'body.min' => 'Напишите хотя бы 10 символов.',
            'body.max' => 'Отзыв не должен превышать 2000 символов.',
        ];
    }
}
