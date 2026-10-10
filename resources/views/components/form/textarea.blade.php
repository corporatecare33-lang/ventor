@props(['name', 'label', 'value' => null, 'required' => false, 'model' => null, 'hint' => null, 'rows' => 4])
@php
    $key = $model ?? $name;
    $id = $attributes->get('id', 'f-'.str_replace(['.', '[', ']'], '-', $key));
    $hasError = $errors->has($key);
    $describedBy = collect([$hint ? $id.'-hint' : null, $hasError ? $id.'-error' : null])->filter()->implode(' ');
@endphp
<div {{ $attributes->only('class') }}>
    <label for="{{ $id }}" class="label">{{ $label }}@if ($required)<span class="text-danger" aria-hidden="true"> *</span>@endif</label>
    <textarea id="{{ $id }}" name="{{ $name }}" rows="{{ $rows }}"
        @if ($model) wire:model.blur="{{ $model }}" @endif
        @required($required)
        @if ($hasError) aria-invalid="true" @endif
        @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif
        {{ $attributes->except(['class', 'id'])->merge(['class' => 'field']) }}>@unless ($model){{ old($name, $value) }}@endunless</textarea>
    @if ($hint)<p id="{{ $id }}-hint" class="hint">{{ $hint }}</p>@endif
    @error($key)<p id="{{ $id }}-error" class="error">{{ $message }}</p>@enderror
</div>
