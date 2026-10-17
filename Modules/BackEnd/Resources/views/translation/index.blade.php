@extends('backend::layouts.master')

@section('content')
    @include('backend::shared.message')
    <div class="card">
        <div class="card-header">
            <h1 class="h4 m-0">{{ $title }}</h1>
        </div>
        <div class="card-body">
            <p class="text-muted">
                Ghép mỗi bản {{ strtoupper($source->code) }} với bản {{ strtoupper($target->code) }} tương ứng. Cặp đã lưu được dùng cho
                hreflang, sitemap và nút đổi ngôn ngữ. <strong>Tự động</strong> = hệ thống tự đoán (như trước đây);
                <strong>Không có bản dịch</strong> = trang chỉ có ở {{ strtoupper($source->code) }}.
            </p>

            <ul class="nav nav-tabs mb-3">
                @foreach ($types as $key => $label)
                    <li class="nav-item">
                        <a class="nav-link{{ $key === $type ? ' active' : '' }}" href="{{ route('backend.translation.index', ['type' => $key]) }}">{{ $label }}</a>
                    </li>
                @endforeach
            </ul>

            {{ Form::open(['url' => route('backend.translation.update'), 'method' => 'post']) }}
                {{ Form::hidden('type', $type) }}
                <div class="table-responsive-sm">
                    <table class="table table-striped table-data">
                        <thead>
                            <tr>
                                <th>Bản {{ strtoupper($source->code) }}</th>
                                <th style="width: 30%;">Đang hiển thị trên web</th>
                                <th style="width: 35%;">Bản {{ strtoupper($target->code) }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($rows as $row)
                                <tr>
                                    <td>#{{ $row->item->id }} {{ $row->item->name }}</td>
                                    <td>
                                        @if ($row->effective)
                                            #{{ $row->effective->id }} {{ $row->effective->name }}
                                        @else
                                            <span class="text-danger">Không có bản {{ strtoupper($target->code) }}</span>
                                        @endif
                                        @if (!$row->linked)
                                            <span class="badge badge-secondary">tự động</span>
                                        @endif
                                    </td>
                                    <td>
                                        <select name="pair[{{ $row->item->id }}]" class="form-control">
                                            <option value=""{{ $row->selected === '' ? ' selected' : '' }}>Tự động</option>
                                            <option value="none"{{ $row->selected === 'none' ? ' selected' : '' }}>Không có bản dịch</option>
                                            @foreach ($targetItems as $targetItem)
                                                <option value="{{ $targetItem->id }}"{{ $row->selected === $targetItem->id ? ' selected' : '' }}>#{{ $targetItem->id }} {{ $targetItem->name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center text-muted">Chưa có dữ liệu.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if (count($rows) > 0)
                    {{ Form::button('<i class="fas fa-save"></i> Lưu', ['type' => 'submit', 'class' => 'btn btn-success']) }}
                @endif
            {{ Form::close() }}

            @if ($unpairedTargets->isNotEmpty())
                <h2 class="h5 mt-4">Bản {{ strtoupper($target->code) }} chưa có bản {{ strtoupper($source->code) }}</h2>
                <ul class="mb-0">
                    @foreach ($unpairedTargets as $targetItem)
                        <li>#{{ $targetItem->id }} {{ $targetItem->name }}</li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
@endsection
