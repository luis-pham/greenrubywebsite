<?php

/*
 * Online booking policy (docs: greenruby-web docs/booking-terms-review.md).
 *
 * Both switches are OFF by default: with them off, /api/payment/init and /api/booking/inquiry behave
 * exactly as before (full payment, no terms acceptance). Turn them on only after TPX has approved the
 * Booking Terms, the migration 2026_10_27_000001 has run, and the web build that sends the new fields
 * is live. Changing a value needs `php artisan config:cache` in production.
 */
return [
    // Rules 1-3: 30% deposit (or full payment) when departing more than `balance_days_before` days
    // from today; full payment otherwise. The balance is due `balance_days_before` days before departure.
    'deposit_enabled' => (bool) env('BOOKING_DEPOSIT_ENABLED', false),
    'deposit_percent' => 30,
    'balance_days_before' => 30,

    // Rule 8: explicit acceptance of the current Booking Terms before confirmation and payment.
    // `terms_version` must equal the version the website shows (BOOKING_TERMS in the web code).
    'terms_required' => (bool) env('BOOKING_TERMS_REQUIRED', false),
    'terms_version' => env('BOOKING_TERMS_VERSION'),

    // "Today" and "days before departure" are Vietnam calendar days.
    'timezone' => 'Asia/Ho_Chi_Minh',
];
