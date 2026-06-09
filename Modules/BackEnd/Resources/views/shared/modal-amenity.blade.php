@php
    $modalId = isset($modalId) ? $modalId : 'amenity-modal';
    $modalTitle = isset($modalTitle) ? $modalTitle : 'CHỌN TIỆN NGHI CAO CẤP';
    $modalSubtitle = isset($modalSubtitle) ? $modalSubtitle : __('backend::cabin.modal_amenity_subtitle');
    $searchInputId = isset($searchInputId) ? $searchInputId : 'amenity-search';
    $modalListId = isset($modalListId) ? $modalListId : 'amenity-modal-list';
    $listAmenity = isset($listAmenity) ? $listAmenity : [];
    $confirmButtonClass = isset($confirmButtonClass) ? $confirmButtonClass : 'btn-confirm-amenity-selection';
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
                        <input type="text" id="{{ $searchInputId }}" class="form-control" placeholder="{{ __('backend::cabin.modal_amenity_search_placeholder') }}" autocomplete="off">
                    </div>
                </div>
                <div class="row" id="{{ $modalListId }}">
                    @foreach ($listAmenity as $amenity)
                        @php
                            $iconUrl = $amenity->icon ? Utilities::getFileLink($amenity->icon) : null;
                        @endphp
                        <div class="col-md-6 mb-3 amenity-card-wrapper">
                            <div class="amenity-item border rounded p-3 h-100 cursor-pointer" data-id="{{ $amenity->id }}" data-name="{{ htmlspecialchars(html_entity_decode($amenity->name, ENT_QUOTES, 'UTF-8'), ENT_QUOTES, 'UTF-8') }}" data-icon="{{ htmlspecialchars($iconUrl ?? '', ENT_QUOTES, 'UTF-8') }}" style="transition: border-color 0.2s, background-color 0.2s;">
                                <div class="position-relative">
                                    <span class="amenity-item-check position-absolute" style="top: 0; right: 0; width: 22px; height: 22px; border-radius: 50%; background: #007bff; color: #fff; display: none; align-items: center; justify-content: center; font-size: 12px;"><i class="fas fa-check"></i></span>
                                    <div class="d-flex align-items-center mb-2">
                                        @if ($iconUrl)
                                            <div class="image-wrapper position-relative mr-2" style="width: 1.5rem; min-width: 1.5rem; height: 1.5rem; flex-shrink: 0;">
                                                <img src="{{ $iconUrl }}" alt="{{ $amenity->name }}" class="position-absolute w-100 h-100" style="object-fit: cover;">
                                            </div>
                                        @else
                                            <span class="text-muted mr-2 d-flex align-items-center justify-content-center" style="width: 1.5rem; min-width: 1.5rem;">—</span>
                                        @endif
                                        <strong class="text-uppercase small mb-0">{{ html_entity_decode($amenity->name, ENT_QUOTES, 'UTF-8') }}</strong>
                                    </div>
                                    @if (!empty($amenity->description))
                                        <p class="small text-muted mb-0" style="padding-left: 2rem;">"{{ $amenity->description }}"</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary {{ $confirmButtonClass }}" data-dismiss="modal">{{ __('backend::cabin.modal_confirm_selection') }} (<span class="amenity-selection-count">0</span>)</button>
            </div>
        </div>
    </div>
</div>
<style>
    .amenity-item.cursor-pointer { cursor: pointer; }
    .amenity-item.selected { border-color: #007bff !important; border-width: 2px !important; background-color: rgba(0,123,255,0.04) !important; }
    .amenity-item.selected .amenity-item-check { display: flex !important; }
    .amenity-item.selected .text-primary { color: #007bff !important; }
</style>
