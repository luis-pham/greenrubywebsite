@extends('backend::layouts.master')

@section('content')
    @include('backend::shared.message')
    @include('backend::shared.modal-delete', [
        'modalId' => 'deleteModal',
        'title' => 'Xóa dịch vụ này?',
        'message' => 'Dịch vụ <strong id="delete-item-name"></strong> sẽ bị gỡ khỏi danh sách hiển thị'
    ])
    @php
        $languageCode = request()->route('languageCode');
        $routeParams = $languageCode ? ['languageCode' => $languageCode] : [];
    @endphp
    <div class="card">
        <div class="card-header">
            <div class="row justify-content-between">
                <div class="col-md-6">
                    <h1 class="h4 m-0">{{ $title }}</h1>
                </div>
                <div class="col-md-6 d-flex justify-content-end">
                    @can('group-service-create')
                        <a href="{{ Utilities::getUrlWithGoBack(route(Utilities::getRouteName('backend.service.create'), $routeParams)) }}" class="btn btn-primary btn-sm">
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
                        <label>TÌM THEO TÊN DỊCH VỤ</label>
                        {{ Form::text('keyword', Request::get('keyword'), ['class' => 'form-control', 'placeholder' => 'Tìm theo tên dịch vụ hoặc phân loại...', 'autocomplete' => 'off']) }}
                    </div>
                    <div class="form-group col-md-2">
                        <label>&nbsp;</label>
                        {{ Form::button('<i class="fas fa-search"></i> Tìm kiếm', ['type' => 'submit', 'class' => 'btn btn-success btn-block']) }}
                    </div>
                </div>
            {{ Form::close() }}
            
            <div class="table-responsive">
                <table class="table table-striped table-data" style="table-layout: fixed; width: 100%;">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 50px;">#</th>
                            <th style="width: 300px;">DỊCH VỤ</th>
                            <th style="width: 125px;">PHÂN LOẠI</th>
                            <th style="width: 125px;">LOẠI HÌNH</th>
                            <th style="width: 125px;">GIÁ NIÊM YẾT</th>
                            @canany(['group-service-update', 'group-service-delete'])
                                <th class="text-center" style="width: 110px;">THAO TÁC</th>
                            @endcanany  
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($list as $i => $item)
                            <tr>
                                <td class="text-center align-middle">{{ $i + $list->firstItem() }}</td>
                                <td class="align-middle">
                                    <div class="d-flex align-items-center">
                                        @if($item->image_link)
                                            <img src="{{ Utilities::getFileLink($item->image_link) }}" alt="{{ $item->name }}" class="img-thumbnail mr-2" style="width: 50px; height: 50px; object-fit: cover;">
                                        @endif
                                        <div>
                                            <a href="{{ Utilities::getUrlWithGoBack(route(Utilities::getRouteName('backend.service.show'), array_merge($routeParams, ['id' => $item->id]))) }}" class="d-block" title="{{ $item->name }}">
                                                {{ $item->name }}
                                            </a>
                                            @if($item->description)
                                                <small class="text-muted d-block">
                                                    {{ Str::limit($item->description, 60) }}
                                                </small>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="align-middle text-uppercase">{{ $item->group_name ?? '-' }}</td>
                                <td class="align-middle">{{ $item->type == 1 ? 'Dịch vụ bao gồm' : 'Dịch vụ không bao gồm' }}</td>
                                <td class="align-middle">
                                    @if($item->type == 1)
                                        <span class="text-success font-weight-bold">Miễn phí</span>
                                    @else
                                        {{ Utilities::formatDisplayCurrency($item->price) }}
                                    @endif
                                </td>
                                @canany(['group-service-update', 'group-service-delete'])
                                    <td class="text-center align-middle">
                                        @can('group-service-update')
                                            <a href="{{ Utilities::getUrlWithGoBack(route(Utilities::getRouteName('backend.service.edit'), array_merge($routeParams, ['id' => $item->id]))) }}" class="btn btn-info btn-sm" title="Sửa">
                                                <i class="fas fa-pencil-alt"></i>
                                            </a>
                                        @endcan
                                        @can('group-service-delete')
                                            <a href="#" class="btn btn-danger btn-sm" title="Xóa" 
                                                   data-modal-delete="deleteModal"
                                                   data-id="{{ $item->id }}" 
                                                   data-name="{{ $item->name }}" 
                                                   data-ajax-url="{{ route(\Modules\BackEnd\Helpers\Utilities::getRouteName('backend.service.destroy'), $routeParams) }}">
                                                <i class="fas fa-trash-alt"></i>
                                            </a>
                                        @endcan
                                    </td>
                                @endcanany
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">Không có dữ liệu</td>
                            </tr>
                        @endforelse
                        
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            {!! $list->appends(Request::all())->links('backend::shared.pagination') !!}
        </div>
    </div>
@endsection
