@extends('backend::layouts.master')

@section('content')
    <div class="card">
        <div class="card-header">
            <h1 class="h4 m-0">{{ $title }}</h1>
        </div>
        <div class="card-body">
            <dl class="row">
                <dt class="col-md-2">Loại</dt>
                <dd class="col-md-10">{!! Utilities::formatDisplayLogType($obj->type) !!}</dd>
            </dl>
            <dl class="row">
                <dt class="col-md-2">Hành động</dt>
                <dd class="col-md-10">{{ $obj->action }}</dd>
            </dl>
            <dl class="row">
                <dt class="col-md-2">Chi tiết</dt>
                <dd class="col-md-10">{{ $obj->detail }}</dd>
            </dl>
            <dl class="row">
                <dt class="col-md-2">User Agent</dt>
                <dd class="col-md-10">{{ $obj->user_agent }}</dd>
            </dl>
            <dl class="row">
                <dt class="col-md-2">IP</dt>
                <dd class="col-md-10">{{ $obj->ip }}</dd>
            </dl>
            <dl class="row">
                <dt class="col-md-2">Người dùng</dt>
                <dd class="col-md-10">
                    @if ($obj->user_id)
                        <a href="{{ route('backend.user.info', ['id' => $obj->user_id]) }}" target="_blank">
                            {{ $obj->user_fullname }}
                        </a>
                    @endif
                </dd>
            </dl>
            <dl class="row">
                <dt class="col-md-2">Thời gian</dt>
                <dd class="col-md-10">{{ Utilities::formatDisplayDateTime($obj->created_at) }}</dd>
            </dl>
        </div>
        <div class="card-footer">
            <a href="{{ Utilities::getGoBackUrl(route('backend.user.index')) }}" class="btn btn-light btn-sm">
                <i class="fas fa-undo"></i> Quay lại
            </a>
        </div>
    </div>
@endsection