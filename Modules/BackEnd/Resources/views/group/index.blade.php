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
            @canany(['group-' . $typeName . '-create', 'group-' . $typeName . '-delete'])
                <div class="mb-3">
                    @can('group-' . $typeName . '-delete')
                        <button type="button" class="btn btn-danger btn-sm btn-delete-multi" data-ajax-url="{{ route(Utilities::getRouteName('backend.group.destroy'), ['languageCode' => $languageCode, 'typeName' => $typeName]) }}">
                            <i class="fas fa-trash-alt"></i> Xóa
                        </button>
                    @endcan
                    @can('group-' . $typeName . '-create')
                        <a href="{{ Utilities::getUrlWithGoBack(route(Utilities::getRouteName('backend.group.create'), ['languageCode' => $languageCode, 'typeName' => $typeName])) }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-file-alt"></i> Thêm
                        </a>
                    @endcan
                </div>
            @endcanany
            <table class="table table-striped table-data">
                <thead>
                    <tr>
                        @can('group-' . $typeName . '-delete')
                            <th class="text-center" style="width: 40px;">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" id="chk-all" class="chk-all custom-control-input" autocomplete="off" />
                                    <label class="custom-control-label" for="chk-all"></label>
                                </div>
                            </th>
                        @endcan
                        <th class="text-center" style="width: 50px;">#</th>
                        <th>Tên</th>
                        @if ($type == config('backend.groupType.expActivity'))
                            <th class="text-center" style="width: 200px;">Tab</th>
                        @endif
                        @canany(['group-' . $typeName . '-update', 'group-' . $typeName . '-delete'])
                            <th class="text-center" style="width: 150px;">Ch.năng</th>
                        @endcanany
                    </tr>
                </thead>
                <tbody>
                    @for ($i = 0; $i < count($list); $i++)
                        <tr data-id="{{ $list[$i]->id }}">
                            @can('group-' . $typeName . '-delete')
                                <td class="text-center">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" id="chk-item-{{ $list[$i]->id }}" class="chk-item custom-control-input" value="{{ $list[$i]->id }}" autocomplete="off" />
                                        <label class="custom-control-label" for="chk-item-{{ $list[$i]->id }}"></label>
                                    </div>
                                </td>
                            @endcan
                            <td class="text-center">{{ $i + 1 }}</td>
                            <td>
                                <a href="{{ Utilities::getUrlWithGoBack(route(Utilities::getRouteName('backend.group.show'), ['languageCode' => $languageCode, 'typeName' => $typeName, 'id' => $list[$i]->id])) }}">
                                    {{ $list[$i]->name }}
                                </a>
                            </td>
                            @if ($type == config('backend.groupType.expActivity'))
                                <td class="text-center">{{ Utilities::formatDisplayGroupTab($list[$i]->type, $list[$i]->tab, $languageCode) }}</td>
                            @endif
                            @canany(['group-' . $typeName . '-update', 'group-' . $typeName . '-delete'])
                                <td class="text-center">
                                    @can('group-' . $typeName . '-update')
                                        <a href="{{ Utilities::getUrlWithGoBack(route(Utilities::getRouteName('backend.group.edit'), ['languageCode' => $languageCode, 'typeName' => $typeName, 'id' => $list[$i]->id])) }}" class="btn btn-info btn-sm" title="Sửa">
                                            <i class="fas fa-pencil-alt"></i>
                                        </a>
                                    @endcan
                                    @can('group-' . $typeName . '-delete')
                                        <a href="#" class="btn btn-danger btn-sm btn-delete-one" title="Xóa" data-id="{{ $list[$i]->id }}" data-ajax-url="{{ route(Utilities::getRouteName('backend.group.destroy'), ['languageCode' => $languageCode, 'typeName' => $typeName]) }}">
                                            <i class="fas fa-trash-alt"></i>
                                        </a>
                                    @endcan
                            @endcanany
                            </td>
                        </tr>
                    @endfor
                </tbody>
            </table>
        </div>
    </div>
@endsection



@section('scripts')
    @can('group-' . $typeName . '-order')
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
                        url: '{{ route(Utilities::getRouteName('backend.group.orderUpdate'), ['languageCode' => $languageCode, 'typeName' => $typeName]) }}',
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