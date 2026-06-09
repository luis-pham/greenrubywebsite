@extends('backend::layouts.master')

@section('content')
    @include('backend::shared.message')
    @php
        $languageCode = request()->route('languageCode');
        $storeRouteName = \Modules\BackEnd\Helpers\Utilities::getRouteName('backend.testimonial.store');
        $storeRouteParams = ['lastUrl' => Request::get('lastUrl')];
        if ($languageCode) {
            $storeRouteParams['languageCode'] = $languageCode;
        }
    @endphp
    {{ Form::open(['route' => [$storeRouteName, $storeRouteParams]]) }}
        <div class="row">
            <div class="col-md-8">
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="m-0"><i class="fas fa-info-circle"></i> THÔNG TIN ĐÁNH GIÁ</h6>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label>Họ và tên <span class="text-danger">*</span></label>
                            {{ Form::text('fullname', null, ['class' => 'form-control', 'placeholder' => 'Ví dụ: Nguyễn Văn A...', 'maxlength' => 255, 'autocomplete' => 'off']) }}
                        </div>
                        <div class="form-group">
                            <label>Vị trí/Chức vụ <span class="text-danger">*</span></label>
                            {{ Form::text('position', null, ['class' => 'form-control', 'placeholder' => 'Ví dụ: CEO, Giám đốc...', 'maxlength' => 255, 'autocomplete' => 'off']) }}
                        </div>
                        <div class="form-group">
                            <label>Nội dung đánh giá <span class="text-danger">*</span></label>
                            {{ Form::textarea('content', null, ['class' => 'form-control', 'placeholder' => 'Nhập nội dung đánh giá...', 'rows' => 5, 'autocomplete' => 'off']) }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="m-0"><i class="fas fa-image"></i> ẢNH ĐẠI DIỆN <span class="text-danger">*</span></h6>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            {{ Form::hidden('avatar', old('avatar'), ['class' => 'image-select', 'data-link-full' => Utilities::getFileLink(old('avatar')), 'data-file-manager-url' => route('backend.file.index', ['layout' => 'popup', 'type' => config('backend.fileType.image')])]) }}
                        </div>
                    </div>
                </div>
                <div class="card mb-3">
                    <div class="card-body">
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-save"></i> Lưu thông tin
                        </button>
                        <a href="{{ Utilities::getGoBackUrl(route(\Modules\BackEnd\Helpers\Utilities::getRouteName('backend.testimonial.index'), $languageCode ? ['languageCode' => $languageCode] : [])) }}" class="btn btn-light btn-block">
                            <i class="fas fa-arrow-left"></i> Hủy bỏ
                        </a>
                    </div>
                </div>
            </div>
        </div>
    {{ Form::close() }}
@endsection
