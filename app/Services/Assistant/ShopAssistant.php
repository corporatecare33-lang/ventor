<?php

namespace App\Services\Assistant;

use App\Enums\OrderStatus;
use App\Models\Faq;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ShippingZone;
use App\Services\CartService;
use App\Support\StorefrontCache;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * The storefront chat assistant. It runs on the server with no external AI service, so it costs nothing
 * per message. It reads a message (English, Banglish or Bangla), works out what the visitor wants and
 * answers only from the store's own data — products, the cart, orders, FAQs, delivery zones, payment
 * methods and contact settings — so it never invents prices, stock or policies.
 */
class ShopAssistant
{
    public function __construct(private CartService $cart, private CatalogSearch $catalog) {}

    public function welcome(): Reply
    {
        return new Reply(
            "Hi! I'm the {$this->name()} assistant. I can find products, add them to your cart, track an order and answer questions about delivery, payment, returns and warranty.",
            actions: $this->suggestions(),
        );
    }

    public function reply(string $message, Conversation $conversation): Reply
    {
        $query = Query::parse($message);

        if ($reply = $this->answerPending($query, $conversation)) {
            return $reply;
        }

        return match (true) {
            $query->has(Lexicon::ADD) && ! $query->has(['how', 'kivabe', 'কিভাবে']) => $this->addFromMessage($query, $conversation),
            $query->has(Lexicon::CHECKOUT) => $this->checkout(),
            $query->orderNumber !== null || $query->has(Lexicon::TRACK) => $this->track($query, $conversation),
            $query->has(Lexicon::CART) => $this->cartSummary(),
            $query->has(Lexicon::MORE) && ! $this->catalog->contentTerms($query) && isset($conversation->context['search']) => $this->more($conversation),
            default => $this->answer($query, $conversation),
        };
    }

    /** Add-to-cart buttons in the chat (product cards and option chips). */
    public function addToCart(int $productId, ?int $variantId, int $quantity, Conversation $conversation): Reply
    {
        unset($conversation->context['awaiting']);

        $product = $this->product($productId);
        if (! $product) {
            return new Reply('Sorry, that product is no longer available.', actions: $this->suggestions());
        }

        $variant = $variantId ? $product->activeVariants->firstWhere('id', $variantId) : null;
        if ($variantId && ! $variant) {
            return new Reply('Sorry, that option is no longer available.', products: [$product->id]);
        }

        return $this->add($product, $variant?->setRelation('product', $product), $quantity, $conversation);
    }

    private function answer(Query $query, Conversation $conversation): Reply
    {
        $contentTerms = $this->catalog->contentTerms($query);

        $topic = match (true) {
            ($query->has(Lexicon::GREETING) || $query->has(Lexicon::HELP)) && ! $query->terms && ! $query->hasFilters() => $this->welcome(),
            $query->has(Lexicon::THANKS) && ! $query->terms => new Reply("You're welcome! Is there anything else I can help with?", actions: $this->suggestions()),
            $query->has(Lexicon::CONTACT) => $this->contact(),
            $query->has(Lexicon::COUPON) => $this->coupons(),
            $query->has(Lexicon::DELIVERY) => $this->delivery(),
            $query->has(Lexicon::PAYMENT) => $this->payment(),
            $query->has(Lexicon::RETURNS) => $this->returns($query),
            $query->has(Lexicon::WARRANTY) => $this->warranty($query, $conversation),
            $query->has(Lexicon::CORPORATE) => $this->corporate($conversation),
            $query->has(Lexicon::NFC_INFO) => $this->tapInfo($query),
            $query->has(Lexicon::MATERIAL) && ! $this->resolveProduct($query, $conversation) => $this->materials(),
            in_array('engraving', $query->features, true) && $query->isQuestion() && ! $contentTerms => $this->engravingInfo($query),
            $query->has(Lexicon::BROWSE) && ! $contentTerms && ! $query->hasFilters() => $this->listing(Query::parse('best sellers'), $this->catalog->find(Query::parse('best sellers')), $conversation),
            default => null,
        };

        if ($topic) {
            return $topic;
        }

        if ($product = $this->resolveProduct($query, $conversation)) {
            return $this->details($product, $conversation, $this->catalog->colors($query));
        }

        if ($query->isQuestion() && ($faq = $this->faq($query))) {
            return $this->faqReply($faq);
        }

        $result = $this->catalog->find($query);
        if ($result['products']->isNotEmpty()) {
            return $this->listing($query, $result, $conversation);
        }

        if ($faq = $this->faq($query)) {
            return $this->faqReply($faq);
        }

        if (in_array('engraving', $query->features, true)) {
            return $this->engravingInfo($query);
        }

        return $this->fallback($query);
    }

