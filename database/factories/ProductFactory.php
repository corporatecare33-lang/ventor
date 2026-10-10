<?php

namespace Database\Factories;

use App\Enums\ProductStatus;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Product> */
class ProductFactory extends Factory
{
    public function definition(): array
    {
        $name = ucfirst(fake()->unique()->words(3, true)).' Wallet';

        return [
            'category_id' => Category::factory(),
            'name' => $name,
            'slug' => Str::slug($name).'-'.Str::lower(Str::random(4)),
            'sku' => 'LX-T-'.Str::upper(Str::random(8)),
            'short_description' => 'Hand-finished full-grain leather.',
            'description' => '<p>Test product.</p>',
            'regular_price' => 2000,
            'sale_price' => null,
            'stock_quantity' => 20,
            'leather_type' => 'Full-grain cowhide',
            'rfid_protection' => true,
            'nfc_enabled' => false,
            'warranty_months' => 12,
            'engraving_available' => true,
            'engraving_price' => 300,
            'status' => ProductStatus::Active,
        ];
    }

    public function nfc(): static
    {
        return $this->state(['nfc_enabled' => true]);
    }

    public function draft(): static
    {
        return $this->state(['status' => ProductStatus::Draft]);
    }
}
