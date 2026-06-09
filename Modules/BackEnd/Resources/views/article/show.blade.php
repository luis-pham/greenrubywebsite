@extends('backend::layouts.master')

@section('styles')
    @include('backend::shared.text-editor-style')
@endsection

@php
    $languageCode = Route::current()->parameter('languageCode');
@endphp

@section('content')
    <h1 class="h3 mb-4 text-center">{{ $title }}</h1>
    @include('backend::shared.message')
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <p class="h5 m-0">Thông tin chính</p>
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-md-3">Chuyên mục</dt>
                        <dd class="col-md-9">{{ $obj->category_name }}</dd>
                    </dl>
                    <dl class="row">
                        <dt class="col-md-3">Tiêu đề</dt>
                        <dd class="col-md-9">{{ $obj->title }}</dd>
                    </dl>
                    <dl class="row">
                        <dt class="col-md-3">Tiêu đề phụ</dt>
                        <dd class="col-md-9">{{ $obj->sub_title }}</dd>
                    </dl>
                    <dl class="row">
                        <dt class="col-md-12 mb-2">Trích dẫn</dt>
                        <div class="col-md-12">
                            <div class="article-content">
                                {!! $obj->lead !!}
                            </div>
                        </div>
                    </dl>
                    <dl class="row">
                        <dt class="col-md-12 mb-2">Nội dung</dt>
                        <div class="col-md-12">
                            <div class="article-content">
                                {!! $obj->content !!}
                            </div>
                        </div>
                    </dl>
                    <dl class="row">
                        <dt class="col-md-3">TG xuất bản</dt>
                        <dd class="col-md-9">{{ Utilities::formatDisplayDateTime($obj->publish_date) }}</dd>
                    </dl>
                    <dl class="row">
                        <dt class="col-md-3">Nổi bật</dt>
                        <dd class="col-md-9">{{ $obj->is_featured ? 'Có' : 'Không' }}</dd>
                    </dl>
                    <dl class="row">
                        <dt class="col-md-3">Xuất bản</dt>
                        <dd class="col-md-9">{{ $obj->is_published ? 'Có' : 'Không' }}</dd>
                    </dl>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <p class="h5 m-0">Ảnh bìa</p>
                </div>
                <div class="card-body">
                    @if ($obj->image_link)
                        <img src="{{ Utilities::getFileLink($obj->image_link) }}" class="img-fluid" />
                    @endif
                </div>
            </div>
            <div class="card">
                <div class="card-header">
                    <p class="h5 m-0">Thông tin SEO</p>
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-md-12">Tiêu đề</dt>
                        <dd class="col-md-12">{{ $obj->seo_title }}</dd>
                    </dl>
                    <dl class="row">
                        <dt class="col-md-12">Mô tả</dt>
                        <dd class="col-md-12">{{ $obj->seo_description }}</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
    @include('backend::shared.audit-trail', ['obj' => $obj])
@endsection

@section('footer')
    <footer class="c-footer c-footer-sticky pl-0 pr-0">
        <div class="container-fluid">
            @can('article-create')
                <a href="{{ Utilities::getUrlWithGoBack(route(Utilities::getRouteName('backend.article.create'), ['languageCode' => $languageCode]), Request::get('lastUrl')) }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-file-alt"></i> Thêm
                </a>
            @endcan
            @can('article-update')
                <a href="{{ Utilities::getUrlWithGoBack(route(Utilities::getRouteName('backend.article.edit'), ['languageCode' => $languageCode, 'id' => $obj->id]), Request::get('lastUrl')) }}" class="btn btn-info btn-sm">
                    <i class="fas fa-pencil-alt"></i> Sửa
                </a>
            @endcan
            @can('article-delete')
                <a href="#" class="btn btn-danger btn-sm btn-delete-one" data-id="{{ $obj->id }}" data-ajax-url="{{ route(Utilities::getRouteName('backend.article.destroy'), ['languageCode' => $languageCode]) }}" data-ajax-url-go-back="{{ Utilities::getGoBackUrl(route(Utilities::getRouteName('backend.article.index'), ['languageCode' => $languageCode]), Request::get('lastUrl')) }}">
                    <i class="fas fa-trash-alt"></i> Xóa
                </a>
            @endcan
            <a href="{{ Utilities::getGoBackUrl(route(Utilities::getRouteName('backend.article.index'), ['languageCode' => $languageCode])) }}" class="btn btn-light btn-sm">
                <i class="fas fa-undo"></i> Quay lại
            </a>
        </div>
    </footer>
@endsection