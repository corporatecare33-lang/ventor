<?php

namespace App\Support;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Collection;

/**
 * The homepage comparison panel (Ventro's "Genesis vs Owen"): two categories side by side, set up
 * in the admin on the `comparison` HomeSection. A row either reads its values from each category's
 * live products (CollectionSummary::METRICS) or shows what the admin typed; a typed value wins.
 * Rows where both columns match move to the "included in both" line, followed by the admin's extra
 * shared features. Nothing is shown unless both categories are live and at least one row differs.
 */
class Comparison
{
    /** Compared until the admin picks two categories. */
    public const DEFAULT_CATEGORIES = ['smart-wallets', 'classic-wallets'];

    public const DEFAULT_ROWS = ['leather', 'nfc', 'rfid', 'cards', 'warranty', 'price', 'engraving'];

    public const DEFAULT_EYEBROW = 'Compare';

    public const DEFAULT_INCLUDED_LABEL = 'Included in both';

    /** Typed cell values that mean a check mark or a dash. */
    private const YES = ['yes', '✓', '✔'];

    private const NO = ['no', '-', '–', '—'];

    /**
     * Section content before the admin has saved any: the default categories and catalogue rows.
     *
     * @return array<string, mixed>
     */
    public static function defaults(): array
    {
        $ids = Category::whereIn('slug', self::DEFAULT_CATEGORIES)->pluck('id', 'slug');

        return [
            'eyebrow' => self::DEFAULT_EYEBROW,
            'first_category_id' => $ids[self::DEFAULT_CATEGORIES[0]] ?? null,
            'second_category_id' => $ids[self::DEFAULT_CATEGORIES[1]] ?? null,
            'rows' => array_map(fn (string $metric) => ['metric' => $metric, 'label' => null, 'first' => null, 'second' => null, 'highlight' => null], self::DEFAULT_ROWS),
            'included_label' => self::DEFAULT_INCLUDED_LABEL,
            'included' => [],
        ];
    }

    /**
     * View data for the panel, or null when there is nothing honest to compare.
     *
     * @param  array<string, mixed>  $content  The `comparison` HomeSection's content
     * @return array{eyebrow: string, columns: array<int, array{category: Category, label: string, button: string}>, rows: array<int, array{label: string, values: array{0: string, 1: string}, highlight: ?int}>, included_label: string, included: array<int, string>}|null
     */
    public static function build(array $content): ?array
    {
        $content += self::defaults();
        $ids = [(int) $content['first_category_id'], (int) $content['second_category_id']];
        $categories = Category::active()->whereKey($ids)->get()->keyBy('id');
        if ($ids[0] === $ids[1] || $categories->count() < 2) {
            return null;
        }

        $products = array_map(fn (int $id) => self::products($id), $ids);
        if ($products[0]->isEmpty() || $products[1]->isEmpty()) {
            return null;
        }

        $rows = collect($content['rows'] ?? [])
            ->map(fn (array $row) => self::row($row, $products))
            ->filter(fn (array $row) => $row['label'] !== '')
            // A row neither collection has says nothing.
            ->reject(fn (array $row) => $row['values'][0] === '—' && $row['values'][1] === '—');
        [$shared, $different] = $rows->partition(fn (array $row) => $row['values'][0] === $row['values'][1]);
        if ($different->isEmpty()) {
            return null;
        }

        $columns = [];
        foreach (['first', 'second'] as $i => $column) {
            $label = self::text($content[$column.'_label'] ?? null) ?? $categories[$ids[$i]]->name;
            $columns[] = [
                'category' => $categories[$ids[$i]],
                'label' => $label,
                'button' => self::text($content[$column.'_button'] ?? null) ?? 'Shop '.$label,
            ];
        }

        return [
            'eyebrow' => self::text($content['eyebrow'] ?? null) ?? self::DEFAULT_EYEBROW,
            'columns' => $columns,
            'rows' => $different->values()->all(),
            'included_label' => self::text($content['included_label'] ?? null) ?? self::DEFAULT_INCLUDED_LABEL,
            'included' => $shared
                ->map(fn (array $row) => $row['values'][0] === 'Yes' ? $row['label'] : $row['label'].': '.$row['values'][0])
                ->merge(collect($content['included'] ?? [])->map(fn ($item) => self::text($item))->filter())
                ->unique()->values()->all(),
        ];
    }

    /** What a catalogue row shows for one category: the admin form's placeholder. */
    public static function preview(?string $metric, mixed $categoryId): ?string
    {
        if (! isset(CollectionSummary::METRICS[$metric]) || ! is_numeric($categoryId)) {
            return null;
        }

        return CollectionSummary::metric($metric, once(fn () => self::products((int) $categoryId)));
    }

    /**
     * @param  array<string, mixed>  $row
     * @param  array{0: Collection<int, Product>, 1: Collection<int, Product>}  $products
     * @return array{label: string, values: array{0: string, 1: string}, highlight: ?int}
     */
    private static function row(array $row, array $products): array
    {
        $metric = $row['metric'] ?? null;
        $isCatalogue = isset(CollectionSummary::METRICS[$metric]);

        $values = [];
        foreach (['first', 'second'] as $i => $column) {
            $typed = self::text($row[$column] ?? null);
            $values[] = match (true) {
                $typed !== null && in_array(mb_strtolower($typed), self::YES, true) => 'Yes',
                $typed !== null && in_array(mb_strtolower($typed), self::NO, true) => '—',
                $typed !== null => $typed,
                $isCatalogue => CollectionSummary::metric($metric, $products[$i]),
                default => '—',
            };
        }

        return [
            'label' => self::text($row['label'] ?? null) ?? ($isCatalogue ? CollectionSummary::METRICS[$metric] : ''),
            'values' => $values,
            'highlight' => ['first' => 0, 'second' => 1][$row['highlight'] ?? ''] ?? null,
        ];
    }

    /** @return Collection<int, Product> */
    private static function products(int $categoryId): Collection
    {
        return Product::visible()->where('category_id', $categoryId)->get();
    }

    private static function text(mixed $value): ?string
    {
        $value = is_scalar($value) ? trim((string) $value) : '';

        return $value === '' ? null : $value;
    }
}