    /** A message that answers the question the assistant asked last (an option, a phone number…). */
    private function answerPending(Query $query, Conversation $conversation): ?Reply
    {
        $pending = $conversation->context['awaiting'] ?? null;
        unset($conversation->context['awaiting']);

        if (($pending['type'] ?? null) === 'variant' && ($product = $this->product($pending['product']))) {
            $variants = $this->inStockVariants($product);
            $colors = $this->catalog->colors($query);
            $words = array_flip($query->words);

            $variant = match (true) {
                $colors !== [] => $variants->whereIn('color_id', array_column($colors, 'id'))->first(),
                $query->ordinal !== null => $query->ordinal === -1 ? $variants->last() : $variants->get($query->ordinal),
                default => $variants->first(fn (ProductVariant $variant) => collect(CatalogSearch::tokens($variant->name()))
                    ->every(fn (string $token) => isset($words[$token]))),
            };

            if ($variant || $colors) {
                return $this->add($product, $variant, $pending['quantity'] ?? 1, $conversation, $colors);
            }
        }

        if (($pending['type'] ?? null) === 'track' && ($query->orderNumber || $query->phone)) {
            return $this->lookUpOrder($query->orderNumber ?? $pending['order'] ?? null, $query->phone ?? $pending['phone'] ?? null, $conversation);
        }

        return null;
    }

    // ---------------------------------------------------------------- Products

    /**
     * The product a message is about: "the second one" from the last list, a product named in full,
     * or — for "it" / "this" (or any add-to-cart message without a name) — the product in focus.
     */
    private function resolveProduct(Query $query, Conversation $conversation, bool $implicit = false): ?Product
    {
        $shown = $conversation->context['products'] ?? [];

        if ($query->ordinal !== null && $shown) {
            $id = $query->ordinal === -1 ? end($shown) : ($shown[$query->ordinal] ?? null);

            return $id ? $this->product($id) : null;
        }

        $terms = $this->catalog->contentTerms($query);

        // "saffiano card holder" names one product; "card holders" (only a category) does not.
        if ($terms && ! $query->hasFilters() && ($named = $this->catalog->named($query))) {
            return $this->product($named->id);
        }

        if (! $terms && ($implicit || $query->has(Lexicon::POINTERS)) && ($id = $this->focus($conversation))) {
            return $this->product($id);
        }

        return null;
    }

    /** @param array<int, array{id: int, name: string, tokens: list<string>}> $colors Colours the visitor asked about */
    private function details(Product $product, Conversation $conversation, array $colors = []): Reply
    {
        $conversation->context['focus'] = $product->id;
        $conversation->context['products'] = [$product->id];

        $variants = $this->inStockVariants($product);

        // "Is it available in black?" gets a direct answer before the details ("black crossbody bag" names the product).
        $nameTokens = CatalogSearch::tokens($product->name);
        $colors = array_values(array_filter($colors, fn (array $color) => ! array_intersect($color['tokens'], $nameTokens)));
        $answer = '';
        if ($colors) {
            $asked = $variants->whereIn('color_id', array_column($colors, 'id'))->map(fn (ProductVariant $variant) => $variant->color?->name)->filter()->unique();
            $answer = $asked->isNotEmpty()
                ? 'Yes, it is in stock in '.$asked->implode(', ').".\n"
                : "It isn't in stock in ".(count($colors) === 1 ? $colors[0]['name'] : last($colors[0]['tokens']))." right now.\n";
        }
        $prices = $variants->map(fn (ProductVariant $variant) => $variant->currentPrice())->unique();
        $stock = $product->availableStock();
        $warranty = $product->warranty_months
            ? ($product->warranty_months % 12 === 0 ? ($product->warranty_months / 12).'-year warranty' : $product->warranty_months.'-month warranty')
            : null;

        $lines = array_filter([
            match (true) {
                $prices->count() > 1 => 'Price: from **'.money($prices->min()).'**',
                $product->hasDiscount() => 'Price: **'.money($product->currentPrice()).'** (regular '.money($product->regular_price).')',
                default => 'Price: **'.money($prices->first() ?? $product->currentPrice()).'**',
            },
            match (true) {
                ! $product->isPurchasable() => 'Sold out right now',
                $stock <= 5 => "Only {$stock} left in stock",
                default => 'In stock',
            },
            ($colors = $variants->map(fn (ProductVariant $variant) => $variant->color?->name)->filter()->unique())->isNotEmpty()
                ? 'Colours in stock: '.$colors->implode(', ')
                : null,
            collect([
                $product->namedLeather(),
                $product->card_capacity ? "holds up to {$product->card_capacity} cards" : null,
                $product->cash_capacity ? "up to {$product->cash_capacity} notes" : null,
                $product->rfid_protection ? 'RFID-blocking lining' : null,
            ])->filter()->implode(' · ') ?: null,
            $product->nfc_enabled ? 'LeatherX Tap NFC chip: shares your contact profile with a tap' : null,
            collect([
                $warranty ? ucfirst($warranty) : null,
                $product->engraving_available ? 'laser engraving +'.money($product->engraving_price) : null,
            ])->filter()->implode(' · ') ?: null,
        ]);

        $actions = array_filter([
            $product->isPurchasable() ? Reply::add('Add to cart', $product->id, null, "Add {$product->name} to cart") : null,
            Reply::link('View details', route('products.show', $product)),
            $product->category ? Reply::ask('Similar products', "Show me {$product->category->name}") : null,
        ]);

        return new Reply(
            $answer."**{$product->name}**".($product->short_description ? ": {$product->short_description}" : '')."\n• ".implode("\n• ", $lines),
            products: [$product->id],
            actions: array_values($actions),
        );
    }

