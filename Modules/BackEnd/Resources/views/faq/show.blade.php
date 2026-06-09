@extends('backend::layouts.master')

@section('styles')
    @include('backend::shared.text-editor-style')
@endsection

@php
    $languageCode = Route::current()->parameter('languageCode');
@endphp

@section('content')
    @include('backend::shared.message')
    <div class="card">
        <div class="card-header">
            <h1 class="h4 m-0">{{ $title }}</h1>
        </div>
        <div class="card-body">
            <dl class="row">
                <dt class="col-md-3">Chuyên mục</dt>
                <dd class="col-md-9">{{ $obj->group_name }}</dd>
            </dl>
            <dl class="row">
                <dt class="col-md-12 mb-2">Câu hỏi</dt>
                <dd class="col-md-12">
                    <div class="article-content">
                        {!! $obj->question !!}
                    </div>
                </dd>
            </dl>
            <dl class="row">
                <dt class="col-md-12 mb-2">Trả lời</dt>
                <dd class="col-md-12">
                    <div class="article-content">
                        {!! $obj->answer !!}
                    </div>
                </dd>
            </dl>
        </div>
    </div>
    @include('backend::shared.audit-trail', ['obj' => $obj])
@endsection

@section('footer')
    <footer class="c-footer c-footer-sticky pl-0 pr-0">
        <div class="container-fluid">
            @can('faq-create')
                <a href="{{ Utilities::getUrlWithGoBack(route(Utilities::getRouteName('backend.faq.create'), ['languageCode' => $languageCode]), Request::get('lastUrl')) }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-file-alt"></i> Thêm
                </a>
            @endcan
            @can('faq-update')
                <a href="{{ Utilities::getUrlWithGoBack(route(Utilities::getRouteName('backend.faq.edit'), ['languageCode' => $languageCode, 'id' => $obj->id]), Request::get('lastUrl')) }}" class="btn btn-info btn-sm">
                    <i class="fas fa-pencil-alt"></i> Sửa
                </a>
            @endcan
            @can('faq-delete')
                <a href="#" class="btn btn-danger btn-sm btn-delete-one" data-id="{{ $obj->id }}" data-ajax-url="{{ route(Utilities::getRouteName('backend.faq.destroy'), ['languageCode' => $languageCode]) }}" data-ajax-url-go-back="{{ Utilities::getGoBackUrl(route(Utilities::getRouteName('backend.faq.index'), ['languageCode' => $languageCode]), Request::get('lastUrl')) }}">
                    <i class="fas fa-trash-alt"></i> Xóa
                </a>
            @endcan
            <a href="{{ Utilities::getGoBackUrl(route(Utilities::getRouteName('backend.faq.index'), ['languageCode' => $languageCode])) }}" class="btn btn-light btn-sm">
                <i class="fas fa-undo"></i> Quay lại
            </a>
        </div>
    </footer>
@endsection