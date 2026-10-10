<div class="card p-6 sm:p-8">
    @if ($reference)
        <div class="py-8 text-center">
            <span class="mx-auto grid size-14 place-items-center rounded-full bg-emerald-50 text-emerald-700"><x-lx-icon name="check" class="size-7" /></span>
            <h2 class="mt-5 font-display text-3xl font-semibold">Submitted successfully</h2>
            <p class="mt-2 text-sm text-ink-600">Your reference number is</p>
            <p class="mt-1 text-2xl font-semibold tracking-wider">{{ $reference }}</p>
            <p class="mx-auto mt-4 max-w-sm text-sm text-ink-600">Our team will review it and contact you within 1–2 working days.</p>
        </div>
    @else
        <div class="mb-6 grid grid-cols-2 rounded-full bg-cream-100 p-1 text-sm font-medium">
            <button type="button" wire:click="$set('type', 'registration')" class="rounded-full py-2.5 {{ $type === 'registration' ? 'bg-ink-900 text-white' : 'text-ink-700' }}">Register warranty</button>
            <button type="button" wire:click="$set('type', 'claim')" class="rounded-full py-2.5 {{ $type === 'claim' ? 'bg-ink-900 text-white' : 'text-ink-700' }}">File a claim</button>
        </div>

        @if (! $orderId)
            <form wire:submit="findOrder" class="grid gap-4 sm:grid-cols-2">
                <x-input name="order_number" model="order_number" label="Order number" required placeholder="LX2410…" />
                <x-input name="phone" model="phone" label="Phone used for the order" required type="tel" placeholder="01XXXXXXXXX" />
                <button class="btn-dark sm:col-span-2" wire:loading.attr="disabled">Find my order</button>
            </form>
        @else
            <form wire:submit="submit" class="grid gap-4 sm:grid-cols-2">
                <div class="flex items-center justify-between rounded-xl bg-cream-50 px-4 py-3 text-sm sm:col-span-2">
                    <span>Order <strong>{{ strtoupper($order_number) }}</strong> found</span>
                    <button type="button" wire:click="resetOrder" class="text-xs underline">Change</button>
                </div>
                <div class="sm:col-span-2">
                    <label for="order_item_id" class="label">Product <span class="text-red-700">*</span></label>
                    <select id="order_item_id" wire:model="order_item_id" class="field">
                        <option value="">Choose a product…</option>
                        @foreach ($items as $item)
                            <option value="{{ $item->id }}">{{ $item->product_name }}{{ $item->variant_name ? ' — '.$item->variant_name : '' }} ({{ $item->warranty_months }} months warranty)</option>
                        @endforeach
                    </select>
                    @error('order_item_id')<p class="error">{{ $message }}</p>@enderror
                </div>
                <x-input name="customer_name" model="customer_name" label="Your name" required />
                <x-input name="email" model="email" label="Email" type="email" />
                @if ($type === 'claim')
                    <div class="sm:col-span-2">
                        <label for="issue_details" class="label">Describe the issue <span class="text-red-700">*</span></label>
                        <textarea id="issue_details" wire:model.blur="issue_details" rows="4" maxlength="2000" class="field" placeholder="What happened, when did you notice it?"></textarea>
                        @error('issue_details')<p class="error">{{ $message }}</p>@enderror
                    </div>
                @endif
                <button class="btn-gold sm:col-span-2" wire:loading.attr="disabled">{{ $type === 'claim' ? 'Submit claim' : 'Register warranty' }}</button>
            </form>
        @endif
    @endif
</div>
