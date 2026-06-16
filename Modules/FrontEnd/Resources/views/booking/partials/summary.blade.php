<section class="summary-panel reservation-summary-section">
    <div class="summary-panel-inner">
    <div class="summary-panel-title">
        <b class="summary-tagline">{{ __('frontend::booking.summary_tagline') }}</b>
        <h3 class="summary-heading">{{ __('frontend::booking.summary_heading') }}</h3>
    </div>
    <div class="summary-voyage-card-wrap">
        <div class="summary-voyage-card">
            <img
                class="summary-voyage-image"
                loading="lazy"
                alt=""
                src="{{ asset('/assets/frontend/images/modules/booking/ticket-svg@2x.png') }}"
            >
            <div class="frame-33877-column-1">
                <b class="summary-voyage-title" id="summary-voyage-title">—</b>
                <div class="green-ruby-12" id="summary-voyage-cruise">—</div>
            </div>
        </div>
    </div>
    <div class="summary-meta-row">
        <div class="label-check-in-container">
            <b class="label-check">{{ __('frontend::booking.summary_departure_date') }}</b>
            <div class="summary-date-value-wrap">
                <div class="mmddyyyy-wrapper">
                    <div class="summary-date-text" id="summary-date-display">{{ __('frontend::booking.step1_date_format') }}</div>
                </div>
            </div>
        </div>
        <div class="label-check-in-parent2">
            <b class="label-check">{{ __('frontend::booking.summary_global_guests') }}</b>
            <div class="summary-guests-input-wrap">
                <input
                    class="summary-guests-input"
                    placeholder="{{ __('frontend::booking.summary_global_guests_placeholder') }}"
                    type="text"
                    readonly
                    tabindex="-1"
                >
            </div>
        </div>
    </div>
    <div class="label-check-in-parent3" id="summary-cabins-block" style="display: none;">
        <b class="label-check">{{ __('frontend::booking.summary_in_voyage_cabins') }}</b>
        <div class="summary-cabins-wrap" id="summary-cabins-list">
        </div>
    </div>
    <div class="label-check-in-parent3" id="summary-amenities-block" style="display: none;">
        <b class="label-check">{{ __('frontend::booking.summary_exclusive_amenities') }}</b>
        <div class="summary-amenities-wrap">
            <div class="summary-amenities-list"></div>
        </div>
    </div>
    <div class="label-check-in-parent5">
        <b class="label-check">{{ __('frontend::booking.summary_booking_policies') }}</b>
        <i class="label-check-container">
            <ul class="cancellation-7-days-before-dep">
                <li>{{ __('frontend::booking.summary_policy_7_days') }}</li>
                <li>{{ __('frontend::booking.summary_policy_3_7_days') }}</li>
                <li>{{ __('frontend::booking.summary_policy_under_3_days') }}</li>
            </ul>
        </i>
    </div>
    <button class="estimated-total-button">
        <div class="arrow-right-long4">
            <div class="icon-glyph-light"></div>
        </div>
        <b class="sign-in17">{{ __('frontend::booking.summary_estimated_total') }}</b>
        <b class="total-amount">$0</b>
    </button>
    </div>
</section>

