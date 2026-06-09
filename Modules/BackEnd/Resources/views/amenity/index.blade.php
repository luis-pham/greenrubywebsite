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
        'title' => 'Xóa tiện ích này?',
        'message' => 'Tiện ích <strong id="delete-item-name"></strong> sẽ bị gỡ khỏi danh sách hiển thị'
    ])
    <div class="card">
        <div class="card-header">
            <div class="row justify-content-between">
                <div class="col-md-6">
                    <h1 class="h4 m-0">{{ $title }}</h1>
                </div>
                <div class="col-md-6 d-flex justify-content-end">
                    @can('group-amenity-create')
                        <a href="{{ Utilities::getUrlWithGoBack(route(Utilities::getRouteName('backend.amenity.create'), $queryStr)) }}" class="btn btn-primary btn-sm">
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
                        <label>TÌM THEO TÊN TIỆN ÍCH</label>
                        {{ Form::text('keyword', Request::get('keyword'), ['class' => 'form-control', 'placeholder' => 'Nhập tên tiện ích...', 'autocomplete' => 'off']) }}
                    </div>                   
                    <div class="form-group col-md-2">
                        <label>&nbsp;</label>
                        {{ Form::button('<i class="fas fa-search"></i> Tìm kiếm', ['type' => 'submit', 'class' => 'btn btn-success btn-block']) }}
                    </div>
                </div>
            {{ Form::close() }}
            
            <div class="row amenity-sortable">
                @forelse ($list as $i => $item)
                    @php
                        $r = $queryStr;
                        $r['id'] = $item->id;
                    @endphp
                    <div class="col-md-6 col-lg-4 mb-4 amenity-item" data-id="{{ $item->id }}">
                        <div class="card h-100 amenity-card shadow-sm">
                            <div class="card-body d-flex flex-column">
                                <div class="d-flex justify-content-between">
                                    <div class="amenity-icon-wrapper mb-3">
                                        @if($item->icon)
                                            @if(Str::contains($item->icon, ['.', '/']) || Str::startsWith($item->icon, 'images/'))
                                                {{-- Đây là đường dẫn ảnh --}}
                                                <img src="{{ Utilities::getFileLink($item->icon) }}" alt="{{ $item->name }}" class="amenity-icon-img">
                                            @else
                                                {{-- Đây là Font Awesome icon class --}}
                                                <i class="{{ $item->icon }} fa-3x"></i>
                                            @endif
                                        @else
                                            <i class="fas fa-image fa-3x text-muted"></i>
                                        @endif
                                    </div>
                                    @canany(['group-amenity-update', 'group-amenity-delete'])
                                        <div class="d-flex align-items-start">
                                            @can('group-amenity-update')
                                                <a href="{{ Utilities::getUrlWithGoBack(route(Utilities::getRouteName('backend.amenity.edit'), $r)) }}" class="btn btn-info btn-sm mr-1" title="Sửa">
                                                    <i class="fas fa-pencil-alt"></i>
                                                </a>
                                            @endcan
                                            @can('group-amenity-delete')
                                                <a href="#" class="btn btn-danger btn-sm" title="Xóa" 
                                                   data-modal-delete="deleteModal"
                                                   data-id="{{ $item->id }}" 
                                                   data-name="{{ $item->name }}" 
                                                   data-ajax-url="{{ route(Utilities::getRouteName('backend.amenity.destroy'), ['languageCode' => $languageCode]) }}">
                                                    <i class="fas fa-trash-alt"></i>
                                                </a>
                                            @endcan
                                        </div>
                                    @endcanany
                                </div>
                                
                                <h5 class="card-title font-weight-bold">
                                    <a href="{{ Utilities::getUrlWithGoBack(route(Utilities::getRouteName('backend.amenity.show'), $r)) }}" class="text-reset">
                                        {{ $item->name }}
                                    </a>
                                </h5>
                                <p class="card-text text-muted flex-grow-1">
                                    @if($item->description)
                                        <em>"{{ Str::limit($item->description, 100) }}"</em>
                                    @else
                                        <em class="text-black-50">Chưa có mô tả</em>
                                    @endif
                                </p>
                                
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="alert alert-info text-center">
                            <i class="fas fa-info-circle"></i> Không có dữ liệu
                        </div>
                    </div>
                @endforelse
            </div>

            {{-- Pagination --}}
            {!! $list->appends(Request::all())->links('backend::shared.pagination') !!}
        </div>
    </div>
@endsection

@section('styles')
<style>
    .amenity-card {
        transition: all 0.3s ease;
        border: 1px solid #e3e6f0;
        border-radius: 0.5rem;
    }
    
    .amenity-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
    }
    
    .amenity-icon-wrapper {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 70px;
        height: 70px;
        border-radius: 0.5rem;
        margin-bottom: 1rem;
        overflow: hidden;
        background: #f8f9fc;
        transition: all 0.3s ease;
    }
    
    .amenity-icon-wrapper i {
        color: #32c36c;
        transition: all 0.3s ease;
    }
    
    .amenity-card:hover .amenity-icon-wrapper {
        transform: scale(1.1);
    }
    
    .amenity-icon-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 0.5rem;
        transition: all 0.3s ease;
    }

    .amenity-card .card-title {
        color: #2e3545;
        font-size: 1.1rem;
        margin-bottom: 0.75rem;
    }
    
    .amenity-card .card-text {
        font-size: 0.9rem;
        line-height: 1.5;
    }

    .amenity-order-badge {
        font-size: 0.75rem;
        height: 22px;
        display: inline-flex;
        align-items: center;
        padding: 0.25rem 0.5rem;
    }
</style>
@endsection

@section('scripts')
    @can('group-amenity-update')
        <script src="{{ asset('/assets/backend/plugins/jquery-ui/jquery-ui.min.js') }}"></script>
        <script src="{{ asset('/assets/backend/plugins/touchpunch/jquery.ui.touch-punch.min.js') }}"></script>
        <script type="text/javascript">
            $('.amenity-sortable').sortable({
                items: '.amenity-item',
                zIndex: 99999,
                update: function (ev, ui) {
                    let listId = {};
                    $('.amenity-sortable .amenity-item').each(function (index) {
                        let item = $(this);
                        var ord = index + 1;
                        var id = item.data('id');
                        item.attr('data-id', id);
                        listId[id] = ord;
                        item.find('.amenity-order-badge').text('#' + ord);
                    });

                    $.ajax({
                        url: '{{ route(Utilities::getRouteName('backend.amenity.orderUpdate'), ['languageCode' => $languageCode]) }}',
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