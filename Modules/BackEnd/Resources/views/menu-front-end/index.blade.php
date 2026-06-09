@extends('backend::layouts.master')

@php
    $languageCode = Route::current()->parameter('languageCode');
@endphp

@section('content')
    @include('backend::shared.message')
    <div class="card">
        <div class="card-header">
            <h1 class="h4 m-0">{{ $title }}</h1>
        </div>
        <div class="card-body">
            {{ Form::open(['method' => 'get', 'class' => 'form-search mb-2']) }}
                <div class="form-row">
                    <div class="form-group col-md-10">
                        <label>Từ khóa</label>
                        {{ Form::text('keyword', Request::get('keyword'), ['class' => 'form-control', 'placeholder' => 'Nhập từ khóa...', 'autocomplete' => 'off']) }}
                    </div>
                    <div class="form-group col-md-2">
                        <label>&nbsp;</label>
                        {{ Form::button('<i class="fas fa-search"></i> Tìm kiếm', ['type' => 'submit', 'class' => 'btn btn-success btn-block']) }}
                    </div>
                </div>
            {{ Form::close() }}
            @canany(['menu-front-end-create', 'menu-front-end-delete'])
                <div class="mb-3">
                    @can('menu-front-end-delete')
                        <button type="button" class="btn btn-danger btn-sm btn-delete-multi" data-ajax-url="{{ route(Utilities::getRouteName('backend.menuFrontEnd.destroy'), ['languageCode' => $languageCode]) }}">
                            <i class="fas fa-trash-alt"></i> Xóa
                        </button>
                    @endcan
                    @can('menu-front-end-create')
                        <a href="{{ Utilities::getUrlWithGoBack(route(Utilities::getRouteName('backend.menuFrontEnd.create'), ['languageCode' => $languageCode])) }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-file-alt"></i> Thêm
                        </a>
                    @endcan
                </div>
            @endcanany
            <table class="table table-striped table-data">
                <thead>
                    <tr>
                        @can('menu-front-end-delete')
                            <th class="text-center" style="width: 40px;">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" id="chk-all" class="chk-all custom-control-input" autocomplete="off" />
                                    <label class="custom-control-label" for="chk-all"></label>
                                </div>
                            </th>
                        @endcan
                        <th class="text-center" style="width: 50px;">#</th>
                        <th>Tên</th>
                        <th>Mã</th>
                        <th class="text-center d-none d-sm-block">Mô tả</th>
                        @canany(['menu-front-end-update', 'menu-front-end-delete'])
                            <th class="text-center" style="width: 110px;">Ch.năng</th>
                        @endcanany
                    </tr>
                </thead>
                <tbody>
                    @for ($i = 0; $i < count($list); $i++)
                        <tr>
                            @can('menu-front-end-delete')
                                <td class="text-center">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" id="chk-item-{{ $list[$i]->id }}" class="chk-item custom-control-input" value="{{ $list[$i]->id }}" autocomplete="off" />
                                        <label class="custom-control-label" for="chk-item-{{ $list[$i]->id }}"></label>
                                    </div>
                                </td>
                            @endcan
                            <td class="text-center">{{ $i + $list->firstItem() }}</td>
                            <td>
                                <a href="{{ Utilities::getUrlWithGoBack(route(Utilities::getRouteName('backend.menuFrontEnd.show'), ['languageCode' => $languageCode, 'id' => $list[$i]->id])) }}">
                                    {{ $list[$i]->name }}
                                </a>
                            </td>
                            <td>{{ $list[$i]->code }}</td>
                            <td class="text-center d-none d-sm-block">{!! nl2br(e($list[$i]->description)) !!}</td>
                            @canany(['menu-front-end-update', 'menu-front-end-delete'])
                                <td class="text-center">
                                    @can('menu-front-end-update')
                                        <a href="{{ Utilities::getUrlWithGoBack(route(Utilities::getRouteName('backend.menuFrontEnd.edit'), ['languageCode' => $languageCode, 'id' => $list[$i]->id])) }}" class="btn btn-info btn-sm" title="Sửa">
                                            <i class="fas fa-pencil-alt"></i>
                                        </a>
                                    @endcan
                                    @can('menu-front-end-delete')
                                        <a href="#" class="btn btn-danger btn-sm btn-delete-one" title="Xóa" data-id="{{ $list[$i]->id }}" data-ajax-url="{{ route(Utilities::getRouteName('backend.menuFrontEnd.destroy'), ['languageCode' => $languageCode]) }}">
                                            <i class="fas fa-trash-alt"></i>
                                        </a>
                                    @endcan
                                </td>
                            @endcanany
                        </tr>
                    @endfor
                </tbody>
            </table>

            {!! $list->appends(Request::all())->links('backend::shared.pagination') !!}
        </div>
    </div>
@endsection