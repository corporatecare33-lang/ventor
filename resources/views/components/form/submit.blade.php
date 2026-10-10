{{-- Submit button with a loading state. For classic forms, pair with x-data="{ busy: false }" @submit="busy = true" on the <form>. --}}
@props(['label', 'loading' => 'Please wait…', 'target' => null, 'variant' => 'btn-primary'])
<button type="submit" {{ $attributes->merge(['class' => $variant]) }}
    @if ($target) wire:loading.attr="disabled" wire:target="{{ $target }}" @else :disabled="busy" :aria-busy="busy.toString()" @endif>
    @if ($target)
        <span wire:loading.remove wire:target="{{ $target }}">{{ $label }}</span>
        <span wire:loading wire:target="{{ $target }}">{{ $loading }}</span>
    @else
        <span x-show="! busy">{{ $label }}</span>
        <span x-cloak x-show="busy">{{ $loading }}</span>
    @endif
</button>
