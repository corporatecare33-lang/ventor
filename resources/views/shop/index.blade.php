@extends('layouts.app')

@section('content')
    <x-page-header
        :title="$category?->name ?? 'Shop all'"
        :subtitle="$category ? str_replace('|', ' · ', (string) ($category->tagline ?: $category->description)) : 'Handcrafted leather wallets, NFC smart wallets, bags and accessories.'"
        :crumbs="$category ? ['Shop' => route('shop'), $category->name => null] : ['Shop' => null]" />

    <livewire:product-listing :category="$category" />
@endsection
