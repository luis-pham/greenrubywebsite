@extends('backend::layouts.master')

@php
    $statusClass = ['draft' => 'secondary', 'published' => 'success', 'closed' => 'dark'];
    $siteUrl = rtrim(config('app.url'), '/');
@endphp

@section('content')
    @include('backend::shared.message')
    @include('backend::shared.modal-delete', [
        'modalId' => 'deleteModal',
        'title' => 'Xóa vị trí tuyển dụng này?',
        'message' => 'Vị trí <strong id="delete-item-name"></strong> sẽ bị xóa khỏi website (đường dẫn trả về 404). Muốn giữ trang và chỉ ngừng nhận hồ sơ, hãy dùng "Đóng tuyển".'
    ])
    <div class="card">
        <div class="card-header">
            <div class="row justify-content-between">
                <div class="col-md-6">
                    <h1 class="h4 m-0">{{ $title }}</h1>
                    <small class="text-muted">Trang công khai: <a href="{{ $siteUrl }}/tuyen-dung" target="_blank" rel="noopener">{{ $siteUrl }}/tuyen-dung</a></small>
                </div>
                <div class="col-md-6 d-flex justify-content-end align-items-start">
                    @can('job-create')
                        <a href="{{ Utilities::getUrlWithGoBack(route('backend.job.create')) }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Thêm vị trí
                        </a>
                    @endcan
                </div>
            </div>
        </div>
        <div class="card-body">
            {{ Form::open(['method' => 'get', 'class' => 'form-search mb-2']) }}
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>TÌM KIẾM</label>
                        {{ Form::text('keyword', Request::get('keyword'), ['class' => 'form-control', 'placeholder' => 'Tên vị trí, bộ phận hoặc slug...', 'autocomplete' => 'off']) }}
                    </div>
                    <div class="form-group col-md-3">
                        <label>TRẠNG THÁI</label>
                        {{ Form::select('status', config('backend.jobStatus'), Request::get('status'), ['class' => 'form-control', 'placeholder' => 'Tất cả']) }}
                    </div>
                    <div class="form-group col-md-3">
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
                            <th>VỊ TRÍ</th>
                            <th>BỘ PHẬN</th>
                            <th>HẠN NỘP</th>
                            <th>TRẠNG THÁI</th>
                            @canany(['job-update', 'job-delete'])
                                <th class="text-center" style="width: 170px;">THAO TÁC</th>
                            @endcanany
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($list as $i => $item)
                            @php $expired = \Modules\BackEnd\Services\AppJobService::isExpired($item); @endphp
                            <tr data-id="{{ $item->id }}">
                                <td class="text-center align-middle">{{ $i + 1 }}</td>
                                <td class="align-middle">
                                    <span class="d-block">{{ $item->title }}</span>
                                    @if ($item->status === 'draft')
                                        <small class="text-muted">/tuyen-dung/{{ $item->slug }}</small>
                                    @else
                                        <small><a href="{{ $siteUrl }}/tuyen-dung/{{ $item->slug }}" target="_blank" rel="noopener">/tuyen-dung/{{ $item->slug }}</a></small>
                                    @endif
                                </td>
                                <td class="align-middle">{{ $item->department }}</td>
                                <td class="align-middle">{{ $item->deadline ? \Carbon\Carbon::parse($item->deadline)->format('d/m/Y') : '—' }}</td>
                                <td class="align-middle">
                                    <span class="badge badge-{{ $statusClass[$item->status] ?? 'secondary' }}">{{ config('backend.jobStatus')[$item->status] ?? $item->status }}</span>
                                    @if ($expired && $item->status === 'published')
                                        <span class="badge badge-warning" title="Đã qua hạn nộp: không còn hiển thị trong danh sách">Quá hạn</span>
                                    @endif
                                </td>
                                @canany(['job-update', 'job-delete'])
                                    <td class="text-center align-middle text-nowrap">
                                        @can('job-update')
                                            <a href="{{ Utilities::getUrlWithGoBack(route('backend.job.edit', ['id' => $item->id])) }}" class="btn btn-info btn-sm" title="Sửa">
                                                <i class="fas fa-pencil-alt"></i>
                                            </a>
                                            @if ($item->status === 'published' && !$expired)
                                                {{ Form::open(['route' => 'backend.job.status', 'class' => 'd-inline']) }}
                                                    {{ Form::hidden('id', $item->id) }}
                                                    {{ Form::hidden('status', 'closed') }}
                                                    <button type="submit" class="btn btn-outline-dark btn-sm" title="Ngừng nhận hồ sơ, giữ trang với thông báo đã đóng">Đóng tuyển</button>
                                                {{ Form::close() }}
                                            @elseif ($item->status === 'closed')
                                                {{ Form::open(['route' => 'backend.job.status', 'class' => 'd-inline']) }}
                                                    {{ Form::hidden('id', $item->id) }}
                                                    {{ Form::hidden('status', 'published') }}
                                                    <button type="submit" class="btn btn-outline-success btn-sm" title="Hiển thị lại trong danh sách đang tuyển">Mở lại</button>
                                                {{ Form::close() }}
                                            @endif
                                        @endcan
                                        @can('job-delete')
                                            <a href="#" class="btn btn-danger btn-sm" title="Xóa"
                                                data-modal-delete="deleteModal"
                                                data-id="{{ $item->id }}"
                                                data-name="{{ $item->title }}"
                                                data-ajax-url="{{ route('backend.job.destroy') }}">
                                                <i class="fas fa-trash-alt"></i>
                                            </a>
                                        @endcan
                                    </td>
                                @endcanany
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">Chưa có vị trí tuyển dụng nào</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    @can('job-update')
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
                        url: '{{ route('backend.job.orderUpdate') }}',
                        type: 'POST',
                        contentType: 'application/json',
                        data: JSON.stringify({id: listId}),
                    });
                }
            });
        </script>
    @endcan
@endsection
