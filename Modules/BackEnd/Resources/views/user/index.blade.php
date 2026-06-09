@extends('backend::layouts.master')

@section('content')
    @include('backend::shared.message')
    <div class="card">
        <div class="card-header">
            <h1 class="h4 m-0">{{ $title }}</h1>
        </div>
        <div class="card-body">
            {{ Form::open(['method' => 'get', 'class' => 'form-search mb-2']) }}
                <div class="form-row">
                    <div class="form-group col-md-5">
                        <label>Từ khóa</label>
                        {{ Form::text('keyword', Request::get('keyword'), ['class' => 'form-control', 'placeholder' => 'Nhập từ khóa...', 'autocomplete' => 'off']) }}
                    </div>
                    <div class="form-group col-md-3">
                        <label>Nhóm quyền</label>
                        {{ Form::select('role_id', $listRole, Request::get('role_id'), ['class' => 'form-control', 'placeholder' => 'Tất cả', 'autocomplete' => 'off']) }}
                    </div>
                    <div class="form-group col-md-2">
                        <label>Trạng thái</label>
                        {{ Form::select('status', $listStatus, Request::get('status'), ['class' => 'form-control', 'placeholder' => 'Tất cả', 'autocomplete' => 'off']) }}
                    </div>
                    <div class="form-group col-md-2">
                        <label>&nbsp;</label>
                        {{ Form::button('<i class="fas fa-search"></i> Tìm kiếm', ['type' => 'submit', 'class' => 'btn btn-success btn-block']) }}
                    </div>
                </div>
            {{ Form::close() }}
            @canany(['user-create', 'user-delete'])
                <div class="mb-3">
                    @can('user-delete')
                        <button type="button" class="btn btn-danger btn-sm btn-delete-multi" data-ajax-url="{{ route('backend.user.destroy') }}">
                            <i class="fas fa-trash-alt"></i> Xóa
                        </button>
                    @endcan
                    @can('user-create')
                        <a href="{{ Utilities::getUrlWithGoBack(route('backend.user.create')) }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-file-alt"></i> Thêm
                        </a>
                    @endcan
                </div>
            @endcanany
            <div class="table-responsive-sm">
                <table class="table table-striped table-data">
                    <thead>
                        <tr>
                            @can('user-delete')
                                <th class="text-center" style="width: 40px;">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" id="chk-all" class="chk-all custom-control-input" autocomplete="off" />
                                        <label class="custom-control-label" for="chk-all"></label>
                                    </div>
                                </th>
                            @endcan
                            <th class="text-center" style="width: 50px;">#</th>
                            <th>Tên đăng nhập</th>
                            <th class="text-center" style="width: 150px;">Họ tên</th>
                            <th class="text-center" style="width: 150px;">Email</th>
                            <th class="text-center" style="width: 150px;">Nhóm quyền</th>
                            <th class="text-center" style="width: 150px;">Trạng thái</th>
                            @canany(['user-update', 'user-delete'])
                                <th class="text-center" style="width: 110px;">Ch.năng</th>
                            @endcanany
                        </tr>
                    </thead>
                    <tbody>
                        @for ($i = 0; $i < count($list); $i++)
                            <tr>
                                @can('user-delete')
                                    <td class="text-center">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" id="chk-item-{{ $list[$i]->id }}" class="chk-item custom-control-input" value="{{ $list[$i]->id }}" autocomplete="off"{{ $list[$i]->id == config('backend.adUserAdmin') || $list[$i]->id == \Auth::user()->id ? ' disabled' : '' }} />
                                            <label class="custom-control-label" for="chk-item-{{ $list[$i]->id }}"></label>
                                        </div>
                                    </td>
                                @endcan
                                <td class="text-center">{{ $i + $list->firstItem() }}</td>
                                <td>
                                    <a href="{{ Utilities::getUrlWithGoBack(route('backend.user.show', ['id' => $list[$i]->id])) }}">
                                        {!! !$list[$i]->provider ? $list[$i]->username : 'Tài khoản <span class="text-capitalize">' . $list[$i]->provider . '</span>' !!}
                                    </a>
                                </td>
                                <td class="text-center">{{ $list[$i]->fullname }}</td>
                                <td class="text-center">{{ $list[$i]->email }}</td>
                                <td class="text-center">
                                    @if ($list[$i]->id == config('backend.adUserAdmin'))
                                        <p class="mb-1">Tài khoản gốc</p>
                                    @elseif ($list[$i]->ad_user_role != null)
                                        @foreach ($list[$i]->ad_user_role as $objUserRole)
                                            @if (isset($listRole[$objUserRole->role_id]))
                                                <p class="mb-1">{{ $listRole[$objUserRole->role_id] }}</p>
                                            @endif
                                        @endforeach
                                    @endif
                                </td>
                                <td class="text-center">{!! Utilities::formatDisplayUserStatus($list[$i]->status) !!}</td>
                                @canany(['user-update', 'user-delete'])
                                    <td class="text-center">
                                        @can('user-update')
                                            <a href="{{ Utilities::getUrlWithGoBack(route('backend.user.edit', ['id' => $list[$i]->id])) }}" class="btn btn-info btn-sm" title="Sửa">
                                                <i class="fas fa-pencil-alt"></i>
                                            </a>
                                        @endcan
                                        @can('user-delete')
                                            <a href="#" class="btn btn-danger btn-sm btn-delete-one{{ $list[$i]->id == config('backend.adUserAdmin') || $list[$i]->id == \Auth::user()->id ? ' disabled' : '' }}" title="Xóa" data-id="{{ $list[$i]->id }}" data-ajax-url="{{ route('backend.user.destroy') }}">
                                                <i class="fas fa-trash-alt"></i>
                                            </a>
                                        @endcan
                                    </td>
                                @endcanany
                            </tr>
                        @endfor
                    </tbody>
                </table>
            </div>

            {!! $list->appends(Request::all())->links('backend::shared.pagination') !!}
        </div>
    </div>
@endsection