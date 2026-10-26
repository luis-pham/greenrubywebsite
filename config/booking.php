<?php

$ids = fn (string $value) => array_values(array_filter(array_map('intval', explode(',', $value))));

/*
 * Online booking policy (TPX management decisions of 27/10/2026; greenruby-web docs/booking-terms-review.md).
 *
 * Every switch is OFF by default: with them off, /api/payment/init and /api/booking/inquiry behave exactly
 * as before (full payment, no terms acceptance) and no balance email is sent. Turn them on only after TPX's
 * final approval and the production checks (credentials, webhooks, reconciliation, refunds, scheduler).
 * Changing a value needs `php artisan config:cache` in production.
 */
return [
    // Decision 2: 30% deposit (or full payment) when departing MORE than `deposit_min_days` days from
    // today; full payment otherwise. Every balance is due `balance_days_before` days before departure.
    'deposit_enabled' => (bool) env('BOOKING_DEPOSIT_ENABLED', false),
    'deposit_percent' => 30,
    'deposit_min_days' => 45,
    'balance_days_before' => 30,

    // Decision 3: balance request and reminders (days before departure), then a formal written notice
    // at the due date with `cure_hours` more to pay. Nothing is ever cancelled automatically.
    'balance_collection_enabled' => (bool) env('BOOKING_BALANCE_COLLECTION_ENABLED', false),
    'balance_request_days' => 45,
    'balance_reminder_days' => [37, 32],
    'cure_hours' => 48,
    // Staff copy of reminder 2, the overdue notice and the end of the cure period.
    'reservations_email' => env('BOOKING_RESERVATIONS_EMAIL'),

    // Decision 6: secure links staff send so guests accept the terms and pay themselves.
    'payment_link_days' => 14,

    // Decision 5: Reservations (any back-office user who can update bookings) requests a refund;
    // only these ad_role ids may approve and record it, and never for their own request.
    'finance_roles' => $ids((string) env('BOOKING_FINANCE_ROLES', '1')),

    // Decision 4: on a valid cancellation for non-payment, at most this share of the total is kept
    // (never more than the deposit actually received).
    'non_payment_retention_percent' => 30,

    // Decision 8: explicit acceptance of the current Booking Terms before confirmation and payment.
    // `terms_version` must equal the version the website shows (BOOKING_TERMS in the web code).
    'terms_required' => (bool) env('BOOKING_TERMS_REQUIRED', false),
    'terms_version' => env('BOOKING_TERMS_VERSION'),

    // "Today" and "days before departure" are Vietnam calendar days.
    'timezone' => 'Asia/Ho_Chi_Minh',
];
