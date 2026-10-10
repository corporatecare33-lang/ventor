<?php

namespace App\Support;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Collection;

/**
 * Summaries of a product collection (a category) for the homepage: the feature line shown
 * under each collection title and the side-by-side comparison table. Every value is derived
 * from the products themselves, so nothing is claimed that the catalogue does not support.
 */
class CollectionSummary
{
    /**
     * @param  Collection<int, Product>  $products
     * @return array<int, array{label: string, url: ?string}>
     */
    public static function features(Collection $products): array
    {
        if ($products->isEmpty()) {
            return [];
        }

        $leathers = $products->map(fn (Product $p) => $p->namedLeather())->filter()->unique()->values();
        $cards = (int) $products->max('card_capacity');
        $notes = (int) $products->max('cash_capacity');

        return array_values(array_filter([
            $products->contains('nfc_enabled', true) ? ['label' => 'LeatherX Tap™ enabled', 'url' => route('tap')] : null,
            $leathers->isNotEmpty() ? ['label' => $leathers->take(2)->implode(' & '), 'url' => null] : null,
            ($warranty = self::warranty($products)) ? ['label' => $warranty, 'url' => route('warranty')] : null,
            $cards ? ['label' => ($products->pluck('card_capacity')->filter()->unique()->count() > 1 ? 'Up to ' : '').$cards.' cards', 'url' => null] : null,
            $notes ? ['label' => 'Up to '.$notes.' notes', 'url' => null] : null,
            $products->contains('rfid_protection', true) ? ['label' => 'RFID protection', 'url' => null] : null,
            $products->contains('engraving_available', true) ? ['label' => 'Personalisation options', 'url' => route('engraving')] : null,
        ]));
    }

    /**
     * Rows comparing two collections. Values are strings; booleans become "Yes", "Selected pieces" or "—".
     *
     * @param  Collection<int, Product>  $a
     * @param  Collection<int, Product>  $b
     * @return array<int, array{label: string, values: array{0: string, 1: string}}>
     */
    public static function compare(Category $first, Collection $a, Category $second, Collection $b): array
    {
        $rows = [
            'Leather' => fn (Collection $p) => $p->map(fn (Product $product) => $product->namedLeather())->filter()->unique()->take(2)->implode(', ') ?: '—',
            'LeatherX Tap™ NFC' => fn (Collection $p) => self::share($p, 'nfc_enabled'),
            'RFID protection' => fn (Collection $p) => self::share($p, 'rfid_protection'),
            'Card capacity' => fn (Collection $p) => $p->max('card_capacity') ? 'Up to '.$p->max('card_capacity').' cards' : '—',
            'Warranty' => fn (Collection $p) => self::warranty($p) ?? '—',
            'Price range' => fn (Collection $p) => self::priceRange($p),
            'Engraving' => fn (Collection $p) => self::share($p, 'engraving_available'),
        ];

        $result = [];
        foreach ($rows as $label => $value) {
            $result[] = ['label' => $label, 'values' => [$value($a), $value($b)]];
        }
        if ($first->tagline || $second->tagline) {
            $result[] = ['label' => 'In short', 'values' => [$first->tagline ?: '—', $second->tagline ?: '—']];
        }

        return $result;
    }

    /** @param  Collection<int, Product>  $products */
    private static function warranty(Collection $products): ?string
    {
        $months = $products->pluck('warranty_months')->filter();
        if ($months->isEmpty()) {
            return null;
        }
        $max = (int) $months->max();
        $text = $max % 12 === 0 ? ($max / 12).'-year warranty' : $max.'-month warranty';

        return ($months->unique()->count() > 1 ? 'Up to ' : '').$text;
    }

    /** @param  Collection<int, Product>  $products */
    private static function share(Collection $products, string $attribute): string
    {
        $count = $products->where($attribute, true)->count();

        return match (true) {
            $count === 0 => '—',
            $count === $products->count() => 'Yes',
            default => 'Selected pieces',
        };
    }

    /** @param  Collection<int, Product>  $products */
    private static function priceRange(Collection $products): string
    {
        $prices = $products->map(fn (Product $p) => $p->currentPrice());
        if ($prices->isEmpty()) {
            return '—';
        }

        return $prices->min() === $prices->max() ? money($prices->min()) : money($prices->min()).' – '.money($prices->max());
    }
}
