@php
    $product = $this->product;
    $stock = $this->stock();
    $price = $this->unitPrice();
    $regular = $this->regularPrice();
    $selectedColor = $colors->firstWhere('id', $colorId);
@endphp
<div class="space-y-6">
    <div class="flex items-baseline gap-3">
        <span class="text-3xl font-semibold text-ink-950">{{ money($price) }}</span>
        @if ($regular > $price)
            <span class="text-base text-ink-600/60 line-through">{{ money($regular) }}</span>
            <span class="rounded-full bg-gold-500/15 px-2.5 py-1 text-xs font-semibold text-gold-600">Save {{ money($regular - $price) }}</span>
        @endif
    </div>

    @if ($colors->isNotEmpty())
        <div>
            <p class="label">Color: <span class="font-semibold text-ink-950">{{ $selectedColor?->name }}</span></p>
            <div class="flex flex-wrap gap-2.5">
                @foreach ($colors as $color)
                    @php $colorStock = $this->variants->where('color_id', $color->id)->sum('stock_quantity'); @endphp
                    <button type="button" wire:click="selectColor({{ $color->id }})"
                            class="relative size-10 rounded-full ring-offset-2 transition {{ $colorId === $color->id ? 'ring-2 ring-ink-900' : 'ring-1 ring-ink-900/15 hover:ring-ink-900/40' }}"
                            style="background-color: {{ $color->value ?? '#ccc' }}" title="{{ $color->name }}{{ $colorStock ? '' : ' (sold out)' }}"
                            aria-label="{{ $color->name }}" aria-pressed="{{ $colorId === $color->id ? 'true' : 'false' }}">
                        @unless ($colorStock)
                            <span class="absolute inset-0 grid place-items-center"><span class="h-px w-8 rotate-45 bg-white/90"></span></span>
                        @endunless
                    </button>
                @endforeach
            </div>
        </div>
    @endif

    @if ($materials->count() > 1)
        <div>
            <label class="label" for="material-{{ $productId }}">Material</label>
            <select id="material-{{ $productId }}" wire:model.live="materialId" class="field">
                @foreach ($materials as $material)<option value="{{ $material->id }}">{{ $material->name }}</option>@endforeach
            </select>
        </div>
    @endif

    @if ($sizes->count() > 1)
        <div>
            <p class="label">Size</p>
            <div class="flex flex-wrap gap-2">
                @foreach ($sizes as $size)
                    <button type="button" wire:click="$set('sizeId', {{ $size->id }})" class="rounded-full border px-4 py-2 text-xs font-medium {{ $sizeId === $size->id ? 'border-ink-900 bg-ink-900 text-white' : 'border-ink-900/15 hover:border-ink-900' }}">{{ $size->name }}</button>
                @endforeach
            </div>
        </div>
    @endif

    <p class="flex items-center gap-2 text-xs font-medium {{ $stock > 0 ? ($stock <= 5 ? 'text-amber-700' : 'text-emerald-700') : 'text-red-700' }}">
        <span class="size-2 rounded-full {{ $stock > 0 ? ($stock <= 5 ? 'bg-amber-500' : 'bg-emerald-500') : 'bg-red-500' }}"></span>
        {{ $stock > 0 ? ($stock <= 5 ? "Only {$stock} left — order soon" : 'In stock, ready to ship') : 'Out of stock' }}
    </p>

    @if ($product->engraving_available && ! $compact)
        <div class="rounded-2xl border border-ink-900/10 bg-cream-50 p-4">
            <label class="flex cursor-pointer items-center justify-between gap-3">
                <span class="flex items-center gap-3">
                    <x-lx-icon name="sparkles" class="size-5 text-gold-600" />
                    <span>
                        <span class="block text-sm font-semibold text-ink-950">Add laser engraving</span>
                        <span class="block text-xs text-ink-600">+{{ money($product->engraving_price) }} per item · 2–3 extra days</span>
                    </span>
                </span>
                <input type="checkbox" wire:model.live="engrave" class="size-5 rounded border-ink-900/30 accent-ink-900">
            </label>

            @if ($engrave)
                <div class="mt-4 grid gap-3 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <span class="label">Engraving type</span>
                        <div class="grid grid-cols-4 gap-2">
                            @foreach (['initials' => 'Initials', 'name' => 'Name', 'quote' => 'Quote', 'logo' => 'Logo'] as $value => $label)
                                <button type="button" wire:click="$set('engravingType', '{{ $value }}')" class="rounded-lg border px-2 py-2 text-xs font-medium {{ $engravingType === $value ? 'border-ink-900 bg-ink-900 text-white' : 'border-ink-900/15 bg-white' }}">{{ $label }}</button>
                            @endforeach
                        </div>
                    </div>
                    @if ($engravingType === 'logo')
                        <div class="sm:col-span-2">
                            <label class="label" for="logo-{{ $productId }}">Logo instruction</label>
                            <textarea id="logo-{{ $productId }}" wire:model.blur="engravingLogo" rows="2" maxlength="500" class="field" placeholder="Company name, size, placement. We will WhatsApp you for the logo file."></textarea>
                            @error('engravingLogo')<p class="error">{{ $message }}</p>@enderror
                        </div>
                    @else
                        <div class="sm:col-span-2">
                            <label class="label" for="text-{{ $productId }}">Text to engrave <span class="text-ink-600/60">(max 30)</span></label>
                            <input id="text-{{ $productId }}" type="text" wire:model.live.debounce.300ms="engravingText" maxlength="30" class="field" placeholder="{{ $engravingType === 'initials' ? 'A.R.K' : ($engravingType === 'name' ? 'Ashik Rahman' : 'Carry with pride') }}">
                            @error('engravingText')<p class="error">{{ $message }}</p>@enderror
                        </div>
                        @if ($engravingText !== '')
                            <div class="rounded-xl bg-leather-800 px-4 py-5 text-center sm:col-span-2" aria-label="Engraving preview">
                                <span class="text-lg tracking-[0.15em] text-tan-300/90 {{ $engravingFont === 'Script' ? 'italic font-display' : ($engravingFont === 'Classic Serif' ? 'font-display' : ($engravingFont === 'Block Capitals' ? 'uppercase font-semibold' : '')) }}">{{ $engravingText }}</span>
                            </div>
                        @endif
                    @endif
                    <div>
                        <label class="label" for="font-{{ $productId }}">Font</label>
                        <select id="font-{{ $productId }}" wire:model.live="engravingFont" class="field">
                            @foreach ($fonts as $font)<option>{{ $font }}</option>@endforeach
                        </select>
                    </div>
                    <div>
                        <label class="label" for="position-{{ $productId }}">Position</label>
                        <select id="position-{{ $productId }}" wire:model="engravingPosition" class="field">
                            @foreach ($positions as $position)<option>{{ $position }}</option>@endforeach
                        </select>
                    </div>
                </div>
            @endif
        </div>
    @endif

    @error('cart')<p class="rounded-xl bg-red-50 px-4 py-3 text-sm text-red-800">{{ $message }}</p>@enderror
    @error('quantity')<p class="error">{{ $message }}</p>@enderror
    @error('variant')<p class="error">{{ $message }}</p>@enderror

    <div class="flex flex-wrap items-stretch gap-3">
        <div class="flex items-center rounded-full border border-ink-900/15">
            <button type="button" wire:click="decrement" class="grid size-12 place-items-center" aria-label="Decrease quantity"><x-lx-icon name="minus" class="size-4" /></button>
            <span class="w-8 text-center text-sm font-semibold" aria-live="polite">{{ $quantity }}</span>
            <button type="button" wire:click="increment" class="grid size-12 place-items-center" aria-label="Increase quantity"><x-lx-icon name="plus" class="size-4" /></button>
        </div>
        <button type="button" wire:click="add" wire:loading.attr="disabled" @disabled($stock < 1) class="btn-dark flex-1">
            <span wire:loading.remove wire:target="add">Add to cart</span>
            <span wire:loading wire:target="add">Adding…</span>
        </button>
        @unless ($compact)
            <button type="button" wire:click="buyNow" wire:loading.attr="disabled" @disabled($stock < 1) class="btn-gold w-full sm:w-auto sm:flex-1">Buy now</button>
        @endunless
    </div>
</div>
