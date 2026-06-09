@php
    $cancelUrl = isset($cancelUrl) ? $cancelUrl : route('backend.cabin.index');
    $cancelLabel = isset($cancelLabel) ? $cancelLabel : 'Hủy bỏ';
    $cancelIcon = isset($cancelIcon) ? $cancelIcon : 'fas fa-arrow-left';
    $submitFormId = isset($submitFormId) ? $submitFormId : 'cabin-form';
    $submitLabel = isset($submitLabel) ? $submitLabel : 'Lưu thông tin';
    $submitIcon = isset($submitIcon) ? $submitIcon : 'fas fa-save';
    $showCancel = isset($showCancel) ? $showCancel : true;
    $showSubmit = isset($showSubmit) ? $showSubmit : true;
@endphp

<div class="page-header-actions">
    @if ($showCancel)
        <a href="{{ Utilities::getGoBackUrl($cancelUrl) }}" class="btn btn-light" title="{{ $cancelLabel }}">
            <i class="{{ $cancelIcon }}"></i><span class="btn-label d-none d-md-inline"> {{ $cancelLabel }}</span>
        </a>
    @endif
    @if ($showSubmit)
        <button type="submit" form="{{ $submitFormId }}" class="btn btn-primary" title="{{ $submitLabel }}">
            <i class="{{ $submitIcon }}"></i><span class="btn-label d-none d-md-inline"> {{ $submitLabel }}</span>
        </button>
    @endif
</div>
