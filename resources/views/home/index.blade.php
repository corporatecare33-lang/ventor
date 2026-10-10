@extends('layouts.app')

@section('content')
    @include('home.sections.hero')

    @foreach ($sections as $key => $section)
        @includeIf('home.sections.'.$key, ['section' => $section])
    @endforeach
@endsection
