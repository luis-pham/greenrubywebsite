@extends('backend::layouts.master')

@section('content')
    @include('backend::shared.modal-delete', [
           'modalId' => 'deleteModal',
           'title' => 'Xóa hoạt động này?',
           'message' => 'Hoạt động <strong id="delete-item-name"></strong> sẽ bị gỡ khỏi danh sách hiển thị.'
       ])
    @include('backend::shared.message')
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
                    @can('group-exp-activity-create')
                        <a href="{{ Utilities::getUrlWithGoBack(route(Utilities::getRouteName('backend.exp-activity.create'), $routeParams)) }}" class="btn btn-primary btn-sm">
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
                        <label>TÌM THEO TÊN HOẠT ĐỘNG</label>
                        {{ Form::text('keyword', Request::get('keyword'), ['class' => 'form-control', 'placeholder' => 'Nhập tên hoạt động...', 'autocomplete' => 'off']) }}
                    </div>
                    <div class="form-group col-md-5">
                        <label>LOẠI HOẠT ĐỘNG</label>
                        {{ Form::select('group_id', $listGroup, Request::get('group_id'), ['class' => 'form-control', 'placeholder' => 'Tất cả loại hoạt động', 'autocomplete' => 'off']) }}
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
                            <th style="width: 200px;">TÊN HOẠT ĐỘNG</th>
                            <th class="" style="width: 250px;">GIỚI THIỆU</th>
                            <th style="width: 200px;">LOẠI HOẠT ĐỘNG</th>
                            <th style="width: 150px;">THỜI LƯỢNG</th>
                            <th style="width: 200px;">THỜI GIAN DIỄN RA</th>
                            @canany(['group-exp-activity-update', 'group-exp-activity-delete'])
                                <th class="text-center" style="width: 110px;">THAO TÁC</th>
                            @endcanany  
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($list as $i => $item)
                            <tr>
                                <td class="text-center align-middle">{{ $i + $list->firstItem() }}</td>
                                <td class="d-flex align-items-center">
                                    @if($item->image_link)
                                        <img src="{{ Utilities::getFileLink($item->image_link) }}" alt="{{ $item->name }}" class="img-thumbnail" style="width: 50px; height: 50px; object-fit: cover; display: block;">
                                    @endif
                                    <a class="ml-2" href="{{ Utilities::getUrlWithGoBack(route(Utilities::getRouteName('backend.exp-activity.show'), array_merge($routeParams, ['id' => $item->id]))) }}">
                                        {{ $item->name }}
                                    </a>
                                </td>
                                <td class="align-middle">
                                    @if($item->summary)
                                        {{ Str::limit($item->summary, 80) }}
                                    @endif
                                </td>
                                <td class="align-middle">{{ $item->group_name ?? '-' }}</td>
                                <td class="text-center align-middle">
                                    @if($item->duration)
                                        {{ $item->duration }} phút
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="align-middle">
                                    @if($item->start_time && $item->end_time)
                                        {{ Utilities::formatDisplayTime($item->start_time) }} - {{ Utilities::formatDisplayTime($item->end_time) }}
                                    @else
                                        -
                                    @endif
                                </td>
                                @canany(['group-exp-activity-update', 'group-exp-activity-delete'])
                                    <td class="text-center align-middle">
                                        @can('group-exp-activity-update')
                                            <a href="{{ Utilities::getUrlWithGoBack(route(Utilities::getRouteName('backend.exp-activity.edit'), array_merge($routeParams, ['id' => $item->id]))) }}" class="btn btn-info btn-sm" title="Sửa">
                                                <i class="fas fa-pencil-alt"></i>
                                            </a>
                                        @endcan
                                        @can('group-exp-activity-delete')
                                            <a href="#" class="btn btn-danger btn-sm" title="Xóa" 
                                                   data-modal-delete="deleteModal"
                                                   data-id="{{ $item->id }}" 
                                                   data-name="{{ $item->name }}" 
                                                   data-ajax-url="{{ route(\Modules\BackEnd\Helpers\Utilities::getRouteName('backend.exp-activity.destroy'), $routeParams) }}">
                                                <i class="fas fa-trash-alt"></i>
                                            </a>
                                        @endcan
                                    </td>
                                @endcanany
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted">Không tìm thấy hoạt động nào</td>
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