{{-- One translation cell (phase 1E-b): publication / workflow state, freshness overlay, pending revision. --}}
@php
    $stateLabels = [
        'missing' => ['Chưa có', 'light'], 'draft' => ['Nháp', 'secondary'], 'translation_review' => ['Duyệt bản dịch', 'info'],
        'seo_review' => ['Duyệt SEO', 'info'], 'legal_review' => ['Duyệt pháp lý', 'info'], 'approved' => ['Đã duyệt', 'primary'],
        'published' => ['Đã xuất bản', 'success'], 'unpublished' => ['Chưa xuất bản', 'dark'],
    ];
    [$stateLabel, $stateColor] = $stateLabels[$cell->state] ?? [$cell->state, 'light'];
@endphp
<span class="badge badge-{{ $stateColor }}" title="{{ $cell->state }}">{{ $stateLabel }}</span>
@if ($cell->published && $cell->workflow && $cell->workflow !== 'approved')
    <span class="badge badge-outline-info" title="Bản đang hiển thị; bản sửa đang ở bước {{ $cell->workflow }}">{{ $stateLabels[$cell->workflow][0] ?? $cell->workflow }}</span>
@endif
@if ($cell->outdated)
    <span class="badge badge-warning" title="Bản gốc đã đổi sau lần dịch/đối chiếu gần nhất">Bản gốc đã đổi</span>
@endif
@if ($cell->pending ?? 0)
    <span class="badge badge-info" title="Có bản sửa chờ duyệt (chưa thay bản đang hiển thị)">{{ $cell->pending }} bản sửa chờ duyệt</span>
@endif
@if ($cell->heuristic)
    <span class="badge badge-secondary" title="Chưa ghép rõ ràng: hệ thống tự đoán">tự động</span>
@endif
