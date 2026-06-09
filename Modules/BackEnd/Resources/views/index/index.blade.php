@extends('backend::layouts.master')

@section('content')
    <h1 class="h2 text-center">
        Hệ thống Quản trị
        @if (array_key_exists('website-name', $config) && $config['website-name'])
            {{ $config['website-name'] }}
        @endif
    </h1>
@endsection