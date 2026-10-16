<?php

namespace Tests\Feature;

use Database\Seeders\StarterPlansSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Billing\Enums\PlanKind;
use Modules\Billing\Models\Product;
use Modules\Billing\Services\PaymentGatewayManager;
use Tests\TestCase;

class StarterPlansSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeding_creates_visible_starter_plans_without_remote_calls_or_demo_accounts(): void
    {
        $this->mock(PaymentGatewayManager::class)->shouldNotReceive('driver');

        $this->seed(StarterPlansSeeder::class);

        $this->assertDatabaseCount('products', 2);
        $this->assertDatabaseCount('prices', 4);
        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('customers', 0);
        $this->assertDatabaseCount('subscriptions', 0);
        $this->assertSame(['free', 'pro'], Product::displayable()->pluck('slug')->all());
        $this->assertSame(PlanKind::Free, Product::where('slug', 'free')->firstOrFail()->kind);
        $this->assertSame(PlanKind::Subscription, Product::where('slug', 'pro')->firstOrFail()->kind);
        $this->assertDatabaseHas('prices', ['interval' => 'month', 'amount' => 2900, 'provider_price_id' => null]);
        $this->assertDatabaseHas('prices', ['interval' => 'year', 'amount' => 29000, 'provider_price_id' => null]);
    }

    public function test_reseeding_preserves_existing_plan_edits_and_provider_prices(): void
    {
        $this->seed(StarterPlansSeeder::class);
        $product = Product::where('slug', 'pro')->firstOrFail();
        $product->update(['name' => 'Custom Pro', 'is_visible' => false, 'provider' => 'stripe', 'provider_product_id' => 'prod_existing']);
        $price = $product->prices()->where('interval', 'month')->firstOrFail();
        $price->update(['provider' => 'stripe', 'provider_price_id' => 'price_existing', 'amount' => 3900, 'is_active' => false]);

        $this->seed(StarterPlansSeeder::class);

        $this->assertDatabaseCount('products', 2);
        $this->assertDatabaseCount('prices', 4);
        $this->assertDatabaseHas('products', ['id' => $product->id, 'name' => 'Custom Pro', 'is_visible' => false, 'provider_product_id' => 'prod_existing']);
        $this->assertDatabaseHas('prices', ['id' => $price->id, 'amount' => 3900, 'provider_price_id' => 'price_existing', 'is_active' => false]);
    }

    public function test_reseeding_does_not_restore_an_archived_plan(): void
    {
        $this->seed(StarterPlansSeeder::class);
        $product = Product::where('slug', 'pro')->firstOrFail();
        $product->delete();

        $this->seed(StarterPlansSeeder::class);

        $this->assertSoftDeleted($product);
        $this->assertDatabaseCount('products', 2);
        $this->assertDatabaseCount('prices', 4);
    }
}
