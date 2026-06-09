@extends('backend::layouts.master')

@section('content')
    @include('backend::shared.message')
    <div class="card">
        <div class="card-header">
            <h1 class="h4 m-0">{{ $title }}</h1>
        </div>
        <div class="card-body">
            <dl class="row">
                <dt class="col-md-2">Tên đăng nhập</dt>
                <dd class="col-md-10">{!! !$obj->provider ? $obj->username : 'Tài khoản <span class="text-capitalize">' . $obj->provider . '</span>' !!}</dd>
            </dl>
            <dl class="row">
                <dt class="col-md-2">Họ tên</dt>
                <dd class="col-md-10">{{ $obj->fullname }}</dd>
            </dl>
            <dl class="row">
                <dt class="col-md-2">Email</dt>
                <dd class="col-md-10">{{ $obj->email }}</dd>
            </dl>
            <dl class="row">
                <dt class="col-md-2">Giao diện</dt>
                <dd class="col-md-10">{!! Utilities::formatDisplayUserTheme($obj->theme) !!}</dd>
            </dl>
            <dl class="row">
                <dt class="col-md-2">Nhóm quyền</dt>
                <dd class="col-md-10">
                    @if ($obj->id == config('backend.adUserAdmin'))
                        <p class="mb-1">Tài khoản gốc</p>
                    @elseif ($obj->ad_user_role != null)
                        @foreach ($obj->ad_user_role as $objUserRole)
                            @if (isset($listRole[$objUserRole->role_id]))
                                <p class="mb-1">{{ $listRole[$objUserRole->role_id] }}</p>
                            @endif
                        @endforeach
                    @endif
                </dd>
            </dl>
            <dl class="row">
                <dt class="col-md-2">Trạng thái</dt>
                <dd class="col-md-10">{!! Utilities::formatDisplayUserStatus($obj->status) !!}</dd>
            </dl>
        </div>
        <div class="card-footer">
            @can('user-create')
                <a href="{{ Utilities::getUrlWithGoBack(route('backend.user.create'), Request::get('lastUrl')) }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-file-alt"></i> Thêm
                </a>
            @endcan
            @can('user-update')
                <a href="{{ Utilities::getUrlWithGoBack(route('backend.user.edit', ['id' => $obj->id]), Request::get('lastUrl')) }}" class="btn btn-info btn-sm">
                    <i class="fas fa-pencil-alt"></i> Sửa
                </a>
            @endcan
            @if (!($obj->id == config('backend.adUserAdmin') || $obj->id == \Auth::user()->id))
                @can('user-delete')
                    <a href="#" class="btn btn-danger btn-sm btn-delete-one" data-id="{{ $obj->id }}" data-ajax-url="{{ route('backend.user.destroy') }}" data-ajax-url-go-back="{{ Utilities::getGoBackUrl(route('backend.user.index'), Request::get('lastUrl')) }}">
                        <i class="fas fa-trash-alt"></i> Xóa
                    </a>
                @endcan
            @endif
            <a href="{{ Utilities::getGoBackUrl(route('backend.user.index')) }}" class="btn btn-light btn-sm">
                <i class="fas fa-undo"></i> Quay lại
            </a>
        </div>
    </div>
    @include('backend::shared.audit-trail', ['obj' => $obj])
@endsection