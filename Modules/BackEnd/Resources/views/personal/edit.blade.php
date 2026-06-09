@extends('backend::layouts.master')

@section('content')
    @include('backend::shared.message')

    {{ Form::open(['route' => 'backend.personal.update', 'class' => 'form-horizontal', 'enctype' => 'multipart/form-data']) }}
        <div class="card">
            <div class="card-header">
                <h1 class="h5 m-0">{{ $title }}</h1>
            </div>
            <div class="card-body">
                <dl class="form-group row">
                    <label class="col-md-2 col-form-label">Tên đăng nhập</label>
                    <div class="col-md-10 col-form-label">{{ $obj->username }}</div>
                </dl>
                <div class="form-group row">
                    <label class="col-md-2 col-form-label">Họ tên <span class="text-danger">*</span></label>
                    <div class="col-md-10">
                        {{ Form::text('fullname', old('fullname', $obj->fullname), ['class' => 'form-control', 'placeholder' => 'Nhập họ tên...', 'maxlength' => 50, 'autocomplete' => 'off']) }}
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-md-2 col-form-label">Ảnh đại diện</label>
                    <div class="col-md-10 col-form-label">
                        <div class="form-check form-check-inline">
                            {{ Form::checkbox('not_use_avatar', true, !$obj->avatar, ['class' => 'form-check-input', 'autocomplete' => 'off']) }}
                            <label class="form-check-label">Không sử dụng</label>
                        </div>
                        <div class="pnl-avatar mt-2 {{ !$obj->avatar ? 'd-none' : '' }}">
                            <div class="mb-1">
                                {{ Form::file('avatar', ['accept' => Utilities::getInputFileAccept(config('backend.fileTypeImage'))]) }}
                            </div>
                            <small>Định dạng cho phép: <strong>{{ implode(', ', config('backend.fileTypeImage')) }}</strong></small><br />
                            <small>Dung lượng tối đa: <strong>1 MB</strong></small>
                        </div>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-md-2 col-form-label">Giao diện</label>
                    <div class="col-md-10">
                        {{ Form::select('theme', $listTheme, old('theme', $obj->theme), ['class' => 'form-control', 'autocomplete' => 'off']) }}
                    </div>
                </div>
            </div>
            <div class="card-footer">
                {{ Form::button('<i class="fas fa-save"></i> Lưu lại', ['type' => 'submit', 'class' => 'btn btn-primary btn-sm']) }}
            </div>
        </div>
    {{ Form::close() }}
@endsection

@section('scripts')
    <script type="text/javascript">
        $(document).ready(function () {
            let chkNotUseAvatar = $('[name="not_use_avatar"]');
            let fileAvatar = $('[name="avatar"]');
            let pnlAvatar = $('.pnl-avatar');
            chkNotUseAvatar.change(function () {
                if (chkNotUseAvatar.is(':checked')) {
                    pnlAvatar.addClass('d-none');
                    fileAvatar.prop('disabled', true);
                } else {
                    pnlAvatar.removeClass('d-none');
                    fileAvatar.prop('disabled', false);
                }
            });
        });
    </script>
@endsection