<div class="modal fade" id="{{ $popupId ?? 'quoteSuccessModal' }}" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered quote-success-dialog" role="document">
        <div class="modal-content quote-success-content">
            <button type="button" class="quote-success-close" data-dismiss="modal" aria-label="Close">
                <i class="fa-solid fa-xmark"></i>
            </button>
            <div class="quote-success-icon">
                <i class="{{ $popupIcon ?? 'fa-solid fa-check' }}"></i>
            </div>
            <h4 class="quote-success-title">{{ $popupTitle ?? '' }}</h4>
            <p class="quote-success-description">{{ $popupDescription ?? '' }}</p>
        </div>
    </div>
</div>
