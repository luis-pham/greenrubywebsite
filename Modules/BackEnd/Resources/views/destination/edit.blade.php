@extends('backend::layouts.master')

@section('content')
    @include('backend::shared.message')
    @php
        $languageCode = request()->route('languageCode');
        $updateRouteParams = ['id' => $obj->id, 'lastUrl' => Request::get('lastUrl')];
        if ($languageCode) {
            $updateRouteParams['languageCode'] = $languageCode;
        }
    @endphp
    {{ Form::open(['route' => [\Modules\BackEnd\Helpers\Utilities::getRouteName('backend.destination.update'), $updateRouteParams]]) }}
        @include('backend::destination._form')
    {{ Form::close() }}
@endsection
