<?php

namespace App\Services\Assistant;

/**
 * The visitor's chat, kept in the session so it follows them from page to page. `context` holds what a
 * conversation needs to resolve "add it" or "the second one": the products last shown, the product in
 * focus, the last search and any question the assistant is waiting on.
 */
final class Conversation
{
    private const KEY = 'assistant';

    private const MAX_MESSAGES = 40;

    /**
     * @param  list<array<string, mixed>>  $messages
     * @param  array<string, mixed>  $context
     */
    private function __construct(public array $messages, public array $context) {}

    public static function load(): self
    {
        $stored = session(self::KEY, []);

        return new self($stored['messages'] ?? [], $stored['context'] ?? []);
    }

    public static function forget(): void
    {
        session()->forget(self::KEY);
    }

    public function addVisitorMessage(string $text): void
    {
        $this->messages[] = ['role' => 'user', 'text' => $text, 'products' => [], 'actions' => []];
    }

    public function addReply(Reply $reply): void
    {
        $this->messages[] = $reply->toArray();
    }

    public function save(): void
    {
        session([self::KEY => [
            'messages' => array_slice($this->messages, -self::MAX_MESSAGES),
            'context' => $this->context,
        ]]);
    }
}
