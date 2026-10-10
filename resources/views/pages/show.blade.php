@extends('layouts.app')

@section('content')
<x-ui.page-header :title="$page->title" :intro="$page->seo_description" :crumbs="[$page->title => null]" />
<article class="container-site py-12 lg:py-16">
    <div class="prose-site">{!! clean_html($page->content) !!}</div>
    <p class="mt-12 text-[13px] text-ink-500">Last updated <time datetime="{{ $page->updated_at->toDateString() }}">{{ $page->updated_at->format('j F Y') }}</time></p>
</article>
@endsection
