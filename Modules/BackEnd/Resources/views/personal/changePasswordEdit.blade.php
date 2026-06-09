@extends('backend::layouts.master')

@section('content')
    @include('backend::shared.message')

    {{ Form::open(['route' => 'backend.personal.changePasswordUpdate', 'class' => 'form-horizontal']) }}
        <div class="card">
            <div class="card-header">
                <h1 class="h5 m-0">{{ $title }}</h1>
            </div>
            <div class="card-body">
                <div class="form-group row">
                    <label class="col-md-3 col-form-label">Mật khẩu hiện tại <span class="text-danger">*</span></label>
                    <div class="col-md-9">
                        {{ Form::password('password', ['class' => 'form-control', 'maxlength' => 50, 'placeholder' => 'Nhập mật khẩu hiện tại...', 'autocomplete' => 'off']) }}
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-md-3 col-form-label">Mật khẩu mới <span class="text-danger">*</span></label>
                    <div class="col-md-9">
                        {{ Form::password('newPassword', ['class' => 'form-control', 'maxlength' => 50, 'placeholder' => 'Nhập mật khẩu mới...', 'autocomplete' => 'off']) }}
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-md-3 col-form-label">Xác nhận Mật khẩu mới <span class="text-danger">*</span></label>
                    <div class="col-md-9">
                        {{ Form::password('newPassword_confirmation', ['class' => 'form-control', 'maxlength' => 50, 'placeholder' => 'Xác nhận mật khẩu mới...', 'autocomplete' => 'off']) }}
                    </div>
                </div>
            </div>
            <div class="card-footer">
                {{ Form::button('<i class="fas fa-save"></i> Lưu lại', ['type' => 'submit', 'class' => 'btn btn-primary btn-sm']) }}
            </div>
        </div>
    {{ Form::close() }}
@endsection