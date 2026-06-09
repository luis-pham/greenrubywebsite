<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\PaymentWebhookController;

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/booking/itineraries', [\App\Http\Controllers\Api\BookingApiController::class, 'itineraries']);
Route::get('/booking/cabins', [\App\Http\Controllers\Api\BookingApiController::class, 'cabins']);
Route::get('/booking/amenities', [\App\Http\Controllers\Api\BookingApiController::class, 'amenities']);
Route::get('/booking/suggest-by-cabin', [\App\Http\Controllers\Api\BookingApiController::class, 'suggestByCabin']);
Route::get('/booking/suggest-by-cruise', [\App\Http\Controllers\Api\BookingApiController::class, 'suggestByCruise']);
Route::get('/booking/itinerary/{id}', [\App\Http\Controllers\Api\BookingApiController::class, 'itineraryDetail']);
Route::get('/booking/departure-dates', [\App\Http\Controllers\Api\BookingApiController::class, 'departureDates']);

Route::post('/quote-request', [\App\Http\Controllers\Api\QuoteRequestController::class, 'store']);
Route::post('/booking/inquiry', [PaymentController::class, 'storeInquiry']);

Route::post('/payment/init', [PaymentController::class, 'init']);
Route::get('/payment/status', [PaymentController::class, 'status']);
Route::get('/payment/callback/{method}', [PaymentController::class, 'callback'])->name('api.payment.callback');
Route::post('/payment/webhook/stripe', [PaymentWebhookController::class, 'stripe']);
Route::post('/payment/webhook/paypal', [PaymentWebhookController::class, 'paypal']);
Route::post('/payment/webhook/sepay', [PaymentWebhookController::class, 'sepay']);
Route::get('/payment/webhook-check', function () {
    return response()->json([
        'message' => 'Webhook endpoint is reachable. Stripe must POST to /api/payment/webhook/stripe',
        'stripe_url' => url('/api/payment/webhook/stripe'),
        'paypal_url' => url('/api/payment/webhook/paypal'),
    ]);
});

