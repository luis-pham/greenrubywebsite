<?php
/**
 * Payment pricing/security regression checks against the local database.
 *
 *   php scripts/check-payment-security.php
 *
 * Runs inside a transaction that is always rolled back; gateways are faked with Http::fake and
 * mail with Mail::fake, so nothing is charged or sent. Relies on the current local catalogue
 * (cabins 24/41, itineraries 2/5, services 4/9/14): update the ids if that data changes.
 * (phpunit can't cover this: there are no migrations for the CMS tables.)
 */
require getcwd() . '/vendor/autoload.php';
$app = require getcwd() . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Payment;
use App\Payments\BookingPricer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

Mail::fake();
$pass = 0; $fail = 0;
function check(string $name, bool $ok, $detail = '') { global $pass, $fail; $ok ? $pass++ : $fail++; echo ($ok ? "PASS " : "FAIL ") . $name . ($ok ? '' : '  => ' . json_encode($detail)) . "\n"; }

DB::beginTransaction();
try {
    $pricer = new BookingPricer();
    $cabin = fn ($id, $a = 2, $extra = []) => array_merge(['cabin_id' => $id, 'quantity' => 1, 'adults' => $a, 'children_6_12' => 0, 'children_2_5' => 0, 'infants' => 0, 'total_price' => 1], $extra);

    // --- BookingPricer
    $r = $pricer->price(['itinerary_id' => 2, 'cabins' => [$cabin(24, 2)]]);
    check('en cabin 24, 2 guests, 2-day = 100 USD', $r && $r['total'] == 100.0 && $r['currency'] === 'usd', $r);
    $r = $pricer->price(['itinerary_id' => 2, 'cabins' => [$cabin(24, 3, ['children_6_12' => 2])]]);
    check('over capacity: 3 adults 6000 + 2 kids 6-12 at 75% of 2000 = 9000', $r && $r['total'] == 9000.0, $r ?? $pricer->error);
    $r = $pricer->price(['itinerary_id' => 2, 'cabins' => [$cabin(24, 4)]]);
    check('4 adults > over_capacity_adult (3, same limit as booking JS) -> rejected', $r === null && $pricer->error === 'cabin_guests_invalid', $pricer->error);
    $r = $pricer->price(['itinerary_id' => 5, 'cabins' => [$cabin(41, 2)]]);
    check('vi cabin 41 = 45,000,000 VND', $r && $r['total'] == 45000000.0 && $r['currency'] === 'vnd', $r);
    $r = $pricer->price(['cabins' => [$cabin(24, 2)]]);
    check('missing itinerary -> rejected', $r === null && $pricer->error === 'itinerary_unknown', $pricer->error);
    $r = $pricer->price(['itinerary_id' => 2, 'cabins' => [['cabin_name' => 'x', 'total_price' => 1, 'quantity' => 1, 'adults' => 2]]]);
    check('cabin without id -> rejected (was: client price)', $r === null && $pricer->error === 'cabin_id_missing', $pricer->error);
    $r = $pricer->price(['itinerary_id' => 2, 'cabins' => [$cabin(99999)]]);
    check('unknown cabin -> rejected', $r === null && $pricer->error === 'cabin_unknown', $pricer->error);
    $r = $pricer->price(['itinerary_id' => 2, 'cabins' => [$cabin(24), $cabin(41)]]);
    check('mixed en/vi cabins -> rejected', $r === null && $pricer->error === 'cabin_language_mixed', $pricer->error);
    $r = $pricer->price(['itinerary_id' => 2, 'cabins' => [$cabin(24, 0)]]);
    check('zero guests -> rejected', $r === null && $pricer->error === 'cabin_guests_invalid', $pricer->error);
    $r = $pricer->price(['itinerary_id' => 2, 'cabins' => [$cabin(24, 2, ['quantity' => 50])]]);
    check('quantity 50 -> rejected', $r === null && $pricer->error === 'cabin_quantity_invalid', $pricer->error);
    $r = $pricer->price(['itinerary_id' => 2, 'cabins' => [$cabin(24)], 'amenities' => [['amenity_id' => 4, 'quantity' => 2, 'total_price' => 0]]]);
    check('amenity 4 x2 priced from DB (100 + 300)', $r && $r['total'] == 400.0 && $r['subtotal_amenities'] == 300.0, $r);
    $r = $pricer->price(['itinerary_id' => 2, 'cabins' => [$cabin(24)], 'amenities' => [['amenity_name' => 'x', 'quantity' => 1, 'total_price' => 0]]]);
    check('amenity without id -> rejected (was: free)', $r === null && $pricer->error === 'amenity_id_missing', $pricer->error);
    $r = $pricer->price(['itinerary_id' => 5, 'cabins' => [$cabin(41)], 'amenities' => [['amenity_id' => 9, 'quantity' => 1]]]);
    check('vi booking + en (USD) amenity -> rejected', $r === null && $pricer->error === 'amenity_unknown', $pricer->error);
    $r = $pricer->price(['itinerary_id' => 2, 'cabins' => [$cabin(24)], 'amenities' => [['amenity_id' => 14, 'quantity' => 1]]]);
    check('inclusive service as paid amenity -> rejected', $r === null && $pricer->error === 'amenity_unknown', $pricer->error);

    // --- /api/payment/init rejects unpriceable bookings before touching any gateway
    Http::swap(new Illuminate\Http\Client\Factory()); Http::fake(fn () => Http::response(['error' => 'should not be called'], 500));
    $before = Payment::count();
    $res = app()->handle(Illuminate\Http\Request::create('/api/payment/init', 'POST', [
        'method' => 'stripe', 'amount' => 1,
        'booking' => ['full_name' => 'Tamper', 'cabins' => [['cabin_id' => 24, 'quantity' => 1, 'adults' => 2, 'total_price' => 0.01]]],
    ], [], [], ['HTTP_ACCEPT' => 'application/json', 'REMOTE_ADDR' => '10.9.9.' . random_int(1, 250)]));
    $body = json_decode($res->getContent(), true);
    check('init without itinerary -> 400, no payment row', $res->getStatusCode() === 400 && ($body['reason'] ?? '') === 'itinerary_unknown' && Payment::count() === $before, [$res->getStatusCode(), $body]);

    // --- Stripe init: charges DB price (100 USD), stores server line items, ignores client total_price
    Http::swap(new Illuminate\Http\Client\Factory()); Http::fake(['*' => Http::response(['id' => 'cs_test_1', 'url' => 'https://checkout.stripe.test/1'], 200)]);
    $res = app()->handle(Illuminate\Http\Request::create('/api/payment/init', 'POST', [
        'method' => 'stripe', 'amount' => 1, 'return_base' => '/booking',
        'booking' => ['full_name' => 'Real Guest', 'email' => 'guest@example.com', 'itinerary_id' => 2,
            'cabins' => [['cabin_id' => 24, 'cabin_name' => 'Imperial', 'quantity' => 1, 'adults' => 2, 'unit_price' => 0.01, 'total_price' => 0.01]],
            'amenities' => [['amenity_id' => 4, 'amenity_name' => 'Spa', 'quantity' => 1, 'unit_price' => 0, 'total_price' => 0]]],
    ], [], [], ['HTTP_ACCEPT' => 'application/json', 'REMOTE_ADDR' => '10.8.8.' . random_int(1, 250)]));
    $body = json_decode($res->getContent(), true);
    $payment = Payment::where('internal_tx_id', $body['internal_tx_id'] ?? '')->first();
    check('stripe init 200 and amount = 25000 cents (100 + 150)', $res->getStatusCode() === 200 && $payment && (int) $payment->amount === 25000, [$res->getStatusCode(), $body, $payment?->amount]);
    $sent = collect(Http::recorded())->map(fn ($p) => $p[0]->data())->first();
    check('stripe session unit_amount = 25000 and PI metadata set', ($sent['line_items[0][price_data][unit_amount]'] ?? null) == 25000 && ($sent['payment_intent_data[metadata][internal_tx_id]'] ?? null) === $payment?->internal_tx_id, $sent);
    $booking = $payment?->booking;
    check('stored line items use server prices', $booking && (int) $booking->cabins[0]->total_price === 10000 && (int) $booking->amenities[0]->total_price === 15000 && (int) $booking->amenities[0]->amenity_id === 4, $booking?->load('cabins', 'amenities')->toArray());
    $tx = $payment->internal_tx_id;
    $token = hash_hmac('sha256', $tx, (string) config('app.key'));

    // --- cancel callback requires the status token
    app()->handle(Illuminate\Http\Request::create("/api/payment/callback/stripe?result=cancel&tx=$tx&method=stripe", 'GET'));
    check('cancel callback without token ignored', $payment->fresh()->status === 'pending', $payment->fresh()->status);

    // --- Stripe webhook: amount mismatch fails, stale signature rejected, correct one succeeds
    $secret = config('services.stripe.webhook_secret');
    $hook = function (array $object, ?int $ts = null) use ($secret) {
        $payload = json_encode(['type' => 'checkout.session.completed', 'data' => ['object' => $object]]);
        $ts = $ts ?? time();
        $sig = 't=' . $ts . ',v1=' . hash_hmac('sha256', $ts . '.' . $payload, $secret);
        return app()->handle(Illuminate\Http\Request::create('/api/payment/webhook/stripe', 'POST', [], [], [], ['CONTENT_TYPE' => 'application/json', 'HTTP_STRIPE_SIGNATURE' => $sig], $payload));
    };
    $res = $hook(['id' => 'cs_test_1', 'payment_status' => 'paid', 'amount_total' => 25000, 'currency' => 'usd', 'metadata' => ['internal_tx_id' => $tx]], time() - 3600);
    check('stripe webhook with 1h-old signature rejected', $res->getStatusCode() >= 400 && $payment->fresh()->status === 'pending', [$res->getStatusCode(), $payment->fresh()->status]);
    $res = $hook(['id' => 'cs_test_1', 'payment_status' => 'unpaid', 'amount_total' => 25000, 'currency' => 'usd', 'metadata' => ['internal_tx_id' => $tx]]);
    check('stripe completed but unpaid -> still pending', $payment->fresh()->status === 'pending', $payment->fresh()->status);
    $res = $hook(['id' => 'cs_test_1', 'payment_status' => 'paid', 'amount_total' => 25000, 'currency' => 'usd', 'metadata' => ['internal_tx_id' => $tx]]);
    check('stripe paid, right amount -> succeeded, booking confirmed', $payment->fresh()->status === 'succeeded' && $payment->fresh()->booking->status === 'confirmed', [$payment->fresh()->status, $payment->fresh()->booking->status]);

    $p2 = Payment::create(['internal_tx_id' => 'tx_test_mismatch', 'gateway' => 'stripe', 'amount' => 25000, 'currency' => 'usd', 'status' => 'pending']);
    $hook(['id' => 'cs_x', 'payment_status' => 'paid', 'amount_total' => 1, 'currency' => 'usd', 'metadata' => ['internal_tx_id' => 'tx_test_mismatch']]);
    check('stripe paid with wrong amount -> failed amount_mismatch', $p2->fresh()->status === 'failed' && $p2->fresh()->error_code === 'amount_mismatch', $p2->fresh()->only('status', 'error_code'));

    // --- PayPal: callback with someone else's order id is not captured
    $p3 = Payment::create(['internal_tx_id' => 'tx_pp_1', 'gateway' => 'paypal', 'provider_tx_id' => 'ORDER_MINE', 'amount' => 25000, 'currency' => 'usd', 'status' => 'pending']);
    Http::swap(new Illuminate\Http\Client\Factory()); Http::fake([
        '*/oauth2/token' => Http::response(['access_token' => 'x']),
        '*/ORDER_CHEAP' => Http::response(['id' => 'ORDER_CHEAP', 'purchase_units' => [['custom_id' => 'tx_other', 'amount' => ['value' => '1.00', 'currency_code' => 'USD']]]]),
        '*/ORDER_CHEAP/capture' => Http::response(['status' => 'COMPLETED', 'purchase_units' => [['payments' => ['captures' => [['status' => 'COMPLETED']]]]]]),
        '*' => Http::response([], 404),
    ]);
    app()->handle(Illuminate\Http\Request::create('/api/payment/callback/paypal?result=success&tx=tx_pp_1&method=paypal&token=ORDER_CHEAP', 'GET'));
    $captured = collect(Http::recorded())->contains(fn ($p) => str_ends_with($p[0]->url(), '/capture'));
    check('paypal: foreign order id -> no capture, still pending', !$captured && $p3->fresh()->status === 'pending', [$captured, $p3->fresh()->status]);

    $p3->update(['provider_tx_id' => null]);
    app()->handle(Illuminate\Http\Request::create('/api/payment/callback/paypal?result=success&tx=tx_pp_1&method=paypal&token=ORDER_CHEAP', 'GET'));
    $captured = collect(Http::recorded())->contains(fn ($p) => str_ends_with($p[0]->url(), '/capture'));
    check('paypal: order with other custom_id -> no capture', !$captured && $p3->fresh()->status === 'pending', [$captured, $p3->fresh()->only('status', 'error_code')]);

    $p4 = Payment::create(['internal_tx_id' => 'tx_pp_2', 'gateway' => 'paypal', 'provider_tx_id' => 'ORDER_OK', 'amount' => 25000, 'currency' => 'usd', 'status' => 'pending']);
    Http::swap(new Illuminate\Http\Client\Factory()); Http::fake([
        '*/oauth2/token' => Http::response(['access_token' => 'x']),
        '*/ORDER_OK/capture' => Http::response(['status' => 'COMPLETED', 'purchase_units' => [['payments' => ['captures' => [['status' => 'COMPLETED', 'amount' => ['value' => '250.00', 'currency_code' => 'USD']]]]]]]),
        '*/ORDER_OK' => Http::response(['id' => 'ORDER_OK', 'purchase_units' => [['custom_id' => 'tx_pp_2', 'amount' => ['value' => '250.00', 'currency_code' => 'USD']]]]),
        '*' => Http::response([], 404),
    ]);
    app()->handle(Illuminate\Http\Request::create('/api/payment/callback/paypal?result=success&tx=tx_pp_2&method=paypal&token=ORDER_OK', 'GET'));
    check('paypal: own order, right amount -> captured, succeeded', $p4->fresh()->status === 'succeeded', $p4->fresh()->only('status', 'error_code', 'error_message'));

    // --- cancel with a valid token works
    $p5 = Payment::create(['internal_tx_id' => 'tx_cancel_ok', 'gateway' => 'stripe', 'amount' => 100, 'currency' => 'usd', 'status' => 'pending']);
    $st = hash_hmac('sha256', 'tx_cancel_ok', (string) config('app.key'));
    Http::swap(new Illuminate\Http\Client\Factory()); Http::fake(['*' => Http::response([], 404)]);
    app()->handle(Illuminate\Http\Request::create("/api/payment/callback/stripe?result=cancel&tx=tx_cancel_ok&method=stripe&st=$st", 'GET'));
    check('cancel callback with valid token -> canceled', $p5->fresh()->status === 'canceled', $p5->fresh()->status);

    // --- inquiry (no charge): server prices when possible, guest totals otherwise
    $inquiry = function (array $booking) {
        $res = app()->handle(Illuminate\Http\Request::create('/api/booking/inquiry', 'POST', ['booking' => $booking], [], [], ['HTTP_ACCEPT' => 'application/json', 'REMOTE_ADDR' => '10.7.7.' . random_int(1, 250)]));
        $code = json_decode($res->getContent(), true)['code'] ?? null;
        return [$res->getStatusCode(), App\Models\Booking::where('code', $code)->first()];
    };
    [$status, $b] = $inquiry(['full_name' => 'Inq A', 'itinerary_id' => 2, 'cabins' => [['cabin_id' => 24, 'quantity' => 1, 'adults' => 2, 'unit_price' => 1, 'total_price' => 1]]]);
    check('inquiry priced from DB (10000 cents)', $status === 201 && $b && (int) $b->total_amount === 10000, [$status, $b?->total_amount]);
    [$status, $b] = $inquiry(['full_name' => 'Inq B', 'cabins' => [['cabin_name' => 'x', 'quantity' => 1, 'adults' => 2, 'unit_price' => 120, 'total_price' => 120]], 'amenities' => [['amenity_name' => 'y', 'quantity' => 2, 'unit_price' => 10, 'total_price' => 10]]]);
    check('inquiry fallback to guest totals (120 + 2x10 = 140 USD)', $status === 201 && $b && (int) $b->total_amount === 14000, [$status, $b?->total_amount, $b?->currency]);

    // --- exact payload shape sent by public/assets/frontend/js/modules/booking/index.js
    $r = (new BookingPricer())->price(['itinerary_id' => '2-3', 'cabins' => [['cabin_id' => '24', 'quantity' => 1, 'adults' => 2, 'children_6_12' => 0, 'children_2_5' => 0, 'infants' => 0, 'unit_price' => 0.5, 'total_price' => 0.5]], 'amenities' => [['amenity_id' => 4, 'quantity' => 1, 'unit_price' => 0, 'total_price' => 0]]]);
    check('booking-page payload ("2-3", string ids, tampered prices) -> 250 USD from DB', $r && $r['total'] == 250.0, $r ?? (new BookingPricer())->error);
    check('parseItineraryId', BookingPricer::parseItineraryId('2-3') === 2 && BookingPricer::parseItineraryId(7) === 7 && BookingPricer::parseItineraryId('x') === null && BookingPricer::parseItineraryId('2-3-4') === null, '');
} finally {
    DB::rollBack();
}
echo "\n$pass passed, $fail failed\n";
exit($fail ? 1 : 0);
