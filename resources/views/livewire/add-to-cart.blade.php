@php
    $product = $this->product;
    $stock = $this->stock();
    $price = $this->unitPrice();
    $regular = $this->regularPrice();
    $selectedColor = $colors->firstWhere('id', $colorId);
    $uid = 'atc-'.$productId.($compact ? '-qv' : '');
@endphp
<div class="space-y-6">
    <div>
        <p class="flex flex-wrap items-baseline gap-x-3">
            @if ($regular > $price)
                <span class="sr-only">Sale price</span>
                <span class="text-[1.75rem] leading-none font-medium text-ink-950">{{ money($price) }}</span>
                <span class="sr-only">Regular price</span>
                <s class="text-base text-ink-500">{{ money($regular) }}</s>
                <span class="text-sm font-medium text-leather-700">You save {{ money($regular - $price) }}</span>
            @else
                <span class="text-[1.75rem] leading-none font-medium text-ink-950">{{ money($price) }}</span>
            @endif
        </p>
        <p class="mt-3 flex items-center gap-2 text-sm {{ $stock > 0 ? ($stock <= 5 ? 'text-warning' : 'text-success') : 'text-danger' }}" aria-live="polite">
            <span class="size-2 rounded-full bg-current" aria-hidden="true"></span>
            {{ $stock > 0 ? ($stock <= 5 ? "Low stock — {$stock} left" : 'In stock, ready to dispatch') : 'Out of stock' }}
        </p>
    </div>

    @if ($colors->isNotEmpty())
        <div>
            <p id="{{ $uid }}-color" class="text-sm text-ink-700">Colour: <span class="font-medium text-ink-950">{{ $selectedColor?->name }}</span></p>
            <div class="mt-3 flex flex-wrap gap-3" role="radiogroup" aria-labelledby="{{ $uid }}-color">
                @foreach ($colors as $color)
                    @php $colorStock = $this->variants->where('color_id', $color->id)->sum('stock_quantity'); @endphp
                    <button type="button" role="radio" aria-checked="{{ $colorId === $color->id ? 'true' : 'false' }}" wire:click="selectColor({{ $color->id }})"
                            class="relative size-9 rounded-full ring-offset-2 transition {{ $colorId === $color->id ? 'ring-2 ring-ink-950' : 'ring-1 ring-line-strong hover:ring-ink-500' }}"
                            style="background-color: {{ $color->value ?? '#ccc' }}">
                        <span class="sr-only">{{ $color->name }}{{ $colorStock ? '' : ' (sold out)' }}</span>
                        @unless ($colorStock)<span class="absolute inset-0 flex items-center justify-center" aria-hidden="true"><span class="h-px w-9 rotate-45 bg-white"></span></span>@endunless
                    </button>
                @endforeach
            </div>
        </div>
    @endif

    @if ($materials->count() > 1)
        <div>
            <label class="label" for="{{ $uid }}-material">Material</label>
            <select id="{{ $uid }}-material" wire:model.live="materialId" class="field">
                @foreach ($materials as $material)<option value="{{ $material->id }}">{{ $material->name }}</option>@endforeach
            </select>
        </div>
    @endif

    @if ($sizes->count() > 1)
        <div>
            <p id="{{ $uid }}-size" class="label">Size</p>
            <div class="flex flex-wrap gap-2" role="radiogroup" aria-labelledby="{{ $uid }}-size">
                @foreach ($sizes as $size)
                    <button type="button" role="radio" aria-checked="{{ $sizeId === $size->id ? 'true' : 'false' }}" wire:click="$set('sizeId', {{ $size->id }})"
                            class="min-h-10 rounded-xs border px-4 text-sm {{ $sizeId === $size->id ? 'border-ink-950 bg-ink-950 text-white' : 'border-line-strong hover:border-ink-950' }}">{{ $size->name }}</button>
                @endforeach
            </div>
        </div>
    @endif

    @if ($product->engraving_available && ! $compact)
        <fieldset class="rounded-sm border border-line-strong">
            <legend class="sr-only">Laser engraving</legend>
            <label class="flex cursor-pointer items-start gap-3 p-4">
                <input type="checkbox" wire:model.live="engrave" class="check mt-0.5">
                <span>
                    <span class="block text-[15px] font-medium text-ink-950">Add laser engraving <span class="font-normal text-ink-600">+{{ money($product->engraving_price) }}</span></span>
                    <span class="mt-0.5 block text-[13px] text-ink-500">We confirm the text with you before engraving. Adds two to three working days.</span>
                </span>
            </label>

            @if ($engrave)
                <div class="grid gap-4 border-t border-line p-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <p id="{{ $uid }}-type" class="label">What should we engrave?</p>
                        <div class="grid grid-cols-2 gap-2 sm:grid-cols-4" role="radiogroup" aria-labelledby="{{ $uid }}-type">
                            @foreach (['initials' => 'Initials', 'name' => 'Name', 'quote' => 'Short line', 'logo' => 'Logo'] as $value => $label)
                                <button type="button" role="radio" aria-checked="{{ $engravingType === $value ? 'true' : 'false' }}" wire:click="$set('engravingType', '{{ $value }}')"
                                        class="min-h-10 rounded-xs border px-2 text-sm {{ $engravingType === $value ? 'border-ink-950 bg-ink-950 text-white' : 'border-line-strong bg-white hover:border-ink-950' }}">{{ $label }}</button>
                            @endforeach
                        </div>
                    </div>

                    @if ($engravingType === 'logo')
                        <div class="sm:col-span-2">
                            <label for="{{ $uid }}-logo" class="label">Describe the logo and placement</label>
                            <textarea id="{{ $uid }}-logo" wire:model.blur="engravingLogo" rows="3" maxlength="500" class="field" @error('engravingLogo') aria-invalid="true" aria-describedby="{{ $uid }}-logo-error" @enderror></textarea>
                            <p class="hint">After you order, we will ask for the logo file on WhatsApp or email.</p>
                            @error('engravingLogo')<p id="{{ $uid }}-logo-error" class="error">{{ $message }}</p>@enderror
                        </div>
                    @else
                        <div class="sm:col-span-2">
                            <label for="{{ $uid }}-text" class="label">Text to engrave</label>
                            <input id="{{ $uid }}-text" type="text" wire:model.live.debounce.300ms="engravingText" maxlength="30" class="field"
                                   @error('engravingText') aria-invalid="true" aria-describedby="{{ $uid }}-text-error" @enderror>
                            <p class="hint">Up to 30 letters, numbers and basic punctuation.</p>
                            @error('engravingText')<p id="{{ $uid }}-text-error" class="error">{{ $message }}</p>@enderror
                        </div>
                        @if ($engravingText !== '')
                            <div class="material-tile flex min-h-24 items-center justify-center rounded-xs px-4 sm:col-span-2" style="--tile: {{ $selectedColor?->value ?? '#5b3a24' }}" aria-label="Engraving preview">
                                <span class="text-[1.375rem] tracking-[0.12em] text-transparent [text-shadow:-1px_-1px_0_rgb(0_0_0/0.45),1px_1px_0_rgb(255_255_255/0.2)] {{ ['Classic Serif' => 'font-display font-semibold', 'Modern Sans' => 'font-sans font-medium', 'Script' => 'font-display italic', 'Block Capitals' => 'font-sans font-bold uppercase'][$engravingFont] ?? '' }}">{{ $engravingText }}</span>
                            </div>
                        @endif
                    @endif

                    <div>
                        <label for="{{ $uid }}-font" class="label">Style</label>
                        <select id="{{ $uid }}-font" wire:model.live="engravingFont" class="field">
                            @foreach ($fonts as $font)<option>{{ $font }}</option>@endforeach
                        </select>
                    </div>
                    <div>
                        <label for="{{ $uid }}-position" class="label">Position</label>
                        <select id="{{ $uid }}-position" wire:model="engravingPosition" class="field">
                            @foreach ($positions as $position)<option>{{ $position }}</option>@endforeach
                        </select>
                    </div>
                </div>
            @endif
        </fieldset>
    @endif

    @error('cart')<x-ui.alert type="error">{{ $message }}</x-ui.alert>@enderror
    @error('quantity')<x-ui.alert type="error">{{ $message }}</x-ui.alert>@enderror
    @error('variant')<x-ui.alert type="error">{{ $message }}</x-ui.alert>@enderror

    <div class="flex flex-wrap gap-3">
        <div class="flex items-center rounded-xs border border-line-strong" role="group" aria-label="Quantity">
            <button type="button" wire:click="decrement" class="inline-flex size-12 items-center justify-center text-ink-700 hover:text-ink-950 disabled:opacity-40" @disabled($quantity <= 1) aria-label="Decrease quantity"><x-lx-icon name="minus" class="size-4" /></button>
            <span class="w-8 text-center text-[15px] font-medium" aria-live="polite"><span class="sr-only">Quantity </span>{{ $quantity }}</span>
            <button type="button" wire:click="increment" class="inline-flex size-12 items-center justify-center text-ink-700 hover:text-ink-950 disabled:opacity-40" @disabled($quantity >= $stock) aria-label="Increase quantity"><x-lx-icon name="plus" class="size-4" /></button>
        </div>
        <button type="button" wire:click="add" wire:loading.attr="disabled" wire:target="add,buyNow" @disabled($stock < 1) class="btn-primary flex-1">
            <span wire:loading.remove wire:target="add">{{ $stock < 1 ? 'Out of stock' : 'Add to cart' }}</span>
            <span wire:loading wire:target="add">Adding…</span>
        </button>
        @unless ($compact)
            <button type="button" wire:click="buyNow" wire:loading.attr="disabled" wire:target="add,buyNow" @disabled($stock < 1) class="btn-secondary w-full">
                <span wire:loading.remove wire:target="buyNow">Buy now</span>
                <span wire:loading wire:target="buyNow">Going to checkout…</span>
            </button>
        @endunless
    </div>
</div>
