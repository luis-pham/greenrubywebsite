@extends('backend::layouts.master')

@section('content')
    @include('backend::shared.message')
    @php
        $languageCode = request()->route('languageCode');
        $storeRouteParams = ['lastUrl' => Request::get('lastUrl')];
        if ($languageCode) {
            $storeRouteParams['languageCode'] = $languageCode;
        }
        $obj = null;
    @endphp
    {{ Form::open(['route' => [\Modules\BackEnd\Helpers\Utilities::getRouteName('backend.destination.store'), $storeRouteParams]]) }}
        @include('backend::destination._form')
    {{ Form::close() }}
@endsection
