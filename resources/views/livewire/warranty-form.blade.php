<div class="rounded-sm border border-line p-6 sm:p-8">
    @if ($reference)
        <div class="py-6 text-center" role="status">
            <span class="mx-auto flex size-12 items-center justify-center rounded-full bg-success-bg text-success"><x-lx-icon name="check" class="size-6" /></span>
            <h2 class="mt-5 font-display text-[1.875rem] text-ink-950">{{ $type === 'claim' ? 'Claim received' : 'Warranty registered' }}</h2>
            <p class="mt-2 text-[15px] text-ink-600">Your reference number is <strong class="tracking-wide text-ink-950">{{ $reference }}</strong>.</p>
            <p class="mx-auto mt-3 max-w-sm text-sm text-ink-600">Keep it for your records. We review submissions within two working days.</p>
        </div>
    @else
        <div class="grid grid-cols-2 border-b border-line" role="tablist" aria-label="Request type">
            @foreach (['registration' => 'Register a product', 'claim' => 'File a claim'] as $value => $label)
                <button type="button" role="tab" aria-selected="{{ $type === $value ? 'true' : 'false' }}" wire:click="$set('type', '{{ $value }}')"
                        class="-mb-px border-b-2 pb-3 text-[15px] {{ $type === $value ? 'border-ink-950 font-medium text-ink-950' : 'border-transparent text-ink-600 hover:text-ink-950' }}">{{ $label }}</button>
            @endforeach
        </div>

        @if (! $orderId)
            <form wire:submit="findOrder" class="mt-6 grid gap-5 sm:grid-cols-2" novalidate>
                <p class="text-[15px] text-ink-600 sm:col-span-2">Step 1 of 2 — find your order.</p>
                <x-form.input name="order_number" model="order_number" label="Order number" required autocomplete="off" hint="Starts with LX, e.g. on your invoice." />
                <x-form.input name="phone" model="phone" label="Phone used at checkout" type="tel" required inputmode="numeric" autocomplete="tel" />
                <x-form.submit label="Find my order" loading="Searching…" target="findOrder" class="sm:col-span-2 sm:justify-self-start" />
            </form>
        @else
            <form wire:submit="submit" class="mt-6 grid gap-5 sm:grid-cols-2" novalidate>
                <div class="flex items-center justify-between gap-3 rounded-xs bg-paper px-4 py-3 text-sm sm:col-span-2">
                    <span>Step 2 of 2 — order <strong>{{ strtoupper($order_number) }}</strong></span>
                    <button type="button" wire:click="resetOrder" class="text-leather-700 underline underline-offset-4">Change order</button>
                </div>
                <x-form.select name="order_item_id" model="order_item_id" label="Product" required placeholder="Choose a product"
                    :options="$items->mapWithKeys(fn ($i) => [$i->id => $i->product_name.($i->variant_name ? ' — '.$i->variant_name : '').($i->warranty_months ? ' ('.$i->warranty_months.'-month warranty)' : '')])->all()" class="sm:col-span-2" />
                <x-form.input name="customer_name" model="customer_name" label="Your name" required autocomplete="name" />
                <x-form.input name="email" model="email" label="Email (optional)" type="email" autocomplete="email" />
                @if ($type === 'claim')
                    <x-form.textarea name="issue_details" model="issue_details" label="Describe the problem" required rows="4" maxlength="2000" hint="What happened and when you noticed it." class="sm:col-span-2" />
                @endif
                <x-form.submit :label="$type === 'claim' ? 'Submit claim' : 'Register warranty'" loading="Submitting…" target="submit" class="sm:col-span-2 sm:justify-self-start" />
            </form>
        @endif
    @endif
</div>
