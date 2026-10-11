<?php

namespace App\Livewire;

use App\Models\Product;
use App\Services\Assistant\Conversation;
use App\Services\Assistant\Reply;
use App\Services\Assistant\ShopAssistant;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Livewire\Component;

/**
 * Floating shop assistant (see App\Services\Assistant\ShopAssistant). The conversation lives in the
 * session; it is only rendered once the visitor opens the panel, so other page loads stay light.
 */
class AssistantChat extends Component
{
    /** Messages per minute per visitor. */
    private const RATE = 20;

    public bool $ready = false;

    public function load(): void
    {
        $this->ready = true;
    }

    public function send(string $message, ShopAssistant $assistant): void
    {
        $message = Str::limit(trim(strip_tags($message)), 300, '');
        if ($message === '') {
            return;
        }

        $this->respond($assistant, $message, fn (Conversation $conversation) => $assistant->reply($message, $conversation));
    }

    public function add(int $product, ?int $variant, int $quantity, string $label, ShopAssistant $assistant): void
    {
        $label = Str::limit(trim(strip_tags($label)), 160, '') ?: 'Add to cart';

        $this->respond($assistant, $label, fn (Conversation $conversation) => $assistant->addToCart($product, $variant, $quantity, $conversation));
    }

    public function restart(): void
    {
        Conversation::forget();
    }

    private function respond(ShopAssistant $assistant, string $visitorMessage, \Closure $answer): void
    {
        $this->ready = true;
        $conversation = Conversation::load();
        // The greeting shown before the first message stays at the top of the conversation.
        if (! $conversation->messages) {
            $conversation->addReply($assistant->welcome());
        }
        $conversation->addVisitorMessage($visitorMessage);

        $key = 'assistant:'.session()->getId();
        if (RateLimiter::tooManyAttempts($key, self::RATE)) {
            $reply = new Reply('You are sending messages very quickly. Please wait a moment and try again.');
        } else {
            RateLimiter::hit($key, 60);
            $reply = $answer($conversation);
        }

        $conversation->addReply($reply);
        $conversation->save();

        if ($reply->cartUpdated) {
            $this->dispatch('cart-updated');
        }
    }

    /** Assistant text: escaped, with **bold** and "• " lines turned into a list. */
    public static function format(string $text): HtmlString
    {
        $html = '';
        $list = [];
        $flush = function () use (&$html, &$list) {
            if ($list) {
                $html .= '<ul class="mt-2 space-y-1.5">'.implode('', $list).'</ul>';
                $list = [];
            }
        };

        foreach (preg_split('/\n/', $text) as $line) {
            $line = preg_replace('/\*\*(.+?)\*\*/s', '<strong class="font-semibold text-ink-950">$1</strong>', e($line));
            if (str_starts_with($line, '• ')) {
                $list[] = '<li class="flex gap-2"><span class="text-ink-500" aria-hidden="true">•</span><span>'.substr($line, strlen('• ')).'</span></li>';

                continue;
            }
            $flush();
            if (trim($line) !== '') {
                $html .= '<p class="[&:not(:first-child)]:mt-2">'.$line.'</p>';
            }
        }
        $flush();

        return new HtmlString($html);
    }

    public function render()
    {
        $messages = [];
        $products = collect();

        if ($this->ready) {
            $messages = Conversation::load()->messages ?: [app(ShopAssistant::class)->welcome()->toArray()];
            $ids = collect($messages)->pluck('products')->flatten()->unique()->values();
            $products = $ids->isEmpty() ? collect() : Product::visible()->forCard()->whereIn('id', $ids)->get()->keyBy('id');
        }

        return view('livewire.assistant-chat', [
            'messages' => $messages,
            'products' => $products,
            'name' => setting('site_name', 'LeatherX'),
        ]);
    }
}
