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
                    <div class="form-group col-md-4">
                        <label>Từ khóa</label>
                        {{ Form::text('keyword', Request::get('keyword'), ['class' => 'form-control', 'placeholder' => 'Nhập từ khóa...', 'autocomplete' => 'off']) }}
                    </div>
                    <div class="form-group col-md-3">
                        <label>Chuyên mục</label>
                        <div class="select2">
                            {{ Form::select('category_id', $listCategory, Request::get('category_id'), ['class' => 'form-control', 'placeholder' => 'Tất cả', 'autocomplete' => 'off']) }}
                        </div>
                    </div>
                    <div class="form-group col-md-2">
                        <label>Nổi bật</label>
                        <select name="is_featured" class="form-control" autocomplete="off">
                            <option value="">Tất cả</option>
                            <option value="1" {{ Request::get('is_featured') == '1' ? 'selected="selected"' : '' }}>Có</option>
                            <option value="0"{{ Request::get('is_featured') == '0' ? 'selected="selected"' : '' }}>Không</option>
                        </select>
                    </div>
                    <div class="form-group col-md-2">
                        <label>Trạng thái</label>
                        <select name="is_published" class="form-control" autocomplete="off">
                            <option value="">Tất cả</option>
                            <option value="1" {{ Request::get('is_published') == '1' ? 'selected="selected"' : '' }}>Đã xuất bản</option>
                            <option value="0"{{ Request::get('is_published') == '0' ? 'selected="selected"' : '' }}>Chưa xuất bản</option>
                        </select>
                    </div>
                    <div class="form-group col-md-1">
                        <label>&nbsp;</label>
                        {{ Form::button('<i class="fas fa-search"></i>', ['type' => 'submit', 'class' => 'btn btn-success btn-block']) }}
                    </div>
                </div>
            {{ Form::close() }}
            @canany(['article-create', 'article-delete', 'article-order'])
                <div class="mb-3">
                    @can('article-delete')
                        <button type="button" class="btn btn-danger btn-sm btn-delete-multi" data-ajax-url="{{ route(Utilities::getRouteName('backend.article.destroy'), ['languageCode' => $languageCode]) }}">
                            <i class="fas fa-trash-alt"></i> Xóa
                        </button>
                    @endcan
                    @can('article-create')
                        <a href="{{ Utilities::getUrlWithGoBack(route(Utilities::getRouteName('backend.article.create'), ['languageCode' => $languageCode])) }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-file-alt"></i> Thêm
                        </a>
                    @endcan
                </div>
            @endcanany
            <div class="table-responsive-sm">
                <table class="table table-striped table-data">
                    <thead>
                        <tr>
                            @can('article-delete')
                                <th class="text-center" style="width: 40px;">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" id="chk-all" class="chk-all custom-control-input" autocomplete="off" />
                                        <label class="custom-control-label" for="chk-all"></label>
                                    </div>
                                </th>
                            @endcan
                            <th class="text-center" style="width: 50px;">#</th>
                            <th>Tiêu đề</th>
                            <th class="text-center" style="width: 120px;">Chuyên mục</th>
                            <th class="text-center" style="width: 120px;">Người tạo</th>
                            <th class="text-center" style="width: 120px;">TG xuất bản</th>
                            <th class="text-center" style="width: 100px;">Trạng thái</th>
                            <th class="text-center" style="width: 120px;">Ch.năng</th>
                        </tr>
                    </thead>
                    <tbody>
                        @for ($i = 0; $i < count($list); $i++)
                            <tr>
                                @can('article-delete')
                                    <td class="text-center">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" id="chk-item-{{ $list[$i]->id }}" class="chk-item custom-control-input" value="{{ $list[$i]->id }}" autocomplete="off" />
                                            <label class="custom-control-label" for="chk-item-{{ $list[$i]->id }}"></label>
                                        </div>
                                    </td>
                                @endcan
                                <td class="text-center">{{ $i + $list->firstItem() }}</td>
                                <td>
                                    <a href="{{ Utilities::getUrlWithGoBack(route(Utilities::getRouteName('backend.article.show'), ['languageCode' => $languageCode, 'id' => $list[$i]->id])) }}">
                                        {{ $list[$i]->title }}
                                    </a>
                                </td>
                                <td class="text-center">
                                    @if ($list[$i]->category_id)
                                        @if (\Auth::user()->can('category-article-read'))
                                            <a href="{{ Utilities::getUrlWithGoBack(route(Utilities::getRouteName('backend.category.show'), ['languageCode' => $languageCode, 'typeName' => 'article', 'id' => $list[$i]->category_id])) }}" target="_blank">
                                                {{ $list[$i]->category_name }}
                                            </a>
                                        @else
                                            {{ $list[$i]->category_name }}
                                        @endif
                                    @endif
                                </td>
                                <td class="text-center"><a href="{{ route('backend.user.info', ['id' => $list[$i]->created_by]) }}" target="_blank">{{ $list[$i]->created_by_fullname }}</a></td>
                                <td class="text-center">{{ Utilities::formatDisplayDateTime($list[$i]->publish_date) }}</td>
                                <td class="text-center">{!! Utilities::formatDisplayArticleStatus($list[$i]->is_published) !!}</td>
                                <td class="text-center">
                                    <a href="{{ route(Utilities::getRouteName('frontend.article.show'), ['languageCode' => $languageCode, 'slug' => Utilities::convertToAlias($list[$i]->title), 'id' => $list[$i]->id]) }}" class="btn btn-primary btn-sm {{ !$list[$i]->is_published ? 'disabled' : '' }}" title="Xem trước" target="_blank">
                                        <i class="fal fa-globe"></i>
                                    </a>
                                    @can('article-update')
                                        <a href="{{ Utilities::getUrlWithGoBack(route(Utilities::getRouteName('backend.article.edit'), ['languageCode' => $languageCode, 'id' => $list[$i]->id])) }}" class="btn btn-info btn-sm" title="Sửa">
                                            <i class="fas fa-pencil-alt"></i>
                                        </a>
                                    @endcan
                                    @can('article-delete')
                                        <a href="#" class="btn btn-danger btn-sm btn-delete-one" title="Xóa" data-id="{{ $list[$i]->id }}" data-ajax-url="{{ route(Utilities::getRouteName('backend.article.destroy'), ['languageCode' => $languageCode]) }}">
                                            <i class="fas fa-trash-alt"></i>
                                        </a>
                                    @endcan
                                </td>
                            </tr>
                        @endfor
                    </tbody>
                </table>
            </div>
            
            {!! $list->appends(Request::all())->links('backend::shared.pagination') !!}
        </div>
    </div>
@endsection