@props(['name', 'label', 'checked' => false, 'model' => null, 'value' => '1'])
@php $id = $attributes->get('id', 'f-'.($model ?? $name)); @endphp
<div {{ $attributes->only('class') }}>
    <label for="{{ $id }}" class="flex cursor-pointer items-start gap-3 text-sm text-ink-700">
        @unless ($model)<input type="hidden" name="{{ $name }}" value="0">@endunless
        <input id="{{ $id }}" type="checkbox" name="{{ $name }}" value="{{ $value }}" class="check mt-0.5"
            @if ($model) wire:model="{{ $model }}" @else @checked(old($name, $checked)) @endif>
        <span>{{ $label }}</span>
    </label>
    @error($model ?? $name)<p class="error">{{ $message }}</p>@enderror
</div>
