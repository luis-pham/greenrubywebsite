@extends('backend::layouts.master')

@php
    $languageCode = Route::current()->parameter('languageCode');
    $routeParams = ['id' => $obj->id, 'lastUrl' => Request::get('lastUrl')];
    if ($languageCode) {
        $routeParams['languageCode'] = $languageCode;
    }
@endphp

@section('content')
    @include('backend::shared.message')
    {{ Form::model($obj, ['route' => [Utilities::getRouteName('backend.amenity.update'), $routeParams]]) }}
        <div class="row">
            <div class="col-md-8">
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="m-0"><i class="fas fa-info-circle"></i> THÔNG TIN TIỆN ÍCH</h6>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label>TÊN TIỆN ÍCH <span class="text-danger">*</span></label>
                            {{ Form::text('name', null, ['class' => 'form-control', 'placeholder' => 'Ví dụ: Buffet sáng quốc tế...', 'style' => 'font-weight: bold;', 'maxlength' => 255, 'autocomplete' => 'off']) }}
                        </div>
                        <div class="form-group">
                            <label>MÔ TẢ NGẮN</label>
                            {{ Form::textarea('description', null, ['class' => 'form-control', 'placeholder' => 'Giải thích rõ hơn về tiện ích này...', 'rows' => 10, 'style' => 'font-style: italic;', 'maxlength' => 500, 'autocomplete' => 'off']) }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="m-0"><i class="fas fa-image"></i> BIỂU TƯỢNG(ICON) <span class="text-danger">*</span></h6>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            {{ Form::hidden('icon', old('icon', $obj->icon), ['class' => 'image-select', 'data-link-full' => Utilities::getFileLink(old('icon', $obj->icon)), 'data-file-manager-url' => route('backend.file.index', ['layout' => 'popup', 'type' => config('backend.fileType.image')])]) }}
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body">
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-save"></i> Lưu thông tin
                        </button>
                        <a href="{{ Utilities::getGoBackUrl(route(Utilities::getRouteName('backend.amenity.index'), $languageCode ? ['languageCode' => $languageCode] : [])) }}" class="btn btn-light btn-block">
                            <i class="fas fa-arrow-left"></i> Hủy bỏ
                        </a>
                    </div>
                </div>
            </div>
        </div>
    {{ Form::close() }}
@endsection