<?php

namespace App\Services\Assistant;

use App\Enums\OptionType;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductOption;
use App\Support\StorefrontCache;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Product search for the shop assistant. Category and colour names come from the database, so new
 * collections and colourways are understood without code changes. Words that name a category or a
 * colour become filters; the remaining words are scored against product names, categories and copy.
 */
class CatalogSearch
{
    /** Products shown per reply. */
    public const PAGE = 4;

    /**
     * @return array{products: Collection<int, Product>, categories: array<int, array>, colors: array<int, array>, relaxed: list<string>, terms: list<string>}
     */
    public function find(Query $query): array
    {
        $categories = $this->categories($query);
        $colors = $this->colors($query);
        $terms = $this->remainingTerms($query, $categories, $colors);

        $relaxed = [];
        $products = $this->run($query, $terms, $categories, $colors, $query->minPrice, $query->maxPrice);

        // Nothing matches everything: drop the colour, then the price limit, and say so.
        if ($products->isEmpty() && $colors) {
            $relaxed[] = 'colour';
            $products = $this->run($query, $terms, $categories, [], $query->minPrice, $query->maxPrice);
        }
        if ($products->isEmpty() && ($query->minPrice || $query->maxPrice)) {
            $relaxed[] = 'price';
            $products = $this->run($query, $terms, $categories, $colors && ! in_array('colour', $relaxed, true) ? $colors : [], null, null);
        }

        // Only the words that found something describe the result ("wallets for my father" → "wallets").
        // "Weak" means the words only appear in product copy, not in a name or category ("leather belt").
        $first = $products->first();
        if ($first) {
            $named = array_values(array_filter($terms, fn (string $term) => self::names($first, $term)));
            $mentioned = array_values(array_filter($terms, fn (string $term) => self::mentions($first, $term)));
            $weak = $mentioned && ! $named;
            $terms = $named ?: $mentioned;
        }

        // A colour no product comes in ("red wallet") is reported instead of silently ignored.
        $known = collect($this->vocabulary()['colors'])->flatMap(fn (array $color) => $color['tokens'])->all();
        $missingColour = $colors ? null : collect($query->words)
            ->first(fn (string $word) => in_array($word, Lexicon::COLOUR_WORDS, true) && ! in_array($word, $known, true) && ! in_array($word, $terms, true));
        if ($missingColour && $first && ! in_array('colour', $relaxed, true)) {
            $relaxed[] = 'colour';
        }

        return [
            'products' => $products, 'categories' => $categories, 'colors' => $colors, 'relaxed' => $relaxed,
            'terms' => $terms, 'weak' => $weak ?? false, 'missing_colour' => $missingColour,
        ];
    }

    /**
     * Search words that don't name a category or a colour.
     *
     * @return list<string>
     */
    public function contentTerms(Query $query): array
    {
        return $this->remainingTerms($query, $this->categories($query), $this->colors($query));
    }

    /** Whether a word appears in a product's name or category. */
    private static function names(Product $product, string $term): bool
    {
        return self::hit(self::tokens($product->name), $term)
            || self::hit(self::tokens(($product->category?->name ?? '').' '.str_replace('-', ' ', $product->category?->slug ?? '')), $term);
    }

    /** Whether a word appears anywhere in a product's name, category or copy. */
    private static function mentions(Product $product, string $term): bool
    {
        return self::names($product, $term) || self::hit(self::tokens($product->leather_type.' '.$product->short_description.' '.$product->sku), $term);
    }

    /**
     * The one product a message names ("saffiano card holder", "black crossbody bag"), or null.
     */
    public function named(Query $query): ?Product
    {
        $terms = $this->remainingTerms($query, [], $this->colors($query, partial: false));
        if (! $terms) {
            return null;
        }

        $candidates = $this->candidates($terms);

        // Words that appear in no product name ("warranty of the classic bifold wallet") are not part of the name.
        // Colour words stay: "red wallet" is a search for a colour, answered by find().
        $terms = array_filter($terms, fn (string $term) => in_array($term, Lexicon::COLOUR_WORDS, true)
            || $candidates->contains(fn (Product $product) => self::hit(self::tokens($product->name), $term)));
        if (! $terms) {
            return null;
        }

        $candidates = $candidates->filter(
            fn (Product $product) => collect($terms)->every(fn (string $term) => self::hit(self::tokens($product->name), $term))
        );

        if ($candidates->count() > 1) {
            // Several names contain the words; keep one only if its full name was typed.
            $words = array_flip($query->words);
            $candidates = $candidates->filter(fn (Product $product) => collect(self::tokens($product->name))
                ->every(fn (string $token) => isset($words[$token]) || Lexicon::isStopWord($token)));
        }

        return $candidates->count() === 1 ? $candidates->first() : null;
    }

    /**
     * Colour options named in a message. A colour matches by its full name ("midnight black") or,
     * when $partial, by its last word ("black" → Midnight Black and Carbon Black).
     *
     * @return array<int, array{id: int, name: string}>
     */
    public function colors(Query $query, bool $partial = true): array
    {
        $words = array_flip($query->words);
        $all = $this->vocabulary()['colors'];

        $full = array_values(array_filter($all, fn (array $color) => collect($color['tokens'])->every(fn (string $token) => isset($words[$token]))));
        if ($full || ! $partial) {
            return $full;
        }

        return array_values(array_filter($all, fn (array $color) => isset($words[last($color['tokens'])])));
    }