    /**
     * @param  array{products: Collection<int, Product>, categories: array<int, array>, colors: array<int, array>, relaxed: list<string>, terms: list<string>}  $result
     */
    private function listing(Query $query, array $result, Conversation $conversation, int $offset = 0): Reply
    {
        $all = $result['products'];
        $total = $all->count();
        $page = $all->slice($offset, CatalogSearch::PAGE)->values();
        $ids = $page->pluck('id')->all();

        $conversation->context['products'] = $ids;
        $conversation->context['focus'] = count($ids) === 1 ? $ids[0] : null;
        $conversation->context['search'] = ['message' => $query->raw, 'offset' => $offset];

        $category = count($result['categories']) === 1 ? $result['categories'][0] : null;
        $plain = ! $result['terms'] && ! $result['colors'] && ! $query->features && ! $query->minPrice && ! $query->maxPrice;
        $collection = $plain && $category !== null && ! $query->sort;
        $sortNote = [
            'price_asc' => 'lowest price first', 'price_desc' => 'highest price first', 'popular' => 'best sellers first',
            'newest' => 'newest first', 'rating' => 'top rated first',
        ][$query->sort] ?? null;

        $text = match (true) {
            $offset > 0 => 'Here are more ('.($offset + 1).'–'.($offset + $page->count())." of {$total}):",
            $result['relaxed'] !== [] => "I couldn't find {$this->describe($query, $result, 2)}, so here ".($total === 1 ? 'is the closest match' : "are the {$total} closest matches")
                .' '.implode(' and ', array_map(fn (string $relaxed) => ['colour' => 'in other colours', 'price' => 'at other prices'][$relaxed], $result['relaxed'])).'.',
            $result['weak'] => 'Nothing is called “'.implode(' ', $result['terms']).'”, but '
                .($total === 1 ? 'this product mentions it:' : "these {$total} products mention it:"),
            $collection => "Here's our **{$category['name']}** collection. "
                .(($about = $category['description'] ?: $category['tagline']) ? rtrim($about, '. ').'. ' : '')
                .$total.' '.Str::plural('product', $total).', from '.money($all->min(fn (Product $product) => $product->currentPrice())).'.',
            $plain && ! $result['categories'] && $query->sort !== null => 'Here are our '.[
                'price_asc' => 'most affordable products', 'price_desc' => 'premium picks', 'popular' => 'best sellers',
                'newest' => 'newest products', 'rating' => 'top-rated products',
            ][$query->sort].':',
            default => "I found **{$total}** {$this->describe($query, $result, $total)}".($sortNote ? " ({$sortNote})" : '').':',
        };

        $actions = [];
        if ($total > $offset + CatalogSearch::PAGE) {
            $actions[] = Reply::ask('Show more');
        }
        if ($collection) {
            $actions[] = Reply::link("View all {$category['name']}", route('categories.show', $category['slug']));
        } elseif ($total > CatalogSearch::PAGE) {
            $actions[] = Reply::link('See all in the shop', $this->catalog->shopUrl($query, $result));
        }

        return new Reply($text, products: $ids, actions: $actions);
    }

