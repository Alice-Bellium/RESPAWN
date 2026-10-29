<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ReviewResource;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ReviewController extends Controller
{
    /** Очередь модерации и опубликованные/отклонённые отзывы с пагинацией. */
    public function index(Request $request): AnonymousResourceCollection
    {
        $input = $request->validate([
            'status' => ['nullable', Rule::in([Review::PENDING, Review::APPROVED, Review::REJECTED])],
            'page' => ['nullable', 'integer', 'min:1', 'max:10000'],
        ]);

        return ReviewResource::collection(Review::query()
            ->with([
                'user:id,name',
                'product:id,name,slug,is_active',
            ])
            ->when($input['status'] ?? null, fn($query, $status) => $query->where('status', $status))
            ->latest('id')
            ->paginate(12));
    }

    /** Версия защищает от публикации текста, изменённого после открытия модератором. */
    public function update(Request $request, Review $review): ReviewResource
    {
        $input = $request->validate([
            'status' => ['required', Rule::in([Review::APPROVED, Review::REJECTED])],
            'revision' => ['required', 'integer', 'min:1'],
            'moderation_note' => ['required_if:status,rejected', 'nullable', 'string', 'max:500'],
        ], [
            'moderation_note.required_if' => 'Укажите причину отклонения для автора.',
        ]);

        $review = DB::transaction(function () use ($request, $review, $input): Review {
            $current = Review::query()
                ->whereKey($review->id)
                ->lockForUpdate()
                ->firstOrFail();
            abort_if(
                $current->revision !== (int)$input['revision'],
                409,
                'Отзыв изменился. Обновите список и проверьте новую версию.'
            );
            $current->status = $input['status'];
            $current->moderation_note = $input['status'] === Review::REJECTED
                ? $input['moderation_note']
                : null;
            $current->moderated_by = $request->user()->id;
            $current->moderated_at = now();
            $current->revision++;
            $current->save();

            return $current;
        }, 3);

        return new ReviewResource($review->load(['user:id,name', 'product:id,name,slug,is_active']));
    }
}
