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
                    <div class="form-group col-md-7">
                        <label>Từ khóa</label>
                        {{ Form::text('keyword', Request::get('keyword'), ['class' => 'form-control', 'placeholder' => 'Nhập từ khóa...', 'autocomplete' => 'off']) }}
                    </div>
                    <div class="form-group col-md-3">
                        <label>Chuyên mục</label>
                        <div class="select2">
                            {{ Form::select('group_id', $listGroup, Request::get('group_id'), ['class' => 'form-control', 'placeholder' => 'Tất cả', 'autocomplete' => 'off']) }}
                        </div>
                    </div>
                    <div class="form-group col-md-2">
                        <label>&nbsp;</label>
                        {{ Form::button('<i class="fas fa-search"></i> Tìm kiếm', ['type' => 'submit', 'class' => 'btn btn-success btn-block']) }}
                    </div>
                </div>
            {{ Form::close() }}
            @canany(['faq-create', 'faq-delete', 'faq-order'])
                <div class="mb-3">
                    @can('faq-delete')
                        <button type="button" class="btn btn-danger btn-sm btn-delete-multi" data-ajax-url="{{ route(Utilities::getRouteName('backend.faq.destroy'), ['languageCode' => $languageCode]) }}">
                            <i class="fas fa-trash-alt"></i> Xóa
                        </button>
                    @endcan
                    @can('faq-create')
                        <a href="{{ Utilities::getUrlWithGoBack(route(Utilities::getRouteName('backend.faq.create'), ['languageCode' => $languageCode])) }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-file-alt"></i> Thêm
                        </a>
                    @endcan
                </div>
            @endcanany
            <table class="table table-striped table-data">
                <thead>
                    <tr>
                        @can('faq-delete')
                            <th class="text-center" style="width: 40px;">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" id="chk-all" class="chk-all custom-control-input" autocomplete="off" />
                                    <label class="custom-control-label" for="chk-all"></label>
                                </div>
                            </th>
                        @endcan
                        <th class="text-center" style="width: 50px;">#</th>
                        <th>Câu hỏi</th>
                        <th>Trả lời</th>
                        <th class="text-center" style="width: 120px;">Chuyên mục</th>
                        @canany(['faq-update', 'faq-delete'])
                            <th class="text-center" style="width: 110px;">Ch.năng</th>
                        @endcanany
                    </tr>
                </thead>
                <tbody>
                    @for ($i = 0; $i < count($list); $i++)
                        <tr data-id="{{ $list[$i]->id }}">
                            @can('faq-delete')
                                <td class="text-center">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" id="chk-item-{{ $list[$i]->id }}" class="chk-item custom-control-input" value="{{ $list[$i]->id }}" autocomplete="off" />
                                        <label class="custom-control-label" for="chk-item-{{ $list[$i]->id }}"></label>
                                    </div>
                                </td>
                            @endcan
                            <td class="text-center">{{ $i + 1 }}</td>
                            <td>
                                <a href="{{ Utilities::getUrlWithGoBack(route(Utilities::getRouteName('backend.faq.show'), ['languageCode' => $languageCode, 'id' => $list[$i]->id])) }}">
                                    {{ strip_tags($list[$i]->question) }}
                                </a>
                            </td>
                            <td>{{ strip_tags($list[$i]->answer) }}</td>
                            <td class="text-center">{{ $list[$i]->group_name }}</td>
                            @canany(['faq-update', 'faq-delete'])
                                <td class="text-center">
                                    @can('faq-update')
                                        <a href="{{ Utilities::getUrlWithGoBack(route(Utilities::getRouteName('backend.faq.edit'), ['languageCode' => $languageCode, 'id' => $list[$i]->id])) }}" class="btn btn-info btn-sm" title="Sửa">
                                            <i class="fas fa-pencil-alt"></i>
                                        </a>
                                    @endcan
                                    @can('faq-delete')
                                        <a href="#" class="btn btn-danger btn-sm btn-delete-one" title="Xóa" data-id="{{ $list[$i]->id }}" data-ajax-url="{{ route(Utilities::getRouteName('backend.faq.destroy'), ['languageCode' => $languageCode]) }}">
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
    </div>
@endsection

@section('scripts')
    @can('faq-order')
        <script src="{{ asset('/assets/backend/plugins/jquery-ui/jquery-ui.min.js') }}"></script>
        <script src="{{ asset('/assets/backend/plugins/touchpunch/jquery.ui.touch-punch.min.js') }}"></script>
        <script type="text/javascript">
            $('.table-data tbody').sortable({
                connectWith: '.table-data tbody',
                zIndex: 99999,
                update: function (ev, ui) {
                    let listId = {};
                    $('.table-data tbody tr').each(function (index) {
                        let tr = $(this);
                        var ord = index + 1;
                        var id = tr.data('id');
                        $('td:eq(1)', tr).text(ord);
                        tr.attr('data-id', id);
                        listId[id] = ord;
                    });

                    $.ajax({
                        url: '{{ route(Utilities::getRouteName('backend.faq.orderUpdate'), ['languageCode' => $languageCode]) }}',
                        type: 'POST',
                        contentType: 'application/json',
                        data: JSON.stringify({id: listId}),
                        traditional: true
                    });
                }
            });
        </script>
    @endcan
@endsection