    /**
     * Link to the shop (or the category page) with the same filters, for "See all".
     *
     * @param  array{categories: array<int, array>, colors: array<int, array>, terms: list<string>}  $result
     */
    public function shopUrl(Query $query, array $result): string
    {
        $params = array_filter([
            'colors' => array_column($result['colors'], 'id'),
            'features' => array_values(array_intersect($query->features, ['nfc', 'rfid', 'engraving', 'sale'])),
            'min' => $query->minPrice,
            'max' => $query->maxPrice,
            'sort' => $query->sort,
            // The shop searches the whole phrase, so only a single leftover word is passed on.
            'q' => count($result['terms']) === 1 ? $result['terms'][0] : null,
        ]);

        if (count($result['categories']) === 1) {
            return route('categories.show', [$result['categories'][0]['slug'], ...$params]);
        }

        return route('shop', array_filter([...$params, 'cat' => array_column($result['categories'], 'slug')]));
    }

    /**
     * Categories a message names in full ("smart accessories", "card holders"). When several match,
     * the most specific wins: "smart accessories" picks Smart Accessories over Accessories.
     *
     * @return array<int, array{id: int, name: string, slug: string, tagline: ?string, description: ?string, count: int}>
     */
    public function categories(Query $query): array
    {
        $words = array_flip($query->words);
        $matches = [];

        foreach ($this->vocabulary()['categories'] as $category) {
            $size = 0;
            foreach ([$category['tokens'], $category['slug_tokens']] as $tokens) {
                if ($tokens && collect($tokens)->every(fn (string $token) => isset($words[$token]))) {
                    $size = max($size, count($tokens));
                }
            }
            if ($size) {
                $matches[] = [$size, $category];
            }
        }

        $best = $matches ? max(array_column($matches, 0)) : 0;

        return array_values(array_map(fn (array $match) => $match[1], array_filter($matches, fn (array $match) => $match[0] === $best)));
    }

    /** @return list<string> */
    private function remainingTerms(Query $query, array $categories, array $colors): array
    {
        $used = collect($categories)->flatMap(fn (array $category) => [...$category['tokens'], ...$category['slug_tokens']])
            ->merge(collect($colors)->flatMap(fn (array $color) => $color['tokens']))
            ->flip();

        return array_values(array_filter($query->terms, fn (string $term) => ! $used->has($term)));
    }

    /** @return Collection<int, Product> */
    private function run(Query $query, array $terms, array $categories, array $colors, ?int $min, ?int $max): Collection
    {
        // Without any filter or word there is nothing to search for.
        if (! $terms && ! $categories && ! $colors && ! $min && ! $max && ! $query->features && ! $query->sort) {
            return collect();
        }

        $price = 'CASE WHEN sale_price IS NOT NULL AND sale_price > 0 AND sale_price < regular_price THEN sale_price ELSE regular_price END';
        // With a filter, words that match nothing ("কম দামে ব্যাগ" → "দামে") must not empty the result.
        $filtered = $categories || $colors || $min || $max || $query->features;

        $filters = function (Builder $builder) use ($query, $categories, $colors, $min, $max, $price) {
            $categoryIds = array_column($categories, 'id');
            $builder
                ->when($categoryIds, fn ($builder) => $builder->where(fn ($builder) => $builder
                    ->whereIn('category_id', $categoryIds)
                    ->orWhereIn('category_id', Category::whereIn('parent_id', $categoryIds)->select('id'))))
                ->when($colors, fn ($builder) => $builder->whereHas('activeVariants', fn ($variants) => $variants->whereIn('color_id', array_column($colors, 'id'))))
                ->when(in_array('nfc', $query->features, true), fn ($builder) => $builder->where('nfc_enabled', true))
                ->when(in_array('rfid', $query->features, true), fn ($builder) => $builder->where('rfid_protection', true))
                ->when(in_array('engraving', $query->features, true), fn ($builder) => $builder->where('engraving_available', true))
                ->when(in_array('sale', $query->features, true), fn ($builder) => $builder->whereNotNull('sale_price')->whereColumn('sale_price', '<', 'regular_price'))
                ->when($min, fn ($builder) => $builder->whereRaw("{$price} >= ?", [$min]))
                ->when($max, fn ($builder) => $builder->whereRaw("{$price} <= ?", [$max]));
        };

        // A category or colour already narrows the list, so every product in it is scored.
        $products = $this->candidates($categories || $colors ? [] : $terms, $filters);
        if ($products->isEmpty() && $terms && $filtered) {
            $products = $this->candidates([], $filters);
        }

        $scored = $products->map(function (Product $product) use ($terms) {
            $name = self::tokens($product->name);
            $category = self::tokens(($product->category?->name ?? '').' '.str_replace('-', ' ', $product->category?->slug ?? ''));
            $copy = self::tokens($product->leather_type.' '.$product->short_description.' '.$product->sku);

            $covered = 0;
            $named = 0;
            $score = 0;
            foreach ($terms as $term) {
                $points = match (true) {
                    self::hit($name, $term) => 4,
                    self::hit($category, $term) => 3,
                    self::hit($copy, $term) => 1,
                    default => 0,
                };
                $covered += $points ? 1 : 0;
                $named += $points >= 3 ? 1 : 0;
                $score += $points;
            }

            return ['product' => $product, 'covered' => $covered, 'named' => $named, 'score' => $score];
        });

        // Keep the products that match the most words ("smart wallet" → smart wallets, not every wallet),
        // then prefer words found in names and categories over a passing mention in a description.
        if ($terms) {
            $best = $scored->max('covered');
            if ($best) {
                $scored = $scored->where('covered', $best);
                $scored = $scored->where('named', $scored->max('named'));
            } elseif (! $filtered) {
                $scored = collect();
            }
        }

        $priceOf = fn (array $row) => $row['product']->currentPrice();

        return $scored
            ->sort(function (array $a, array $b) use ($query, $priceOf) {
                $order = [$b['product']->isPurchasable() <=> $a['product']->isPurchasable()];
                $order[] = match ($query->sort) {
                    'price_asc' => $priceOf($a) <=> $priceOf($b),
                    'price_desc' => $priceOf($b) <=> $priceOf($a),
                    'popular' => $b['product']->sold_count <=> $a['product']->sold_count,
                    'rating' => [$b['product']->rating_avg, $b['product']->reviews_count] <=> [$a['product']->rating_avg, $a['product']->reviews_count],
                    'newest' => $b['product']->id <=> $a['product']->id,
                    default => $b['score'] <=> $a['score'],
                };
                $order[] = [$b['product']->is_featured, $b['product']->sold_count] <=> [$a['product']->is_featured, $a['product']->sold_count];

                return collect($order)->first(fn (int $result) => $result !== 0) ?? 0;
            })
            ->pluck('product')
            ->values();
    }

