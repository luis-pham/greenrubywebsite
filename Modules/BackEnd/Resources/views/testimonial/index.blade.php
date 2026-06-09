@extends('backend::layouts.master')

@php
    $languageCode = Route::current()->parameter('languageCode');
    $queryStr = request()->query();
    $queryStr['languageCode'] = $languageCode;
@endphp

@section('content')
    @include('backend::shared.message')
    @include('backend::shared.modal-delete', [
        'modalId' => 'deleteModal',
        'title' => 'Xóa đánh giá này?',
        'message' => 'Đánh giá của <strong id="delete-item-name"></strong> sẽ bị gỡ khỏi danh sách hiển thị'
    ])
    <div class="card">
        <div class="card-header">
            <div class="row justify-content-between">
                <div class="col-md-6">
                    <h1 class="h4 m-0">{{ $title }}</h1>
                </div>
                <div class="col-md-6 d-flex justify-content-end">
                    @can('group-testimonial-create')
                        <a href="{{ Utilities::getUrlWithGoBack(route(Utilities::getRouteName('backend.testimonial.create'), $queryStr)) }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Thêm mới
                        </a>
                    @endcan
                </div>
            </div>
        </div>
        <div class="card-body">
            {{ Form::open(['method' => 'get', 'class' => 'form-search mb-2']) }}
                <div class="form-row">
                    <div class="form-group col-md-5">
                        <label>TÌM THEO TÊN KHÁCH HÀNG</label>
                        {{ Form::text('keyword', Request::get('keyword'), ['class' => 'form-control', 'placeholder' => 'Nhập tên khách hàng...', 'autocomplete' => 'off']) }}
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
                            <th style="width: 200px;">KHÁCH HÀNG</th>

                            <th style="width: 200px;">VỊ TRÍ</th>
                            <th class="" style="width: 250px;">ĐÁNH GIÁ</th>
                            @canany(['group-testimonial-update', 'group-testimonial-delete'])
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
                                        <img src="{{ Utilities::getFileLink($item->avatar) }}" alt="{{ $item->fullname }}" class="mr-2" style="width: 50px; height: 50px; border-radius: 50%; object-fit: cover;">
                                        <div class="d-flex flex-column">
                                            <a href="{{ Utilities::getUrlWithGoBack(route(Utilities::getRouteName('backend.testimonial.show'), $r)) }}" class="d-block" title="{{ $item->fullname }}">
                                                {{ $item->fullname }}
                                            </a>
                                            <small class="text-muted d-block">
                                                {{ Utilities::formatDisplayDateOnly($item->created_at) }}
                                            </small>
                                        </div>
                                    </div>
                                </td>
                                <td class="align-middle">{{ $item->position }}</td>
                                <td class="align-middle">
                                    @if($item->content)
                                        {{ Str::limit($item->content, 80) }}
                                    @endif
                                </td>
                                @canany(['group-testimonial-update', 'group-testimonial-delete'])
                                    <td class="text-center align-middle">
                                        @can('group-testimonial-update')
                                            <a href="{{ Utilities::getUrlWithGoBack(route(Utilities::getRouteName('backend.testimonial.edit'), $r)) }}" class="btn btn-info btn-sm" title="Sửa">
                                                <i class="fas fa-pencil-alt"></i>
                                            </a>
                                        @endcan
                                        @can('group-testimonial-delete')
                                            <a href="#" class="btn btn-danger btn-sm" title="Xóa" 
                                                   data-modal-delete="deleteModal"
                                                   data-id="{{ $item->id }}" 
                                                   data-name="{{ $item->fullname }}" 
                                                   data-ajax-url="{{ route(Utilities::getRouteName('backend.testimonial.destroy'), ['languageCode' => $languageCode]) }}">
                                                <i class="fas fa-trash-alt"></i>
                                            </a>
                                        @endcan
                                    </td>
                                @endcanany
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ \Illuminate\Support\Facades\Gate::any(['group-testimonial-update', 'group-testimonial-delete']) ? 5 : 4 }}" class="text-center text-muted">Không có dữ liệu</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    @can('group-testimonial-update')
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
                        $('td:eq(0)', tr).text(ord);
                        tr.attr('data-id', id);
                        listId[id] = ord;
                    });

                    $.ajax({
                        url: '{{ route(Utilities::getRouteName('backend.testimonial.orderUpdate'), ['languageCode' => $languageCode]) }}',
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