@php
    $modalId = isset($modalId) ? $modalId : 'audience-modal';
    $modalTitle = isset($modalTitle) ? $modalTitle : 'CHỌN ĐỐI TƯỢNG PHÙ HỢP';
    $modalSubtitle = isset($modalSubtitle) ? $modalSubtitle : __('backend::cabin.modal_audience_subtitle');
    $searchInputId = isset($searchInputId) ? $searchInputId : 'audience-search';
    $modalListId = isset($modalListId) ? $modalListId : 'audience-modal-list';
    $listAudience = isset($listAudience) ? $listAudience : [];
    $confirmButtonClass = isset($confirmButtonClass) ? $confirmButtonClass : 'btn-confirm-audience-selection';
@endphp

<div class="modal fade" id="{{ $modalId }}" tabindex="-1" role="dialog" aria-hidden="true" style="display: none;">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title text-uppercase font-weight-bold mb-1">{{ $modalTitle }}</h5>
                    <small class="text-muted d-block">{{ $modalSubtitle }}</small>
                </div>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group mb-3">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                        </div>
                        <input type="text" id="{{ $searchInputId }}" class="form-control" placeholder="{{ __('backend::cabin.modal_audience_search_placeholder') }}" autocomplete="off">
                    </div>
                </div>
                <div class="row" id="{{ $modalListId }}">
                    @foreach ($listAudience as $audience)
                        @php
                            $icon = null;
                            if ($audience->description) {
                                $descData = json_decode($audience->description, true);
                                $icon = $descData['icon'] ?? null;
                            }
                        @endphp
                        <div class="col-md-6 mb-3 audience-card-wrapper">
                            <div class="audience-item border rounded p-3 h-100 cursor-pointer" 
                                 data-id="{{ $audience->id }}" 
                                 data-name="{{ htmlspecialchars(html_entity_decode($audience->name, ENT_QUOTES, 'UTF-8'), ENT_QUOTES, 'UTF-8') }}" 
                                 data-icon="{{ htmlspecialchars($icon ?? '', ENT_QUOTES, 'UTF-8') }}" 
                                 style="transition: border-color 0.2s, background-color 0.2s;">
                                <div class="position-relative">
                                    <span class="audience-item-check position-absolute" style="top: 0; right: 0; width: 22px; height: 22px; border-radius: 50%; background: #007bff; color: #fff; display: none; align-items: center; justify-content: center; font-size: 12px;"><i class="fas fa-check"></i></span>
                                    <div class="d-flex align-items-center mb-2">
                                        @if ($icon)
                                            <i class="{{ $icon }} text-primary mr-2 d-flex align-items-center justify-content-center" style="font-size: 1.25rem; width: 1.5rem; min-width: 1.5rem; flex-shrink: 0; line-height: 1;"></i>
                                        @else
                                            <span class="text-muted mr-2 d-flex align-items-center justify-content-center" style="width: 1.5rem; min-width: 1.5rem;">—</span>
                                        @endif
                                        <strong class="text-uppercase small mb-0">{{ html_entity_decode($audience->name, ENT_QUOTES, 'UTF-8') }}</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary {{ $confirmButtonClass }}" data-dismiss="modal">{{ __('backend::cabin.modal_confirm_selection') }} (<span class="audience-selection-count">0</span>)</button>
            </div>
        </div>
    </div>
</div>
<style>
    .audience-item.cursor-pointer { cursor: pointer; }
    .audience-item.selected { border-color: #007bff !important; border-width: 2px !important; background-color: rgba(0,123,255,0.04) !important; }
    .audience-item.selected .audience-item-check { display: flex !important; }
    .audience-item.selected .text-primary { color: #007bff !important; }
</style>