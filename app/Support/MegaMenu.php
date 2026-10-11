<?php

namespace App\Support;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Collection;

/**
 * The desktop mega menu (Ventro-style): each top-level item groups a few categories.
 * Category names are rendered with every page; the product rows are fetched on first
 * open from /menu/{group}, so pages don't carry dozens of hidden product cards.
 */
class MegaMenu
{
    /** @var array<string, array{label: string, categories: array<int, string>}> */
    public const GROUPS = [
        'wallets' => ['label' => 'Wallets', 'categories' => ['smart-wallets', 'classic-wallets', 'card-holders', 'passport-holders']],
        'bags' => ['label' => 'Bags', 'categories' => ['bags']],
        'accessories' => ['label' => 'Smart Accessories', 'categories' => ['smart-accessories', 'key-holders', 'accessories']],
    ];

    /** Products shown per category in the menu row. */
    public const PRODUCTS = 6;

    /**
     * Groups with their live categories (in the configured order). Groups without any
     * category that has live products are left out.
     *
     * @param  Collection<int, Category>  $categories  Categories with active_products_count
     * @return array<string, array{label: string, categories: Collection<int, Category>}>
     */
    public static function groups(Collection $categories): array
    {
        $bySlug = $categories->filter(fn (Category $category) => $category->active_products_count > 0)->keyBy('slug');

        $groups = [];
        foreach (self::GROUPS as $key => $group) {
            $live = collect($group['categories'])->map(fn (string $slug) => $bySlug->get($slug))->filter()->values();
            if ($live->isNotEmpty()) {
                $groups[$key] = ['label' => $group['label'], 'categories' => $live];
            }
        }

        return $groups;
    }

    /**
     * The most popular products of each category in a group, for the menu's product row.
     *
     * @return Collection<int, array{category: Category, products: Collection<int, Product>}>
     */
    public static function products(string $group): Collection
    {
        $categories = Category::active()->whereIn('slug', self::GROUPS[$group]['categories'] ?? [])
            ->withCount('activeProducts')->get(['id', 'name', 'slug']);

        return collect(self::GROUPS[$group]['categories'] ?? [])
            ->map(fn (string $slug) => $categories->firstWhere('slug', $slug))
            ->filter(fn (?Category $category) => $category && $category->active_products_count > 0)
            ->map(fn (Category $category) => [
                'category' => $category,
                'products' => Product::visible()->forCard()->where('category_id', $category->id)
                    ->orderByDesc('sold_count')->orderByDesc('is_featured')->orderBy('sort_order')
                    ->limit(self::PRODUCTS)->get(),
            ])
            ->values();
    }
}
