<?php

namespace App\Services\Assistant;

/**
 * One visitor message, parsed: normalised words, catalogue search terms, filters (price, features,
 * sort) and the references a shop conversation needs (order number, phone, quantity, "the second one").
 */
final class Query
{
    /**
     * @param  list<string>  $words  Every word, synonyms applied and singular
     * @param  list<string>  $terms  Words left for a catalogue search (no stop words, filters or numbers)
     * @param  list<string>  $features  Keys of Lexicon::FEATURES
     */
    private function __construct(
        public readonly string $raw,
        public readonly string $text,
        public readonly string $canonical,
        public readonly array $words,
        public readonly array $terms,
        public readonly array $features,
        public readonly ?string $sort,
        public readonly ?int $minPrice,
        public readonly ?int $maxPrice,
        public readonly ?int $quantity,
        public readonly ?int $ordinal,
        public readonly ?string $orderNumber,
        public readonly ?string $phone,
    ) {}

    public static function parse(string $raw): self
    {
        $raw = trim(strip_tags($raw));
        $working = strtr($raw, ['০' => '0', '১' => '1', '২' => '2', '৩' => '3', '৪' => '4', '৫' => '5', '৬' => '6', '৭' => '7', '৮' => '8', '৯' => '9']);

        // Order numbers look like LX261011A1B2C (see Order::generateNumber()).
        $orderNumber = null;
        if (preg_match('/\bLX[\s-]?(\d{6}[A-Z0-9]{3,10})\b/i', $working, $match)) {
            $orderNumber = 'LX'.strtoupper($match[1]);
            $working = str_replace($match[0], ' ', $working);
        }

        $phone = null;
        if (preg_match('/(?<!\d)(?:\+?88[\s-]?)?(01[3-9](?:[\s-]?\d){8})(?!\d)/', $working, $match)) {
            $phone = normalize_phone($match[1]);
            $working = str_replace($match[0], ' ', $working);
        }

        $text = Lexicon::normalize($working);

        $words = [];
        foreach ($text === '' ? [] : explode(' ', $text) as $word) {
            foreach (explode(' ', Lexicon::synonym($word) ?? $word) as $part) {
                $words[] = Lexicon::singular($part);
            }
        }
        $canonical = implode(' ', $words);

        $features = array_keys(array_filter(Lexicon::FEATURES, fn (array $phrases) => Lexicon::contains($text, $phrases) || Lexicon::contains($canonical, $phrases)));
        $sort = collect(Lexicon::SORTS)->search(fn (array $phrases) => Lexicon::contains($text, $phrases) || Lexicon::contains($canonical, $phrases)) ?: null;
        [$min, $max] = self::priceRange($text);

        // Words that only set a filter or point at something are not search terms.
        $filterWords = collect(Lexicon::FEATURES)->only($features)->flatten()
            ->merge(Lexicon::POINTERS)->merge(Lexicon::GREETING)->merge(Lexicon::THANKS)
            ->merge(array_keys(Lexicon::ORDINALS))->merge(array_keys(Lexicon::NUMBER_WORDS))
            ->flatMap(fn (string $phrase) => explode(' ', Lexicon::normalize($phrase)))
            ->map(fn (string $word) => Lexicon::singular($word))
            ->flip();

        $terms = collect($words)
            ->reject(fn (string $word) => Lexicon::isStopWord($word) || $filterWords->has($word) || preg_match('/\d/', $word) || mb_strlen($word) < 2)
            ->unique()->values()->all();

        return new self(
            raw: $raw,
            text: $text,
            canonical: $canonical,
            words: $words,
            terms: $terms,
            features: $features,
            sort: $sort,
            minPrice: $min,
            maxPrice: $max,
            quantity: self::quantity($text),
            ordinal: self::ordinal($text),
            orderNumber: $orderNumber,
            phone: $phone,
        );
    }

    /** @param array<int, string> $phrases */
    public function has(array $phrases): bool
    {
        return Lexicon::contains($this->text, $phrases) || Lexicon::contains($this->canonical, $phrases);
    }

