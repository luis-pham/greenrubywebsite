<?php

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// Trang form SePay: đọc dữ liệu từ cache và render form POST tới SePay checkout/init, sau đó auto-submit.
Route::get('/payment/sepay/form', function () {
    $tx = request()->query('tx');
    if (!$tx) {
        abort(404);
    }
    $cacheKey = 'sepay_form_' . $tx;
    $fields = Cache::get($cacheKey);
    if (!$fields || !is_array($fields)) {
        abort(404);
    }
    $formAction = rtrim(config('services.sepay.base_url'), '/') . '/v1/checkout/init';
    return view('payment.sepay-form', [
        'formAction' => $formAction,
        'fields' => $fields,
    ]);
});