    private function more(Conversation $conversation): Reply
    {
        $search = $conversation->context['search'];
        $query = Query::parse($search['message']);
        $result = $this->catalog->find($query);
        $offset = $search['offset'] + CatalogSearch::PAGE;

        if ($offset >= $result['products']->count()) {
            return new Reply("That's everything I found for that search.", actions: [Reply::link('Browse the shop', route('shop')), ...array_slice($this->suggestions(), 0, 2)]);
        }

        return $this->listing($query, $result, $conversation, $offset);
    }

    /** "black NFC wallets under ৳3,000" — built from what the search actually used. */
    private function describe(Query $query, array $result, int $count): string
    {
        $noun = match (true) {
            count($result['categories']) === 1 => $result['categories'][0]['name'],
            $result['terms'] !== [] => implode(' ', $result['terms']),
            default => 'product',
        };
        $noun = $count === 1 ? Str::singular($noun) : Str::plural($noun);

        $colors = $result['colors'];
        $colour = match (true) {
            count($colors) === 1 => $colors[0]['name'],
            count($colors) > 1 => last($colors[0]['tokens']),
            default => $result['missing_colour'] ?? null,
        };

        $price = match (true) {
            $query->minPrice && $query->maxPrice => 'between '.money($query->minPrice).' and '.money($query->maxPrice),
            (bool) $query->maxPrice => 'under '.money($query->maxPrice),
            (bool) $query->minPrice => 'over '.money($query->minPrice),
            default => null,
        };

        return implode(' ', array_filter([
            in_array('sale', $query->features, true) ? 'discounted' : null,
            $colour,
            in_array('nfc', $query->features, true) && ! Str::contains(Str::lower($noun), ['nfc', 'tap']) ? 'NFC' : null,
            $noun,
            in_array('rfid', $query->features, true) ? 'with RFID blocking' : null,
            in_array('engraving', $query->features, true) ? 'that can be engraved' : null,
            $price,
        ]));
    }

    // ---------------------------------------------------------------- Cart

    private function addFromMessage(Query $query, Conversation $conversation): Reply
    {
        $product = $this->resolveProduct($query, $conversation, implicit: true);
        if ($product) {
            return $this->add($product, null, $query->quantity ?? 1, $conversation, $this->catalog->colors($query));
        }

        $shown = $this->products($conversation->context['products'] ?? []);
        if ($shown->count() > 1 && ! $this->catalog->contentTerms($query)) {
            return new Reply('Which one would you like to add?', actions: $shown
                ->map(fn (Product $product) => Reply::add($product->name, $product->id, null, "Add {$product->name} to cart", $query->quantity ?? 1))
                ->values()->all());
        }

        $result = $this->catalog->find($query);
        if ($result['products']->isNotEmpty()) {
            $reply = $this->listing($query, $result, $conversation);
            $reply->text = 'Which of these would you like? Tap + on a product to add it to your cart.';

            return $reply;
        }

        return new Reply('Which product would you like to add? Tell me its name, or ask me to show you some products.', actions: $this->suggestions());
    }

    /** @param array<int, array{id: int, name: string}> $colors Colours the visitor asked for */
    private function add(Product $product, ?ProductVariant $variant, int $quantity, Conversation $conversation, array $colors = []): Reply
    {
        $quantity = max(1, min(20, $quantity));
        $conversation->context['focus'] = $product->id;
        $conversation->context['products'] = [$product->id];

        if (! $variant && $product->activeVariants->isNotEmpty()) {
            $variants = $this->inStockVariants($product);
            if ($variants->isEmpty()) {
                return new Reply("Sorry, **{$product->name}** is sold out right now.", products: [$product->id],
                    actions: $product->category ? [Reply::ask('Similar products', "Show me {$product->category->name}")] : []);
            }

            $wanted = $colors ? $variants->whereIn('color_id', array_column($colors, 'id')) : collect();
            $variant = $wanted->first() ?? ($variants->count() === 1 ? $variants->first() : null);

            if (! $variant) {
                $conversation->context['awaiting'] = ['type' => 'variant', 'product' => $product->id, 'quantity' => $quantity];
                $note = $colors ? "**{$product->name}** isn't in stock in ".implode(' or ', array_column($colors, 'name')).'. ' : '';

                return new Reply(
                    $note."Which option would you like for **{$product->name}**?",
                    products: [$product->id],
                    actions: $variants->map(fn (ProductVariant $option) => Reply::add(
                        $option->name().($option->stock_quantity <= 5 ? " (only {$option->stock_quantity} left)" : ''),
                        $product->id, $option->id, "Add {$product->name} ({$option->name()}) to cart", $quantity,
                    ))->values()->all(),
                );
            }
        }

        try {
            $this->cart->add($product, $variant, $quantity);
        } catch (ValidationException $e) {
            return new Reply(collect($e->errors())->flatten()->first() ?? 'Sorry, that item could not be added.', products: [$product->id]);
        }

        $count = $this->cart->count();

        return new Reply(
            'Added '.($quantity > 1 ? "{$quantity} × " : '')."**{$product->name}**".($variant ? " ({$variant->name()})" : '')
                .' to your cart. You now have '.$count.' '.Str::plural('item', $count).' in your cart ('.money($this->cart->subtotal()).').',
            actions: [
                Reply::link('Checkout', route('checkout'), primary: true),
                Reply::link('View cart', route('cart')),
                Reply::ask('Keep shopping', 'Show me best sellers'),
            ],
            cartUpdated: true,
        );
    }