    public function isQuestion(): bool
    {
        if (str_ends_with($this->raw, '?')) {
            return true;
        }

        $first = strtok($this->text, ' ');

        return $first !== false && Lexicon::contains($first, Lexicon::QUESTION);
    }

    public function hasFilters(): bool
    {
        return $this->features || $this->sort || $this->minPrice || $this->maxPrice;
    }

    /**
     * Words worth comparing with FAQ questions: everything except stop words (filter words stay).
     *
     * @return list<string>
     */
    public function keywords(): array
    {
        return collect($this->words)->reject(fn (string $word) => Lexicon::isStopWord($word) || mb_strlen($word) < 2)->unique()->values()->all();
    }

    /*
     * The patterns below run on normalised text (words separated by single spaces), so (?<!\S) and (?!\S)
     * mark word edges: \b only knows ASCII letters and would miss Bangla words.
     */

    /** @return array{0: ?int, 1: ?int} */
    private static function priceRange(string $text): array
    {
        $tk = '(?:(?:tk|taka|takar|টাকা|টাকার)\s)?';
        $amount = '(\d{3,6})(?!\S)';

        if (preg_match("/(?<!\S){$tk}{$amount}\s{$tk}(?:(?:to|and|theke|থেকে)\s)?{$tk}{$amount}/u", $text, $m) && (int) $m[1] < (int) $m[2]) {
            return [(int) $m[1], (int) $m[2]];
        }

        if (preg_match("/(?<!\S)(?:under|below|less than|lower than|within|upto|up to|max|maximum|budget|not more than)\s{$tk}{$amount}/u", $text, $m)
            || preg_match("/(?<!\S){$amount}\s{$tk}(?:(?:er|r|এর)\s)?(?:moddhe|modhe|vitore|bhitore|niche|nice|kome|or less|or below|max|মধ্যে|ভিতরে|নিচে|কমে)(?!\S)/u", $text, $m)) {
            return [null, (int) $m[1]];
        }

        if (preg_match("/(?<!\S)(?:over|above|more than|at least|min|minimum|starting|starting at)\s{$tk}{$amount}/u", $text, $m)
            || preg_match("/(?<!\S){$amount}\s{$tk}(?:(?:er|এর)\s)?(?:upore|opore|beshi|or more|plus|উপরে|বেশি)(?!\S)/u", $text, $m)) {
            return [(int) $m[1], null];
        }

        if (preg_match("/(?<!\S)(?:around|about|approx|approximately|near)\s{$tk}{$amount}/u", $text, $m)
            || preg_match("/(?<!\S){$amount}\s{$tk}(?:er\s)?(?:kache|kachakachi|কাছাকাছি)(?!\S)/u", $text, $m)) {
            return [(int) round($m[1] * 0.8), (int) round($m[1] * 1.2)];
        }

        return [null, null];
    }

    private static function quantity(string $text): ?int
    {
        if (preg_match('/(?<!\S)(?:add|qty|quantity)\s(\d{1,2})(?!\S)|(?<!\S)(\d{1,2})\s?(?:x|pcs|pc|piece|pieces|unit|units|ta|ti|টা|টি)(?!\S)|(?<!\S)x\s?(\d{1,2})(?!\S)/u', $text, $m)) {
            $quantity = (int) ($m[1] ?: ($m[2] ?? '') ?: ($m[3] ?? 0));

            return $quantity > 0 ? $quantity : null;
        }

        foreach (Lexicon::NUMBER_WORDS as $word => $number) {
            if ($number > 1 && Lexicon::contains($text, [$word])) {
                return $number;
            }
        }

        return null;
    }

    private static function ordinal(string $text): ?int
    {
        if (preg_match('/(?<!\S)(?:number|no|nombor|nambar|নম্বর)\s?(\d)(?!\S)|(?<!\S)(\d)\s?(?:number|nombor|nambar|no|নম্বর)(?!\S)/u', $text, $m)) {
            return max(0, (int) ($m[1] ?: $m[2]) - 1);
        }

        foreach (Lexicon::ORDINALS as $word => $index) {
            if (Lexicon::contains($text, [$word])) {
                return $index;
            }
        }

        return null;
    }
}
