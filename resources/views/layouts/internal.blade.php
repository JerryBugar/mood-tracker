@extends('layouts.app')

@section('content')
    @yield('main-content')
    @include('components.mood-modal')
    @include('layouts.partials.bottom-nav')
@endsection
