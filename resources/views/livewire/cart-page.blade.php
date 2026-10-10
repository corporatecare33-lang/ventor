<section class="container-site py-10 lg:py-14" aria-labelledby="cart-title">
    <h1 id="cart-title" class="text-h1 text-ink-950">Your cart</h1>

    @if ($totals['items']->isEmpty())
        <x-ui.empty-state icon="bag" title="Your cart is empty" text="Browse wallets, card holders, bags and LeatherX Tap smart wallets." class="mt-10">
            <a href="{{ route('shop') }}" class="btn-primary">Shop all products</a>
        </x-ui.empty-state>
    @else
        @error('cart')<x-ui.alert type="error" class="mt-6">{{ $message }}</x-ui.alert>@enderror

        <div class="mt-8 grid gap-10 lg:grid-cols-12 lg:gap-12">
            <div class="lg:col-span-7">
                <h2 class="sr-only">Items</h2>
                <ul class="border-t border-line" wire:loading.class="opacity-60" wire:target="updateQuantity,remove">
                    @foreach ($totals['items'] as $item)
                        @php $available = $item->product && ! $item->product->trashed() && $item->product->isPurchasable(); @endphp
                        <li class="flex gap-4 border-b border-line py-6 sm:gap-6" wire:key="item-{{ $item->id }}">
                            <a href="{{ route('products.show', $item->product) }}" class="block h-30 w-24 shrink-0 overflow-hidden rounded-xs bg-paper sm:h-36 sm:w-28" tabindex="-1" aria-hidden="true">
                                <x-product.media :product="$item->product" sizes="7rem" />
                            </a>
                            <div class="flex min-w-0 flex-1 flex-col">
                                <div class="flex items-start justify-between gap-4">
                                    <div class="min-w-0">
                                        <a href="{{ route('products.show', $item->product) }}" class="font-medium text-ink-950 underline-offset-4 hover:underline">{{ $item->product->name }}</a>
                                        @if ($item->variant)<p class="mt-0.5 text-sm text-ink-500">{{ $item->variant->name() }}</p>@endif
                                        <p class="mt-1 text-sm text-ink-600">{{ money($item->unitPrice()) }} each</p>
                                        @if ($item->engraving)
                                            <p class="mt-2 text-[13px] text-ink-600">
                                                Engraving: <span class="font-medium text-ink-950">{{ $item->engraving['text'] ?? 'Logo' }}</span>
                                                · {{ $item->engraving['font'] ?? '' }} · +{{ money($item->engravingFee()) }}
                                            </p>
                                        @endif
                                        @unless ($available)<p class="mt-2 text-[13px] font-medium text-danger">No longer available — please remove it to continue.</p>@endunless
                                    </div>
                                    <p class="shrink-0 font-medium text-ink-950">{{ money($item->lineTotal()) }}</p>
                                </div>
                                <div class="mt-auto flex items-center justify-between gap-4 pt-4">
                                    <div class="flex items-center rounded-xs border border-line-strong" role="group" aria-label="Quantity for {{ $item->product->name }}">
                                        <button type="button" wire:click="updateQuantity({{ $item->id }}, {{ $item->quantity - 1 }})" class="inline-flex size-10 items-center justify-center" aria-label="Decrease quantity"><x-lx-icon name="minus" class="size-3.5" /></button>
                                        <span class="w-7 text-center text-sm" aria-live="polite">{{ $item->quantity }}</span>
                                        <button type="button" wire:click="updateQuantity({{ $item->id }}, {{ $item->quantity + 1 }})" class="inline-flex size-10 items-center justify-center" aria-label="Increase quantity"><x-lx-icon name="plus" class="size-3.5" /></button>
                                    </div>
                                    <button type="button" wire:click="remove({{ $item->id }})" class="inline-flex min-h-10 items-center gap-1.5 text-sm text-ink-600 hover:text-danger">
                                        <x-lx-icon name="trash" class="size-4" /> Remove<span class="sr-only"> {{ $item->product->name }}</span>
                                    </button>
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
                <a href="{{ route('shop') }}" class="link-arrow mt-6"><x-lx-icon name="chevron-left" class="size-4" /> Continue shopping</a>
            </div>

            <aside class="lg:col-span-5" aria-labelledby="summary-title">
                <div class="rounded-sm bg-paper p-6 sm:p-8 lg:sticky lg:top-28">
                    <h2 id="summary-title" class="font-display text-2xl text-ink-950">Order summary</h2>

                    <form wire:submit="applyCoupon" class="mt-6">
                        @if ($totals['coupon'])
                            <div class="flex items-center justify-between gap-3 rounded-xs border border-success/25 bg-success-bg px-4 py-3 text-sm text-success">
                                <span>Coupon <strong>{{ $totals['coupon']->code }}</strong> applied</span>
                                <button type="button" wire:click="removeCoupon" class="text-ink-700 underline underline-offset-4">Remove</button>
                            </div>
                        @else
                            <label for="coupon" class="label">Coupon code</label>
                            <div class="flex gap-2">
                                <input id="coupon" type="text" wire:model="couponCode" autocomplete="off" class="field uppercase" @error('couponCode') aria-invalid="true" aria-describedby="coupon-error" @enderror>
                                <button class="btn-secondary shrink-0 px-5" wire:loading.attr="disabled" wire:target="applyCoupon">Apply</button>
                            </div>
                            @error('couponCode')<p id="coupon-error" class="error">{{ $message }}</p>@enderror
                            @if ($totals['coupon_error'])<p class="error">{{ $totals['coupon_error'] }}</p>@endif
                        @endif
                    </form>

                    <dl class="mt-6 space-y-3 border-t border-line-strong pt-6 text-[15px]">
                        <div class="flex justify-between"><dt class="text-ink-600">Subtotal</dt><dd>{{ money($totals['subtotal']) }}</dd></div>
                        @if ($totals['engraving'] > 0)<div class="flex justify-between"><dt class="text-ink-600">Engraving</dt><dd>{{ money($totals['engraving']) }}</dd></div>@endif
                        @if ($totals['discount'] > 0)<div class="flex justify-between text-success"><dt>Discount</dt><dd>−{{ money($totals['discount']) }}</dd></div>@endif
                        <div class="flex justify-between"><dt class="text-ink-600">Delivery</dt><dd class="text-ink-600">Calculated at checkout</dd></div>
                        <div class="flex justify-between border-t border-line-strong pt-4 text-lg font-medium text-ink-950"><dt>Total</dt><dd>{{ money($totals['total']) }}</dd></div>
                    </dl>
                    @if ($cheapestZone?->free_shipping_threshold && $totals['subtotal'] < $cheapestZone->free_shipping_threshold)
                        <p class="mt-4 text-sm text-ink-600">Add {{ money($cheapestZone->free_shipping_threshold - $totals['subtotal']) }} more for free delivery ({{ $cheapestZone->name }}).</p>
                    @endif
                    <a href="{{ route('checkout') }}" class="btn-primary mt-6 w-full">Continue to checkout</a>
                    <p class="mt-4 flex items-center justify-center gap-2 text-[13px] text-ink-500"><x-lx-icon name="lock" class="size-4" /> Cash on delivery available</p>
                </div>
            </aside>
        </div>
    @endif
</section>
