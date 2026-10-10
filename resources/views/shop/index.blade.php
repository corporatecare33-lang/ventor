@extends('layouts.app')

@section('content')
    <x-ui.page-header
        :title="$category?->name ?? 'All products'"
        :intro="$category?->description ?? 'Leather wallets, LeatherX Tap smart wallets, card holders, bags, travel pieces and accessories.'"
        :crumbs="$category ? ['Shop' => route('shop'), $category->name => null] : ['Shop' => null]" />

    <livewire:product-listing :category="$category" />
@endsection
