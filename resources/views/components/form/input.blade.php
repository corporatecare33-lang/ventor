{{--
    Accessible text input. Works with classic forms (name + old()) or Livewire (model="...").
    Errors are linked via aria-describedby and aria-invalid.
--}}
@props(['name', 'label', 'type' => 'text', 'value' => null, 'required' => false, 'model' => null, 'hint' => null, 'live' => false])
@php
    $key = $model ?? $name;
    $id = $attributes->get('id', 'f-'.str_replace(['.', '[', ']'], '-', $key));
    $hasError = $errors->has($key);
    $describedBy = collect([$hint ? $id.'-hint' : null, $hasError ? $id.'-error' : null])->filter()->implode(' ');
@endphp
<div {{ $attributes->only('class') }}>
    <label for="{{ $id }}" class="label">{{ $label }}@if ($required)<span class="text-danger" aria-hidden="true"> *</span>@endif</label>
    <input id="{{ $id }}" name="{{ $name }}" type="{{ $type }}"
        @if ($model) wire:model{{ $live ? '.live.debounce.400ms' : '.blur' }}="{{ $model }}" @else value="{{ old($name, $value) }}" @endif
        @required($required)
        @if ($hasError) aria-invalid="true" @endif
        @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif
        {{ $attributes->except(['class', 'id'])->merge(['class' => 'field']) }}>
    @if ($hint)<p id="{{ $id }}-hint" class="hint">{{ $hint }}</p>@endif
    @error($key)<p id="{{ $id }}-error" class="error">{{ $message }}</p>@enderror
</div>
