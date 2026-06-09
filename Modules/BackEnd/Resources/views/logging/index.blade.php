@extends('backend::layouts.master')

@section('content')
    <div class="card">
        <div class="card-header">
            <h1 class="h4 m-0">{{ $title }}</h1>
        </div>
        <div class="card-body">
            {{ Form::open(['method' => 'get', 'class' => 'form-search mb-2']) }}
                <div class="form-row">
                    <div class="form-group col-md-2">
                        <label>Từ khóa</label>
                        {{ Form::text('keyword', Request::get('keyword'), ['class' => 'form-control', 'placeholder' => 'Nhập từ khóa...', 'autocomplete' => 'off']) }}
                    </div>
                    <div class="form-group col-md-2">
                        <label>Từ ngày</label>
                        {{ Form::text('from_date', Request::get('from_date'), ['class' => 'form-control date-picker', 'placeholder' => 'Nhập từ ngày...', 'autocomplete' => 'off']) }}
                    </div>
                    <div class="form-group col-md-2">
                        <label>Đến ngày</label>
                        {{ Form::text('to_date', Request::get('to_date'), ['class' => 'form-control date-picker', 'placeholder' => 'Nhập đến ngày...', 'autocomplete' => 'off']) }}
                    </div>
                    <div class="form-group col-md-3">
                        <label>Người dùng</label>
                        <div class="select2">
                            <select name="user_id">
                                <option value="">Tất cả</option>
                                @foreach ($listUser as $objUser)
                                    <option value="{{ $objUser->id }}"{{ $objUser->id == Request::get('user_id') ? ' selected="selected"': ''}}>{{ $objUser->fullname }} [{{ $objUser->username }}]</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-group col-md-2">
                        <label>Loại</label>
                        {{ Form::select('type', $listType, Request::get('type'), ['class' => 'form-control', 'placeholder' => 'Tất cả', 'autocomplete' => 'off']) }}
                    </div>
                    <div class="form-group col-md-1">
                        <label>&nbsp;</label>
                        {{ Form::button('<i class="fas fa-search"></i>', ['type' => 'submit', 'class' => 'btn btn-success btn-block']) }}
                    </div>
                </div>
            {{ Form::close() }}
            <div class="table-responsive-sm">
                <table class="table table-striped table-data">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 50px;">#</th>
                            <th class="text-center" style="width: 100px;">Loại</th>
                            <th>Hành động</th>
                            <th class="text-center" style="width: 150px;">IP</th>
                            <th class="text-center" style="width: 150px;">Người dùng</th>
                            <th class="text-center" style="width: 150px;">Thời gian</th>
                        </tr>
                    </thead>
                    <tbody>
                        @for ($i = 0; $i < count($list); $i++)
                            <tr>
                                <td class="text-center">{{ $i + $list->firstItem() }}</td>
                                <td class="text-center">{!! Utilities::formatDisplayLogType($list[$i]->type) !!}</td>
                                <td>
                                    <a href="{{ Utilities::getUrlWithGoBack(route('backend.logging.show', ['id' => $list[$i]->id])) }}">
                                        {{ $list[$i]->action }}
                                    </a>
                                </td>
                                <td class="text-center">{{ $list[$i]->ip }}</td>
                                <td class="text-center">
                                    @if ($list[$i]->user_id)
                                        <a href="{{ route('backend.user.info', ['id' => $list[$i]->user_id]) }}" target="_blank">
                                            {{ $list[$i]->user_fullname }}
                                        </a>
                                    @endif
                                </td>
                                <td class="text-center">{{ Utilities::formatDisplayDateTime($list[$i]->created_at) }}</td>
                            </tr>
                        @endfor
                    </tbody>
                </table>
            </div>

            {!! $list->appends(Request::all())->links('backend::shared.pagination') !!}
        </div>
    </div>
@endsection