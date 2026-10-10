{{-- $options: [value => label]. --}}
@props(['name', 'label', 'options' => [], 'value' => null, 'required' => false, 'model' => null, 'placeholder' => null, 'hint' => null])
@php
    $key = $model ?? $name;
    $id = $attributes->get('id', 'f-'.str_replace(['.', '[', ']'], '-', $key));
    $hasError = $errors->has($key);
    $selected = (string) old($name, $value);
    $describedBy = collect([$hint ? $id.'-hint' : null, $hasError ? $id.'-error' : null])->filter()->implode(' ');
@endphp
<div {{ $attributes->only('class') }}>
    <label for="{{ $id }}" class="label">{{ $label }}@if ($required)<span class="text-danger" aria-hidden="true"> *</span>@endif</label>
    <select id="{{ $id }}" name="{{ $name }}" @if ($model) wire:model.live="{{ $model }}" @endif @required($required)
        @if ($hasError) aria-invalid="true" @endif @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif
        {{ $attributes->except(['class', 'id'])->merge(['class' => 'field']) }}>
        @if ($placeholder)<option value="">{{ $placeholder }}</option>@endif
        @foreach ($options as $optionValue => $optionLabel)
            <option value="{{ $optionValue }}" @selected(! $model && $selected === (string) $optionValue)>{{ $optionLabel }}</option>
        @endforeach
    </select>
    @if ($hint)<p id="{{ $id }}-hint" class="hint">{{ $hint }}</p>@endif
    @error($key)<p id="{{ $id }}-error" class="error">{{ $message }}</p>@enderror
</div>
