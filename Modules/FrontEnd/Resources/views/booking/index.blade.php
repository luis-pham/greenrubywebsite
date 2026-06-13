@extends('frontend::layouts.master')

@section('content')
    <section class="section-cover section-booking-cover position-relative">
        <div class="container-fluid px-0">
            <div class="page-cover">
                <div class="image-wrapper position-relative">
                    <img src="{{ asset('/assets/frontend/images/booking-banner.svg') }}" alt="Booking banner" class="position-absolute w-100 h-100" />
                </div>
                <div class="container position-absolute">
                    <div class="main-info mx-auto text-white text-center">
                        <p class="title font-heading font-weight-bold">
                            {{ __('frontend::booking.step1_title') }}
                        </p>
                        <p class="description mb-4">
                            {{ __('frontend::booking.step1_subtitle') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @php
        $bookingLanguageCode = $languageCode ?? null;
        $bookingItineraryShowTemplate = null;

        if ($bookingLanguageCode === 'vi') {
            $bookingItineraryShowTemplate = route(
                \Modules\BackEnd\Helpers\Utilities::bindRouteNameMultiLanguage('frontend.itinerary.show'),
                [
                    'languageCode' => $bookingLanguageCode,
                    'slug' => '__SLUG__',
                    'cruise_id' => '__CRUISE__',
                    'itinerary_id' => '__ITINERARY__',
                ]
            );
        } else {
            $bookingItineraryShowTemplate = route('frontend.itinerary.show', [
                'slug' => '__SLUG__',
                'cruise_id' => '__CRUISE__',
                'itinerary_id' => '__ITINERARY__',
            ]);
        }
    @endphp

    <div
        id="booking-page"
        class="booking-page-shell booking-page"
        data-init-result="{{ request()->has('result') && request()->has('tx') ? '1' : '0' }}"
        data-lang="{{ $languageCode ?? 'all' }}"
        data-itinerary-url-template="{{ $bookingItineraryShowTemplate }}"
        data-api-itineraries="{{ url('/api/booking/itineraries') }}"
        data-api-itinerary-detail="{{ url('/api/booking/itinerary') }}"
        data-api-cabins="{{ url('/api/booking/cabins') }}"
        data-api-amenities="{{ url('/api/booking/amenities') }}"
        data-api-departure-dates="{{ url('/api/booking/departure-dates') }}"
        data-init-cabin-id="{{ request('cabin_id') }}"
        data-init-cruise-id="{{ request('cruise_id') }}"
        data-init-itinerary-id="{{ request('itinerary_id') }}"
        data-msg-voyage-required="{{ __('frontend::booking.validation_voyage_required') }}"
        data-msg-cabin-required="{{ __('frontend::booking.validation_cabin_required') }}"
        data-msg-guest-per-cabin="{{ __('frontend::booking.validation_guest_per_cabin') }}"
        data-msg-guest-full-name-required="{{ __('frontend::booking.validation_guest_full_name_required') }}"
        data-msg-guest-phone-required="{{ __('frontend::booking.validation_guest_phone_required') }}"
        data-msg-guest-phone-numeric="{{ __('frontend::booking.validation_guest_phone_numeric') }}"
        data-msg-guest-email-required="{{ __('frontend::booking.validation_guest_email_required') }}"
        data-msg-guest-email-invalid="{{ __('frontend::booking.validation_guest_email_invalid') }}"
        data-msg-date-required="{{ __('frontend::booking.validation_departure_date_required') }}"
        data-currency-mismatch-usd-en="{{ \Illuminate\Support\Facades\Lang::get('frontend::booking.payment_currency_mismatch_usd_only_en', [], 'en') }}"
        data-currency-mismatch-usd-vi="{{ \Illuminate\Support\Facades\Lang::get('frontend::booking.payment_currency_mismatch_usd_only_vi', [], 'vi') }}"
        data-currency-mismatch-vnd-en="{{ \Illuminate\Support\Facades\Lang::get('frontend::booking.payment_currency_mismatch_vnd_only_en', [], 'en') }}"
        data-currency-mismatch-vnd-vi="{{ \Illuminate\Support\Facades\Lang::get('frontend::booking.payment_currency_mismatch_vnd_only_vi', [], 'vi') }}"
        data-complete-success-title="{{ __('frontend::booking.complete_success_title') }}"
        data-complete-success-message="{{ __('frontend::booking.complete_success_message') }}"
        data-complete-pending-title="{{ __('frontend::booking.complete_pending_title') }}"
        data-complete-pending-message="{{ __('frontend::booking.complete_pending_message') }}"
        data-complete-failed-title="{{ __('frontend::booking.complete_failed_title') }}"
        data-complete-failed-message="{{ __('frontend::booking.complete_failed_message') }}"
    >
        @include('frontend::booking.partials.progress')

        <div class="main-content-area booking-main-content">
            <div class="booking-steps-column" id="booking-steps-panel">
                <div class="step-1-section itinerary-step-section">
                    <div class="itinerary-step-header">
                        <div class="step-header-content">
                            <div class="step-number-wrap">
                                <div class="step-number-badge-one">
                                    <h3 class="step-number-text">1</h3>
                                </div>
                            </div>
                            <h3 class="step-section-title">{{ __('frontend::booking.step1_title') }}</h3>
                        </div>
                    </div>
                    <div class="itinerary-step-body">
                        <div class="content-row">
                            <div class="label-check-in-parent">
                                <b class="label-check">{{ __('frontend::booking.step1_departure_date') }}</b>
                                <div class="date-input-wrap" id="booking-date-input-wrap">
                                    <div class="date-picker-itinerary">
                                        <input
                                            id="booking-departure-date"
                                            class="booking-date-input-native"
                                            name="date"
                                            value=""
                                            readonly
                                            autocomplete="off"
                                            placeholder="{{ __('frontend::booking.step1_date_format') }}"
                                        />
                                    </div>
                                    <div class="mmddyyyy-parent">
                                        <div
                                            class="mmddyyyy booking-date-display"
                                            id="booking-date-display"
                                            data-placeholder="{{ __('frontend::booking.step1_departure_date') }}"
                                        >
                                            {{ __('frontend::booking.step1_departure_date') }}
                                        </div>
                                        <img class="icon" alt="" src="{{ asset('/assets/frontend/images/modules/booking/Icon.svg') }}">
                                    </div>
                                </div>
                                <div class="booking-field-error" id="booking-error-date" role="alert"></div>
                            </div>
                            <div class="label-check-in-group">
                                <b class="label-check">{{ __('frontend::booking.step1_choose_voyage') }}</b>
                                <div class="voyage-select-wrap" id="booking-voyage-select-wrap">
                                    <div class="ha-long-bay-wonder-discovery-parent voyage-select-trigger">
                                        <div class="ha-long-bay voyage-select-display" id="voyage-select-display">
                                            {{ __('frontend::booking.step1_voyage_placeholder') }}
                                        </div>
                                        <img class="icon" alt="" src="{{ asset('/assets/frontend/images/modules/booking/Icon.svg') }}">
                                    </div>
                                    <div class="voyage-dropdown-panel" id="voyage-dropdown-panel">
                                        <div class="voyage-dropdown-loading" id="voyage-dropdown-loading">
                                            {{ __('frontend::booking.step1_voyage_loading') }}
                                        </div>
                                        <div class="voyage-dropdown-list" id="voyage-dropdown-list"></div>
                                    </div>
                                </div>
                                <div class="booking-field-error" id="booking-error-voyage" role="alert"></div>
                            </div>
                        </div>
                        @php
                            $bookingInitialItinerary = $bookingInitialItinerary ?? null;
                            if ($bookingInitialItinerary) {
                                $bookingItineraryCruiseName = $bookingInitialItinerary->cruise_name
                                    ?? ($bookingInitialItinerary->cruise->name ?? '—');
                                $bookingItineraryDuration = FeCruiseUtils::formatDisplayDurationName(
                                    $bookingInitialItinerary->duration ?? 0,
                                    true
                                );

                                $bookingItineraryDestinationList = [];
                                if (!empty($bookingInitialItinerary->destination)) {
                                    $decodedDestinations = json_decode($bookingInitialItinerary->destination, true);
                                    if (is_array($decodedDestinations)) {
                                        $bookingItineraryDestinationList = $decodedDestinations;
                                    }
                                }
                                $bookingItineraryDestination = count($bookingItineraryDestinationList)
                                    ? $bookingItineraryDestinationList[0]
                                    : '—';
                            } else {
                                $bookingItineraryCruiseName = '—';
                                $bookingItineraryDuration = '—';
                                $bookingItineraryDestination = '—';
                            }
                        @endphp
                        <div class="itinerary-card" id="booking-itinerary-card" style="display: none;">
                            <div class="itinerary-card-content">
                                <div class="itinerary-card-header">
                                    <h3 class="section-heading-md" id="itinerary-card-title">
                                        {{ $bookingItineraryCruiseName }}
                                    </h3>
                                    @php
                                        $itineraryLanguageCode = \Route::current() ? \Route::current()->parameter('languageCode') : null;
                                        $itineraryIndexUrl = $itineraryLanguageCode
                                            ? route(Utilities::bindRouteNameMultiLanguage('frontend.itinerary.index'), ['languageCode' => $itineraryLanguageCode])
                                            : route('frontend.itinerary.index');
                                    @endphp
                                    <a
                                        href="{{ $itineraryIndexUrl }}"
                                        id="booking-itinerary-detail-link"
                                        class="action-button-primary"
                                        style="text-decoration:none;color:inherit;display:inline-flex;"
                                        target="_blank"
                                    >
                                        <b class="sign-in9">{{ __('frontend::booking.step1_button_see_detail') }}</b>
                                        <div class="arrow-right-long">
                                            <i class="fa-solid fa-arrow-right-long" style="color:#ffffff;font-size:16px;"></i>
                                        </div>
                                    </a>
                                </div>
                                <div class="itinerary-meta-row">
                                    <div class="ship-parent">
                                        <div class="calendar-check">
                                            <i class="fa-solid fa-ship" style="color:#0E5F4B;font-size:12px;"></i>
                                        </div>
                                        <div class="itinerary-chip-text" id="itinerary-card-cruise">
                                            {{ $bookingItineraryCruiseName }}
                                        </div>
                                    </div>
                                    <div class="ship-parent">
                                        <div class="calendar-check">
                                            <i class="fa-solid fa-calendar" style="color:#0E5F4B;font-size:12px;"></i>
                                        </div>
                                        <div class="itinerary-chip-text" id="itinerary-card-duration">
                                            {{ $bookingItineraryDuration }}
                                        </div>
                                    </div>
                                    <div class="itinerary-chip">
                                        <div class="calendar-check">
                                            <i class="fa-solid fa-location-dot" style="color:#0E5F4B;font-size:12px;"></i>
                                        </div>
                                        <div class="itinerary-chip-text" id="itinerary-card-destination">
                                            {{ $bookingItineraryDestination }}
                                        </div>
                                    </div>
                                </div>
                                <div class="itinerary-timeline-wrap" style="display: none;">
                                    <div class="group-14-row-0-parent" id="itinerary-card-timeline">
                                        <div class="group-14-row-0" id="itinerary-card-timeline-line"></div>
                                        <div class="group-14-row-1" id="itinerary-card-timeline-labels"></div>
                                    </div>
                                </div>
                                <div class="inclusions-parent" style="display: none;">
                                    <b class="inclusions">{{ __('frontend::booking.step1_inclusions_title') }}</b>
                                    <div class="inclusions-row-one" id="itinerary-card-inclusions-list"></div>
                                </div>
                            </div>
                            <div class="symbol"></div>
                        </div>
                    </div>
                </div>

                <div class="step-2-section cabin-step-section" style="display: none;">
                    <div class="cabin-step-header-bg">
                        <div class="step-header-content">
                            <div class="step-number-wrap">
                                <div class="step-number-badge-two">
                                    <h3 class="step-number-two-text">2</h3>
                                </div>
                            </div>
                            <h3 class="step-section-title">{{ __('frontend::booking.step2_title') }}</h3>
                        </div>
                    </div>
                    <div class="cabin-step-content">
                        <section class="in-voyage-panel" id="in-voyage-section">
                            <div class="in-voyage-title-wrap">
                                <h3 class="section-heading-md" id="in-voyage-title">
                                    {{ __('frontend::booking.step2_in_voyage_title') }}
                                </h3>
                            </div>
                            <div class="in-voyage-empty-state" id="in-voyage-empty-state">
                                <div class="ha-long-bay" id="in-voyage-empty">
                                    {{ __('frontend::booking.step2_empty_message') }}
                                </div>
                            </div>
                            <div class="in-voyage-cabins-list" id="in-voyage-cabins-list" style="display: none;"></div>
                            <div class="booking-field-error" id="booking-error-cabin" role="alert"></div>
                            <div class="in-voyage-indicator"></div>
                        </section>
                            <div class="cabin-card-left">
                            <div class="cabin-card-image-left">
                                <div class="chevron-left">
                                    <i class="fa-solid fa-chevron-left"></i>
                                </div>
                                <div class="cabin-nav-next-mobile" aria-hidden="true">
                                    <i class="fa-solid fa-chevron-right"></i>
                                </div>
                            </div>
                            <div class="cabin-card-content-left">
                                <div class="cabin-card-content">
                                    <h3 class="cabin-card-title"><span class="cabin-card-title-text">—</span></h3>
                                    <div class="max-2-guests-01-queen-sized"><span class="max-2-guests-01-queen-sized-text">—</span></div>
                                    <div class="cabin-price-row">
                                        <div class="cabin-price-content">
                                            <div class="icon-glyph">
                                                <b class="price-prefix">{{ __('frontend::booking.step2_price_from') }}  </b>
                                                <b class="price-amount">$0</b>
                                            </div>
                                            <div class="arrow-right-long2">
                                                <div class="icon-glyph-base"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="button-container">
                                        <div class="cabin-detail-link">
                                            <a href="javascript:;" class="btn-view-cabin-details btn-view-details d-inline-flex align-items-center" data-id="">
                                                <span class="sign-in11">
                                                    {{ __('frontend::booking.step2_button_view_detail') }}
                                                </span>
                                                <span class="arrow-right-long2">
                                                    <span class="icon-glyph-base"></span>
                                                </span>
                                            </a>
                                        </div>
                                        <button
                                            class="action-button-primary add-cabin-btn"
                                            id="button2"
                                            data-cabin-name=""
                                            data-cabin-price="0"
                                            data-cabin-description=""
                                            data-cabin-image=""
                                        >
                                            <b class="sign-in9">
                                                {{ __('frontend::booking.step2_button_add_cabin') }}
                                            </b>
                                            <div class="arrow-right-long4">
                                                <div class="icon-glyph-light"></div>
                                            </div>
                                        </button>
                                    </div>
                                </div>
                                <div class="cabin-card-footer-indicator-wrap">
                                    <div class="cabin-card-footer-indicator"></div>
                                </div>
                            </div>
                        </div>
                        <div class="cabin-card-right">
                            <div class="cabin-card-image-right">
                                <div class="chevron-left-icon">
                                    <i class="fa-solid fa-chevron-right"></i>
                                </div>
                            </div>
                            <div class="cabin-card-content-right">
                                <div class="cabin-card-content">
                                    <h3 class="cabin-card-title"><span class="cabin-card-title-text">—</span></h3>
                                    <div class="max-2-guests-01-queen-sized"><span class="max-2-guests-01-queen-sized-text">—</span></div>
                                    <div class="cabin-price-row">
                                        <div class="cabin-price-content">
                                            <div class="icon-glyph">
                                                <b class="price-prefix">{{ __('frontend::booking.step2_price_from') }}  </b>
                                                <b class="price-amount">$0</b>
                                            </div>
                                            <div class="arrow-right-long2">
                                                <div class="icon-glyph-base"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="button-container">
                                        <div class="cabin-detail-link">
                                            <a href="javascript:;" class="btn-view-cabin-details btn-view-details d-inline-flex align-items-center" data-id="">
                                                <span class="sign-in11">
                                                    {{ __('frontend::booking.step2_button_view_detail') }}
                                                </span>
                                                <span class="arrow-right-long2">
                                                    <span class="icon-glyph-base"></span>
                                                </span>
                                            </a>
                                        </div>
                                        <button
                                            class="action-button-primary add-cabin-btn"
                                            data-cabin-name=""
                                            data-cabin-price="0"
                                            data-cabin-description=""
                                            data-cabin-image=""
                                        >
                                            <b class="sign-in9">
                                                {{ __('frontend::booking.step2_button_add_cabin') }}
                                            </b>
                                            <div class="arrow-right-long4">
                                                <div class="icon-glyph-light"></div>
                                            </div>
                                        </button>
                                    </div>
                                </div>
                                <div class="cabin-card-footer-indicator-wrap">
                                    <div class="cabin-card-footer-indicator"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="step-3-section amenities-step-section" style="display: none;">
                    <div class="amenities-step-header-bg">
                        <div class="step-header-content">
                            <div class="amenities-step-badge-wrap">
                                <div class="step-number-badge-three">
                                    <h3 class="section-heading-md">3</h3>
                                </div>
                            </div>
                            <h3 class="step-section-title">{{ __('frontend::booking.step3_title') }}</h3>
                        </div>
                    </div>
                    <div class="amenities-step-content">
                        <div class="services-row-1-parent">
                            <div class="content-row">
                                <div class="amenity-card-transfer">
                                    <div class="amenity-icon-wrap">
                                        <div class="pen">
                                            <i class="fa-solid fa-car-side"></i>
                                        </div>
                                    </div>
                                    <div class="amenity-card-content">
                                        <b class="amenity-title">
                                            {{ __('frontend::booking.step3_amenity_transfer') }}
                                        </b>
                                        <div class="label-check">$0</div>
                                        <div class="quantity-parent">
                                            <div class="mmddyyyy">
                                                {{ __('frontend::booking.step3_quantity_label') }}
                                            </div>
                                            <div class="plus-parent">
                                                <i class="plus-icon fa-solid fa-minus"></i>
                                                <div class="mmddyyyy">0</div>
                                                <i class="plus-icon fa-solid fa-plus"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="amenity-card-spa">
                                    <div class="amenity-icon-wrap">
                                        <div class="pen">
                                            <i class="fa-solid fa-spa"></i>
                                        </div>
                                    </div>
                                    <div class="amenity-card-content-alt">
                                        <b class="amenity-title">
                                            {{ __('frontend::booking.step3_amenity_spa') }}
                                        </b>
                                        <div class="label-check">$0</div>
                                        <div class="quantity-group">
                                            <div class="mmddyyyy">
                                                {{ __('frontend::booking.step3_quantity_label') }}
                                            </div>
                                            <div class="plus-group">
                                                <i class="plus-icon fa-solid fa-minus"></i>
                                                <div class="mmddyyyy">0</div>
                                                <i class="plus-icon fa-solid fa-plus"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="services-row-2">
                                <div class="amenity-card-welcome">
                                    <div class="amenity-icon-wrap">
                                        <div class="pen">
                                            <i class="fa-brands fa-pagelines"></i>
                                        </div>
                                    </div>
                                    <div class="amenity-card-content-wide">
                                        <b class="amenity-title-wide">
                                            {{ __('frontend::booking.step3_amenity_welcome') }}
                                        </b>
                                        <div class="label-check">$0</div>
                                        <div class="quantity-group">
                                            <div class="mmddyyyy">
                                                {{ __('frontend::booking.step3_quantity_label') }}
                                            </div>
                                            <div class="plus-container">
                                                <i class="plus-icon fa-solid fa-minus"></i>
                                                <div class="mmddyyyy">0</div>
                                                <i class="plus-icon fa-solid fa-plus"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="amenity-card-photo">
                                    <div class="amenity-icon-wrap">
                                        <div class="camera">
                                            <div class="check">
                                                <i class="fa-solid fa-camera"></i>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="amenity-card-content-wide">
                                        <b class="amenity-title-wide">
                                            {{ __('frontend::booking.step3_amenity_photo') }}
                                        </b>
                                        <div class="label-check">$0</div>
                                        <div class="quantity-group">
                                            <div class="mmddyyyy">
                                                {{ __('frontend::booking.step3_quantity_label') }}
                                            </div>
                                            <div class="plus-container">
                                                <i class="plus-icon fa-solid fa-minus"></i>
                                                <div class="mmddyyyy">0</div>
                                                <i class="plus-icon fa-solid fa-plus"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="amenity-card-drone">
                                <div class="amenity-icon-wrap">
                                    <div class="pen">
                                        <i class="fa-solid fa-video"></i>
                                    </div>
                                </div>
                                <div class="amenity-card-content-wide">
                                    <b class="amenity-title-wide">
                                        {{ __('frontend::booking.step3_amenity_drone') }}
                                    </b>
                                    <div class="label-check">$0</div>
                                    <div class="quantity-group">
                                        <div class="mmddyyyy">
                                            {{ __('frontend::booking.step3_quantity_label') }}
                                        </div>
                                        <div class="plus-container">
                                            <i class="plus-icon fa-solid fa-minus"></i>
                                            <div class="mmddyyyy">0</div>
                                            <i class="plus-icon fa-solid fa-plus"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <button class="continue-voyage-button" id="button4">
                    <b class="sign-in16">{{ __('frontend::booking.button_continue_voyage') }}</b>
                    <div class="calendar-check">
                        <i class="fa-solid fa-arrow-right-long" style="color:#ffffff;font-size:16px;"></i>
                    </div>
                </button>
            </div>

            @include('frontend::booking.partials.guest-details')
            @include('frontend::booking.partials.payment')
            @include('frontend::booking.partials.complete')
            @include('frontend::booking.partials.summary')
        </div>

        @include('frontend::booking.partials.payment-loading')
    </div>
@endsection

@push('scripts')
    <script>
        window.bookingLabels = {!! json_encode([
            'failed_load' => __('frontend::booking.js.failed_load'),
            'connection_error' => __('frontend::booking.js.connection_error'),
            'empty_itinerary' => __('frontend::booking.js.empty_itinerary'),
            'select_voyage' => __('frontend::booking.js.select_voyage'),
            'max_guests' => __('frontend::booking.js.max_guests'),
            'room_size' => __('frontend::booking.js.room_size'),
            'person' => __('frontend::booking.js.person'),
            'people' => __('frontend::booking.js.people'),
            'guests_count' => __('frontend::booking.js.guests_count'),
            'in_voyage_cabins' => __('frontend::booking.js.in_voyage_cabins'),
            'adjusted_fare' => __('frontend::booking.js.adjusted_fare'),
            'remove' => __('frontend::booking.js.remove'),
            'adults' => __('frontend::booking.js.adults'),
            'child_6_12' => __('frontend::booking.js.child_6_12'),
            'child_2_5' => __('frontend::booking.js.child_2_5'),
            'infant' => __('frontend::booking.js.infant'),
            'adult_plural' => __('frontend::booking.js.adult_plural'),
            'adult_singular' => __('frontend::booking.js.adult_singular'),
            'child_6_12_label' => __('frontend::booking.js.child_6_12_label'),
            'child_2_5_label' => __('frontend::booking.js.child_2_5_label'),
            'infant_plural' => __('frontend::booking.js.infant_plural'),
            'infant_singular' => __('frontend::booking.js.infant_singular'),
            'quantity' => __('frontend::booking.js.quantity'),
            'email_required' => __('frontend::booking.js.email_required'),
            'payment_redirect' => __('frontend::booking.js.payment_redirect'),
        ], JSON_UNESCAPED_UNICODE) !!};
    </script>
@endpush
