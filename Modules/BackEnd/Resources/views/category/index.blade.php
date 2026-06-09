@extends('backend::layouts.master')

@section('styles')
    <link href="{{ asset('/assets/frontend/plugins/font-awesome/css/all.min.css') }}" rel="stylesheet" />
@endsection

@php
    $typeName = Route::current()->parameter('typeName');
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
                    <div class="form-group col-md-6">
                        <label>Từ khóa</label>
                        {{ Form::text('keyword', Request::get('keyword'), ['class' => 'form-control', 'placeholder' => 'Nhập từ khóa...', 'autocomplete' => 'off']) }}
                    </div>
                    <div class="form-group col-md-4">
                        <label>Chuyên mục</label>
                        <div class="select2">
                            {{ Form::select('parent_id', $listCategory, Request::get('parent_id'), ['class' => 'form-control', 'placeholder' => 'Tất cả', 'autocomplete' => 'off']) }}
                        </div>
                    </div>
                    <div class="form-group col-md-2">
                        <label>&nbsp;</label>
                        {{ Form::button('<i class="fas fa-search"></i> Tìm kiếm', ['type' => 'submit', 'class' => 'btn btn-success btn-block']) }}
                    </div>
                </div>
            {{ Form::close() }}
            @canany(['category-' . $typeName . '-create', 'category-' . $typeName . '-delete'])
                <div class="mb-3">
                    @can('category-' . $typeName . '-delete')
                        <button type="button" class="btn btn-danger btn-sm btn-delete-multi" data-ajax-url="{{ route(Utilities::getRouteName('backend.category.destroy'), ['languageCode' => $languageCode, 'typeName' => $typeName]) }}">
                            <i class="fas fa-trash-alt"></i> Xóa
                        </button>
                    @endcan
                    @can('category-' . $typeName . '-create')
                        <a href="{{ Utilities::getUrlWithGoBack(route(Utilities::getRouteName('backend.category.create'), ['languageCode' => $languageCode, 'typeName' => $typeName])) }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-file-alt"></i> Thêm
                        </a>
                    @endcan
                </div>
            @endcanany
            <table class="table table-striped table-data">
                <thead>
                    <tr>
                        @can('category-' . $typeName . '-delete')
                            <th class="text-center" style="width: 40px;">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" id="chk-all" class="chk-all custom-control-input" autocomplete="off" />
                                    <label class="custom-control-label" for="chk-all"></label>
                                </div>
                            </th>
                        @endcan
                        <th class="text-center" style="width: 50px;">#</th>
                        <th>Tên</th>
                        <th class="text-center" style="width: 200px;">Ch.năng</th>
                    </tr>
                </thead>
                <tbody>
                    @for ($i = 0; $i < count($list); $i++)
                        <tr>
                            @can('category-' . $typeName . '-delete')
                                <td class="text-center">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" id="chk-item-{{ $list[$i]->id }}" class="chk-item custom-control-input" value="{{ $list[$i]->id }}" autocomplete="off" />
                                        <label class="custom-control-label" for="chk-item-{{ $list[$i]->id }}"></label>
                                    </div>
                                </td>
                            @endcan
                            <td class="text-center">{{ $i + $list->firstItem() }}</td>
                            <td>
                                <a href="{{ Utilities::getUrlWithGoBack(route(Utilities::getRouteName('backend.category.show'), ['languageCode' => $languageCode, 'typeName' => $typeName, 'id' => $list[$i]->id])) }}">
                                    @if ($list[$i]->icon)
                                        <i class="{{ $list[$i]->icon }} mr-1"></i>
                                    @endif
                                    {{ Utilities::getCategoryNameByLevel($list[$i]->name, $list[$i]->lvl) }}
                                </a>
                            </td>
                            <td class="text-center">
                                <a href="{{ route(Utilities::getRouteName('frontend.article.category'), ['languageCode' => $languageCode, 'slug' => $list[$i]->slug]) }}" class="btn btn-primary btn-sm" title="Xem trước" target="_blank">
                                    <i class="fal fa-globe"></i>
                                </a>
                                @can('category-' . $typeName . '-update')
                                    <a href="{{ Utilities::getUrlWithGoBack(route(Utilities::getRouteName('backend.category.edit'), ['languageCode' => $languageCode, 'typeName' => $typeName, 'id' => $list[$i]->id])) }}" class="btn btn-info btn-sm" title="Sửa">
                                        <i class="fas fa-pencil-alt"></i>
                                    </a>
                                @endcan
                                @can('category-' . $typeName . '-update')
                                    <a href="#" class="btn btn-warning btn-sm btn-move" title="Xuống" data-id="{{ $list[$i]->id }}" data-ajax-url="{{ route(Utilities::getRouteName('backend.category.moveDown'), ['languageCode' => $languageCode, 'typeName' => $typeName]) }}">
                                        <i class="fas fa-chevron-down"></i>
                                    </a>
                                    <a href="#" class="btn btn-warning btn-sm btn-move" title="Lên" data-id="{{ $list[$i]->id }}" data-ajax-url="{{ route(Utilities::getRouteName('backend.category.moveUp'), ['languageCode' => $languageCode, 'typeName' => $typeName]) }}">
                                        <i class="fas fa-chevron-up"></i>
                                    </a>
                                @endcan
                                @can('category-' . $typeName . '-delete')
                                    <a href="#" class="btn btn-danger btn-sm btn-delete-one" title="Xóa" data-id="{{ $list[$i]->id }}" data-ajax-url="{{ route(Utilities::getRouteName('backend.category.destroy'), ['languageCode' => $languageCode, 'typeName' => $typeName]) }}">
                                        <i class="fas fa-trash-alt"></i>
                                    </a>
                                @endcan
                            </td>
                        </tr>
                    @endfor
                </tbody>
            </table>

            {!! $list->appends(Request::all())->links('backend::shared.pagination') !!}
        </div>
    </div>
@endsection

@section('scripts')
    <script type="text/javascript">
        $(document).ready(function () {
            $('.btn-move').click(function (e) {
                e.preventDefault();

                let id = parseInt($(this).attr('data-id'));
                let url = $(this).attr('data-ajax-url');

                $.ajax({
                    type: 'POST',
                    url: url,
                    data: {
                        id: id
                    },
                    traditional: true,
                    complete: function () {
                        window.location.reload();
                    }
                });
            });
        });
    </script>
@endsection