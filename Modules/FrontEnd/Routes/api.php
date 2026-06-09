<?php

use Illuminate\Support\Facades\Route;
use Modules\BackEnd\Services\AdLanguageService;


$listLanguage = AdLanguageService::getAll();
$listLanguageCode = implode('|', $listLanguage->where('is_default', false)->pluck('code')->toArray());

Route::middleware('auth:api')->get('/frontend', function (Request $request) {
    return $request->user();
});

// cruises
Route::get('/cruises', 'PublicDataController@index')->defaults('type', 'cruises');
Route::get('/{languageCode}/cruises', 'PublicDataController@index')
    ->where('languageCode', $listLanguageCode)
    ->defaults('type', 'cruises');

// cabins
Route::get('/cabins', 'PublicDataController@index')->defaults('type', 'cabins');
Route::get('/{languageCode}/cabins', 'PublicDataController@index')
    ->where('languageCode', $listLanguageCode)
    ->defaults('type', 'cabins');

// itineraries
Route::get('/itineraries', 'PublicDataController@index')->defaults('type', 'itineraries');
Route::get('/{languageCode}/itineraries', 'PublicDataController@index')
    ->where('languageCode', $listLanguageCode)
    ->defaults('type', 'itineraries');

// experiences
Route::get('/experiences', 'PublicDataController@index')->defaults('type', 'experiences');
Route::get('/{languageCode}/experiences', 'PublicDataController@index')
    ->where('languageCode', $listLanguageCode)
    ->defaults('type', 'experiences');

// departures
Route::get('/departures', 'PublicDataController@index')->defaults('type', 'departures');
Route::get('/{languageCode}/departures', 'PublicDataController@index')
    ->where('languageCode', $listLanguageCode)
    ->defaults('type', 'departures');