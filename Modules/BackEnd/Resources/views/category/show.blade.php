@extends('backend::layouts.master')

@section('styles')
    <link href="{{ asset('/assets/frontend/plugins/font-awesome/css/all.min.css') }}" rel="stylesheet" />
@endsection

@php
    $typeName = Route::current()->parameter('typeName');
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
                        <dt class="col-md-4">Tên</dt>
                        <dd class="col-md-8">{{ $obj->name }}</dd>
                    </dl>
                    <dl class="row">
                        <dt class="col-md-4">Slug</dt>
                        <dd class="col-md-8">{{ $obj->slug }}</dd>
                    </dl>
                    <dl class="row">
                        <dt class="col-md-4">Chuyên mục cha</dt>
                        <dd class="col-md-8">{{ $obj->parent_name }}</dd>
                    </dl>
                    <dl class="row">
                        <dt class="col-md-4">Biểu tượng</dt>
                        <dd class="col-md-8">
                            @if ($obj->icon)
                                <i class="{{ $obj->icon }}"></i>
                            @endif
                        </dd>
                    </dl>
                    <dl class="row">
                        <dt class="col-md-4">Mô tả</dt>
                        <dd class="col-md-8">{!! nl2br(e($obj->description)) !!}</dd>
                    </dl>
                </div>
            </div>
        </div>
        <div class="col-lg-4">  
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
            @can('category-' . $typeName . '-create')
                <a href="{{ Utilities::getUrlWithGoBack(route(Utilities::getRouteName('backend.category.create'), ['languageCode' => $languageCode, 'typeName' => $typeName]), Request::get('lastUrl')) }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-file-alt"></i> Thêm
                </a>
            @endcan
            @can('category-' . $typeName . '-update')
                <a href="{{ Utilities::getUrlWithGoBack(route(Utilities::getRouteName('backend.category.edit'), ['languageCode' => $languageCode, 'typeName' => $typeName, 'id' => $obj->id]), Request::get('lastUrl')) }}" class="btn btn-info btn-sm">
                    <i class="fas fa-pencil-alt"></i> Sửa
                </a>
            @endcan
            @can('category-' . $typeName . '-delete')
                <a href="#" class="btn btn-danger btn-sm btn-delete-one" data-id="{{ $obj->id }}" data-ajax-url="{{ route(Utilities::getRouteName('backend.category.destroy'), ['languageCode' => $languageCode, 'typeName' => $typeName]) }}" data-ajax-url-go-back="{{ Utilities::getGoBackUrl(route(Utilities::getRouteName('backend.category.index'), ['languageCode' => $languageCode, 'typeName' => $typeName]), Request::get('lastUrl')) }}">
                    <i class="fas fa-trash-alt"></i> Xóa
                </a>
            @endcan
            <a href="{{ Utilities::getGoBackUrl(route(Utilities::getRouteName('backend.category.index'), ['languageCode' => $languageCode, 'typeName' => $typeName])) }}" class="btn btn-light btn-sm">
                <i class="fas fa-undo"></i> Quay lại
            </a>
        </div>
    </footer>
@endsection