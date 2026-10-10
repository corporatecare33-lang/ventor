@extends('layouts.app')

@section('content')
<x-ui.page-header eyebrow="Personalisation" :title="$section->title ?? 'Laser engraving'" :intro="$section->subtitle ?? null" :crumbs="['Engraving' => null]" />

<section class="section-y" aria-labelledby="process-title">
    <div class="container-site grid gap-12 lg:grid-cols-12">
        <div class="lg:col-span-5">
            <x-ui.section-heading id="process-title" title="How engraving works" />
            <ol class="mt-8 space-y-6">
                @foreach ($section->content['items'] ?? [] as $item)
                    <li class="grid grid-cols-[2.5rem_1fr] gap-x-4">
                        <span class="font-display text-2xl leading-none text-leather-600" aria-hidden="true">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                        <div>
                            <h3 class="font-sans text-base font-medium text-ink-950">{{ $item['title'] }}</h3>
                            @if (! empty($item['text']))<p class="mt-1 text-[15px] leading-relaxed text-ink-600">{{ $item['text'] }}</p>@endif
                        </div>
                    </li>
                @endforeach
            </ol>
            <dl class="mt-10 grid grid-cols-2 gap-6 border-t border-line pt-6 text-[15px]">
                <div><dt class="label-caps text-ink-500">Styles</dt><dd class="mt-2 text-ink-950">{{ implode(', ', \App\Models\EngravingRequest::FONTS) }}</dd></div>
                <div><dt class="label-caps text-ink-500">Price</dt><dd class="mt-2 text-ink-950">Shown on each product page</dd></div>
            </dl>
        </div>
        @if ($image = $section?->getFirstMedia('image'))
            <figure class="lg:col-span-6 lg:col-start-7">
                <div class="aspect-[4/3] overflow-hidden rounded-sm">
                    <x-ui.picture :media="$image" :conversions="['mobile' => 800, 'desktop' => 1400]" sizes="(min-width: 1024px) 45vw, 100vw" width="1400" height="1050" eager />
                </div>
            </figure>
        @endif
    </div>
</section>

<section class="border-t border-line bg-paper" aria-labelledby="request-title">
    <div class="container-site grid gap-12 py-14 lg:grid-cols-12 lg:py-20">
        <div class="lg:col-span-4">
            <h2 id="request-title" class="text-h2 text-ink-950">Engraving for a piece you already own</h2>
            <p class="mt-4 text-[15px] leading-relaxed text-ink-600">For new orders, add engraving on the product page. Use this form for LeatherX pieces bought earlier, bulk logo work or anything custom — we reply with the price and timeline.</p>
        </div>
        <form method="POST" action="{{ route('engraving.store') }}" class="grid gap-5 sm:grid-cols-2 lg:col-span-7 lg:col-start-6" x-data="{ type: @js(old('type', 'name')), busy: false }" @submit="busy = true" novalidate>
            @csrf
            @if ($errors->any())<x-ui.alert type="error" class="sm:col-span-2">Please check the highlighted fields.</x-ui.alert>@endif
            <x-form.input name="customer_name" label="Your name" required autocomplete="name" />
            <x-form.input name="phone" label="Mobile number" type="tel" required inputmode="numeric" autocomplete="tel" hint="11 digits, starting with 01." />
            <x-form.input name="email" label="Email (optional)" type="email" autocomplete="email" class="sm:col-span-2" />
            <x-form.select name="product_id" label="Product" :options="$products->pluck('name', 'id')->all()" placeholder="Not sure yet / custom" class="sm:col-span-2" />
            <div>
                <label for="f-type" class="label">What should we engrave?<span class="text-danger" aria-hidden="true"> *</span></label>
                <select id="f-type" name="type" x-model="type" class="field" required>
                    @foreach (\App\Enums\EngravingType::cases() as $t)<option value="{{ $t->value }}">{{ $t->getLabel() }}</option>@endforeach
                </select>
            </div>
            <x-form.input name="quantity" label="Quantity" type="number" min="1" :value="old('quantity', 1)" required inputmode="numeric" />
            <div class="sm:col-span-2" x-show="type !== 'logo'"><x-form.input name="text" label="Text to engrave" maxlength="40" hint="Up to 40 characters." /></div>
            <div class="sm:col-span-2" x-show="type === 'logo'" x-cloak><x-form.textarea name="logo_instruction" label="Describe the logo, size and placement" rows="3" hint="We will ask for the logo file after you submit." /></div>
            <x-form.select name="font" label="Style" :options="array_combine(\App\Models\EngravingRequest::FONTS, \App\Models\EngravingRequest::FONTS)" />
            <x-form.select name="position" label="Position" :options="array_combine(\App\Models\EngravingRequest::POSITIONS, \App\Models\EngravingRequest::POSITIONS)" />
            <x-form.submit label="Send engraving request" loading="Sending…" class="sm:col-span-2 sm:justify-self-start" />
        </form>
    </div>
</section>
@endsection
