@extends('layouts.app')

@section('content')
<x-page-header eyebrow="Personalisation" :title="$section->title ?? 'Laser engraving'" :subtitle="$section->subtitle ?? null" :crumbs="['Engraving' => null]" />

<section class="container-lx py-16">
    <ol class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
        @foreach ($section->content['items'] ?? [] as $item)
            <li class="card p-6">
                <span class="font-display text-4xl font-semibold text-tan-300">{{ $item['meta'] ?? $loop->iteration }}</span>
                <h2 class="mt-3 font-sans text-[15px] font-semibold">{{ $item['title'] }}</h2>
                <p class="mt-2 text-sm leading-6 text-ink-600">{{ $item['text'] ?? '' }}</p>
            </li>
        @endforeach
    </ol>

    <div class="mt-16 grid gap-10 lg:grid-cols-[1fr_1.2fr]">
        <div>
            <h2 class="section-title">Already own a LeatherX piece?</h2>
            <p class="mt-4 text-sm leading-6 text-ink-600">The fastest way is to add engraving on the product page when you order. For pieces bought earlier, bulk logo work or anything custom, send us a request and we will confirm the price and timeline.</p>
            <ul class="mt-6 space-y-3 text-sm text-ink-700">
                <li class="flex gap-3"><x-lx-icon name="check" class="size-5 text-gold-600" /> Names, initials, short quotes or logos</li>
                <li class="flex gap-3"><x-lx-icon name="check" class="size-5 text-gold-600" /> Four font styles, several positions</li>
                <li class="flex gap-3"><x-lx-icon name="check" class="size-5 text-gold-600" /> We check every request before engraving</li>
            </ul>
        </div>
        <form method="POST" action="{{ route('engraving.store') }}" class="card grid gap-4 p-6 sm:grid-cols-2 sm:p-8" x-data="{ type: '{{ old('type', 'name') }}' }">
            @csrf
            <x-input name="customer_name" label="Your name" required />
            <x-input name="phone" label="Mobile" type="tel" required placeholder="01XXXXXXXXX" />
            <x-input name="email" label="Email" type="email" class="sm:col-span-2" />
            <div class="sm:col-span-2">
                <label for="product_id" class="label">Product</label>
                <select id="product_id" name="product_id" class="field">
                    <option value="">Not sure / custom</option>
                    @foreach ($products as $p)<option value="{{ $p->id }}" @selected(old('product_id') == $p->id)>{{ $p->name }}</option>@endforeach
                </select>
            </div>
            <div>
                <label for="type" class="label">Type</label>
                <select id="type" name="type" x-model="type" class="field">
                    @foreach (\App\Enums\EngravingType::cases() as $t)<option value="{{ $t->value }}">{{ $t->getLabel() }}</option>@endforeach
                </select>
            </div>
            <x-input name="quantity" label="Quantity" type="number" min="1" value="1" required />
            <div class="sm:col-span-2" x-show="type !== 'logo'"><x-input name="text" label="Text to engrave" maxlength="40" /></div>
            <div class="sm:col-span-2" x-show="type === 'logo'" x-cloak>
                <label for="logo_instruction" class="label">Logo instructions</label>
                <textarea id="logo_instruction" name="logo_instruction" rows="3" class="field" placeholder="Describe your logo, size and placement. We'll WhatsApp you for the file.">{{ old('logo_instruction') }}</textarea>
                @error('logo_instruction')<p class="error">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="font" class="label">Font</label>
                <select id="font" name="font" class="field">@foreach (\App\Models\EngravingRequest::FONTS as $f)<option>{{ $f }}</option>@endforeach</select>
            </div>
            <div>
                <label for="position" class="label">Position</label>
                <select id="position" name="position" class="field">@foreach (\App\Models\EngravingRequest::POSITIONS as $pos)<option>{{ $pos }}</option>@endforeach</select>
            </div>
            <button class="btn-dark sm:col-span-2">Send engraving request</button>
        </form>
    </div>
</section>
@endsection
