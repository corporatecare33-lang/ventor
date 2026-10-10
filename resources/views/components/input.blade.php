@props(['name', 'label' => null, 'type' => 'text', 'value' => null, 'required' => false, 'model' => null])
<div {{ $attributes->only('class') }}>
    @if ($label)
        <label for="{{ $name }}" class="label">{{ $label }} @if ($required)<span class="text-red-700">*</span>@endif</label>
    @endif
    <input id="{{ $name }}" name="{{ $name }}" type="{{ $type }}"
           @if ($model) wire:model.blur="{{ $model }}" @else value="{{ old($name, $value) }}" @endif
           @if ($required) required @endif
           {{ $attributes->except('class')->merge(['class' => 'field']) }}>
    @error($model ?? $name)<p class="error">{{ $message }}</p>@enderror
</div>
