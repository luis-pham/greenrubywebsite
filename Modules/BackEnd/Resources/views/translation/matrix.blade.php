@extends('backend::layouts.master')

@section('content')
    @include('backend::shared.message')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h1 class="h4 m-0">{{ $title }}</h1>
            <div>
                <a href="{{ route('backend.translation.index', ['type' => $type, 'view' => 'pairs']) }}" class="btn btn-sm btn-outline-secondary">Ghép EN/VI (cũ)</a>
                <a href="{{ route('backend.language.index') }}" class="btn btn-sm btn-outline-secondary">Ngôn ngữ</a>
            </div>
        </div>
        <div class="card-body">
            <p class="text-muted">
                Mỗi dòng là một bản {{ strtoupper($source->code) }}; mỗi cột là một ngôn ngữ đang biên tập hoặc công khai.
                <strong>Xuất bản</strong>, <strong>bước duyệt</strong> và <strong>bản gốc đã đổi</strong> là ba việc riêng:
                bản đã xuất bản vẫn hiển thị khi bản gốc đổi, cho tới khi có người chủ động gỡ.
                "Tạo bản dịch" chỉ chép phần chữ (chưa xuất bản); giá, sức chứa, thông số và lịch khởi hành luôn lấy từ bản EN.
            </p>

            <ul class="nav nav-tabs mb-3">
                @foreach ($types as $key => $label)
                    <li class="nav-item">
                        <a class="nav-link{{ $key === $type ? ' active' : '' }}" href="{{ route('backend.translation.index', ['type' => $key]) }}">{{ $label }}</a>
                    </li>
                @endforeach
            </ul>

            <form method="get" action="{{ route('backend.translation.index') }}" class="form-inline mb-3">
                <input type="hidden" name="type" value="{{ $type }}">
                <select name="language" class="form-control form-control-sm mr-2">
                    <option value="">Mọi ngôn ngữ</option>
                    @foreach ($allLanguages as $l)
                        <option value="{{ $l->code }}"{{ ($filters['language'] ?? '') === $l->code ? ' selected' : '' }}>{{ $l->native_name ?? $l->name }}</option>
                    @endforeach
                </select>
                <select name="state" class="form-control form-control-sm mr-2">
                    <option value="">Mọi trạng thái</option>
                    @foreach (\App\Services\Content\TranslationMatrix::STATES as $state)
                        <option value="{{ $state }}"{{ ($filters['state'] ?? '') === $state ? ' selected' : '' }}>{{ $state }}</option>
                    @endforeach
                </select>
                <label class="mr-2"><input type="checkbox" name="outdated" value="1" class="mr-1"{{ !empty($filters['outdated']) ? ' checked' : '' }}>Chỉ bản gốc đã đổi</label>
                <button class="btn btn-sm btn-primary">Lọc</button>
            </form>

            <div class="small mb-2">
                @foreach ($languages as $l)
                    <span class="mr-3"><strong>{{ strtoupper($l->code) }}</strong>:
                        @foreach ($counts[$l->code] ?? [] as $state => $n) {{ $state }} {{ $n }}@if (!$loop->last), @endif @endforeach
                    </span>
                @endforeach
            </div>

            <div class="table-responsive-sm">
                <table class="table table-bordered table-sm table-data">
                    <thead>
                        <tr>
                            <th>Bản {{ strtoupper($source->code) }}</th>
                            @foreach ($languages as $l)
                                <th lang="{{ $l->code }}">{{ $l->native_name ?? $l->name }} @include('backend::partials.locale-status-badge', ['status' => \App\Services\Locale\LocaleRegistry::status($l)])</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($rows as $row)
                            <tr>
                                <td>
                                    #{{ $row->item->id }} {{ $row->item->name }}
                                    @if ($grouped)
                                        <div class="small"><a href="{{ route('backend.translation.history', ['type' => $type, 'id' => $row->item->id]) }}">Lịch sử liên kết</a>
                                            @if (!$row->linked)<span class="text-muted"> · chưa thuộc nhóm (ghép EN/VI trước)</span>@endif
                                        </div>
                                    @endif
                                </td>
                                @foreach ($languages as $l)
                                    @php $cell = $row->cells[$l->code]; @endphp
                                    <td>
                                        @include('backend::partials.translation-state', ['cell' => $cell])
                                        @if ($cell->id)
                                            <div class="small mt-1">
                                                #{{ $cell->id }} <span lang="{{ $l->code }}">{{ \Illuminate\Support\Str::limit($cell->name, 40) }}</span>
                                                @if ($edit = \Modules\BackEnd\Helpers\AdminLinks::edit($type, $cell->id, $l))
                                                    · <a href="{{ $edit }}">Sửa</a>
                                                @endif
                                            </div>
                                            @if ($cell->outdated)
                                                <form method="post" action="{{ route('backend.translation.confirmFresh') }}" class="d-inline">@csrf
                                                    <input type="hidden" name="type" value="{{ $type }}"><input type="hidden" name="id" value="{{ $cell->id }}">
                                                    <button class="btn btn-link btn-sm p-0">Đã đối chiếu bản gốc</button>
                                                </form>
                                            @endif
                                            @if ($grouped && !$cell->heuristic)
                                                <details class="small"><summary>Gỡ khỏi nhóm</summary>
                                                    <form method="post" action="{{ route('backend.translation.detach') }}">@csrf
                                                        <input type="hidden" name="type" value="{{ $type }}"><input type="hidden" name="id" value="{{ $cell->id }}">
                                                        <input name="reason" class="form-control form-control-sm my-1" placeholder="Lý do (≥ 10 ký tự)" minlength="10" required>
                                                        @if (\App\Services\Locale\LocaleRegistry::isLegacy($l))
                                                            <label><input type="checkbox" name="confirm_legacy" value="1" required> Tôi muốn tách bản {{ strtoupper($l->code) }} khỏi cặp EN/VI</label>
                                                        @endif
                                                        <button class="btn btn-sm btn-outline-danger">Gỡ (chỉ bản này)</button>
                                                    </form>
                                                </details>
                                            @endif
                                        @elseif (!\App\Services\Locale\LocaleRegistry::isLegacy($l))
                                            <form method="post" action="{{ route('backend.translation.create') }}" class="mt-1">@csrf
                                                <input type="hidden" name="type" value="{{ $type }}"><input type="hidden" name="source_id" value="{{ $row->item->id }}"><input type="hidden" name="language" value="{{ $l->code }}">
                                                <button class="btn btn-sm btn-outline-primary">Tạo bản {{ $l->native_name ?? $l->code }}</button>
                                            </form>
                                            @if ($grouped && $row->linked)
                                                <details class="small"><summary>Gắn bản có sẵn</summary>
                                                    <form method="post" action="{{ route('backend.translation.attach') }}">@csrf
                                                        <input type="hidden" name="type" value="{{ $type }}"><input type="hidden" name="source_id" value="{{ $row->item->id }}">
                                                        <select name="target_id" class="form-control form-control-sm my-1" required>
                                                            @foreach (\App\Services\Content\ContentTranslations::items($type, (int) $l->id) as $candidate)
                                                                @unless (\App\Services\Content\ContentTranslations::isLinked($type, $candidate->id))
                                                                    <option value="{{ $candidate->id }}">#{{ $candidate->id }} {{ $candidate->name }}</option>
                                                                @endunless
                                                            @endforeach
                                                        </select>
                                                        <input name="reason" class="form-control form-control-sm mb-1" placeholder="Lý do (≥ 10 ký tự)" minlength="10" required>
                                                        <button class="btn btn-sm btn-outline-secondary">Gắn</button>
                                                    </form>
                                                </details>
                                            @endif
                                        @else
                                            <div class="small text-muted">Ghép ở trang "Ghép EN/VI"</div>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @empty
                            <tr><td colspan="{{ count($languages) + 1 }}" class="text-center text-muted">Không có dữ liệu.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
