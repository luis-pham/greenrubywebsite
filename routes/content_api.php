<?php

use App\Http\Controllers\Api\Content\CabinController;
use App\Http\Controllers\Api\Content\ContactController;
use App\Http\Controllers\Api\Content\PageController;
use App\Http\Controllers\Api\Content\SiteController;
use Illuminate\Support\Facades\Route;

/*
| Read-only Content API consumed server-to-server by the Next.js frontend.
| Loaded by RouteServiceProvider under prefix "api/v1/content" with
| middleware ['api', 'content.key'].
*/

Route::get('/site', [SiteController::class, 'show'])->name('api.content.site');
Route::get('/cabins', [CabinController::class, 'index'])->name('api.content.cabins.index');
Route::get('/cabins/{id}', [CabinController::class, 'show'])
    ->name('api.content.cabins.show');

Route::get('/pages/{code}', [PageController::class, 'show'])
    ->where('code', '[a-z0-9-]+')
    ->name('api.content.pages.show');

Route::post('/contact', [ContactController::class, 'store'])
    ->middleware('throttle:sensitive-api')
    ->name('api.content.contact');

// JSON 404 for anything else under the prefix (instead of the Blade 404 page).
Route::any('/{any}', fn () => response()->json(['error' => 'not_found'], 404))
    ->where('any', '.*')
    ->name('api.content.fallback');
