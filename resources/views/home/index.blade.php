@extends('layouts.app')

@section('content')
    @include('home.sections.hero')

    {{-- The comparison table is placed inside the collections section, after the first collection. --}}
    @foreach ($sections->except('comparison') as $key => $section)
        @includeIf('home.sections.'.$key, ['section' => $section])
    @endforeach
@endsection
