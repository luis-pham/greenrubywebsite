@extends('backend::layouts.master')

@section('content')
    @include('backend::shared.message')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h1 class="h4 m-0">{{ $title }}</h1>
            <a href="{{ route('backend.language.index', ['check' => 1]) }}" class="btn btn-sm btn-outline-secondary">Kiểm tra lại website</a>
        </div>
        <div class="card-body">
            <p class="text-muted">
                <strong>Tắt</strong>: không sửa được, không hiển thị. <strong>Đang biên tập</strong>: biên tập viên tạo, dịch và xem trước nội dung,
                khách không thấy gì. <strong>Công khai</strong>: hiển thị trên website khi mọi máy chủ website đã hỗ trợ ngôn ngữ đó.
                Ngôn ngữ của giao diện quản trị không đổi theo ngôn ngữ nội dung.
            </p>
            @if (!$migrated)
                <div class="alert alert-warning">Chưa chạy migration registry ngôn ngữ (2026_11_01_000001): chỉ EN/VI.</div>
            @endif
            @if ($holdBack)
                <div class="alert alert-info mb-3">Ngôn ngữ mới đang bị khóa công khai (I18N_ALLOW_PUBLIC_NEW_LOCALES tắt): chỉ chuyển được sang "Đang biên tập".</div>
            @endif
            <div class="table-responsive-sm">
                <table class="table table-striped table-data">
                    <thead>
                        <tr>
                            <th>Ngôn ngữ</th>
                            <th>Mã / URL</th>
                            <th>Trạng thái</th>
                            <th>Website{{ $fresh ? ' (vừa kiểm tra)' : '' }}</th>
                            <th>Bảng giá</th>
                            <th style="width: 34%;">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($languages as $row)
                            @php $l = $row->model; @endphp
                            <tr>
                                <td>
                                    <strong lang="{{ $l->code }}">{{ $l->native_name ?? $l->name }}</strong>
                                    <div class="small text-muted">{{ $l->english_name ?? $l->name }}</div>
                                </td>
                                <td>
                                    <code>{{ $l->code }}</code>
                                    <div class="small text-muted">{{ $l->url_prefix ? '/' . $l->url_prefix : '/' }} · {{ $row->legacy ? 'legacy' : 'intl' }}</div>
                                </td>
                                <td>
                                    @include('backend::partials.locale-status-badge', ['status' => $row->status])
                                    @if ($row->effectivePublic)
                                        <div class="small text-success">Đang hiển thị</div>
                                    @elseif ($row->status === 'public')
                                        <div class="small text-danger">Chưa hiển thị: website chưa sẵn sàng</div>
                                    @endif
                                </td>
                                <td class="small">
                                    @if ($row->legacy)
                                        Luôn hỗ trợ
                                    @elseif ($row->webSupports)
                                        Hỗ trợ: {{ implode(', ', $row->sections ?? []) ?: '—' }}
                                    @else
                                        <span class="text-muted">Chưa hỗ trợ</span>
                                    @endif
                                </td>
                                <td><code>{{ $row->priceList ?? '—' }}</code></td>
                                <td>
                                    {{ Form::open(['url' => route('backend.language.updateDisplay', ['id' => $l->id]), 'method' => 'post', 'class' => 'form-inline mb-2']) }}
                                        {{ Form::text('native_name', $l->native_name, ['class' => 'form-control form-control-sm mr-1 mb-1', 'placeholder' => 'Tên bản ngữ', 'maxlength' => 64]) }}
                                        {{ Form::text('english_name', $l->english_name, ['class' => 'form-control form-control-sm mr-1 mb-1', 'placeholder' => 'Tên tiếng Anh', 'maxlength' => 64]) }}
                                        {{ Form::button('Lưu tên', ['type' => 'submit', 'class' => 'btn btn-sm btn-outline-primary mb-1']) }}
                                    {{ Form::close() }}
                                    @if ($row->legacy)
                                        <div class="small text-muted">Ngôn ngữ gốc của website: luôn công khai.</div>
                                    @elseif ($row->transitions)
                                        {{ Form::open(['url' => route('backend.language.updateStatus', ['id' => $l->id]), 'method' => 'post']) }}
                                            <div class="form-row">
                                                <div class="col-4">{{ Form::select('status', array_combine($row->transitions, $row->transitions), null, ['class' => 'form-control form-control-sm']) }}</div>
                                                <div class="col-3">{{ Form::text('confirm_code', null, ['class' => 'form-control form-control-sm', 'placeholder' => 'gõ "' . $l->code . '"', 'required' => true]) }}</div>
                                                <div class="col-5">{{ Form::text('reason', null, ['class' => 'form-control form-control-sm', 'placeholder' => 'Lý do (≥ 10 ký tự)', 'minlength' => 10, 'required' => true]) }}</div>
                                            </div>
                                            {{ Form::button('Đổi trạng thái', ['type' => 'submit', 'class' => 'btn btn-sm btn-warning mt-1']) }}
                                        {{ Form::close() }}
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
