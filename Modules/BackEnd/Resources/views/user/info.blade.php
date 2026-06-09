@extends('backend::layouts.master')

@section('content')
    <div class="card">
        <div class="card-header">
            <h1 class="h4 m-0">{{ $title }}</h1>
        </div>
        <div class="card-body">
            <dl class="row">
                <dt class="col-md-2">Họ tên</dt>
                <dd class="col-md-10">{{ $obj->fullname }}</dd>
            </dl>
            <dl class="row">
                <dt class="col-md-2">Email</dt>
                <dd class="col-md-10">{{ $obj->email }}</dd>
            </dl>
        </div>
    </div>
@endsection