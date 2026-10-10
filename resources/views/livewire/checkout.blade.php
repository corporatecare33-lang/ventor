<section class="container-site py-10 lg:py-14" aria-labelledby="checkout-title">
    <x-ui.breadcrumbs :items="['Cart' => route('cart'), 'Checkout' => null]" />
    <h1 id="checkout-title" class="mt-6 text-h1 text-ink-950">Checkout</h1>
    @guest
        <p class="mt-3 text-[15px] text-ink-600">No account needed. <a href="{{ route('login') }}" class="text-leather-700 underline underline-offset-4">Sign in</a> to use your saved addresses.</p>
    @endguest

    @error('checkout')<x-ui.alert type="error" class="mt-6">{{ $message }}</x-ui.alert>@enderror
    @if ($errors->any() && ! $errors->has('checkout'))
        <x-ui.alert type="error" class="mt-6">Please check the highlighted fields below.</x-ui.alert>
    @endif

    <form wire:submit="placeOrder" class="mt-8 grid gap-12 lg:grid-cols-12" novalidate>
        <div class="space-y-12 lg:col-span-7">
            <fieldset>
                <legend class="flex items-center gap-3 font-display text-2xl text-ink-950"><span class="font-sans text-sm text-ink-500">1</span> Delivery details</legend>

                @if ($addresses->isNotEmpty())
                    <div class="mt-5 flex flex-wrap gap-2" role="group" aria-label="Saved addresses">
                        @foreach ($addresses as $saved)
                            <button type="button" wire:click="useAddress({{ $saved->id }})" aria-pressed="{{ $address_id === $saved->id ? 'true' : 'false' }}"
                                    class="rounded-xs border px-4 py-2.5 text-left text-[13px] {{ $address_id === $saved->id ? 'border-ink-950 bg-ink-950 text-white' : 'border-line-strong hover:border-ink-950' }}">
                                <span class="block font-medium">{{ $saved->label }}</span>
                                <span class="block opacity-75">{{ str($saved->address)->limit(30) }}</span>
                            </button>
                        @endforeach
                    </div>
                @endif

                <div class="mt-6 grid gap-5 sm:grid-cols-2">
                    <x-form.input name="customer_name" model="customer_name" label="Full name" required autocomplete="name" />
                    <x-form.input name="phone" model="phone" label="Mobile number" required type="tel" inputmode="numeric" autocomplete="tel" hint="11 digits, starting with 01. We call this number to confirm." />
                    <x-form.input name="email" model="email" label="Email (optional)" type="email" autocomplete="email" hint="For your order confirmation." class="sm:col-span-2" />
                    <x-form.input name="address" model="address" label="Address" required autocomplete="street-address" hint="House, road, block or village." class="sm:col-span-2" />
                    <x-form.input name="area" model="area" label="Area / thana" autocomplete="address-level3" />
                    <x-form.input name="city" model="city" label="City" required autocomplete="address-level2" />
                    <x-form.input name="district" model="district" label="District" autocomplete="address-level1" />
                    <x-form.input name="postal_code" model="postal_code" label="Postcode" inputmode="numeric" autocomplete="postal-code" />
                </div>
                @auth
                    @unless ($address_id)
                        <x-form.checkbox name="save_address" model="save_address" label="Save this address to my account" class="mt-5" />
                    @endunless
                @endauth
            </fieldset>

            <fieldset>
                <legend class="flex items-center gap-3 font-display text-2xl text-ink-950"><span class="font-sans text-sm text-ink-500">2</span> Delivery area</legend>
                <div class="mt-5 grid gap-3 sm:grid-cols-3">
                    @foreach ($zones as $zone)
                        @php $charge = $zone->chargeFor($totals['subtotal'] - $totals['discount']); @endphp
                        <label class="relative cursor-pointer rounded-xs border p-4 transition has-focus-visible:outline-2 has-focus-visible:outline-brass-500 {{ $shipping_zone_id === $zone->id ? 'border-ink-950 ring-1 ring-ink-950' : 'border-line-strong hover:border-ink-500' }}">
                            <input type="radio" wire:model.live="shipping_zone_id" value="{{ $zone->id }}" class="sr-only">
                            <span class="block text-[15px] font-medium text-ink-950">{{ $zone->name }}</span>
                            <span class="mt-0.5 block text-[13px] text-ink-500">{{ $zone->estimate() }}</span>
                            <span class="mt-2 block text-[15px] text-ink-950">{{ $charge > 0 ? money($charge) : 'Free' }}</span>
                        </label>
                    @endforeach
                </div>
                @error('shipping_zone_id')<p class="error">{{ $message }}</p>@enderror
            </fieldset>

            <fieldset>
                <legend class="flex items-center gap-3 font-display text-2xl text-ink-950"><span class="font-sans text-sm text-ink-500">3</span> Payment</legend>
                <div class="mt-5 space-y-3">
                    @foreach ($methods as $method)
                        <label class="block cursor-pointer rounded-xs border p-4 transition {{ $payment_method === $method->code ? 'border-ink-950 ring-1 ring-ink-950' : 'border-line-strong hover:border-ink-500' }}">
                            <span class="flex items-center gap-3">
                                <input type="radio" wire:model.live="payment_method" value="{{ $method->code }}" class="check">
                                <span class="text-[15px] font-medium text-ink-950">{{ $method->name }}</span>
                            </span>
                            @if ($payment_method === $method->code && $method->instructions)
                                <span class="mt-2 block pl-7.5 text-sm leading-relaxed text-ink-600">{{ $method->instructions }}</span>
                                @if ($method->account_number)
                                    <span class="mt-2 block pl-7.5 text-sm text-ink-950">Send to <strong class="tracking-wide">{{ $method->account_number }}</strong> · Amount <strong>{{ money($totals['total']) }}</strong></span>
                                @endif
                            @endif
                        </label>
                    @endforeach
                </div>
                @if ($selectedMethod?->requires_transaction_id)
                    <div class="mt-5 grid gap-5 sm:grid-cols-2">
                        <x-form.input name="transaction_id" model="transaction_id" label="Transaction ID (TrxID)" required autocomplete="off" hint="Shown in the confirmation SMS." />
                        <x-form.input name="sender_number" model="sender_number" label="Sent from number" type="tel" inputmode="numeric" />
                    </div>
                @endif
            </fieldset>

            <x-form.textarea name="customer_note" model="customer_note" label="Order note (optional)" rows="3" maxlength="500" hint="Delivery time preference or a gift message." />
        </div>

        <aside class="lg:col-span-5" aria-labelledby="order-summary-title">
            <div class="rounded-sm bg-paper p-6 sm:p-8 lg:sticky lg:top-28">
                <h2 id="order-summary-title" class="font-display text-2xl text-ink-950">Your order</h2>
                <ul class="mt-6 space-y-5">
                    @foreach ($totals['items'] as $item)
                        <li class="flex gap-4">
                            <div class="relative h-20 w-16 shrink-0 overflow-hidden rounded-xs bg-paper-200">
                                <x-product.media :product="$item->product" sizes="4rem" />
                            </div>
                            <div class="min-w-0 flex-1 text-sm">
                                <p class="font-medium text-ink-950">{{ $item->product->name }}</p>
                                <p class="text-ink-500">{{ $item->variant?->name() }}@if ($item->variant) · @endif Qty {{ $item->quantity }}</p>
                                @if ($item->engraving)<p class="text-ink-600">Engraving: {{ $item->engraving['text'] ?? 'Logo' }}</p>@endif
                            </div>
                            <p class="text-sm font-medium text-ink-950">{{ money($item->lineTotal()) }}</p>
                        </li>
                    @endforeach
                </ul>
                <dl class="mt-6 space-y-3 border-t border-line-strong pt-6 text-[15px]">
                    <div class="flex justify-between"><dt class="text-ink-600">Subtotal</dt><dd>{{ money($totals['subtotal']) }}</dd></div>
                    @if ($totals['engraving'] > 0)<div class="flex justify-between"><dt class="text-ink-600">Engraving</dt><dd>{{ money($totals['engraving']) }}</dd></div>@endif
                    @if ($totals['discount'] > 0)<div class="flex justify-between text-success"><dt>Discount ({{ $totals['coupon']->code }})</dt><dd>−{{ money($totals['discount']) }}</dd></div>@endif
                    <div class="flex justify-between"><dt class="text-ink-600">Delivery</dt><dd>{{ $totals['shipping'] > 0 ? money($totals['shipping']) : 'Free' }}</dd></div>
                    <div class="flex justify-between border-t border-line-strong pt-4 text-lg font-medium text-ink-950"><dt>Total</dt><dd>{{ money($totals['total']) }}</dd></div>
                </dl>

                <x-form.checkbox name="accept_terms" model="accept_terms" class="mt-6" label="I agree to the terms and the return policy." />
                <p class="mt-1.5 pl-7.5 text-[13px] text-ink-500">
                    <a href="{{ route('pages.terms') }}" target="_blank" class="underline underline-offset-4">Terms</a> ·
                    <a href="{{ route('pages.return-policy') }}" target="_blank" class="underline underline-offset-4">Return policy</a>
                </p>

                <x-form.submit label="Place order · {{ money($totals['total']) }}" loading="Placing your order…" target="placeOrder" class="mt-6 w-full" />
                @if ($note = setting('checkout_note'))<p class="mt-4 text-center text-[13px] text-ink-500">{{ $note }}</p>@endif
            </div>
        </aside>
    </form>
</section>
