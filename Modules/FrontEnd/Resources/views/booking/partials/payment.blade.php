<div class="payment-column" id="payment-panel">
    <div class="payment-card">
        <div class="payment-header itinerary-step-header">
            <div class="step-header-content">
                <h3 class="step-section-title">{{ __('frontend::booking.payment_confirmation_method') }}</h3>
            </div>
        </div>
        <div class="payment-body">
            <div class="payment-row payment-row-top">
                <button type="button" class="payment-option payment-option-primary" data-confirmation="direct">
                    <div class="payment-option-icon">
                        <i class="fa-solid fa-credit-card"></i>
                    </div>
                    <div class="payment-option-text">
                        <span class="payment-option-title">{{ __('frontend::booking.payment_direct_title') }}</span>
                        <span class="payment-option-subtitle">{{ __('frontend::booking.payment_direct_subtitle') }}</span>
                    </div>
                </button>
                <button type="button" class="payment-option" data-confirmation="inquiry">
                    <div class="payment-option-icon">
                        <i class="fa-solid fa-paper-plane"></i>
                    </div>
                    <div class="payment-option-text">
                        <span class="payment-option-title">{{ __('frontend::booking.payment_inquiry_title') }}</span>
                        <span class="payment-option-subtitle">{{ __('frontend::booking.payment_inquiry_subtitle') }}</span>
                    </div>
                </button>
            </div>
        </div>
    </div>

    <div class="payment-methods-card">
        <div class="payment-body payment-body-methods" id="payment-body-methods">
            <p class="payment-currency-notice">{{ __('frontend::booking.payment_currency_notice') }}</p>
            <div class="payment-row">
                <button type="button" class="payment-option payment-option-primary" data-method="stripe">
                    <div class="payment-option-icon">
                        <i class="fa-brands fa-cc-mastercard"></i>
                    </div>
                    <div class="payment-option-text">
                        <span class="payment-option-title">{{ __('frontend::booking.payment_method_stripe') }}</span>
                    </div>
                </button>
                <button type="button" class="payment-option" data-method="paypal">
                    <div class="payment-option-icon">
                        <i class="fa-brands fa-paypal"></i>
                    </div>
                    <div class="payment-option-text">
                        <span class="payment-option-title">{{ __('frontend::booking.payment_method_paypal') }}</span>
                    </div>
                </button>
                <button type="button" class="payment-option" data-method="sepay">
                    <div class="payment-option-icon">
                        <i class="fa-solid fa-building-columns"></i>
                    </div>
                    <div class="payment-option-text">
                        <span class="payment-option-title">{{ __('frontend::booking.payment_method_sepay') }}</span>
                    </div>
                </button>
            </div>
        </div>
        <div class="payment-body payment-body-inquiry-message" id="payment-inquiry-message" style="display: none;">
            <p class="payment-inquiry-notice">{{ __('frontend::booking.payment_inquiry_continue_before') }}<strong>{{ __('frontend::booking.button_continue_voyage') }}</strong>{{ __('frontend::booking.payment_inquiry_continue_after') }}</p>
        </div>
    </div>

    <div class="guest-details-actions">
        <button type="button" class="guest-back-button" id="payment-back-button">
            <span class="guest-back-icon">&#8592;</span>
            <span class="guest-back-label">{{ __('frontend::booking.button_back') }}</span>
        </button>
        <button type="button" class="continue-voyage-button" id="payment-continue-button">
            <b class="sign-in16">{{ __('frontend::booking.button_continue_voyage') }}</b>
            <div class="calendar-check">
                <i class="fa-solid fa-arrow-right-long" style="color:#ffffff;font-size:16px;"></i>
            </div>
        </button>
    </div>
</div>

