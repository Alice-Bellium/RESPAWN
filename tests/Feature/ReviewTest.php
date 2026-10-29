<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Tests\TestCase;

class ReviewTest extends TestCase
{
    use RefreshDatabase;

    private function purchase(User $buyer, Product $product, string $status = Order::COMPLETED): void
    {
        $order = Order::factory()->create([
            'user_id' => $buyer->id,
            'status' => $status,
        ]);
        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
        ]);
    }

    private function payload(int $rating = 5, int $revision = 0): array
    {
        return [
            'rating' => $rating,
            'body' => 'Удобная клавиатура, пользуюсь каждый день.',
            'revision' => $revision,
        ];
    }

    public function test_only_received_purchases_of_this_product_allow_a_review(): void
    {
        $buyer = User::factory()->create();
        $product = Product::factory()->create();
        $url = '/api/products/' . $product->slug . '/review';
        $this->putJson($url, $this->payload())->assertUnauthorized();
        $this->getJson($url)->assertUnauthorized();
        $this
            ->actingAs($buyer)
            ->getJson($url)
            ->assertJsonPath('can_review', false)
            ->assertJsonPath('review', null);
        $this->putJson($url, $this->payload())->assertForbidden();

        foreach ([Order::PROCESSING, Order::SHIPPED, Order::CANCELLED] as $status) {
            $this->purchase($buyer, $product, $status);
            $this->putJson($url, $this->payload())->assertForbidden();
        }

        $this->purchase(User::factory()->create(), $product);
        $this->purchase($buyer, Product::factory()->create());
        $this->putJson($url, $this->payload())->assertForbidden();
        $this->purchase($buyer, $product);
        $this->getJson($url)->assertJsonPath('can_review', true);
        $this
            ->putJson($url, $this->payload() + [
                'status' => 'approved',
                'user_id' => 999,
            ])
            ->assertCreated()
            ->assertJsonPath('data.status', 'pending');
        $this->assertDatabaseHas('reviews', [
            'user_id' => $buyer->id,
            'product_id' => $product->id,
            'rating' => 5,
        ]);
    }

    public function test_one_review_per_buyer_and_product_with_editing_and_stale_write_protection(): void
    {
        $buyer = User::factory()->create();
        $product = Product::factory()->create();
        $this->purchase($buyer, $product);
        $this->purchase($buyer, $product);
        $url = '/api/products/' . $product->slug . '/review';
        $this
            ->actingAs($buyer)
            ->putJson($url, $this->payload())
            ->assertCreated()
            ->assertJsonPath('data.revision', 1);
        $this->putJson($url, $this->payload())->assertStatus(409);
        $this
            ->putJson($url, $this->payload(4, 1))
            ->assertOk()
            ->assertJsonPath('data.rating', 4)
            ->assertJsonPath('data.revision', 2);
        $this
            ->putJson($url, $this->payload(4, 2))
            ->assertOk()
            ->assertJsonPath('data.revision', 2);
        $this->assertDatabaseCount('reviews', 1);
        $this
            ->actingAs(User::factory()->create())
            ->getJson($url)
            ->assertJsonPath('review', null);
    }

    public function test_rating_and_public_pagination_include_only_approved_reviews(): void
    {
        $product = Product::factory()->create();
        $author = User::factory()->create([
            'name' => 'Mary Example',
        ]);
        Review::factory()
            ->approved()
            ->create([
                'product_id' => $product->id,
                'user_id' => $author->id,
                'rating' => 5,
            ]);
        Review::factory()->approved()->create([
            'product_id' => $product->id,
            'rating' => 2,
        ]);
        Review::factory()->create([
            'product_id' => $product->id,
            'rating' => 1,
        ]);
        Review::factory()->create([
            'product_id' => $product->id,
            'rating' => 1,
            'status' => Review::REJECTED,
            'moderation_note' => 'PRIVATE NOTE',
        ]);
        $this
            ->getJson('/api/products/' . $product->slug)
            ->assertJsonPath('data.rating', 3.5)
            ->assertJsonPath('data.reviews_count', 2);
        $this
            ->getJson('/api/products')
            ->assertJsonPath('data.0.rating', 3.5)
            ->assertJsonPath('data.0.reviews_count', 2);
        $this
            ->getJson('/api/products/' . $product->slug . '/reviews')
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('summary.average', 3.5)
            ->assertJsonPath('data.1.author', 'Mary')
            ->assertJsonMissingPath('data.0.status')
            ->assertJsonMissingPath('data.0.moderation_note')
            ->assertJsonMissingPath('data.0.user_id')
            ->assertDontSee($author->email)
            ->assertDontSee('PRIVATE NOTE');
        Review::factory()
            ->approved()
            ->count(4)
            ->create([
                'product_id' => $product->id,
            ]);
        $this
            ->getJson('/api/products/' . $product->slug . '/reviews')
            ->assertJsonCount(5, 'data')
            ->assertJsonPath('meta.last_page', 2);
        $this->getJson('/api/products/' . $product->slug . '/reviews?page=2')->assertJsonCount(1, 'data');
    }

    public function test_pending_and_empty_ratings_have_no_fake_stars(): void
    {
        $product = Product::factory()->create();
        Review::factory()->create([
            'product_id' => $product->id,
        ]);
        $this
            ->getJson('/api/products/' . $product->slug)
            ->assertJsonPath('data.rating', null)
            ->assertJsonPath('data.reviews_count', 0);
        $this
            ->getJson('/api/products/' . $product->slug . '/reviews')
            ->assertJsonCount(0, 'data')
            ->assertJsonPath('summary.average', null);
    }

    public function test_only_admin_can_moderate_and_an_edit_removes_the_old_rating_until_reapproved(): void
    {
        $buyer = User::factory()->create();
        $admin = User::factory()->create([
            'is_admin' => true,
        ]);
        $product = Product::factory()->create();
        $this->purchase($buyer, $product);
        $review = Review::factory()->create([
            'product_id' => $product->id,
            'user_id' => $buyer->id,
            'rating' => 5,
        ]);
        $url = '/api/admin/reviews/' . $review->id;
        $this->getJson('/api/admin/reviews')->assertUnauthorized();
        $this
            ->actingAs($buyer)
            ->getJson('/api/admin/reviews')
            ->assertForbidden();
        $this->patchJson($url, [
            'status' => 'approved',
            'revision' => 1,
        ])->assertForbidden();
        $this
            ->actingAs($admin)
            ->getJson('/api/admin/reviews?status=pending')
            ->assertJsonCount(1, 'data');
        $this
            ->patchJson($url, [
                'status' => 'approved',
                'revision' => 1,
            ])
            ->assertOk()
            ->assertJsonPath('data.revision', 2);
        $this->assertSame($admin->id, $review->fresh()->moderated_by);
        $this->getJson('/api/products/' . $product->slug)->assertJsonPath('data.reviews_count', 1);
        $this
            ->actingAs($buyer)
            ->putJson('/api/products/' . $product->slug . '/review', $this->payload(3, 2))
            ->assertOk()
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonPath('data.revision', 3);
        $this->getJson('/api/products/' . $product->slug)->assertJsonPath('data.reviews_count', 0);
        $this->assertNull($review->fresh()->moderated_at);
        $this
            ->actingAs($admin)
            ->patchJson($url, [
                'status' => 'approved',
                'revision' => 2,
            ])
            ->assertStatus(409);
        $this->patchJson($url, [
            'status' => 'approved',
            'revision' => 3,
        ])->assertOk();
        $this->getJson('/api/products/' . $product->slug)->assertJsonPath('data.rating', 3);
    }

    public function test_rejection_requires_a_reason_and_can_be_corrected_by_the_buyer(): void
    {
        $product = Product::factory()->create();
        $buyer = User::factory()->create();
        $this->purchase($buyer, $product);
        $review = Review::factory()->approved()->create([
            'product_id' => $product->id,
            'user_id' => $buyer->id,
        ]);
        $url = '/api/admin/reviews/' . $review->id;
        $this
            ->actingAs(User::factory()->create([
                'is_admin' => true,
            ]))
            ->patchJson($url, [
                'status' => 'rejected',
                'revision' => 1,
            ])
            ->assertUnprocessable();
        $this->patchJson(
            $url,
            [
                'status' => 'rejected',
                'revision' => 1,
                'moderation_note' => 'Уберите личные контакты.',
            ]
        )
            ->assertOk();
        $this
            ->actingAs($buyer)
            ->getJson('/api/products/' . $product->slug . '/review')
            ->assertJsonPath('review.status', 'rejected')
            ->assertJsonPath('review.moderation_note', 'Уберите личные контакты.');
        $this
            ->putJson('/api/products/' . $product->slug . '/review', $this->payload(4, 2))
            ->assertOk()
            ->assertJsonPath('data.moderation_note', null)
            ->assertJsonPath('data.status', 'pending');
    }

    public function test_validation_rejects_invalid_ratings_text_versions_and_hidden_products(): void
    {
        $this->withoutMiddleware(ThrottleRequests::class);
        $buyer = User::factory()->create();
        $product = Product::factory()->create();
        $this->purchase($buyer, $product);
        $url = '/api/products/' . $product->slug . '/review';
        $this->actingAs($buyer);

        foreach ([0, 6, 2.5, 'bad', null] as $rating) {
            $this
                ->putJson($url, array_replace($this->payload(), [
                    'rating' => $rating,
                ]))
                ->assertUnprocessable();
        }

        foreach (['', '   ', 'short', str_repeat('x', 2001), ['x']] as $body) {
            $this->putJson($url, array_replace($this->payload(), [
                'body' => $body,
            ]))->assertUnprocessable();
        }

        $this->putJson($url, array_replace($this->payload(), [
            'revision' => -1,
        ]))->assertUnprocessable();
        $this->getJson('/api/products/' . $product->slug . '/reviews?page=-1')->assertUnprocessable();
        $this
            ->putJson($url, array_replace($this->payload(), [
                'rating' => '5',
                'revision' => '0',
            ]))
            ->assertCreated();
        $product->update([
            'is_active' => false,
        ]);
        $this->putJson($url, $this->payload())->assertNotFound();
        $this->getJson($url)->assertNotFound();
        $this->getJson('/api/products/' . $product->slug . '/reviews')->assertNotFound();
    }
}
