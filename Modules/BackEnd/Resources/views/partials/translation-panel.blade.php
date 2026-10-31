{{-- Translation panel above a content edit form (phase 1E-c). $panel: Modules\BackEnd\Helpers\TranslationPanel::for(). --}}
@php
    $p = $panel;
    $workflowLabels = ['draft' => 'Nháp', 'translation_review' => 'Duyệt bản dịch', 'seo_review' => 'Duyệt SEO', 'legal_review' => 'Duyệt pháp lý', 'approved' => 'Đã duyệt'];
@endphp
<div class="card border-info mb-3" id="translation-panel" data-type="{{ $p['type'] }}">
    <div class="card-header py-2 d-flex flex-wrap align-items-center">
        <strong class="mr-2">{{ $p['label'] }} #{{ $p['row']->id }}</strong>
        <span class="mr-2" lang="{{ $p['language']->code }}">{{ $p['language']->native_name ?? $p['language']->name }}</span>
        @include('backend::partials.locale-status-badge', ['status' => \App\Services\Locale\LocaleRegistry::status($p['language'])])
        <span class="badge badge-{{ $p['published'] ? 'success' : 'dark' }} ml-2">{{ $p['published'] ? 'Đã xuất bản' : 'Chưa xuất bản' }}</span>
        @if ($p['workflow'])
            <span class="badge badge-info ml-1">{{ $workflowLabels[$p['workflow']] ?? $p['workflow'] }}</span>
        @endif
        @if ($p['freshness'] === 'outdated')
            <span class="badge badge-warning ml-1">Bản gốc đã đổi</span>
        @elseif ($p['freshness'] === 'current')
            <span class="badge badge-light ml-1">Khớp bản gốc</span>
        @endif
        @if ($p['pending'])
            <span class="badge badge-info ml-1" title="Người soạn: #{{ $p['pending']->updated_by ?? $p['pending']->created_by }}">Có bản sửa chờ duyệt (bản đang hiển thị chưa đổi)</span>
        @endif
        @foreach ($p['versions'] as $v)
            <span class="ml-2 small">
                @if ($v['edit'])<a href="{{ $v['edit'] }}" lang="{{ $v['language']->code }}">{{ $v['language']->native_name ?? $v['language']->code }} #{{ $v['id'] }}</a>
                @else<span class="text-muted">{{ $v['language']->native_name ?? $v['language']->code }}: chưa có</span>@endif
            </span>
        @endforeach
    </div>
    <div class="card-body py-2">
        @if ($p['source'])
            <details{{ $p['freshness'] === 'outdated' ? ' open' : '' }}>
                <summary>Bản gốc {{ strtoupper($p['sourceLanguage']->code ?? '') }} #{{ $p['source']->id }} (chỉ đọc, để dịch đối chiếu)</summary>
                <table class="table table-sm table-bordered mt-2 mb-2">
                    @foreach ($p['sourceText'] as $f)
                        @if (trim(strip_tags((string) $f['value'])) !== '')
                            <tr><th style="width: 18%"><code>{{ $f['field'] }}</code></th><td lang="{{ $p['sourceLanguage']->code ?? '' }}" style="white-space: pre-line">{{ \Illuminate\Support\Str::limit(trim(strip_tags((string) $f['value'])), 1200) }}</td></tr>
                        @endif
                    @endforeach
                </table>
                @if ($p['freshness'] === 'outdated')
                    <form method="post" action="{{ route('backend.translation.confirmFresh') }}" class="d-inline">@csrf
                        <input type="hidden" name="type" value="{{ $p['type'] }}"><input type="hidden" name="id" value="{{ $p['row']->id }}">
                        <button class="btn btn-sm btn-outline-warning">Đã cập nhật theo bản gốc hiện tại</button>
                    </form>
                @endif
            </details>
        @endif

        @if ($p['business'])
            <div class="alert alert-secondary py-2 my-2 small">
                <strong>Theo bản EN (chỉ đọc):</strong>
                @foreach ($p['business'] as $b) <code>{{ $b['field'] }}</code> = {{ $b['inherited'] ?? '—' }}@if (!$loop->last); @endif @endforeach.
                Giá, sức chứa, thông số, giờ và lịch khởi hành chỉ sửa ở bản EN; ngôn ngữ này không lưu được các trường đó.
            </div>
        @endif

        @if ($p['slug'] !== null)
            <div class="my-2">
                <strong>URL (slug):</strong> <code>{{ $p['slug'] !== '' ? $p['slug'] : '— chưa đặt —' }}</code>
                <span class="small text-muted">Đổi tên không đổi URL. Đổi slug là thao tác riêng; URL cũ chuyển hướng 308.</span>
                <details class="d-inline-block ml-2"><summary class="small">Đổi slug</summary>
                    <form method="post" action="{{ route('backend.slug.change') }}" class="form-inline mt-1">@csrf
                        <input type="hidden" name="type" value="{{ $p['type'] }}"><input type="hidden" name="id" value="{{ $p['row']->id }}">
                        <input name="slug" class="form-control form-control-sm mr-1" placeholder="vi-du-slug-ascii" pattern="[a-z0-9]+(-[a-z0-9]+)*" maxlength="160" required>
                        <input name="reason" class="form-control form-control-sm mr-1" placeholder="Lý do (≥ 10 ký tự)" minlength="10" required>
                        <button class="btn btn-sm btn-outline-primary">Đổi slug</button>
                    </form>
                </details>
                @if ($p['slugHistory']->isNotEmpty())
                    <div class="small text-muted">Slug cũ (đang chuyển hướng): @foreach ($p['slugHistory'] as $h) <code>{{ $h->old_slug }}</code>@if (!$loop->last), @endif @endforeach</div>
                @endif
            </div>
        @endif

        @if ($p['actions'])
            <div class="my-2">
                @foreach ($p['actions'] as $action)
                    <form method="post" action="{{ route('backend.workflow.transition') }}" class="d-inline-block mr-1">@csrf
                        <input type="hidden" name="type" value="{{ $p['type'] }}"><input type="hidden" name="id" value="{{ $p['row']->id }}"><input type="hidden" name="action" value="{{ $action['key'] }}">
                        <button class="btn btn-sm btn-{{ $action['style'] ?? 'outline-secondary' }}"@if (($action['allowed'] ?? true) === false) disabled title="{{ $action['reason'] }}"@endif>{{ $action['label'] }}</button>
                    </form>
                @endforeach
                @if (\Illuminate\Support\Facades\Route::has('backend.preview.start') && in_array($p['type'], \App\Services\Preview\Preview::TYPES, true))
                    <form method="post" action="{{ route('backend.preview.start') }}" class="d-inline-block" target="_blank">@csrf
                        <input type="hidden" name="type" value="{{ $p['type'] }}"><input type="hidden" name="id" value="{{ $p['row']->id }}">
                        <button class="btn btn-sm btn-outline-dark">Xem trước</button>
                    </form>
                @endif
            </div>
        @endif
    </div>
</div>
@if ($p['business'])
    <script>
        // Read-only business fields on a text-only language (also refused server-side: BusinessFieldsObserver).
        document.addEventListener('DOMContentLoaded', function () {
            var fields = @json($p['businessFields']);
            fields.forEach(function (name) {
                document.querySelectorAll('[name="' + name + '"]').forEach(function (el) { el.readOnly = true; el.classList.add('bg-light'); el.title = 'Theo bản EN'; });
            });
            document.querySelectorAll('[name^="price["]').forEach(function (el) { el.disabled = true; el.title = 'Giá theo bản EN'; });
            document.querySelectorAll('[data-business-inventory]').forEach(function (el) { el.style.display = 'none'; });
        });
        // The price table is rebuilt after load: never submit prices from this language (they are the EN ones).
        document.addEventListener('submit', function (e) {
            e.target.querySelectorAll('[name^="price["]').forEach(function (el) { el.disabled = true; });
        }, true);
    </script>
@endif
