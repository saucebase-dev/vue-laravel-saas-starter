<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Billing\Enums\BillingScheme;
use Modules\Billing\Enums\Currency;
use Modules\Billing\Enums\PlanKind;
use Modules\Billing\Models\Product;

class StarterPlansSeeder extends Seeder
{
    public function run(): void
    {
        $free = Product::withTrashed()->firstOrCreate(['slug' => 'free'], [
            'name' => 'Free',
            'sku' => 'free',
            'kind' => PlanKind::Free,
            'description' => 'Get started with the application.',
            'is_active' => true,
            'is_visible' => true,
            'display_order' => 1,
        ]);

        $pro = Product::withTrashed()->firstOrCreate(['slug' => 'pro'], [
            'name' => 'Pro',
            'sku' => 'pro',
            'kind' => PlanKind::Subscription,
            'description' => 'The paid subscription example. Customize it for your product.',
            'is_active' => true,
            'is_visible' => true,
            'is_highlighted' => true,
            'display_order' => 2,
        ]);

        foreach (['month' => 2900, 'year' => 29000] as $interval => $amount) {
            foreach ([[$free, 0], [$pro, $amount]] as [$product, $price]) {
                $product->prices()->firstOrCreate(['interval' => $interval], [
                    'currency' => Currency::default(),
                    'amount' => $price,
                    'billing_scheme' => BillingScheme::FlatRate,
                    'interval_count' => 1,
                    'is_active' => true,
                ]);
            }
        }
    }
}
