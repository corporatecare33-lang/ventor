@extends('layouts.app')

@section('content')
<x-account-layout title="Orders">
    @include('account._orders-table', ['orders' => $orders])
    <div class="mt-8">{{ $orders->links('components.pagination') }}</div>
</x-account-layout>
@endsection
