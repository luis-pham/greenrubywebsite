@extends('backend::layouts.master')

@section('content')
    @include('backend::shared.message')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h1 class="h4 m-0">{{ $title }}: {{ $type }} #{{ $id }}</h1>
            <a href="{{ route('backend.translation.index', ['type' => $type]) }}" class="btn btn-sm btn-outline-secondary">Ma trận bản dịch</a>
        </div>
        <div class="card-body">
            <p class="text-muted">Mọi thay đổi liên kết của nhóm này. "Hoàn tác" chỉ đảo đúng một mục, và chỉ khi bản đó chưa bị đổi lại sau đó.</p>
            <table class="table table-sm table-striped">
                <thead><tr><th>#</th><th>Thời gian</th><th>Thao tác</th><th>Bản</th><th>Ngôn ngữ</th><th>Người làm</th><th>Lý do</th><th></th></tr></thead>
                <tbody>
                    @forelse ($entries as $e)
                        <tr>
                            <td>{{ $e->id }}</td>
                            <td>{{ $e->created_at }}</td>
                            <td><code>{{ $e->action }}</code></td>
                            <td>#{{ $e->entity_id }}</td>
                            <td>{{ $languages[$e->language_id]->code ?? $e->language_id }}</td>
                            <td>{{ $e->actor ?? 'CLI' }}</td>
                            <td>{{ $e->reason }}</td>
                            <td>
                                @if ($e->action !== 'restore')
                                    <form method="post" action="{{ route('backend.translation.undo') }}" class="form-inline">@csrf
                                        <input type="hidden" name="type" value="{{ $type }}"><input type="hidden" name="id" value="{{ $id }}"><input type="hidden" name="log_id" value="{{ $e->id }}">
                                        <input name="reason" class="form-control form-control-sm mr-1" placeholder="Lý do (≥ 10 ký tự)" minlength="10" required>
                                        <button class="btn btn-sm btn-outline-warning">Hoàn tác</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted">Chưa có thay đổi nào được ghi.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
