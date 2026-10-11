<?php

namespace App\Support;

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

    /** Comparison-table rows that can be read from a collection's products, keyed for HomeSection content. */
    public const METRICS = [
        'leather' => 'Leather',
        'nfc' => 'LeatherX Tap™ NFC',
        'rfid' => 'RFID protection',
        'cards' => 'Card capacity',
        'notes' => 'Cash capacity',
        'warranty' => 'Warranty',
        'price' => 'Price range',
        'engraving' => 'Engraving',
    ];

    /**
     * One comparison-table value for a collection. Booleans become "Yes", "Selected pieces" or "—".
     *
     * @param  Collection<int, Product>  $products
     */
    public static function metric(string $metric, Collection $products): string
    {
        return match ($metric) {
            'leather' => $products->map(fn (Product $product) => $product->namedLeather())->filter()->unique()->take(2)->implode(', ') ?: '—',
            'nfc' => self::share($products, 'nfc_enabled'),
            'rfid' => self::share($products, 'rfid_protection'),
            'cards' => $products->max('card_capacity') ? 'Up to '.$products->max('card_capacity').' cards' : '—',
            'notes' => $products->max('cash_capacity') ? 'Up to '.$products->max('cash_capacity').' notes' : '—',
            'warranty' => self::warrantyYears($products) ?? '—',
            'price' => self::priceRange($products),
            'engraving' => self::share($products, 'engraving_available'),
            default => '—',
        };
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

    /** Warranty for a table cell, e.g. "Up to 2 years" or "1 year". @param  Collection<int, Product>  $products */
    private static function warrantyYears(Collection $products): ?string
    {
        $months = $products->pluck('warranty_months')->filter();
        if ($months->isEmpty()) {
            return null;
        }
        $max = (int) $months->max();
        $text = $max % 12 === 0 ? ($max / 12).' '.str('year')->plural($max / 12) : $max.' months';

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
