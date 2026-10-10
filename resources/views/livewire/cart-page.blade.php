<div class="container-lx py-10 sm:py-14">
    <h1 class="font-display text-4xl font-semibold text-ink-950">Your cart</h1>

    @if ($totals['items']->isEmpty())
        <div class="mt-10 rounded-3xl border border-dashed border-ink-900/15 px-6 py-20 text-center">
            <x-lx-icon name="bag" class="mx-auto size-10 text-ink-600/40" />
            <p class="mt-4 font-display text-2xl text-ink-950">Your cart is empty</p>
            <p class="mt-2 text-sm text-ink-600">Discover handcrafted wallets, bags and smart NFC accessories.</p>
            <a href="{{ route('shop') }}" class="btn-dark mt-6">Start shopping</a>
        </div>
    @else
        @error('cart')<p class="mt-6 rounded-xl bg-red-50 px-4 py-3 text-sm text-red-800">{{ $message }}</p>@enderror

        <div class="mt-8 grid gap-10 lg:grid-cols-[1fr_380px]">
            <ul class="divide-y divide-ink-900/10 border-y border-ink-900/10" wire:loading.class="opacity-60">
                @foreach ($totals['items'] as $item)
                    @php $available = $item->product && ! $item->product->trashed() && $item->product->isPurchasable(); @endphp
                    <li class="flex gap-4 py-5 sm:gap-6" wire:key="item-{{ $item->id }}">
                        <a href="{{ route('products.show', $item->product) }}" class="size-24 shrink-0 overflow-hidden rounded-xl bg-cream-100 sm:size-28">
                            <x-product-image :product="$item->product" />
                        </a>
                        <div class="flex min-w-0 flex-1 flex-col">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <a href="{{ route('products.show', $item->product) }}" class="line-clamp-2 text-sm font-medium text-ink-950 hover:text-leather-700">{{ $item->product->name }}</a>
                                    @if ($item->variant)<p class="mt-0.5 text-xs text-ink-600">{{ $item->variant->name() }}</p>@endif
                                    @if ($item->engraving)
                                        <p class="mt-1.5 inline-flex items-center gap-1 rounded-md bg-cream-100 px-2 py-1 text-[11px] text-leather-800">
                                            <x-lx-icon name="sparkles" class="size-3" />
                                            Engraving: {{ $item->engraving['text'] ?? 'Logo' }} · {{ $item->engraving['font'] ?? '' }} (+{{ money($item->engravingFee()) }})
                                        </p>
                                    @endif
                                    @unless ($available)<p class="mt-1 text-xs font-medium text-red-700">No longer available — please remove</p>@endunless
                                </div>
                                <p class="text-sm font-semibold whitespace-nowrap text-ink-950">{{ money($item->lineTotal()) }}</p>
                            </div>
                            <div class="mt-auto flex items-center justify-between pt-3">
                                <div class="flex items-center rounded-full border border-ink-900/15">
                                    <button type="button" wire:click="updateQuantity({{ $item->id }}, {{ $item->quantity - 1 }})" class="grid size-9 place-items-center" aria-label="Decrease"><x-lx-icon name="minus" class="size-3.5" /></button>
                                    <span class="w-7 text-center text-sm">{{ $item->quantity }}</span>
                                    <button type="button" wire:click="updateQuantity({{ $item->id }}, {{ $item->quantity + 1 }})" class="grid size-9 place-items-center" aria-label="Increase"><x-lx-icon name="plus" class="size-3.5" /></button>
                                </div>
                                <button type="button" wire:click="remove({{ $item->id }})" class="inline-flex items-center gap-1 text-xs text-ink-600 hover:text-red-700">
                                    <x-lx-icon name="trash" class="size-4" /> Remove
                                </button>
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>

            <aside class="h-fit rounded-3xl bg-cream-50 p-6 lg:sticky lg:top-24">
                <h2 class="font-display text-2xl font-semibold">Order summary</h2>

                <form wire:submit="applyCoupon" class="mt-5">
                    @if ($totals['coupon'])
                        <div class="flex items-center justify-between rounded-xl border border-emerald-700/20 bg-emerald-50 px-4 py-3 text-sm">
                            <span><span class="font-semibold">{{ $totals['coupon']->code }}</span> applied</span>
                            <button type="button" wire:click="removeCoupon" class="text-xs text-ink-600 underline">Remove</button>
                        </div>
                    @else
                        <label for="coupon" class="label">Coupon code</label>
                        <div class="flex gap-2">
                            <input id="coupon" type="text" wire:model="couponCode" class="field uppercase" placeholder="WELCOME10">
                            <button class="btn-outline px-5">Apply</button>
                        </div>
                        @error('couponCode')<p class="error">{{ $message }}</p>@enderror
                        @if ($totals['coupon_error'])<p class="error">{{ $totals['coupon_error'] }}</p>@endif
                    @endif
                </form>

                <dl class="mt-6 space-y-3 text-sm">
                    <div class="flex justify-between"><dt class="text-ink-600">Subtotal</dt><dd>{{ money($totals['subtotal']) }}</dd></div>
                    @if ($totals['engraving'] > 0)
                        <div class="flex justify-between"><dt class="text-ink-600">Engraving</dt><dd>{{ money($totals['engraving']) }}</dd></div>
                    @endif
                    @if ($totals['discount'] > 0)
                        <div class="flex justify-between text-emerald-700"><dt>Discount</dt><dd>−{{ money($totals['discount']) }}</dd></div>
                    @endif
                    <div class="flex justify-between"><dt class="text-ink-600">Delivery</dt><dd class="text-ink-600">Calculated at checkout</dd></div>
                    <div class="flex justify-between border-t border-ink-900/10 pt-4 text-base font-semibold"><dt>Total</dt><dd>{{ money($totals['total']) }}</dd></div>
                </dl>
                @if ($cheapestZone?->free_shipping_threshold && $totals['subtotal'] < $cheapestZone->free_shipping_threshold)
                    <p class="mt-4 rounded-xl bg-white px-4 py-3 text-xs text-ink-700">Add <strong>{{ money($cheapestZone->free_shipping_threshold - $totals['subtotal']) }}</strong> more for free delivery {{ strtolower($cheapestZone->name) }}.</p>
                @endif
                <a href="{{ route('checkout') }}" class="btn-gold mt-6 w-full">Proceed to checkout <x-lx-icon name="arrow-right" class="size-4" /></a>
                <p class="mt-4 flex items-center justify-center gap-2 text-xs text-ink-600"><x-lx-icon name="lock" class="size-3.5" /> Secure checkout · Cash on delivery available</p>
            </aside>
        </div>
    @endif
</div>
