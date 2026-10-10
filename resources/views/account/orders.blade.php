@extends('layouts.app')

@section('content')
<x-account-layout title="My orders">
    <div class="card p-6">
        @include('account._orders-table', ['orders' => $orders])
        <div class="mt-6">{{ $orders->links('components.pagination') }}</div>
    </div>
</x-account-layout>
@endsection
