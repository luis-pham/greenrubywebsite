@extends('backend::layouts.master')

@section('styles')
    <link href="{{ asset('/assets/backend/plugins/kendo-ui/css/default-main.css') }}" rel="stylesheet" />
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
                <dt class="col-md-2">Tên</dt>
                <dd class="col-md-10">{{ $obj->name }}</dd>
            </dl>
            <dl class="row">
                <dt class="col-md-2">Mã</dt>
                <dd class="col-md-10">{{ $obj->code }}</dd>
            </dl>
            <dl class="row">
                <dt class="col-md-2">Mô tả</dt>
                <dd class="col-md-10">{!! nl2br(e($obj->description)) !!}</dd>
            </dl>
            <dl class="row">
                <dt class="col-md-2">Menu</dt>
                <dd class="col-md-10">
                    {{ Form::hidden('menu', $obj->menu) }}
                    <div id="treeview"></div>
                </dd>
            </dl>
        </div>
    </div>
    @include('backend::shared.audit-trail', ['obj' => $obj])
@endsection

@section('footer')
    <footer class="c-footer c-footer-sticky pl-0 pr-0">
        <div class="container-fluid">
            @can('menu-front-end-create')
                <a href="{{ Utilities::getUrlWithGoBack(route(Utilities::getRouteName('backend.menuFrontEnd.create'), ['languageCode' => $languageCode]), Request::get('lastUrl')) }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-file-alt"></i> Thêm
                </a>
            @endcan
            @can('menu-front-end-update')
                <a href="{{ Utilities::getUrlWithGoBack(route(Utilities::getRouteName('backend.menuFrontEnd.edit'), ['languageCode' => $languageCode, 'id' => $obj->id]), Request::get('lastUrl')) }}" class="btn btn-info btn-sm">
                    <i class="fas fa-pencil-alt"></i> Sửa
                </a>
            @endcan
            @can('menu-front-end-delete')
                <a href="#" class="btn btn-danger btn-sm btn-delete-one" data-id="{{ $obj->id }}" data-ajax-url="{{ route(Utilities::getRouteName('backend.menuFrontEnd.destroy'), ['languageCode' => $languageCode]) }}" data-ajax-url-go-back="{{ Utilities::getGoBackUrl(route(Utilities::getRouteName('backend.menuFrontEnd.index'), ['languageCode' => $languageCode]), Request::get('lastUrl')) }}">
                    <i class="fas fa-trash-alt"></i> Xóa
                </a>
            @endcan
            <a href="{{ Utilities::getGoBackUrl(route(Utilities::getRouteName('backend.menuFrontEnd.index'), ['languageCode' => $languageCode])) }}" class="btn btn-light btn-sm">
                <i class="fas fa-undo"></i> Quay lại
            </a>
        </div>
    </footer>
@endsection

@section('scripts')
    <script src="{{ asset('/assets/backend/plugins/kendo-ui/js/kendo.all.min.js') }}"></script>
    <script src="{{ asset('/assets/backend/js/modules/menu-front-end/common.js') }}"></script>
@endsection