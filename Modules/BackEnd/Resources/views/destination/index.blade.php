@extends('backend::layouts.master')

@php
    $languageCode = Route::current()->parameter('languageCode');
    $queryStr = request()->query();
    $queryStr['languageCode'] = $languageCode;
    $statusClass = ['draft' => 'secondary', 'review' => 'warning', 'published' => 'success'];
@endphp

@section('content')
    @include('backend::shared.message')
    @include('backend::shared.modal-delete', [
        'modalId' => 'deleteModal',
        'title' => 'Xóa điểm đến này?',
        'message' => 'Điểm đến <strong id="delete-item-name"></strong> sẽ bị xóa khỏi website'
    ])
    <div class="card">
        <div class="card-header">
            <div class="row justify-content-between">
                <div class="col-md-6">
                    <h1 class="h4 m-0">{{ $title }}</h1>
                </div>
                <div class="col-md-6 d-flex justify-content-end">
                    @can('destination-create')
                        <a href="{{ Utilities::getUrlWithGoBack(route(Utilities::getRouteName('backend.destination.create'), $queryStr)) }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Thêm mới
                        </a>
                    @endcan
                </div>
            </div>
        </div>
        <div class="card-body">
            {{ Form::open(['method' => 'get', 'class' => 'form-search mb-2']) }}
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label>TÌM THEO TÊN</label>
                        {{ Form::text('keyword', Request::get('keyword'), ['class' => 'form-control', 'placeholder' => 'Nhập tên hoặc slug...', 'autocomplete' => 'off']) }}
                    </div>
                    <div class="form-group col-md-2">
                        <label>VỊNH</label>
                        {{ Form::select('bay', config('backend.destinationBay'), Request::get('bay'), ['class' => 'form-control', 'placeholder' => 'Tất cả']) }}
                    </div>
                    <div class="form-group col-md-2">
                        <label>LOẠI</label>
                        {{ Form::select('category', config('backend.destinationCategory'), Request::get('category'), ['class' => 'form-control', 'placeholder' => 'Tất cả']) }}
                    </div>
                    <div class="form-group col-md-2">
                        <label>TRẠNG THÁI</label>
                        {{ Form::select('status', config('backend.destinationStatus'), Request::get('status'), ['class' => 'form-control', 'placeholder' => 'Tất cả']) }}
                    </div>
                    <div class="form-group col-md-2">
                        <label>&nbsp;</label>
                        {{ Form::button('<i class="fas fa-search"></i> Tìm kiếm', ['type' => 'submit', 'class' => 'btn btn-success btn-block']) }}
                    </div>
                </div>
            {{ Form::close() }}

            <div class="table-responsive-sm">
                <table class="table table-striped table-data">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 10px;">#</th>
                            <th>ĐIỂM ĐẾN</th>
                            <th>VỊNH</th>
                            <th>LOẠI</th>
                            <th>BẢN ĐỒ</th>
                            <th>TRẠNG THÁI</th>
                            @canany(['destination-update', 'destination-delete'])
                                <th class="text-center" style="width: 110px;">THAO TÁC</th>
                            @endcanany
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($list as $i => $item)
                            @php
                                $r = $queryStr;
                                $r['id'] = $item->id;
                            @endphp
                            <tr data-id="{{ $item->id }}">
                                <td class="text-center align-middle">{{ $i + 1 }}</td>
                                <td class="align-middle">
                                    <div class="d-flex align-items-center">
                                        @if ($item->image_link)
                                            <img src="{{ Utilities::getFileLink($item->image_link) }}" alt="" class="mr-2" style="width: 64px; height: 44px; object-fit: cover;">
                                        @endif
                                        <div>
                                            <span class="d-block">{{ $item->name }}</span>
                                            <small class="text-muted">/{{ $item->slug }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td class="align-middle">{{ config('backend.destinationBay')[$item->bay] ?? $item->bay }}</td>
                                <td class="align-middle">{{ config('backend.destinationCategory')[$item->category] ?? $item->category }}</td>
                                <td class="align-middle">
                                    @if ($item->coords_verified)
                                        <span class="text-success"><i class="fas fa-check"></i> Đã kiểm chứng</span>
                                    @else
                                        <span class="text-muted">Không hiển thị</span>
                                    @endif
                                </td>
                                <td class="align-middle">
                                    <span class="badge badge-{{ $statusClass[$item->status] ?? 'secondary' }}">{{ config('backend.destinationStatus')[$item->status] ?? $item->status }}</span>
                                </td>
                                @canany(['destination-update', 'destination-delete'])
                                    <td class="text-center align-middle">
                                        @can('destination-update')
                                            <a href="{{ Utilities::getUrlWithGoBack(route(Utilities::getRouteName('backend.destination.edit'), $r)) }}" class="btn btn-info btn-sm" title="Sửa">
                                                <i class="fas fa-pencil-alt"></i>
                                            </a>
                                        @endcan
                                        @can('destination-delete')
                                            <a href="#" class="btn btn-danger btn-sm" title="Xóa"
                                                data-modal-delete="deleteModal"
                                                data-id="{{ $item->id }}"
                                                data-name="{{ $item->name }}"
                                                data-ajax-url="{{ route(Utilities::getRouteName('backend.destination.destroy'), ['languageCode' => $languageCode]) }}">
                                                <i class="fas fa-trash-alt"></i>
                                            </a>
                                        @endcan
                                    </td>
                                @endcanany
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted">Không có dữ liệu</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    @can('destination-update')
        <script src="{{ asset('/assets/backend/plugins/jquery-ui/jquery-ui.min.js') }}"></script>
        <script src="{{ asset('/assets/backend/plugins/touchpunch/jquery.ui.touch-punch.min.js') }}"></script>
        <script type="text/javascript">
            $('.table-data tbody').sortable({
                update: function () {
                    let listId = {};
                    $('.table-data tbody tr').each(function (index) {
                        $('td:eq(0)', this).text(index + 1);
                        listId[$(this).data('id')] = index + 1;
                    });
                    $.ajax({
                        url: '{{ route(Utilities::getRouteName('backend.destination.orderUpdate'), ['languageCode' => $languageCode]) }}',
                        type: 'POST',
                        contentType: 'application/json',
                        data: JSON.stringify({id: listId}),
                    });
                }
            });
        </script>
    @endcan
@endsection