    /**
     * Visible products that mention at least one of the words (all products when there are none).
     *
     * @return Collection<int, Product>
     */
    private function candidates(array $terms, ?\Closure $filters = null): Collection
    {
        return Product::visible()->forCard()
            ->when($filters, $filters)
            ->when($terms, fn (Builder $builder) => $builder->where(function (Builder $builder) use ($terms) {
                foreach ($terms as $term) {
                    // "accessory" must find "Accessories": search the stem.
                    $like = '%'.str_replace(['%', '_'], ['\%', '\_'], preg_replace('/y$/', '', $term)).'%';
                    $builder->orWhere('name', 'like', $like)
                        ->orWhere('short_description', 'like', $like)
                        ->orWhere('leather_type', 'like', $like)
                        ->orWhere('sku', 'like', $like)
                        ->orWhereHas('category', fn (Builder $category) => $category->where('name', 'like', $like)->orWhere('slug', 'like', $like));
                }
            }))
            ->limit(300)
            ->get();
    }

    /**
     * Category and colour names, tokenised once and cached with the storefront.
     *
     * @return array{categories: array<int, array>, colors: array<int, array{id: int, name: string, tokens: list<string>}>}
     */
    private function vocabulary(): array
    {
        return StorefrontCache::remember('assistant-vocabulary', function () {
            $brand = self::tokens((string) setting('site_name', 'LeatherX'));

            return [
                'categories' => Category::active()->withCount('activeProducts')->orderBy('sort_order')
                    ->get(['id', 'name', 'slug', 'tagline', 'description'])
                    ->map(fn (Category $category) => [
                        'id' => $category->id,
                        'name' => $category->name,
                        'slug' => $category->slug,
                        'tagline' => $category->tagline,
                        'description' => $category->description,
                        'count' => $category->active_products_count,
                        'tokens' => array_values(array_diff(self::tokens($category->name), $brand)),
                        'slug_tokens' => self::tokens(str_replace('-', ' ', $category->slug)),
                    ])->all(),
                'colors' => ProductOption::ofType(OptionType::Color)->get(['id', 'name'])
                    ->map(fn (ProductOption $color) => ['id' => $color->id, 'name' => $color->name, 'tokens' => self::tokens($color->name)])
                    ->filter(fn (array $color) => $color['tokens'])
                    ->values()->all(),
            ];
        }, 1800);
    }

    /**
     * Normalised, singular words of a piece of text, without stop words.
     *
     * @return list<string>
     */
    public static function tokens(string $text): array
    {
        $normalized = Lexicon::normalize($text);

        return $normalized === '' ? [] : array_values(array_unique(array_filter(
            array_map(fn (string $word) => Lexicon::singular($word), explode(' ', $normalized)),
            fn (string $word) => ! Lexicon::isStopWord($word),
        )));
    }

    /** A word matches a token exactly, or as the start of a longer word ("bifold" in "bifolds"). */
    private static function hit(array $tokens, string $term): bool
    {
        foreach ($tokens as $token) {
            if ($token === $term || (mb_strlen($term) >= 4 && str_starts_with($token, $term))) {
                return true;
            }
        }

        return false;
    }
}
