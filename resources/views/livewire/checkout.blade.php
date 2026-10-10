<div class="container-lx py-10 sm:py-14">
    <h1 class="font-display text-4xl font-semibold text-ink-950">Checkout</h1>
    @guest
        <p class="mt-2 text-sm text-ink-600">Have an account? <a href="{{ route('login') }}" class="font-medium text-leather-700 underline">Sign in</a> for faster checkout — or continue as a guest.</p>
    @endguest

    @error('checkout')<p class="mt-6 rounded-xl bg-red-50 px-4 py-3 text-sm text-red-800" role="alert">{{ $message }}</p>@enderror

    <form wire:submit="placeOrder" class="mt-8 grid gap-10 lg:grid-cols-[1fr_400px]">
        <div class="space-y-10">
            <section>
                <h2 class="mb-5 flex items-center gap-3 font-display text-2xl font-semibold"><span class="grid size-8 place-items-center rounded-full bg-ink-900 font-sans text-xs text-white">1</span> Delivery details</h2>

                @if ($addresses->isNotEmpty())
                    <div class="mb-5 flex flex-wrap gap-2">
                        @foreach ($addresses as $saved)
                            <button type="button" wire:click="useAddress({{ $saved->id }})" class="rounded-xl border px-4 py-2.5 text-left text-xs {{ $address_id === $saved->id ? 'border-ink-900 bg-ink-900 text-white' : 'border-ink-900/15 hover:border-ink-900' }}">
                                <span class="block font-semibold">{{ $saved->label }}</span>
                                <span class="block opacity-70">{{ str($saved->address)->limit(28) }}</span>
                            </button>
                        @endforeach
                    </div>
                @endif

                <div class="grid gap-4 sm:grid-cols-2">
                    <x-input name="customer_name" model="customer_name" label="Full name" required autocomplete="name" />
                    <x-input name="phone" model="phone" label="Mobile number" required type="tel" inputmode="numeric" placeholder="01XXXXXXXXX" autocomplete="tel" />
                    <x-input name="email" model="email" label="Email (for order updates)" type="email" class="sm:col-span-2" autocomplete="email" />
                    <x-input name="address" model="address" label="Full address" required class="sm:col-span-2" placeholder="House, road, block" autocomplete="street-address" />
                    <x-input name="area" model="area" label="Area / Thana" placeholder="Dhanmondi" />
                    <x-input name="city" model="city" label="City" required autocomplete="address-level2" />
                    <x-input name="district" model="district" label="District" />
                    <x-input name="postal_code" model="postal_code" label="Postal code" autocomplete="postal-code" />
                </div>
                @auth
                    @unless ($address_id)
                        <label class="mt-4 flex items-center gap-2 text-sm text-ink-700"><input type="checkbox" wire:model="save_address" class="size-4 accent-ink-900"> Save this address to my account</label>
                    @endunless
                @endauth
            </section>

            <section>
                <h2 class="mb-5 flex items-center gap-3 font-display text-2xl font-semibold"><span class="grid size-8 place-items-center rounded-full bg-ink-900 font-sans text-xs text-white">2</span> Delivery area</h2>
                <div class="grid gap-3 sm:grid-cols-3">
                    @foreach ($zones as $zone)
                        <label class="relative cursor-pointer rounded-2xl border p-4 transition {{ $shipping_zone_id === $zone->id ? 'border-ink-900 ring-1 ring-ink-900' : 'border-ink-900/15 hover:border-ink-900/40' }}">
                            <input type="radio" wire:model.live="shipping_zone_id" value="{{ $zone->id }}" class="sr-only">
                            <span class="block text-sm font-semibold">{{ $zone->name }}</span>
                            <span class="mt-1 block text-xs text-ink-600">{{ $zone->estimate() }}</span>
                            <span class="mt-2 block text-sm font-semibold">{{ $zone->chargeFor($totals['subtotal'] - $totals['discount']) > 0 ? money($zone->chargeFor($totals['subtotal'] - $totals['discount'])) : 'Free' }}</span>
                        </label>
                    @endforeach
                </div>
                @error('shipping_zone_id')<p class="error">{{ $message }}</p>@enderror
            </section>

            <section>
                <h2 class="mb-5 flex items-center gap-3 font-display text-2xl font-semibold"><span class="grid size-8 place-items-center rounded-full bg-ink-900 font-sans text-xs text-white">3</span> Payment</h2>
                <div class="space-y-3">
                    @foreach ($methods as $method)
                        <label class="block cursor-pointer rounded-2xl border p-4 transition {{ $payment_method === $method->code ? 'border-ink-900 ring-1 ring-ink-900' : 'border-ink-900/15 hover:border-ink-900/40' }}">
                            <span class="flex items-center gap-3">
                                <input type="radio" wire:model.live="payment_method" value="{{ $method->code }}" class="size-4 accent-ink-900">
                                <span class="text-sm font-semibold">{{ $method->name }}</span>
                            </span>
                            @if ($payment_method === $method->code && $method->instructions)
                                <span class="mt-3 block pl-7 text-xs leading-5 text-ink-600">{{ $method->instructions }}</span>
                                @if ($method->account_number)
                                    <span class="mt-2 block pl-7 text-sm">Number: <strong class="tracking-wider">{{ $method->account_number }}</strong> · Amount: <strong>{{ money($totals['total']) }}</strong></span>
                                @endif
                            @endif
                        </label>
                    @endforeach
                </div>
                @if ($selectedMethod?->requires_transaction_id)
                    <div class="mt-4 grid gap-4 sm:grid-cols-2">
                        <x-input name="transaction_id" model="transaction_id" label="Transaction ID (TrxID)" required placeholder="e.g. 9BX7K2LQ1M" />
                        <x-input name="sender_number" model="sender_number" label="Sent from number" type="tel" placeholder="01XXXXXXXXX" />
                    </div>
                @endif
            </section>

            <section>
                <label for="customer_note" class="label">Order note (optional)</label>
                <textarea id="customer_note" wire:model.blur="customer_note" rows="3" maxlength="500" class="field" placeholder="Delivery time preference, gift message…"></textarea>
            </section>
        </div>

        <aside class="h-fit rounded-3xl bg-cream-50 p-6 lg:sticky lg:top-24">
            <h2 class="font-display text-2xl font-semibold">Your order</h2>
            <ul class="mt-5 max-h-72 space-y-4 overflow-y-auto pr-1">
                @foreach ($totals['items'] as $item)
                    <li class="flex gap-3">
                        <div class="relative size-16 shrink-0 overflow-hidden rounded-lg bg-cream-100">
                            <x-product-image :product="$item->product" />
                            <span class="absolute -top-0 -right-0 grid size-5 place-items-center rounded-bl-lg bg-ink-900 text-[10px] text-white">{{ $item->quantity }}</span>
                        </div>
                        <div class="min-w-0 flex-1 text-xs">
                            <p class="line-clamp-2 font-medium text-ink-950">{{ $item->product->name }}</p>
                            @if ($item->variant)<p class="text-ink-600">{{ $item->variant->name() }}</p>@endif
                            @if ($item->engraving)<p class="text-leather-700">Engraving: {{ $item->engraving['text'] ?? 'Logo' }}</p>@endif
                        </div>
                        <p class="text-xs font-semibold">{{ money($item->lineTotal()) }}</p>
                    </li>
                @endforeach
            </ul>
            <dl class="mt-6 space-y-3 border-t border-ink-900/10 pt-5 text-sm">
                <div class="flex justify-between"><dt class="text-ink-600">Subtotal</dt><dd>{{ money($totals['subtotal']) }}</dd></div>
                @if ($totals['engraving'] > 0)<div class="flex justify-between"><dt class="text-ink-600">Engraving</dt><dd>{{ money($totals['engraving']) }}</dd></div>@endif
                @if ($totals['discount'] > 0)<div class="flex justify-between text-emerald-700"><dt>Discount ({{ $totals['coupon']->code }})</dt><dd>−{{ money($totals['discount']) }}</dd></div>@endif
                <div class="flex justify-between"><dt class="text-ink-600">Delivery</dt><dd>{{ $totals['shipping'] > 0 ? money($totals['shipping']) : 'Free' }}</dd></div>
                <div class="flex justify-between border-t border-ink-900/10 pt-4 text-lg font-semibold"><dt>Total</dt><dd>{{ money($totals['total']) }}</dd></div>
            </dl>

            <label class="mt-5 flex items-start gap-2 text-xs text-ink-600">
                <input type="checkbox" wire:model="accept_terms" class="mt-0.5 size-4 accent-ink-900">
                <span>I agree to the <a href="{{ route('pages.terms') }}" target="_blank" class="underline">terms</a> and <a href="{{ route('pages.return-policy') }}" target="_blank" class="underline">return policy</a>.</span>
            </label>
            @error('accept_terms')<p class="error">{{ $message }}</p>@enderror

            <button type="submit" class="btn-gold mt-5 w-full py-4" wire:loading.attr="disabled" wire:target="placeOrder">
                <span wire:loading.remove wire:target="placeOrder">Place order · {{ money($totals['total']) }}</span>
                <span wire:loading wire:target="placeOrder">Placing your order…</span>
            </button>
            @if ($note = setting('checkout_note'))<p class="mt-4 text-center text-xs text-ink-600">{{ $note }}</p>@endif
        </aside>
    </form>
</div>