    private function cartSummary(): Reply
    {
        $totals = $this->cart->totals();
        if ($totals['items']->isEmpty()) {
            return new Reply('Your cart is empty. Can I help you find something?', actions: $this->suggestions());
        }

        $lines = $totals['items']->map(fn ($item) => "{$item->quantity} × {$item->product->name}"
            .($item->variant ? " ({$item->variant->name()})" : '').' — '.money($item->lineTotal()));

        return new Reply(
            "Here's your cart:\n• ".$lines->implode("\n• ")
                .($totals['discount'] > 0 ? "\nCoupon {$totals['coupon']->code}: −".money($totals['discount']) : '')
                ."\nSubtotal: **".money($totals['subtotal'] + $totals['engraving'] - $totals['discount']).'**. Delivery is added at checkout.',
            actions: [Reply::link('Checkout', route('checkout'), primary: true), Reply::link('View cart', route('cart'))],
        );
    }

    private function checkout(): Reply
    {
        $count = $this->cart->count();
        if ($count === 0) {
            return new Reply('Your cart is empty, so there is nothing to check out yet. Can I help you find something?', actions: $this->suggestions());
        }

        return new Reply(
            "You have {$count} ".Str::plural('item', $count).' in your cart ('.money($this->cart->subtotal()).'). Delivery is calculated at checkout from your area.',
            actions: [Reply::link('Go to checkout', route('checkout'), primary: true), Reply::link('Review cart', route('cart'))],
        );
    }

    // ---------------------------------------------------------------- Orders

    private function track(Query $query, Conversation $conversation): Reply
    {
        if ($query->orderNumber || $query->phone) {
            return $this->lookUpOrder($query->orderNumber, $query->phone, $conversation);
        }

        if ($user = auth()->user()) {
            $orders = Order::where('user_id', $user->id)->latest('id')->limit(3)->get(['id', 'order_number', 'status', 'total', 'created_at']);
            if ($orders->isNotEmpty()) {
                return new Reply(
                    "Your recent orders:\n• ".$orders->map(fn (Order $order) => "**{$order->order_number}** — {$order->status->getLabel()} · "
                        .money($order->total).' · '.$order->created_at->format('j M Y'))->implode("\n• "),
                    actions: [
                        ...$orders->map(fn (Order $order) => Reply::ask("Track {$order->order_number}"))->all(),
                        Reply::link('All orders', route('account.orders')),
                    ],
                );
            }
        }

        $conversation->context['awaiting'] = ['type' => 'track'];

        return new Reply(
            'Please send your order number (it starts with LX and is in your order confirmation) and the phone number you used at checkout.',
            actions: [Reply::link('Open the tracking page', route('orders.track'))],
        );
    }

    private function lookUpOrder(?string $number, ?string $phone, Conversation $conversation): Reply
    {
        // A signed-in customer can look up their own orders without the phone number.
        if ($number && auth()->check() && ($own = Order::where('user_id', auth()->id())->where('order_number', $number)->first())) {
            return $this->orderStatus($own);
        }

        if (! $number || ! $phone) {
            $conversation->context['awaiting'] = ['type' => 'track', 'order' => $number, 'phone' => $phone];

            return new Reply(
                $number ? "Thanks. What's the phone number used for order **{$number}**?" : "Thanks. What's the order number? It starts with LX and is in your order confirmation.",
                actions: [Reply::link('Open the tracking page', route('orders.track'))],
            );
        }

        $key = 'assistant-track:'.session()->getId();
        if (RateLimiter::tooManyAttempts($key, 6)) {
            return new Reply('Too many lookups in a short time. Please wait a minute and try again.');
        }
        RateLimiter::hit($key, 60);

        $order = Order::where('order_number', $number)->where('phone', normalize_phone($phone))->first();
        if (! $order) {
            return new Reply(
                "I couldn't find order **{$number}** with that phone number. Please check both: the phone must be the one used at checkout.",
                actions: [Reply::link('Open the tracking page', route('orders.track')), Reply::ask('Contact the team', 'How can I contact you?')],
            );
        }

        return $this->orderStatus($order);
    }

