<?php

namespace App\Services\Assistant;

/**
 * One assistant message: text (with **bold** and "• " bullet lines), product cards by id, and actions —
 * suggestion chips that send a message, add-to-cart buttons and links.
 */
final class Reply
{
    /**
     * @param  list<int>  $products
     * @param  list<array<string, mixed>>  $actions
     */
    public function __construct(
        public string $text,
        public array $products = [],
        public array $actions = [],
        public bool $cartUpdated = false,
    ) {}

    /** A chip that sends $message (or the label itself) as the visitor's next message. */
    public static function ask(string $label, ?string $message = null): array
    {
        return ['type' => 'ask', 'label' => $label, 'message' => $message ?? $label];
    }

    public static function link(string $label, string $url, bool $primary = false): array
    {
        return ['type' => 'link', 'label' => $label, 'url' => $url, 'primary' => $primary, 'external' => ! str_starts_with($url, url('/'))];
    }

    public static function add(string $label, int $product, ?int $variant, string $message, int $quantity = 1): array
    {
        return ['type' => 'add', 'label' => $label, 'product' => $product, 'variant' => $variant, 'quantity' => $quantity, 'message' => $message];
    }

    /** @return array{role: string, text: string, products: list<int>, actions: list<array<string, mixed>>} */
    public function toArray(): array
    {
        return ['role' => 'assistant', 'text' => $this->text, 'products' => $this->products, 'actions' => $this->actions];
    }
}
