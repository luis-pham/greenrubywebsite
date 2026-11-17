@extends('backend::layouts.master')

@section('content')
    @include('backend::shared.message')
    {{ Form::open(['route' => ['backend.job.store', ['lastUrl' => Request::get('lastUrl')]]]) }}
        @include('backend::job._form')
    {{ Form::close() }}
@endsection
