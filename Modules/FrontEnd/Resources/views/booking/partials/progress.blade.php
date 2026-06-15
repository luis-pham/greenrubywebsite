<section class="booking-progress-bar booking-progress-section"
    data-step-1="{{ __('frontend::booking.progress_booking_info') }}"
    data-step-2="{{ __('frontend::booking.progress_guest_details') }}"
    data-step-3="{{ __('frontend::booking.progress_payment') }}"
    data-step-4="{{ __('frontend::booking.progress_complete') }}">
    <div class="booking-progress-track">
        <div class="booking-progress-track-desktop">
            <div class="progress-step-current" id="progress-step-booking-info">
                <div class="progress-step-icon-active">
                    <span class="progress-step-number">1</span>
                </div>
                <h3 class="booking-progress-title">{{ __('frontend::booking.progress_booking_info') }}</h3>
            </div>
            <div class="line-wrapper">
                <div class="booking-progress-divider" id="progress-divider-1"></div>
            </div>
            <div class="booking-step-separator-wrap">
                <div class="progress-step-group" id="progress-step-guest-details">
                    <div class="progress-step-icon">
                        <span class="progress-step-number">2</span>
                    </div>
                    <h3 class="booking-progress-label">{{ __('frontend::booking.progress_guest_details') }}</h3>
                </div>
            </div>
            <div class="booking-progress-divider-mid" id="progress-divider-mid"></div>
            <div class="booking-progress-payment">
                <div class="progress-step-group" id="progress-step-payment">
                    <div class="progress-step-icon">
                        <span class="progress-step-number">3</span>
                    </div>
                    <h3 class="booking-progress-label">{{ __('frontend::booking.progress_payment') }}</h3>
                </div>
            </div>
            <div class="line-container">
                <div class="booking-progress-divider" id="progress-divider-2"></div>
            </div>
            <div class="booking-progress-complete" id="progress-step-complete">
                <div class="progress-step-icon">
                    <span class="progress-step-number">4</span>
                </div>
                <h3 class="booking-progress-label">{{ __('frontend::booking.progress_complete') }}</h3>
            </div>
        </div>
        <div class="booking-progress-track-mobile" id="booking-progress-track-mobile">
            <div class="progress-step-icon-active">
                <span class="progress-step-number" id="progress-mobile-number">1</span>
            </div>
            <h3 class="booking-progress-title" id="progress-mobile-title">{{ __('frontend::booking.progress_booking_info') }}</h3>
        </div>
    </div>
</section>
