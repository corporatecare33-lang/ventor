<?php

namespace App\Support;

/**
 * Permission map for the admin panel. Each area has a "view" and a "manage" permission.
 * Roles are seeded from ROLES; super_admin bypasses every check via Gate::before.
 */
class Permissions
{
    public const AREAS = [
        'catalog' => 'Products, categories, variants, stock & reviews',
        'orders' => 'Orders, payments & carts',
        'customers' => 'Customer accounts',
        'marketing' => 'Coupons, banners & homepage sections',
        'services' => 'Engraving, NFC profiles, warranty, corporate & contact',
        'content' => 'CMS pages, blog & FAQ',
        'settings' => 'Shipping, payment methods, website & SEO settings',
        'reports' => 'Sales & stock reports',
        'users' => 'Admin users, roles & permissions',
    ];

    /** @return array<string, array<int, string>> role => permissions */
    public static function roles(): array
    {
        $all = self::all();

        return [
            'super_admin' => $all,
            'admin' => array_values(array_diff($all, ['manage users'])),
            'manager' => [
                'view catalog', 'manage catalog', 'view orders', 'manage orders', 'view customers',
                'view marketing', 'manage marketing', 'view services', 'manage services',
                'view content', 'manage content', 'view reports',
            ],
            'support' => [
                'view catalog', 'view orders', 'view customers', 'view services', 'manage services',
            ],
            'customer' => [],
        ];
    }

    /** @return array<int, string> */
    public static function all(): array
    {
        return collect(array_keys(self::AREAS))
            ->flatMap(fn (string $area) => ["view {$area}", "manage {$area}"])
            ->all();
    }
}