    private function orderStatus(Order $order): Reply
    {
        $order->loadMissing('items:id,order_id,quantity');
        $items = (int) $order->items->sum('quantity');

        $hint = match ($order->status) {
            OrderStatus::Pending => 'It is waiting for confirmation.',
            OrderStatus::Confirmed => 'It is confirmed and being prepared.',
            OrderStatus::Processing => 'It is being prepared for dispatch.',
            OrderStatus::Shipped => 'It is on the way.',
            OrderStatus::Delivered => 'It was delivered'.($order->delivered_at ? ' on '.$order->delivered_at->format('j M Y') : '').'.',
            OrderStatus::Cancelled => 'This order was cancelled.',
            OrderStatus::Returned => 'This order was returned.',
        };

        $lines = array_filter([
            'Placed '.$order->created_at->format('j M Y')." · {$items} ".Str::plural('item', $items).' · '.money($order->total),
            $order->courier_name ? "Courier: {$order->courier_name}".($order->tracking_number ? ", tracking number {$order->tracking_number}" : '') : null,
        ]);

        $owner = auth()->check() && $order->user_id === auth()->id();

        return new Reply(
            "Order **{$order->order_number}** is **{$order->status->getLabel()}**. {$hint}\n• ".implode("\n• ", $lines),
            actions: [$owner
                ? Reply::link('Order details', route('account.orders.show', $order))
                : Reply::link('Open the tracking page', route('orders.track'))],
        );
    }

    // ---------------------------------------------------------------- Store information

    private function delivery(): Reply
    {
        $zones = ShippingZone::active()->get();
        $actions = [Reply::link('Delivery policy', route('pages.delivery-policy')), Reply::ask('Track my order')];

        if ($zones->isEmpty()) {
            $faq = $this->faqIn(['Delivery']);

            return new Reply($faq['answer'] ?? 'Delivery charges for your area are shown at checkout before you place the order.', actions: $actions);
        }

        return new Reply(
            "Delivery charges by area:\n• ".$zones->map(fn (ShippingZone $zone) => "**{$zone->name}** — "
                .((float) $zone->charge > 0 ? money($zone->charge) : 'free').' · '.$zone->estimate()
                .($zone->free_shipping_threshold ? ' · free over '.money($zone->free_shipping_threshold) : ''))->implode("\n• "),
            actions: $actions,
        );
    }

    private function payment(): Reply
    {
        $methods = PaymentMethod::active()->get(['name', 'instructions']);

        if ($methods->isEmpty()) {
            $faq = $this->faqIn(['Payment']);

            return new Reply($faq['answer'] ?? 'The available payment options are shown at checkout.');
        }

        return new Reply(
            "You can pay with:\n• ".$methods->map(fn (PaymentMethod $method) => "**{$method->name}**"
                .($method->instructions ? ' — '.Str::of($method->instructions)->explode('. ')->first().(str_contains($method->instructions, '. ') ? '.' : '') : ''))->implode("\n• "),
            actions: [Reply::ask('Delivery charges')],
        );
    }

    private function returns(Query $query): Reply
    {
        $faq = $this->faq($query, ['Returns', 'Engraving']) ?? $this->faqIn(['Returns']);

        return new Reply(
            $faq ? "**{$faq['question']}**\n{$faq['answer']}" : 'The conditions for exchanges and returns are in our return policy.',
            actions: [Reply::link('Return policy', route('pages.return-policy')), Reply::ask('Contact the team', 'How can I contact you?')],
        );
    }

    private function warranty(Query $query, Conversation $conversation): Reply
    {
        $actions = [Reply::link('Register a warranty', route('warranty'))];
        $product = $this->resolveProduct($query, $conversation);

        if ($product) {
            $conversation->context['focus'] = $product->id;

            return new Reply(
                $product->warranty_months
                    ? "**{$product->name}** comes with a {$product->warranty_months}-month warranty."
                    : "**{$product->name}** doesn't list a warranty period. Please ask our team for details.",
                products: [$product->id],
                actions: $actions,
            );
        }

        $faq = $this->faqIn(['Warranty']);

        return new Reply($faq ? "**{$faq['question']}**\n{$faq['answer']}" : 'Each product page shows its warranty period. Register your product to keep the warranty on record.', actions: $actions);
    }

