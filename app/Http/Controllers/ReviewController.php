<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveReviewRequest;
use App\Http\Resources\ReviewResource;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Throwable;

class ReviewController extends Controller
{
    /** Публичны только прошедшие модерацию отзывы; выдача не зависит от сессии. */
    public function index(Request $request, Product $product): AnonymousResourceCollection
    {
        abort_unless($product->is_active, 404);
        $request->validate([
            'page' => ['nullable', 'integer', 'min:1', 'max:10000'],
        ]);
        $product->loadCount('approvedReviews')->loadAvg('approvedReviews', 'rating');

        return ReviewResource::collection($product
            ->approvedReviews()
            ->with('user:id,name')
            ->latest('id')
            ->paginate(5))
            ->additional([
                'summary' => [
                    'count' => $product->approved_reviews_count,
                    'average' => $product->approved_reviews_avg_rating === null
                        ? null
                        : round((float)$product->approved_reviews_avg_rating, 1),
                ],
            ]);
    }

    /** Собственный отзыв и возможность его оставить не раскрываются другим пользователям. */
    public function mine(Request $request, Product $product): JsonResponse
    {
        abort_unless($product->is_active, 404);
        $review = Review::query()
            ->with('user:id,name')
            ->where('product_id', $product->id)
            ->where('user_id', $request->user()->id)
            ->first();

        return response()->json([
            'can_review' => $this->hasReceivedProduct($request->user(), $product),
            'review' => $review ? new ReviewResource($review) : null,
        ])->header('Cache-Control', 'no-store');
    }

    /** Блокировка автора и уникальный индекс исключают два отзыва при параллельной отправке.
     *
     * @throws Throwable
     */
    public function save(SaveReviewRequest $request, Product $product): ReviewResource
    {
        abort_unless($product->is_active, 404);
        $input = $request->validated();
        $input['rating'] = (int)$input['rating'];
        $input['revision'] = (int)$input['revision'];
        $review = DB::transaction(function () use ($request, $product, $input): Review {
            $user = User::query()
                ->whereKey($request->user()->id)
                ->lockForUpdate()
                ->firstOrFail();

            abort_unless(
                $this->hasReceivedProduct($user, $product),
                403,
                'Отзыв можно оставить после получения этого товара.'
            );

            $review = Review::query()
                ->where('user_id', $user->id)
                ->where('product_id', $product->id)
                ->lockForUpdate()
                ->first();

            abort_if(
                ($review?->revision ?? 0) !== $input['revision'],
                409,
                'Отзыв уже изменился. Обновите его перед сохранением.'
            );

            if ($review && $review->rating === $input['rating'] && $review->body === $input['body']) {
                return $review;
            }

            $review ??= new Review;
            $review->user_id = $user->id;
            $review->product_id = $product->id;
            $review->fill([
                'rating' => $input['rating'],
                'body' => $input['body'],
            ]);
            $review->status = Review::PENDING;
            $review->revision = ($review->revision ?? 0) + 1;
            $review->moderation_note = null;
            $review->moderated_by = null;
            $review->moderated_at = null;
            $review->save();

            return $review;
        }, 3);

        return new ReviewResource($review->load('user:id,name'));
    }

    /** Учитываются только свои полученные заказы, содержащие именно этот товар. */
    private function hasReceivedProduct(User $user, Product $product): bool
    {
        return Order::query()
            ->where('user_id', $user->id)
            ->where('status', Order::COMPLETED)
            ->whereHas('items', fn($query) => $query->where('product_id', $product->id))
            ->exists();
    }
}
