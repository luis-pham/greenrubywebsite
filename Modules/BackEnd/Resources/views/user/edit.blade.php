@extends('backend::layouts.master')

@section('content')
    @include('backend::shared.message')
    {{ Form::open(['route' => ['backend.user.update', ['id' => $obj->id, 'lastUrl' => Request::get('lastUrl')]] ]) }}
        <div class="card">
            <div class="card-header">
                <h1 class="h5 m-0">{{ $title }}</h1>
            </div>
            <div class="card-body">
                <div class="form-horizontal">
                    @if (!$obj->provider)
                        <div class="form-group row">
                            <label class="col-md-2 col-form-label">Tên đăng nhập <span class="text-danger">*</span></label>
                            <div class="col-md-10">
                                {{ Form::text('username', old('username', $obj->username), ['class' => 'form-control', 'placeholder' => 'Nhập tên đăng nhập...', 'maxlength' => 50, 'autocomplete' => 'off']) }}
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
                    @else
                        <div class="form-group row">
                            <label class="col-md-2 col-form-label">Tên đăng nhập</label>
                            <div class="col-md-10 col-form-label">
                                Tài khoản <span class="text-capitalize">{{ $obj->provider }}</span>
                            </div>
                        </div>
                    @endif
                    <div class="form-group row">
                        <label class="col-md-2 col-form-label">Họ tên <span class="text-danger">*</span></label>
                        <div class="col-md-10">
                            {{ Form::text('fullname', old('fullname', $obj->fullname), ['class' => 'form-control', 'placeholder' => 'Nhập họ tên...', 'maxlength' => 50, 'autocomplete' => 'off']) }}
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-2 col-form-label">Email <span class="text-danger">*</span></label>
                        <div class="col-md-10">
                            {{ Form::text('email', old('email', $obj->email), ['class' => 'form-control', 'placeholder' => 'Nhập email...', 'maxlength' => 50, 'autocomplete' => 'off']) }}
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-2 col-form-label">Giao diện</label>
                        <div class="col-md-10">
                            {{ Form::select('theme', $listTheme, old('theme', $obj->theme), ['class' => 'form-control', 'autocomplete' => 'off']) }}
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-2 col-form-label">Nhóm quyền</label>
                        <div class="col-md-10">
                            @php
                                $listRoleAtribute = ['class' => 'form-control', 'multiple'=>'multiple', 'autocomplete' => 'off'];
                                if ($obj->id == config('backend.adUserAdmin')) {
                                    $listRoleAtribute['disabled'] = 'disabled';
                                }  
                            @endphp
                            {{ Form::select('role_id[]', $listRole, old('role_id', $obj->ad_user_role->pluck('role_id')), $listRoleAtribute) }}
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-md-2 col-form-label">Trạng thái <span class="text-danger">*</span></label>
                        <div class="col-md-10">
                            @php
                                $listStatusAtribute = ['class' => 'form-control', 'autocomplete' => 'off'];
                                if ($obj->id == config('backend.adUserAdmin') || $obj->id == \Auth::user()->id) {
                                    $listStatusAtribute['disabled'] = 'disabled';
                                }  
                            @endphp
                            {{ Form::select('status', $listStatus, old('status', $obj->status), $listStatusAtribute) }}
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
    @include('backend::shared.audit-trail', ['obj' => $obj])
@endsection