    private function corporate(Conversation $conversation): Reply
    {
        $query = Query::parse('corporate gifts');
        $result = $this->catalog->find($query);
        $ids = $result['products']->take(CatalogSearch::PAGE)->pluck('id')->all();
        $conversation->context['products'] = $ids;

        return new Reply(
            'For corporate and bulk orders, send us your requirements on the corporate gifting page and our team will prepare a quotation.',
            products: $ids,
            actions: [Reply::link('Request a quotation', route('corporate'), primary: true)],
        );
    }

    private function tapInfo(Query $query): Reply
    {
        $faq = $this->faq($query, ['LeatherX Tap']) ?? $this->faqIn(['LeatherX Tap']);

        return new Reply(
            $faq ? "**{$faq['question']}**\n{$faq['answer']}" : 'LeatherX Tap wallets carry an NFC chip that opens your contact profile on a phone.',
            actions: [Reply::link('How LeatherX Tap works', route('tap')), Reply::ask('Show NFC wallets', 'Show me NFC wallets')],
        );
    }

    /** What the catalogue is made of, from each product's own leather type. */
    private function materials(): Reply
    {
        $types = Product::visible()->whereNotNull('leather_type')->where('leather_type', '!=', '')
            ->selectRaw('leather_type, COUNT(*) as total')->groupBy('leather_type')->orderByDesc('total')->pluck('total', 'leather_type');

        if ($types->isEmpty()) {
            return new Reply('Each product page lists what the product is made of.', actions: [Reply::link('Browse the shop', route('shop'))]);
        }

        return new Reply(
            "Each product page lists its material. Here's what our products are made of:\n• "
                .$types->map(fn ($total, $type) => "**{$type}** — {$total} ".Str::plural('product', (int) $total))->implode("\n• "),
            actions: [Reply::link('Browse the shop', route('shop'))],
        );
    }

    private function engravingInfo(Query $query): Reply
    {
        $engravable = Product::visible()->where('engraving_available', true);
        $count = (clone $engravable)->count();
        $from = (clone $engravable)->min('engraving_price');
        $faq = $this->faq($query);

        $text = $count
            ? "Laser engraving is available on {$count} ".Str::plural('product', $count).($from > 0 ? ', from **+'.money($from).'** per item' : '')
                .'. Choose it on the product page: add the text, style and position before adding to cart.'
            : 'Engraving is not available on any product right now.';

        return new Reply(
            $text.($faq ? "\n\n**{$faq['question']}**\n{$faq['answer']}" : ''),
            actions: array_values(array_filter([
                Reply::link('Engraving service', route('engraving')),
                $count ? Reply::ask('Engravable products', 'Show me products with engraving') : null,
            ])),
        );
    }

    private function contact(): Reply
    {
        $whatsapp = setting('contact_whatsapp');
        $phone = setting('contact_phone');

        $lines = array_filter([
            $phone ? "Phone: **{$phone}**" : null,
            $whatsapp ? "WhatsApp: **{$whatsapp}**" : null,
            ($email = setting('contact_email')) ? "Email: **{$email}**" : null,
            ($hours = setting('business_hours')) ? "Hours: {$hours}" : null,
            ($address = setting('contact_address')) ? "Address: {$address}" : null,
        ]);

        return new Reply(
            $lines ? "You can reach our team here:\n• ".implode("\n• ", $lines) : 'You can send our team a message through the contact page.',
            actions: array_values(array_filter([
                $whatsapp ? Reply::link('Chat on WhatsApp', 'https://wa.me/'.preg_replace('/\D/', '', $whatsapp), primary: true) : null,
                $phone ? Reply::link('Call us', 'tel:'.preg_replace('/[^\d+]/', '', $phone)) : null,
                Reply::link('Contact page', route('contact')),
            ])),
        );
    }

    private function coupons(): Reply
    {
        return new Reply(
            'Have a coupon code? Enter it on your cart page and the discount is applied before checkout.',
            actions: [Reply::link('Open cart', route('cart')), Reply::ask('Products on sale', 'Show me products on sale')],
        );
    }

