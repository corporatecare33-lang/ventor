@extends('layouts.app')

@section('content')
<x-page-header :title="$page->title" :crumbs="[$page->title => null]" />
<article class="container-lx max-w-3xl py-14">
    <div class="prose-lx">{!! clean_html($page->content) !!}</div>
    <p class="mt-12 text-xs text-ink-600">Last updated {{ $page->updated_at->format('d M Y') }}</p>
</article>
@endsection
