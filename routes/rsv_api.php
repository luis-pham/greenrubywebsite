<?php

use App\Http\Controllers\Api\Rsv\AuthController;
use App\Http\Controllers\Api\Rsv\InboxController;
use App\Http\Controllers\Api\Rsv\ItemController;
use Illuminate\Support\Facades\Route;

/*
| API of the reservations desk (rsv.greenruby.cruises, app greenruby-rsv). Loaded by RouteServiceProvider
| under prefix "api/v1/rsv" with middleware "rsv". Everything except login needs rsv.auth.
*/

Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:sensitive-api');

Route::middleware('rsv.auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::get('/users', [AuthController::class, 'users']);

    Route::get('/inbox', [InboxController::class, 'index']);
    Route::get('/stats', [InboxController::class, 'stats']);
    Route::get('/export', [InboxController::class, 'export']);

    Route::get('/contacts/{id}', [ItemController::class, 'contact'])->whereNumber('id');
    Route::get('/bookings/{id}', [ItemController::class, 'booking'])->whereNumber('id');
    Route::put('/items/{type}/{id}', [ItemController::class, 'update'])->whereNumber('id');
    Route::post('/items/{type}/{id}/notes', [ItemController::class, 'note'])->whereNumber('id');
});