    private function fallback(Query $query): Reply
    {
        $search = Str::limit($query->raw, 60, '');

        return new Reply(
            "Sorry, I don't have an answer for that. I can search the catalogue, check your cart, track an order, or answer questions about delivery, payment, returns and warranty.",
            actions: array_values(array_filter([
                $query->terms ? Reply::link("Search the shop for “{$search}”", route('shop', ['q' => $search])) : null,
                ...array_slice($this->suggestions(), 0, 3),
                Reply::link('Contact us', route('contact')),
            ])),
        );
    }

    // ---------------------------------------------------------------- FAQs

    /**
     * The FAQ that best matches the message. Without $categories it must share most of the message's
     * keywords with a question; with $categories it only searches those FAQ groups.
     *
     * @param  array<int, string>  $categories
     * @return array{category: ?string, question: string, answer: string}|null
     */
    private function faq(Query $query, array $categories = []): ?array
    {
        $keywords = $query->keywords();
        if (! $keywords) {
            return null;
        }

        $best = null;
        $bestScore = 0;
        $bestMatched = 0;
        foreach ($this->faqs($categories) as $faq) {
            $score = 0;
            $matched = 0;
            foreach ($keywords as $word) {
                $points = in_array($word, $faq['question_tokens'], true) ? 2 : (in_array($word, $faq['answer_tokens'], true) ? 1 : 0);
                $score += $points;
                $matched += $points ? 1 : 0;
            }
            if ($score > $bestScore) {
                [$best, $bestScore, $bestMatched] = [$faq, $score, $matched];
            }
        }

        if ($categories) {
            return $bestScore >= 1 ? $best : null;
        }

        // Most of the message's words must appear in the FAQ ("engravable wallets" is not the NFC wallet FAQ).
        return $bestScore >= 2 && $bestMatched >= ceil(count($keywords) * 0.6) ? $best : null;
    }

    /** First FAQ in one of the groups (in the admin's order). */
    private function faqIn(array $categories): ?array
    {
        return $this->faqs($categories)[0] ?? null;
    }

    /** @return list<array{category: ?string, question: string, answer: string, question_tokens: list<string>, answer_tokens: list<string>}> */
    private function faqs(array $categories = []): array
    {
        $faqs = StorefrontCache::remember('assistant-faqs', fn () => Faq::active()->get(['category', 'question', 'answer'])
            ->map(fn (Faq $faq) => [
                'category' => $faq->category,
                'question' => $faq->question,
                'answer' => trim(strip_tags((string) $faq->answer)),
                'question_tokens' => CatalogSearch::tokens($faq->question),
                'answer_tokens' => CatalogSearch::tokens(strip_tags((string) $faq->answer)),
            ])->all(), 1800);

        if (! $categories) {
            return $faqs;
        }

        $wanted = array_map('mb_strtolower', $categories);

        return array_values(array_filter($faqs, fn (array $faq) => in_array(mb_strtolower((string) $faq['category']), $wanted, true)));
    }

    private function faqReply(array $faq): Reply
    {
        return new Reply("**{$faq['question']}**\n{$faq['answer']}", actions: [Reply::ask('Ask something else', 'What can you do?')]);
    }

    // ---------------------------------------------------------------- Helpers

    private function product(int $id): ?Product
    {
        return Product::visible()
            ->with(['category:id,name,slug', 'activeVariants.color', 'activeVariants.material', 'activeVariants.size'])
            ->find($id);
    }

    /** @return Collection<int, Product> Products in the given order */
    private function products(array $ids): Collection
    {
        $products = Product::visible()->whereIn('id', $ids)->get(['id', 'name'])->keyBy('id');

        return collect($ids)->map(fn (int $id) => $products->get($id))->filter()->values();
    }

    /** @return Collection<int, ProductVariant> */
    private function inStockVariants(Product $product): Collection
    {
        return $product->activeVariants
            ->filter(fn (ProductVariant $variant) => $variant->stock_quantity > 0)
            ->each(fn (ProductVariant $variant) => $variant->setRelation('product', $product))
            ->values();
    }

    private function focus(Conversation $conversation): ?int
    {
        $shown = $conversation->context['products'] ?? [];

        return $conversation->context['focus'] ?? (count($shown) === 1 ? $shown[0] : null);
    }

    /** @return list<array<string, mixed>> */
    private function suggestions(): array
    {
        return [
            Reply::ask('Best sellers', 'Show me best sellers'),
            Reply::ask('NFC smart wallets', 'Show me NFC wallets'),
            Reply::ask('Wallets under ৳2,500', 'Wallets under 2500'),
            Reply::ask('Track my order'),
            Reply::ask('Delivery charges'),
        ];
    }

    private function name(): string
    {
        return (string) setting('site_name', 'LeatherX');
    }
}
