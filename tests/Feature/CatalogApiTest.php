<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Database\Seeders\CatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalog_excludes_drafts_and_exposes_category_without_n_plus_one(): void
    {
        $category = Category::factory()->create();
        Product::factory()
            ->count(13)
            ->for($category)
            ->create();
        Product::factory()
            ->inactive()
            ->for($category)
            ->create();
        $this
            ->getJson('/api/products')
            ->assertOk()
            ->assertJsonCount(12, 'data')
            ->assertJsonPath('meta.total', 13)
            ->assertJsonStructure([
                'data' => [
                    [
                        'category' => [
                            'id',
                            'slug',
                        ],
                    ],
                ],
            ]);
        $this->getJson('/api/products?page=2')->assertJsonCount(1, 'data');
    }

    public function test_filter_sort_and_pagination_are_stable(): void
    {
        $category = Category::factory()->create([
            'slug' => 'keyboards',
        ]);
        $items = Product::factory()
            ->count(15)
            ->for($category)
            ->create([
                'price' => 100_00,
            ]);
        Product::factory()->create([
            'price' => 1,
        ]);
        $first = $this
            ->getJson('/api/products?category=keyboards&sort=price_asc')
            ->assertOk()
            ->assertJsonPath('meta.total', 15);
        $second = $this->getJson('/api/products?category=keyboards&sort=price_asc&page=2')->assertOk();
        $ids = [...array_column($first->json('data'), 'id'), ...array_column($second->json('data'), 'id')];
        $this->assertSame($items->modelKeys(), $ids);
        $this->assertStringContainsString('category=keyboards', $first->json('links.next'));
        $this->getJson('/api/products?category[]=keyboards')->assertUnprocessable();
        $this->getJson('/api/products?sort=unknown')->assertUnprocessable();
        $this->getJson('/api/products?page=0')->assertUnprocessable();
    }

    public function test_public_detail_and_unavailable_products(): void
    {
        $product = Product::factory()->outOfStock()->create();
        $draft = Product::factory()->inactive()->create();
        $this
            ->getJson('/api/products/' . $product->slug)
            ->assertOk()
            ->assertJsonPath('data.stock', 0);
        $this->getJson('/api/products/' . $draft->slug)->assertNotFound();
        $this->getJson('/api/products/missing')->assertNotFound();
        $this->getJson('/api/products?in_stock=1')->assertJsonCount(0, 'data');
    }

    public function test_demo_catalog_seeder_is_repeatable(): void
    {
        $this->seed(CatalogSeeder::class);
        $ids = Product::query()
            ->orderBy('id')
            ->pluck('id')
            ->all();
        $this->seed(CatalogSeeder::class);
        $this->assertDatabaseCount('categories', 4);
        $this->assertDatabaseCount('products', 24);
        $this->assertSame($ids, Product::query()
            ->orderBy('id')
            ->pluck('id')
            ->all());
    }
}
