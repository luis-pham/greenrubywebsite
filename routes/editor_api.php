<?php

use App\Http\Controllers\Api\Editor\ArticleController;
use App\Http\Controllers\Api\Editor\AuthController;
use App\Http\Controllers\Api\Editor\MediaController;
use App\Http\Controllers\Api\Editor\PlanController;
use Illuminate\Support\Facades\Route;

/*
| API of the writing app (write.greenrubycruises.com, greenruby-write). Loaded by RouteServiceProvider
| under prefix "api/v1/editor" with middleware "editor". Everything except login needs editor.auth.
*/

Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:sensitive-api');

Route::middleware('editor.auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    Route::get('/categories', [ArticleController::class, 'categories']);
    Route::get('/articles', [ArticleController::class, 'index']);
    Route::post('/articles', [ArticleController::class, 'store']);
    Route::get('/articles/{id}', [ArticleController::class, 'show'])->whereNumber('id');
    Route::put('/articles/{id}', [ArticleController::class, 'update'])->whereNumber('id');
    Route::delete('/articles/{id}', [ArticleController::class, 'destroy'])->whereNumber('id');
    Route::post('/articles/{id}/translate', [ArticleController::class, 'translate'])->whereNumber('id');
    Route::put('/articles/{id}/translation', [ArticleController::class, 'link'])->whereNumber('id');

    Route::get('/media', [MediaController::class, 'index']);
    Route::post('/media', [MediaController::class, 'store']);
    Route::put('/media/{id}', [MediaController::class, 'update'])->whereNumber('id');

    Route::get('/plan', [PlanController::class, 'index']);
    Route::post('/plan/import', [PlanController::class, 'import']);
    Route::put('/plan/{id}', [PlanController::class, 'update'])->whereNumber('id');
    Route::post('/plan/{id}/start', [PlanController::class, 'start'])->whereNumber('id');
});
