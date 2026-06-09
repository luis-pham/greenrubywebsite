@extends('backend::layouts.master')

@php
    $languageCode = Route::current()->parameter('languageCode');
    $langParam = $languageCode ? ['languageCode' => $languageCode] : [];
@endphp

@section('content')
    @include('backend::shared.message')
    <div class="card">
        <div class="card-header">
            <h1 class="h4 m-0">{{ $title }}</h1>
        </div>
        <div class="card-body">
            <dl class="row">
                <dt class="col-md-2">Tên tiện ích</dt>
                <dd class="col-md-10">{{ $obj->name }}</dd>
            </dl>
            <dl class="row">
                <dt class="col-md-2">Mô tả</dt>
                <dd class="col-md-10">{{ $obj->description ?? '-' }}</dd>
            </dl>
            <dl class="row">
                <dt class="col-md-2">Icon</dt>
                <dd class="col-md-10">
                    @if($obj->icon)
                        <i class="fas fa-{{ $obj->icon }} fa-2x"></i>
                        <span class="ml-2 text-muted">({{ $obj->icon }})</span>
                    @else
                        -
                    @endif
                </dd>
            </dl>
            <dl class="row">
                <dt class="col-md-2">Thứ tự</dt>
                <dd class="col-md-10">{{ $obj->ord ?? 0 }}</dd>
            </dl>
        </div>
        <div class="card-footer">
            @can('group-amenity-create')
                <a href="{{ Utilities::getUrlWithGoBack(route(Utilities::getRouteName('backend.amenity.create'), $langParam), Request::get('lastUrl')) }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-file-alt"></i> Thêm
                </a>
            @endcan
            @can('group-amenity-update')
                <a href="{{ Utilities::getUrlWithGoBack(route(Utilities::getRouteName('backend.amenity.edit'), array_merge($langParam, ['id' => $obj->id])), Request::get('lastUrl')) }}" class="btn btn-info btn-sm">
                    <i class="fas fa-pencil-alt"></i> Sửa
                </a>
            @endcan
            @can('group-amenity-delete')
                <a href="#" class="btn btn-danger btn-sm btn-delete-one" data-id="{{ $obj->id }}" data-ajax-url="{{ route(Utilities::getRouteName('backend.amenity.destroy'), $langParam) }}" data-ajax-url-go-back="{{ Utilities::getGoBackUrl(route(Utilities::getRouteName('backend.amenity.index'), $langParam), Request::get('lastUrl')) }}">
                    <i class="fas fa-trash-alt"></i> Xóa
                </a>
            @endcan
            <a href="{{ Utilities::getGoBackUrl(route(Utilities::getRouteName('backend.amenity.index'), $langParam)) }}" class="btn btn-light btn-sm">
                <i class="fas fa-undo"></i> Quay lại
            </a>
        </div>
    </div>
@endsection