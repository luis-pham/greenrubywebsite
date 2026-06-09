@extends('frontend::layouts.master')

@section('content')
    <div id="page-error">
        @include('frontend::shared.section.section-cover', [
            'class' => 'section-1',
            'list' => [(object)[
                "title" => $statusCode,
                "link" => asset("/assets/frontend/images/modules/error/page-cover.jpg"),
                "description" => FeUtils::getErrorMessageByStatusCode($statusCode)
            ]]
        ])
    </div>
@endsection