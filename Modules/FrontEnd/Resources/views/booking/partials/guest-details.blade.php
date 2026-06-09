<div class="guest-details-column" id="guest-details-panel">
    <div class="guest-details-card">
        <div class="guest-details-header itinerary-step-header">
            <div class="step-header-content">
                <h3 class="step-section-title">{{ __('frontend::booking.guest_details_title') }}</h3>
            </div>
        </div>
        <div class="guest-details-body">
            <div class="guest-details-row">
                <div class="guest-details-field">
                    <label class="label-check">
                        {{ __('frontend::booking.guest_full_name') }}
                        <span class="field-required">*</span>
                    </label>
                    <input type="text" id="guest-full-name" class="guest-input" maxlength="100" placeholder="{{ __('frontend::booking.guest_input_placeholder') }}">
                    <div class="booking-field-error" id="booking-error-fullName" role="alert"></div>
                </div>
                <div class="guest-details-field">
                    <label class="label-check">
                        {{ __('frontend::booking.guest_phone_number') }}
                        <span class="field-required">*</span>
                    </label>
                    <input type="text" id="guest-phone" class="guest-input" maxlength="20" placeholder="{{ __('frontend::booking.guest_input_placeholder') }}">
                    <div class="booking-field-error" id="booking-error-phone" role="alert"></div>
                </div>
            </div>
            <div class="guest-details-row">
                <div class="guest-details-field guest-details-field-full">
                    <label class="label-check">
                        {{ __('frontend::booking.guest_email_address') }}
                        <span class="field-required">*</span>
                    </label>
                    <input type="email" id="guest-email" class="guest-input" maxlength="100" placeholder="{{ __('frontend::booking.guest_input_placeholder') }}">
                    <div class="booking-field-error" id="booking-error-email" role="alert"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="guest-details-actions">
        <button type="button" class="guest-back-button" id="guest-back-button">
            <div class="calendar-check">
                <i class="fa-solid fa-arrow-left-long" style="color:#ffffff;font-size:16px;"></i>
            </div>
            <span class="guest-back-label">{{ __('frontend::booking.button_back') }}</span>
        </button>
        <button type="button" class="continue-voyage-button" id="guest-continue-button">
            <b class="sign-in16">{{ __('frontend::booking.button_continue_voyage') }}</b>
            <div class="calendar-check">
                <i class="fa-solid fa-arrow-right-long" style="color:#ffffff;font-size:16px;"></i>
            </div>
        </button>
    </div>
</div>

