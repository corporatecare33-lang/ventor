@props(['eyebrow' => null, 'title', 'intro' => null, 'inverse' => false, 'id' => null])
<div {{ $attributes->merge(['class' => 'reveal max-w-2xl']) }}>
    @if ($eyebrow)<p class="{{ $inverse ? 'eyebrow-inverse' : 'eyebrow' }} mb-3">{{ $eyebrow }}</p>@endif
    <h2 @if ($id) id="{{ $id }}" @endif class="text-h2 {{ $inverse ? 'text-white' : 'text-ink-950' }}">{{ $title }}</h2>
    @if ($intro)<p class="mt-4 text-[1.0625rem] leading-relaxed {{ $inverse ? 'text-white/75' : 'text-ink-600' }}">{{ $intro }}</p>@endif
</div>
