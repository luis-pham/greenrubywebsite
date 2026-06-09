@extends('backend::layouts.master')

@section('styles')
    <link href="{{ asset('/assets/backend/plugins/kendo-ui/css/default-main.css') }}" rel="stylesheet" />
@endsection

@php
    $languageCode = Route::current()->parameter('languageCode');
@endphp

@section('content')
    @include('backend::shared.message')
    {{ Form::open(['route' => [Utilities::getRouteName('backend.menuFrontEnd.update'), ['languageCode' => $languageCode, 'id' => $obj->id, 'lastUrl' => Request::get('lastUrl')]], 'id' => 'frm']) }}
        <div class="card">
            <div class="card-header">
                <h1 class="h5 m-0">{{ $title }}</h1>
            </div>
            <div class="card-body">
                <div class="form-horizontal">
                    <div class="form-group row">
                        <label class="col-md-2 col-form-label">Tên <span class="text-danger">*</span></label>
                        <div class="col-md-10">
                            {{ Form::text('name', old('name', $obj->name), ['class' => 'form-control', 'placeholder' => 'Nhập tên...', 'maxlength' => 50, 'autocomplete' => 'off']) }}
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-2 col-form-label">Mã <span class="text-danger">*</span></label>
                        <div class="col-md-10">
                            {{ Form::text('code', old('code', $obj->code), ['class' => 'form-control', 'placeholder' => 'Nhập tên...', 'maxlength' => 50, 'autocomplete' => 'off']) }}
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-2 col-form-label">Mô tả</label>
                        <div class="col-md-10">
                            {{ Form::textarea('description', old('description', $obj->description), ['rows'=> 5, 'class' => 'form-control', 'placeholder' => 'Nhập mô tả...', 'autocomplete' => 'off']) }}
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-2 col-form-label">Menu</label>
                        <div class="col-md-10">
                            {{ Form::hidden('menu', old('menu', $obj->menu)) }}
                            <div class="frm-create-menu form-row mb-3">
                                <div class="col-md-3">
                                    <input type="text" class="txt-name form-control" placeholder="Nhập tên..." autocomplete="off" />
                                </div>
                                <div class="col-md-3">
                                    <input type="text" class="txt-url form-control" placeholder="Nhập đường dẫn..." autocomplete="off" />
                                </div>
                                <div class="col-md-2">
                                    <input type="text" class="txt-icon form-control" placeholder="Nhập biểu tượng..." autocomplete="off" />
                                </div>
                                <div class="col-md-3">
                                    <select class="lsb-target form-control">
                                        <option value="_self">Không mở tab</option>
                                        <option value="_blank">Mở tab mới</option>
                                    </select>
                                </div>
                                <div class="col-md-1">
                                    <button type="button" class="btn btn-primary btn-block btn-save">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                            <div id="treeview" data-drag-and-drop="true"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    {{ Form::close() }}

    @include('backend::menu-front-end.shared.modal-menu')
@endsection

@section('footer')
    <footer class="c-footer c-footer-sticky pl-0 pr-0">
        <div class="container-fluid">
            <button type="button" class="btn btn-primary btn-sm" onclick="$('#frm').submit()">
                <i class="fas fa-save"></i> Lưu lại
            </button>
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