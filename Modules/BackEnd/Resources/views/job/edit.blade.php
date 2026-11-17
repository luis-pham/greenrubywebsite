@extends('backend::layouts.master')

@section('content')
    @include('backend::shared.message')
    {{ Form::open(['route' => ['backend.job.update', ['id' => $obj->id, 'lastUrl' => Request::get('lastUrl')]]]) }}
        @include('backend::job._form')
    {{ Form::close() }}
@endsection
