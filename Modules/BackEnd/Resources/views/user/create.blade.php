@extends('backend::layouts.master')

@section('content')
    @include('backend::shared.message')
    {{ Form::open(['route' => ['backend.user.store', ['lastUrl' => Request::get('lastUrl')]] ]) }}
        <div class="card">
            <div class="card-header">
                <h1 class="h5 m-0">{{ $title }}</h1>
            </div>
            <div class="card-body">
                <div class="form-horizontal">
                    <div class="form-group row">
                        <label class="col-md-2 col-form-label">Tên đăng nhập <span class="text-danger">*</span></label>
                        <div class="col-md-10">
                            {{ Form::text('username', null, ['class' => 'form-control', 'placeholder' => 'Nhập tên đăng nhập...', 'maxlength' => 50, 'autocomplete' => 'off']) }}
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-2 col-form-label">Mật khẩu <span class="text-danger">*</span></label>
                        <div class="col-md-10">
                            {{ Form::password('password', ['class' => 'form-control', 'maxlength' => 50, 'placeholder' => 'Nhập mật khẩu...', 'autocomplete' => 'off']) }}
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-2 col-form-label">Xác nhận Mật khẩu <span class="text-danger">*</span></label>
                        <div class="col-md-10">
                            {{ Form::password('password_confirmation', ['class' => 'form-control', 'maxlength' => 50, 'placeholder' => 'Xác nhận mật khẩu...', 'autocomplete' => 'off']) }}
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-2 col-form-label">Họ tên <span class="text-danger">*</span></label>
                        <div class="col-md-10">
                            {{ Form::text('fullname', null, ['class' => 'form-control', 'placeholder' => 'Nhập họ tên...', 'maxlength' => 50, 'autocomplete' => 'off']) }}
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-2 col-form-label">Email <span class="text-danger">*</span></label>
                        <div class="col-md-10">
                            {{ Form::text('email', null, ['class' => 'form-control', 'placeholder' => 'Nhập email...', 'maxlength' => 50, 'autocomplete' => 'off']) }}
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-2 col-form-label">Giao diện</label>
                        <div class="col-md-10">
                            {{ Form::select('theme', $listTheme, null, ['class' => 'form-control', 'autocomplete' => 'off']) }}
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-2 col-form-label">Nhóm quyền</label>
                        <div class="col-md-10">
                            {{ Form::select('role_id[]', $listRole, null, ['class' => 'form-control', 'multiple'=>'multiple', 'autocomplete' => 'off']) }}
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-2 col-form-label">Trạng thái <span class="text-danger">*</span></label>
                        <div class="col-md-10">
                            {{ Form::select('status', $listStatus, null, ['class' => 'form-control', 'autocomplete' => 'off']) }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="fas fa-save"></i> Lưu lại
                </button>
                <a href="{{ Utilities::getGoBackUrl(route('backend.user.index')) }}" class="btn btn-light btn-sm">
                    <i class="fas fa-undo"></i> Quay lại
                </a>
            </div>
        </div>
    {{ Form::close() }}
@endsection