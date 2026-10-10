<?php

namespace Tests;

use App\Models\Product;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\StoreSetupSeeder;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /** Roles, shipping zones, payment methods and coupons used by most tests. */
    protected function seedStore(): void
    {
        $this->seed([RolesAndPermissionsSeeder::class, StoreSetupSeeder::class]);
    }

    protected function customer(array $attributes = []): User
    {
        $user = User::factory()->create(['phone' => '01712345678', ...$attributes]);
        $user->assignRole('customer');

        return $user;
    }

    protected function staff(string $role = 'super_admin'): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    protected function product(array $attributes = []): Product
    {
        return Product::factory()->create($attributes);
    }
}
