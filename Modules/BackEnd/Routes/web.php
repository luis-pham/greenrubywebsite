<?php

use Illuminate\Support\Facades\Route;
use Modules\BackEnd\Helpers\Utilities;
use Modules\BackEnd\Services\AdLanguageService;
use Modules\BackEnd\Constants\PageConfigConsts;

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

Route::prefix('admincp')->middleware('guest:admin')->group(function () {
    Route::get('/login', 'AuthController@index')->name('backend.auth.index');
    Route::post('/login', 'AuthController@login')->middleware('throttle:5,1')->name('backend.auth.login');
});

Route::prefix('admincp')->middleware('auth:admin')->group(function () {
    Route::get('/changeLanguage', 'ChangeLanguageController@index')->name('backend.changeLanguage');

    Route::get('/personal/edit', 'PersonalController@edit')->name('backend.personal.edit');
    Route::post('/personal/update', 'PersonalController@update')->name('backend.personal.update');
    Route::get('/personal/change-password', 'PersonalController@changePasswordEdit')->name('backend.personal.changePasswordEdit');
    Route::post('/personal/change-password', 'PersonalController@changePasswordUpdate')->name('backend.personal.changePasswordUpdate');
    Route::post('/personal/update-theme', 'PersonalController@updateTheme')->name('backend.personal.updateTheme');

    Route::post('/logout', 'AuthController@logout')->name('backend.auth.logout');

    Route::get('/user', 'UserController@index')->name('backend.user.index')->middleware('can:user-read');
    Route::get('/user/show/{id}', 'UserController@show')->name('backend.user.show')->middleware('can:user-read');
    Route::get('/user/create', 'UserController@create')->name('backend.user.create')->middleware('can:user-create');
    Route::post('/user/store', 'UserController@store')->name('backend.user.store')->middleware('can:user-create');
    Route::get('/user/edit/{id}', 'UserController@edit')->name('backend.user.edit')->middleware('can:user-update');
    Route::post('/user/update/{id}', 'UserController@update')->name('backend.user.update')->middleware('can:user-update');
    Route::post('/user/destroy', 'UserController@destroy')->name('backend.user.destroy')->middleware('can:user-delete');
    Route::get('/user/info/{id}', 'UserController@info')->name('backend.user.info')->middleware('can:user-read');

    Route::get('/file', 'FileController@index')->name('backend.file.index')->middleware('can:file-read');
    Route::get('/file/show/{id}', 'FileController@show')->name('backend.file.show')->middleware('can:file-read');
    Route::post('/file/store', 'FileController@store')->name('backend.file.store')->middleware('can:file-create');
    Route::post('/file/update/{id}', 'FileController@update')->name('backend.file.update')->middleware('can:file-update');
    Route::post('/file/destroy', 'FileController@destroy')->name('backend.file.destroy')->middleware('can:file-delete');

    Route::get('/logging', 'LoggingController@index')->name('backend.logging.index')->middleware('can:logging-read');
    Route::get('/logging/show/{id}', 'LoggingController@show')->name('backend.logging.show')->middleware('can:logging-read');
});

Route::prefix('admincp')->middleware(['auth:admin', 'language.backend'])->group(function () {
    $listLanguage = AdLanguageService::getAll();
    $listLanguageCode = implode('|', $listLanguage->where('is_default', false)->pluck('code')->toArray());

    Route::get('/', 'IndexController@index')->name('backend.index');
    Route::get('/{languageCode}', 'IndexController@index')->where('languageCode', $listLanguageCode)->name(Utilities::bindRouteNameMultiLanguage('backend.index'));

    Route::get('/category/{typeName}', 'CategoryController@index')->name('backend.category.index')->middleware('can-category:read');
    Route::get('/category/{typeName}/show/{id}', 'CategoryController@show')->name('backend.category.show')->middleware('can-category:read');
    Route::get('/category/{typeName}/create', 'CategoryController@create')->name('backend.category.create')->middleware('can-category:create');
    Route::post('/category/{typeName}/store', 'CategoryController@store')->name('backend.category.store')->middleware('can-category:create');
    Route::get('/category/{typeName}/edit/{id}', 'CategoryController@edit')->name('backend.category.edit')->middleware('can-category:update');
    Route::post('/category/{typeName}/update/{id}', 'CategoryController@update')->name('backend.category.update')->middleware('can-category:update');
    Route::post('/category/{typeName}/destroy', 'CategoryController@destroy')->name('backend.category.destroy')->middleware('can-category:delete');
    Route::post('/category/{typeName}/move-up', 'CategoryController@moveUp')->name('backend.category.moveUp')->middleware('can-category:update');
    Route::post('/category/{typeName}/move-down', 'CategoryController@moveDown')->name('backend.category.moveDown')->middleware('can-category:update');
    Route::get('/{languageCode}/category/{typeName}', 'CategoryController@index')->name(Utilities::bindRouteNameMultiLanguage('backend.category.index'))->where('languageCode', $listLanguageCode)->middleware('can-category:read');
    Route::get('/{languageCode}/category/{typeName}/show/{id}', 'CategoryController@show')->name(Utilities::bindRouteNameMultiLanguage('backend.category.show'))->where('languageCode', $listLanguageCode)->middleware('can-category:read');
    Route::get('/{languageCode}/category/{typeName}/create', 'CategoryController@create')->name(Utilities::bindRouteNameMultiLanguage('backend.category.create'))->where('languageCode', $listLanguageCode)->middleware('can-category:create');
    Route::post('/{languageCode}/category/{typeName}/store', 'CategoryController@store')->name(Utilities::bindRouteNameMultiLanguage('backend.category.store'))->where('languageCode', $listLanguageCode)->middleware('can-category:create');
    Route::get('/{languageCode}/category/{typeName}/edit/{id}', 'CategoryController@edit')->name(Utilities::bindRouteNameMultiLanguage('backend.category.edit'))->where('languageCode', $listLanguageCode)->middleware('can-category:update');
    Route::post('/{languageCode}/category/{typeName}/update/{id}', 'CategoryController@update')->name(Utilities::bindRouteNameMultiLanguage('backend.category.update'))->where('languageCode', $listLanguageCode)->middleware('can-category:update');
    Route::post('/{languageCode}/category/{typeName}/destroy', 'CategoryController@destroy')->name(Utilities::bindRouteNameMultiLanguage('backend.category.destroy'))->where('languageCode', $listLanguageCode)->middleware('can-category:delete');
    Route::post('/{languageCode}/category/{typeName}/move-up', 'CategoryController@moveUp')->name(Utilities::bindRouteNameMultiLanguage('backend.category.moveUp'))->where('languageCode', $listLanguageCode)->middleware('can-category:update');
    Route::post('/{languageCode}/category/{typeName}/move-down', 'CategoryController@moveDown')->name(Utilities::bindRouteNameMultiLanguage('backend.category.moveDown'))->where('languageCode', $listLanguageCode)->middleware('can-category:update');

    Route::get('/group/{typeName}', 'GroupController@index')->name('backend.group.index')->middleware('can-group:read');
    Route::get('/group/{typeName}/show/{id}', 'GroupController@show')->name('backend.group.show')->middleware('can-group:read');
    Route::get('/group/{typeName}/create', 'GroupController@create')->name('backend.group.create')->middleware('can-group:create');
    Route::post('/group/{typeName}/store', 'GroupController@store')->name('backend.group.store')->middleware('can-group:create');
    Route::get('/group/{typeName}/edit/{id}', 'GroupController@edit')->name('backend.group.edit')->middleware('can-group:update');
    Route::post('/group/{typeName}/update/{id}', 'GroupController@update')->name('backend.group.update')->middleware('can-group:update');
    Route::post('/group/{typeName}/destroy', 'GroupController@destroy')->name('backend.group.destroy')->middleware('can-group:delete');
    Route::post('/group/{typeName}/order-update', 'GroupController@orderUpdate')->name('backend.group.orderUpdate')->middleware('can-group:order');
    Route::get('/{languageCode}/group/{typeName}', 'GroupController@index')->name(Utilities::bindRouteNameMultiLanguage('backend.group.index'))->where('languageCode', $listLanguageCode)->middleware('can-group:read');
    Route::get('/{languageCode}/group/{typeName}/show/{id}', 'GroupController@show')->name(Utilities::bindRouteNameMultiLanguage('backend.group.show'))->where('languageCode', $listLanguageCode)->middleware('can-group:read');
    Route::get('/{languageCode}/group/{typeName}/create', 'GroupController@create')->name(Utilities::bindRouteNameMultiLanguage('backend.group.create'))->where('languageCode', $listLanguageCode)->middleware('can-group:create');
    Route::post('/{languageCode}/group/{typeName}/store', 'GroupController@store')->name(Utilities::bindRouteNameMultiLanguage('backend.group.store'))->where('languageCode', $listLanguageCode)->middleware('can-group:create');
    Route::get('/{languageCode}/group/{typeName}/edit/{id}', 'GroupController@edit')->name(Utilities::bindRouteNameMultiLanguage('backend.group.edit'))->where('languageCode', $listLanguageCode)->middleware('can-group:update');
    Route::post('/{languageCode}/group/{typeName}/update/{id}', 'GroupController@update')->name(Utilities::bindRouteNameMultiLanguage('backend.group.update'))->where('languageCode', $listLanguageCode)->middleware('can-group:update');
    Route::post('/{languageCode}/group/{typeName}/destroy', 'GroupController@destroy')->name(Utilities::bindRouteNameMultiLanguage('backend.group.destroy'))->where('languageCode', $listLanguageCode)->middleware('can-group:delete');
    Route::post('/{languageCode}/group/{typeName}/order-update', 'GroupController@orderUpdate')->name(Utilities::bindRouteNameMultiLanguage('backend.group.orderUpdate'))->where('languageCode', $listLanguageCode)->middleware('can-group:order');

    Route::get('/article', 'ArticleController@index')->name('backend.article.index')->middleware('can:article-read');
    Route::get('/article/show/{id}', 'ArticleController@show')->name('backend.article.show')->middleware('can:article-read');
    Route::get('/article/create', 'ArticleController@create')->name('backend.article.create')->middleware('can:article-create');
    Route::post('/article/store', 'ArticleController@store')->name('backend.article.store')->middleware('can:article-create');
    Route::get('/article/edit/{id}', 'ArticleController@edit')->name('backend.article.edit')->middleware('can:article-update');
    Route::post('/article/update/{id}', 'ArticleController@update')->name('backend.article.update')->middleware('can:article-update');
    Route::post('/article/destroy', 'ArticleController@destroy')->name('backend.article.destroy')->middleware('can:article-delete');
    Route::get('/{languageCode}/article', 'ArticleController@index')->name(Utilities::bindRouteNameMultiLanguage('backend.article.index'))->where('languageCode', $listLanguageCode)->middleware('can:article-read');
    Route::get('/{languageCode}/article/show/{id}', 'ArticleController@show')->name(Utilities::bindRouteNameMultiLanguage('backend.article.show'))->where('languageCode', $listLanguageCode)->middleware('can:article-read');
    Route::get('/{languageCode}/article/create', 'ArticleController@create')->name(Utilities::bindRouteNameMultiLanguage('backend.article.create'))->where('languageCode', $listLanguageCode)->middleware('can:article-create');
    Route::post('/{languageCode}/article/store', 'ArticleController@store')->name(Utilities::bindRouteNameMultiLanguage('backend.article.store'))->where('languageCode', $listLanguageCode)->middleware('can:article-create');
    Route::get('/{languageCode}/article/edit/{id}', 'ArticleController@edit')->name(Utilities::bindRouteNameMultiLanguage('backend.article.edit'))->where('languageCode', $listLanguageCode)->middleware('can:article-update');
    Route::post('/{languageCode}/article/update/{id}', 'ArticleController@update')->name(Utilities::bindRouteNameMultiLanguage('backend.article.update'))->where('languageCode', $listLanguageCode)->middleware('can:article-update');
    Route::post('/{languageCode}/article/destroy', 'ArticleController@destroy')->name(Utilities::bindRouteNameMultiLanguage('backend.article.destroy'))->where('languageCode', $listLanguageCode)->middleware('can:article-delete');

    Route::get('/menu-front-end', 'MenuFrontEndController@index')->name('backend.menuFrontEnd.index')->middleware('can:menu-front-end-read');
    Route::get('/menu-front-end/show/{id}', 'MenuFrontEndController@show')->name('backend.menuFrontEnd.show')->middleware('can:menu-front-end-read');
    Route::get('/menu-front-end/create', 'MenuFrontEndController@create')->name('backend.menuFrontEnd.create')->middleware('can:menu-front-end-create');
    Route::post('/menu-front-end/store', 'MenuFrontEndController@store')->name('backend.menuFrontEnd.store')->middleware('can:menu-front-end-create');
    Route::get('/menu-front-end/edit/{id}', 'MenuFrontEndController@edit')->name('backend.menuFrontEnd.edit')->middleware('can:menu-front-end-update');
    Route::post('/menu-front-end/update/{id}', 'MenuFrontEndController@update')->name('backend.menuFrontEnd.update')->middleware('can:menu-front-end-update');
    Route::post('/menu-front-end/destroy', 'MenuFrontEndController@destroy')->name('backend.menuFrontEnd.destroy')->middleware('can:menu-front-end-delete');
    Route::get('/{languageCode}/menu-front-end', 'MenuFrontEndController@index')->name(Utilities::bindRouteNameMultiLanguage('backend.menuFrontEnd.index'))->where('languageCode', $listLanguageCode)->middleware('can:menu-front-end-read');
    Route::get('/{languageCode}/menu-front-end/show/{id}', 'MenuFrontEndController@show')->name(Utilities::bindRouteNameMultiLanguage('backend.menuFrontEnd.show'))->where('languageCode', $listLanguageCode)->middleware('can:menu-front-end-read');
    Route::get('/{languageCode}/menu-front-end/create', 'MenuFrontEndController@create')->name(Utilities::bindRouteNameMultiLanguage('backend.menuFrontEnd.create'))->where('languageCode', $listLanguageCode)->middleware('can:menu-front-end-create');
    Route::post('/{languageCode}/menu-front-end/store', 'MenuFrontEndController@store')->name(Utilities::bindRouteNameMultiLanguage('backend.menuFrontEnd.store'))->where('languageCode', $listLanguageCode)->middleware('can:menu-front-end-create');
    Route::get('/{languageCode}/menu-front-end/edit/{id}', 'MenuFrontEndController@edit')->name(Utilities::bindRouteNameMultiLanguage('backend.menuFrontEnd.edit'))->where('languageCode', $listLanguageCode)->middleware('can:menu-front-end-update');
    Route::post('/{languageCode}/menu-front-end/update/{id}', 'MenuFrontEndController@update')->name(Utilities::bindRouteNameMultiLanguage('backend.menuFrontEnd.update'))->where('languageCode', $listLanguageCode)->middleware('can:menu-front-end-update');
    Route::post('/{languageCode}/menu-front-end/destroy', 'MenuFrontEndController@destroy')->name(Utilities::bindRouteNameMultiLanguage('backend.menuFrontEnd.destroy'))->where('languageCode', $listLanguageCode)->middleware('can:menu-front-end-delete');

    Route::get('/faq', 'FaqController@index')->name('backend.faq.index')->middleware('can:faq-read');
    Route::get('/faq/show/{id}', 'FaqController@show')->name('backend.faq.show')->middleware('can:faq-read');
    Route::get('/faq/create', 'FaqController@create')->name('backend.faq.create')->middleware('can:faq-create');
    Route::post('/faq/store', 'FaqController@store')->name('backend.faq.store')->middleware('can:faq-create');
    Route::get('/faq/edit/{id}', 'FaqController@edit')->name('backend.faq.edit')->middleware('can:faq-update');
    Route::post('/faq/update/{id}', 'FaqController@update')->name('backend.faq.update')->middleware('can:faq-update');
    Route::post('/faq/destroy', 'FaqController@destroy')->name('backend.faq.destroy')->middleware('can:faq-delete');
    Route::post('/faq/order-update', 'FaqController@orderUpdate')->name('backend.faq.orderUpdate')->middleware('can:faq-order');
    Route::get('/{languageCode}/faq', 'FaqController@index')->name(Utilities::bindRouteNameMultiLanguage('backend.faq.index'))->where('languageCode', $listLanguageCode)->middleware('can:faq-read');
    Route::get('/{languageCode}/faq/show/{id}', 'FaqController@show')->name(Utilities::bindRouteNameMultiLanguage('backend.faq.show'))->where('languageCode', $listLanguageCode)->middleware('can:faq-read');
    Route::get('/{languageCode}/faq/create', 'FaqController@create')->name(Utilities::bindRouteNameMultiLanguage('backend.faq.create'))->where('languageCode', $listLanguageCode)->middleware('can:faq-create');
    Route::post('/{languageCode}/faq/store', 'FaqController@store')->name(Utilities::bindRouteNameMultiLanguage('backend.faq.store'))->where('languageCode', $listLanguageCode)->middleware('can:faq-create');
    Route::get('/{languageCode}/faq/edit/{id}', 'FaqController@edit')->name(Utilities::bindRouteNameMultiLanguage('backend.faq.edit'))->where('languageCode', $listLanguageCode)->middleware('can:faq-update');
    Route::post('/{languageCode}/faq/update/{id}', 'FaqController@update')->name(Utilities::bindRouteNameMultiLanguage('backend.faq.update'))->where('languageCode', $listLanguageCode)->middleware('can:faq-update');
    Route::post('/{languageCode}/faq/destroy', 'FaqController@destroy')->name(Utilities::bindRouteNameMultiLanguage('backend.faq.destroy'))->where('languageCode', $listLanguageCode)->middleware('can:faq-delete');
    Route::post('/{languageCode}/faq/order-update', 'FaqController@orderUpdate')->name(Utilities::bindRouteNameMultiLanguage('backend.faq.orderUpdate'))->where('languageCode', $listLanguageCode)->middleware('can:faq-order');

    Route::get('/booking-manager', 'BookingController@index')->name('backend.booking.index')->middleware('can:booking-manager-read');
    Route::get('/booking-manager/show/{id}', 'BookingController@show')->name('backend.booking.show')->middleware('can:booking-manager-read');
    Route::get('/booking-manager/detail/{id}', 'BookingController@detailModal')->name('backend.booking.detail')->middleware('can:booking-manager-read');
    Route::post('/booking-manager/confirm/{id}', 'BookingController@confirm')->name('backend.booking.confirm')->middleware('can:booking-manager-update');
    Route::post('/booking-manager/cancel/{id}', 'BookingController@cancel')->name('backend.booking.cancel')->middleware('can:booking-manager-update');
    Route::post('/booking-manager/destroy', 'BookingController@destroy')->name('backend.booking.destroy')->middleware('can:booking-manager-delete');
    Route::post('/booking-manager/quote-destroy', 'BookingController@destroyQuote')->name('backend.booking.quoteDestroy')->middleware('can:booking-manager-delete');
    Route::post('/booking-manager/quote-status/{id}', 'BookingController@quoteStatus')->name('backend.booking.quoteStatus')->middleware('can:booking-manager-update');
    Route::get('/{languageCode}/booking-manager', 'BookingController@index')->name(Utilities::bindRouteNameMultiLanguage('backend.booking.index'))->where('languageCode', $listLanguageCode)->middleware('can:booking-manager-read');
    Route::get('/{languageCode}/booking-manager/show/{id}', 'BookingController@show')->name(Utilities::bindRouteNameMultiLanguage('backend.booking.show'))->where('languageCode', $listLanguageCode)->middleware('can:booking-manager-read');
    Route::get('/{languageCode}/booking-manager/detail/{id}', 'BookingController@detailModal')->name(Utilities::bindRouteNameMultiLanguage('backend.booking.detail'))->where('languageCode', $listLanguageCode)->middleware('can:booking-manager-read');
    Route::post('/{languageCode}/booking-manager/confirm/{id}', 'BookingController@confirm')->name(Utilities::bindRouteNameMultiLanguage('backend.booking.confirm'))->where('languageCode', $listLanguageCode)->middleware('can:booking-manager-update');
    Route::post('/{languageCode}/booking-manager/cancel/{id}', 'BookingController@cancel')->name(Utilities::bindRouteNameMultiLanguage('backend.booking.cancel'))->where('languageCode', $listLanguageCode)->middleware('can:booking-manager-update');
    Route::post('/{languageCode}/booking-manager/destroy', 'BookingController@destroy')->name(Utilities::bindRouteNameMultiLanguage('backend.booking.destroy'))->where('languageCode', $listLanguageCode)->middleware('can:booking-manager-delete');
    Route::post('/{languageCode}/booking-manager/quote-destroy', 'BookingController@destroyQuote')->name(Utilities::bindRouteNameMultiLanguage('backend.booking.quoteDestroy'))->where('languageCode', $listLanguageCode)->middleware('can:booking-manager-delete');
    Route::post('/{languageCode}/booking-manager/quote-status/{id}', 'BookingController@quoteStatus')->name(Utilities::bindRouteNameMultiLanguage('backend.booking.quoteStatus'))->where('languageCode', $listLanguageCode)->middleware('can:booking-manager-update');

    Route::get('/cabin-manager', 'CabinController@index')->name('backend.cabin.index')->middleware('can:cabin-manager-read');
    Route::get('/cabin-manager/show/{id}', 'CabinController@show')->name('backend.cabin.show')->middleware('can:cabin-manager-read');
    Route::get('/cabin-manager/create', 'CabinController@create')->name('backend.cabin.create')->middleware('can:cabin-manager-create');
    Route::post('/cabin-manager/store', 'CabinController@store')->name('backend.cabin.store')->middleware('can:cabin-manager-create');
    Route::get('/cabin-manager/edit/{id}', 'CabinController@edit')->name('backend.cabin.edit')->middleware('can:cabin-manager-update');
    Route::post('/cabin-manager/update/{id}', 'CabinController@update')->name('backend.cabin.update')->middleware('can:cabin-manager-update');
    Route::post('/cabin-manager/destroy', 'CabinController@destroy')->name('backend.cabin.destroy')->middleware('can:cabin-manager-delete');
    Route::get('/{languageCode}/cabin-manager', 'CabinController@index')->name(Utilities::bindRouteNameMultiLanguage('backend.cabin.index'))->where('languageCode', $listLanguageCode)->middleware('can:cabin-manager-read');
    Route::get('/{languageCode}/cabin-manager/show/{id}', 'CabinController@show')->name(Utilities::bindRouteNameMultiLanguage('backend.cabin.show'))->where('languageCode', $listLanguageCode)->middleware('can:cabin-manager-read');
    Route::get('/{languageCode}/cabin-manager/create', 'CabinController@create')->name(Utilities::bindRouteNameMultiLanguage('backend.cabin.create'))->where('languageCode', $listLanguageCode)->middleware('can:cabin-manager-create');
    Route::post('/{languageCode}/cabin-manager/store', 'CabinController@store')->name(Utilities::bindRouteNameMultiLanguage('backend.cabin.store'))->where('languageCode', $listLanguageCode)->middleware('can:cabin-manager-create');
    Route::get('/{languageCode}/cabin-manager/edit/{id}', 'CabinController@edit')->name(Utilities::bindRouteNameMultiLanguage('backend.cabin.edit'))->where('languageCode', $listLanguageCode)->middleware('can:cabin-manager-update');
    Route::post('/{languageCode}/cabin-manager/update/{id}', 'CabinController@update')->name(Utilities::bindRouteNameMultiLanguage('backend.cabin.update'))->where('languageCode', $listLanguageCode)->middleware('can:cabin-manager-update');
    Route::post('/{languageCode}/cabin-manager/destroy', 'CabinController@destroy')->name(Utilities::bindRouteNameMultiLanguage('backend.cabin.destroy'))->where('languageCode', $listLanguageCode)->middleware('can:cabin-manager-delete');

    Route::get('/exp-activity', 'ExpActivityController@index')->name('backend.exp-activity.index')->middleware('can:group-exp-activity-read');
    Route::get('/exp-activity/show/{id}', 'ExpActivityController@show')->name('backend.exp-activity.show')->middleware('can:group-exp-activity-read');
    Route::get('/exp-activity/create', 'ExpActivityController@create')->name('backend.exp-activity.create')->middleware('can:group-exp-activity-create');
    Route::post('/exp-activity/store', 'ExpActivityController@store')->name('backend.exp-activity.store')->middleware('can:group-exp-activity-create');
    Route::get('/exp-activity/edit/{id}', 'ExpActivityController@edit')->name('backend.exp-activity.edit')->middleware('can:group-exp-activity-update');
    Route::post('/exp-activity/update/{id}', 'ExpActivityController@update')->name('backend.exp-activity.update')->middleware('can:group-exp-activity-update');
    Route::post('/exp-activity/destroy', 'ExpActivityController@destroy')->name('backend.exp-activity.destroy')->middleware('can:group-exp-activity-delete');
    Route::get('/{languageCode}/exp-activity', 'ExpActivityController@index')->name(Utilities::bindRouteNameMultiLanguage('backend.exp-activity.index'))->where('languageCode', $listLanguageCode)->middleware('can:group-exp-activity-read');
    Route::get('/{languageCode}/exp-activity/show/{id}', 'ExpActivityController@show')->name(Utilities::bindRouteNameMultiLanguage('backend.exp-activity.show'))->where('languageCode', $listLanguageCode)->middleware('can:group-exp-activity-read');
    Route::get('/{languageCode}/exp-activity/create', 'ExpActivityController@create')->name(Utilities::bindRouteNameMultiLanguage('backend.exp-activity.create'))->where('languageCode', $listLanguageCode)->middleware('can:group-exp-activity-create');
    Route::post('/{languageCode}/exp-activity/store', 'ExpActivityController@store')->name(Utilities::bindRouteNameMultiLanguage('backend.exp-activity.store'))->where('languageCode', $listLanguageCode)->middleware('can:group-exp-activity-create');
    Route::get('/{languageCode}/exp-activity/edit/{id}', 'ExpActivityController@edit')->name(Utilities::bindRouteNameMultiLanguage('backend.exp-activity.edit'))->where('languageCode', $listLanguageCode)->middleware('can:group-exp-activity-update');
    Route::post('/{languageCode}/exp-activity/update/{id}', 'ExpActivityController@update')->name(Utilities::bindRouteNameMultiLanguage('backend.exp-activity.update'))->where('languageCode', $listLanguageCode)->middleware('can:group-exp-activity-update');
    Route::post('/{languageCode}/exp-activity/destroy', 'ExpActivityController@destroy')->name(Utilities::bindRouteNameMultiLanguage('backend.exp-activity.destroy'))->where('languageCode', $listLanguageCode)->middleware('can:group-exp-activity-delete');

    Route::get('/service', 'ServiceController@index')->name('backend.service.index')->middleware('can:group-service-read');
    Route::get('/service/show/{id}', 'ServiceController@show')->name('backend.service.show')->middleware('can:group-service-read');
    Route::get('/service/create', 'ServiceController@create')->name('backend.service.create')->middleware('can:group-service-create');
    Route::post('/service/store', 'ServiceController@store')->name('backend.service.store')->middleware('can:group-service-create');
    Route::get('/service/edit/{id}', 'ServiceController@edit')->name('backend.service.edit')->middleware('can:group-service-update');
    Route::post('/service/update/{id}', 'ServiceController@update')->name('backend.service.update')->middleware('can:group-service-update');
    Route::post('/service/destroy', 'ServiceController@destroy')->name('backend.service.destroy')->middleware('can:group-service-delete');
    Route::get('/{languageCode}/service', 'ServiceController@index')->name(Utilities::bindRouteNameMultiLanguage('backend.service.index'))->where('languageCode', $listLanguageCode)->middleware('can:group-service-read');
    Route::get('/{languageCode}/service/show/{id}', 'ServiceController@show')->name(Utilities::bindRouteNameMultiLanguage('backend.service.show'))->where('languageCode', $listLanguageCode)->middleware('can:group-service-read');
    Route::get('/{languageCode}/service/create', 'ServiceController@create')->name(Utilities::bindRouteNameMultiLanguage('backend.service.create'))->where('languageCode', $listLanguageCode)->middleware('can:group-service-create');
    Route::post('/{languageCode}/service/store', 'ServiceController@store')->name(Utilities::bindRouteNameMultiLanguage('backend.service.store'))->where('languageCode', $listLanguageCode)->middleware('can:group-service-create');
    Route::get('/{languageCode}/service/edit/{id}', 'ServiceController@edit')->name(Utilities::bindRouteNameMultiLanguage('backend.service.edit'))->where('languageCode', $listLanguageCode)->middleware('can:group-service-update');
    Route::post('/{languageCode}/service/update/{id}', 'ServiceController@update')->name(Utilities::bindRouteNameMultiLanguage('backend.service.update'))->where('languageCode', $listLanguageCode)->middleware('can:group-service-update');
    Route::post('/{languageCode}/service/destroy', 'ServiceController@destroy')->name(Utilities::bindRouteNameMultiLanguage('backend.service.destroy'))->where('languageCode', $listLanguageCode)->middleware('can:group-service-delete');

    Route::get('/amenity', 'AmenityController@index')->name('backend.amenity.index')->middleware('can:group-amenity-read');
    Route::get('/amenity/show/{id}', 'AmenityController@show')->name('backend.amenity.show')->middleware('can:group-amenity-read');
    Route::get('/amenity/create', 'AmenityController@create')->name('backend.amenity.create')->middleware('can:group-amenity-create');
    Route::post('/amenity/store', 'AmenityController@store')->name('backend.amenity.store')->middleware('can:group-amenity-create');
    Route::get('/amenity/edit/{id}', 'AmenityController@edit')->name('backend.amenity.edit')->middleware('can:group-amenity-update');
    Route::post('/amenity/update/{id}', 'AmenityController@update')->name('backend.amenity.update')->middleware('can:group-amenity-update');
    Route::post('/amenity/destroy', 'AmenityController@destroy')->name('backend.amenity.destroy')->middleware('can:group-amenity-delete');
    Route::post('/amenity/order-update', 'AmenityController@orderUpdate')->name('backend.amenity.orderUpdate')->middleware('can:group-amenity-update');
    Route::get('/{languageCode}/amenity', 'AmenityController@index')->name(Utilities::bindRouteNameMultiLanguage('backend.amenity.index'))->where('languageCode', $listLanguageCode)->middleware('can:group-amenity-read');
    Route::get('/{languageCode}/amenity/show/{id}', 'AmenityController@show')->name(Utilities::bindRouteNameMultiLanguage('backend.amenity.show'))->where('languageCode', $listLanguageCode)->middleware('can:group-amenity-read');
    Route::get('/{languageCode}/amenity/create', 'AmenityController@create')->name(Utilities::bindRouteNameMultiLanguage('backend.amenity.create'))->where('languageCode', $listLanguageCode)->middleware('can:group-amenity-create');
    Route::post('/{languageCode}/amenity/store', 'AmenityController@store')->name(Utilities::bindRouteNameMultiLanguage('backend.amenity.store'))->where('languageCode', $listLanguageCode)->middleware('can:group-amenity-create');
    Route::get('/{languageCode}/amenity/edit/{id}', 'AmenityController@edit')->name(Utilities::bindRouteNameMultiLanguage('backend.amenity.edit'))->where('languageCode', $listLanguageCode)->middleware('can:group-amenity-update');
    Route::post('/{languageCode}/amenity/update/{id}', 'AmenityController@update')->name(Utilities::bindRouteNameMultiLanguage('backend.amenity.update'))->where('languageCode', $listLanguageCode)->middleware('can:group-amenity-update');
    Route::post('/{languageCode}/amenity/destroy', 'AmenityController@destroy')->name(Utilities::bindRouteNameMultiLanguage('backend.amenity.destroy'))->where('languageCode', $listLanguageCode)->middleware('can:group-amenity-delete');
    Route::post('/{languageCode}/amenity/order-update', 'AmenityController@orderUpdate')->name(Utilities::bindRouteNameMultiLanguage('backend.amenity.orderUpdate'))->where('languageCode', $listLanguageCode)->middleware('can:group-amenity-update');

    Route::get('/testimonial', 'TestimonialController@index')->name('backend.testimonial.index')->middleware('can:group-testimonial-read');
    Route::get('/testimonial/show/{id}', 'TestimonialController@show')->name('backend.testimonial.show')->middleware('can:group-testimonial-read');
    Route::get('/testimonial/create', 'TestimonialController@create')->name('backend.testimonial.create')->middleware('can:group-testimonial-create');
    Route::post('/testimonial/store', 'TestimonialController@store')->name('backend.testimonial.store')->middleware('can:group-testimonial-create');
    Route::get('/testimonial/edit/{id}', 'TestimonialController@edit')->name('backend.testimonial.edit')->middleware('can:group-testimonial-update');
    Route::post('/testimonial/update/{id}', 'TestimonialController@update')->name('backend.testimonial.update')->middleware('can:group-testimonial-update');
    Route::post('/testimonial/destroy', 'TestimonialController@destroy')->name('backend.testimonial.destroy')->middleware('can:group-testimonial-delete');
    Route::post('/testimonial/order-update', 'TestimonialController@orderUpdate')->name('backend.testimonial.orderUpdate')->middleware('can:group-testimonial-update');
    Route::get('/{languageCode}/testimonial', 'TestimonialController@index')->name(Utilities::bindRouteNameMultiLanguage('backend.testimonial.index'))->where('languageCode', $listLanguageCode)->middleware('can:group-testimonial-read');
    Route::get('/{languageCode}/testimonial/show/{id}', 'TestimonialController@show')->name(Utilities::bindRouteNameMultiLanguage('backend.testimonial.show'))->where('languageCode', $listLanguageCode)->middleware('can:group-testimonial-read');
    Route::get('/{languageCode}/testimonial/create', 'TestimonialController@create')->name(Utilities::bindRouteNameMultiLanguage('backend.testimonial.create'))->where('languageCode', $listLanguageCode)->middleware('can:group-testimonial-create');
    Route::post('/{languageCode}/testimonial/store', 'TestimonialController@store')->name(Utilities::bindRouteNameMultiLanguage('backend.testimonial.store'))->where('languageCode', $listLanguageCode)->middleware('can:group-testimonial-create');
    Route::get('/{languageCode}/testimonial/edit/{id}', 'TestimonialController@edit')->name(Utilities::bindRouteNameMultiLanguage('backend.testimonial.edit'))->where('languageCode', $listLanguageCode)->middleware('can:group-testimonial-update');
    Route::post('/{languageCode}/testimonial/update/{id}', 'TestimonialController@update')->name(Utilities::bindRouteNameMultiLanguage('backend.testimonial.update'))->where('languageCode', $listLanguageCode)->middleware('can:group-testimonial-update');
    Route::post('/{languageCode}/testimonial/destroy', 'TestimonialController@destroy')->name(Utilities::bindRouteNameMultiLanguage('backend.testimonial.destroy'))->where('languageCode', $listLanguageCode)->middleware('can:group-testimonial-delete');
    Route::post('/{languageCode}/testimonial/order-update', 'TestimonialController@orderUpdate')->name(Utilities::bindRouteNameMultiLanguage('backend.testimonial.orderUpdate'))->where('languageCode', $listLanguageCode)->middleware('can:group-testimonial-update');

    Route::get('/page-config/{pageCode}', 'PageConfigController@index')->name('backend.page-config.index')->middleware('can:page-config-update');
    Route::post('/page-config/update/{pageCode}', 'PageConfigController@update')->name('backend.page-config.update')->middleware('can:page-config-update');
    Route::get('/{languageCode}/page-config/{pageCode}', 'PageConfigController@index')->name(Utilities::bindRouteNameMultiLanguage('backend.page-config.index'))->where('languageCode', $listLanguageCode)->middleware('can:page-config-update');
    Route::post('/{languageCode}/page-config/update/{pageCode}', 'PageConfigController@update')->name(Utilities::bindRouteNameMultiLanguage('backend.page-config.update'))->where('languageCode', $listLanguageCode)->middleware('can:page-config-update');

    Route::get('/config', 'ConfigController@index')->name('backend.config.index')->middleware('can:config-update');
    Route::post('/config/update', 'ConfigController@update')->name('backend.config.update')->middleware('can:config-update');
    Route::get('/{languageCode}/config', 'ConfigController@index')->name(Utilities::bindRouteNameMultiLanguage('backend.config.index'))->where('languageCode', $listLanguageCode)->middleware('can:config-update');
    Route::post('/{languageCode}/config/update', 'ConfigController@update')->name(Utilities::bindRouteNameMultiLanguage('backend.config.update'))->where('languageCode', $listLanguageCode)->middleware('can:config-update');

    Route::get('/source-data/' . PageConfigConsts::SOURCE_DATA_TYPE_ARTICLE, 'SourceDataController@articleIndex')->name('backend.sourceData.article.index')->middleware(['can:config-update', 'can:page-config-update']);
    Route::post('/source-data/' . PageConfigConsts::SOURCE_DATA_TYPE_ARTICLE . '/getById', 'SourceDataController@articleGetById')->name('backend.sourceData.article.getById')->middleware(['can:config-update', 'can:page-config-update']);
    Route::get('/source-data/' . PageConfigConsts::SOURCE_DATA_TYPE_FAQ, 'SourceDataController@faqIndex')->name('backend.sourceData.faq.index')->middleware(['can:config-update', 'can:page-config-update']);
    Route::post('/source-data/' . PageConfigConsts::SOURCE_DATA_TYPE_FAQ . '/getById', 'SourceDataController@faqGetById')->name('backend.sourceData.faq.getById')->middleware(['can:config-update', 'can:page-config-update']);
    Route::get('/source-data/' . PageConfigConsts::SOURCE_DATA_TYPE_CRUISE_ITINERARY, 'SourceDataController@cruiseItineraryIndex')->name('backend.sourceData.cruiseItinerary.index')->middleware(['can:config-update', 'can:page-config-update']);
    Route::post('/source-data/' . PageConfigConsts::SOURCE_DATA_TYPE_CRUISE_ITINERARY . '/getById', 'SourceDataController@cruiseItineraryGetById')->name('backend.sourceData.cruiseItinerary.getById')->middleware(['can:config-update', 'can:page-config-update']);
    Route::get('/source-data/' . PageConfigConsts::SOURCE_DATA_TYPE_CRUISE, 'SourceDataController@cruiseIndex')->name('backend.sourceData.cruise.index')->middleware(['can:config-update', 'can:page-config-update']);
    Route::post('/source-data/' . PageConfigConsts::SOURCE_DATA_TYPE_CRUISE . '/getById', 'SourceDataController@cruiseGetById')->name('backend.sourceData.cruise.getById')->middleware(['can:config-update', 'can:page-config-update']);
    Route::get('/source-data/' . PageConfigConsts::SOURCE_DATA_TYPE_CABIN, 'SourceDataController@cabinIndex')->name('backend.sourceData.cabin.index')->middleware(['can:config-update', 'can:page-config-update']);
    Route::post('/source-data/' . PageConfigConsts::SOURCE_DATA_TYPE_CABIN . '/getById', 'SourceDataController@cabinGetById')->name('backend.sourceData.cabin.getById')->middleware(['can:config-update', 'can:page-config-update']);
    Route::get('/source-data/' . PageConfigConsts::SOURCE_DATA_TYPE_SERVICE, 'SourceDataController@serviceIndex')->name('backend.sourceData.service.index')->middleware(['can:config-update', 'can:page-config-update']);
    Route::post('/source-data/' . PageConfigConsts::SOURCE_DATA_TYPE_SERVICE .'/getById', 'SourceDataController@serviceGetById')->name('backend.sourceData.service.getById')->middleware(['can:config-update', 'can:page-config-update']);
    Route::get('/source-data/' . PageConfigConsts::SOURCE_DATA_TYPE_AMENITY, 'SourceDataController@amenityIndex')->name('backend.sourceData.amenity.index')->middleware(['can:config-update', 'can:page-config-update']);
    Route::post('/source-data/' . PageConfigConsts::SOURCE_DATA_TYPE_AMENITY . '/getById', 'SourceDataController@amenityGetById')->name('backend.sourceData.amenity.getById')->middleware(['can:config-update', 'can:page-config-update']);
    Route::get('/source-data/' . PageConfigConsts::SOURCE_DATA_TYPE_EXP_ACTIVITY, 'SourceDataController@expActivityIndex')->name('backend.sourceData.expActivity.index')->middleware(['can:config-update', 'can:page-config-update']);
    Route::post('/source-data/' . PageConfigConsts::SOURCE_DATA_TYPE_EXP_ACTIVITY . '/getById', 'SourceDataController@expActivityGetById')->name('backend.sourceData.expActivity.getById')->middleware(['can:config-update', 'can:page-config-update']);
    Route::get('/{languageCode}/source-data/' . PageConfigConsts::SOURCE_DATA_TYPE_ARTICLE, 'SourceDataController@articleIndex')->name(Utilities::bindRouteNameMultiLanguage('backend.sourceData.article.index'))->where('languageCode', $listLanguageCode)->middleware(['can:config-update', 'can:page-config-update']);
    Route::post('/{languageCode}/source-data/' . PageConfigConsts::SOURCE_DATA_TYPE_ARTICLE . '/getById', 'SourceDataController@articleGetById')->name(Utilities::bindRouteNameMultiLanguage('backend.sourceData.article.getById'))->where('languageCode', $listLanguageCode)->middleware(['can:config-update', 'can:page-config-update']);
    Route::get('/{languageCode}/source-data/' . PageConfigConsts::SOURCE_DATA_TYPE_FAQ, 'SourceDataController@faqIndex')->name(Utilities::bindRouteNameMultiLanguage('backend.sourceData.faq.index'))->where('languageCode', $listLanguageCode)->middleware(['can:config-update', 'can:page-config-update']);
    Route::post('/{languageCode}/source-data/' . PageConfigConsts::SOURCE_DATA_TYPE_FAQ .'/getById', 'SourceDataController@faqGetById')->name(Utilities::bindRouteNameMultiLanguage('backend.sourceData.faq.getById'))->where('languageCode', $listLanguageCode)->middleware(['can:config-update', 'can:page-config-update']);
    Route::get('/{languageCode}/source-data/' . PageConfigConsts::SOURCE_DATA_TYPE_CRUISE_ITINERARY, 'SourceDataController@cruiseItineraryIndex')->name(Utilities::bindRouteNameMultiLanguage('backend.sourceData.cruiseItinerary.index'))->where('languageCode', $listLanguageCode)->middleware(['can:config-update', 'can:page-config-update']);
    Route::post('/{languageCode}/source-data/' . PageConfigConsts::SOURCE_DATA_TYPE_CRUISE_ITINERARY .'/getById', 'SourceDataController@cruiseItineraryGetById')->name(Utilities::bindRouteNameMultiLanguage('backend.sourceData.cruiseItinerary.getById'))->where('languageCode', $listLanguageCode)->middleware(['can:config-update', 'can:page-config-update']);
    Route::get('/{languageCode}/source-data/' . PageConfigConsts::SOURCE_DATA_TYPE_CRUISE, 'SourceDataController@cruiseIndex')->name(Utilities::bindRouteNameMultiLanguage('backend.sourceData.cruise.index'))->where('languageCode', $listLanguageCode)->middleware(['can:config-update', 'can:page-config-update']);
    Route::post('/{languageCode}/source-data/' . PageConfigConsts::SOURCE_DATA_TYPE_CRUISE .'/getById', 'SourceDataController@cruiseGetById')->name(Utilities::bindRouteNameMultiLanguage('backend.sourceData.cruise.getById'))->where('languageCode', $listLanguageCode)->middleware(['can:config-update', 'can:page-config-update']);
    Route::get('/{languageCode}/source-data/' . PageConfigConsts::SOURCE_DATA_TYPE_CABIN, 'SourceDataController@cabinIndex')->name(Utilities::bindRouteNameMultiLanguage('backend.sourceData.cabin.index'))->where('languageCode', $listLanguageCode)->middleware(['can:config-update', 'can:page-config-update']);
    Route::post('/{languageCode}/source-data/' . PageConfigConsts::SOURCE_DATA_TYPE_CABIN .'/getById', 'SourceDataController@cabinGetById')->name(Utilities::bindRouteNameMultiLanguage('backend.sourceData.cabin.getById'))->where('languageCode', $listLanguageCode)->middleware(['can:config-update', 'can:page-config-update']);
    Route::get('/{languageCode}/source-data/' . PageConfigConsts::SOURCE_DATA_TYPE_SERVICE, 'SourceDataController@serviceIndex')->name(Utilities::bindRouteNameMultiLanguage('backend.sourceData.service.index'))->where('languageCode', $listLanguageCode)->middleware(['can:config-update', 'can:page-config-update']);
    Route::post('/{languageCode}/source-data/' . PageConfigConsts::SOURCE_DATA_TYPE_SERVICE .'/getById', 'SourceDataController@serviceGetById')->name(Utilities::bindRouteNameMultiLanguage('backend.sourceData.service.getById'))->where('languageCode', $listLanguageCode)->middleware(['can:config-update', 'can:page-config-update']);
    Route::get('/{languageCode}/source-data/' . PageConfigConsts::SOURCE_DATA_TYPE_AMENITY, 'SourceDataController@amenityIndex')->name(Utilities::bindRouteNameMultiLanguage('backend.sourceData.amenity.index'))->where('languageCode', $listLanguageCode)->middleware(['can:config-update', 'can:page-config-update']);
    Route::post('/{languageCode}/source-data/' . PageConfigConsts::SOURCE_DATA_TYPE_AMENITY .'/getById', 'SourceDataController@amenityGetById')->name(Utilities::bindRouteNameMultiLanguage('backend.sourceData.amenity.getById'))->where('languageCode', $listLanguageCode)->middleware(['can:config-update', 'can:page-config-update']);
    Route::get('/{languageCode}/source-data/' . PageConfigConsts::SOURCE_DATA_TYPE_EXP_ACTIVITY, 'SourceDataController@expActivityIndex')->name(Utilities::bindRouteNameMultiLanguage('backend.sourceData.expActivity.index'))->where('languageCode', $listLanguageCode)->middleware(['can:config-update', 'can:page-config-update']);
    Route::post('/{languageCode}/source-data/' . PageConfigConsts::SOURCE_DATA_TYPE_EXP_ACTIVITY .'/getById', 'SourceDataController@expActivityGetById')->name(Utilities::bindRouteNameMultiLanguage('backend.sourceData.expActivity.getById'))->where('languageCode', $listLanguageCode)->middleware(['can:config-update', 'can:page-config-update']);

    Route::get('/cruise', 'CruiseController@index')->name('backend.cruise.index')->middleware('can:cruise-read');
    Route::get('/cruise/show/{id}', 'CruiseController@show')->name('backend.cruise.show')->middleware('can:cruise-read');
    Route::get('/cruise/create', 'CruiseController@create')->name('backend.cruise.create')->middleware('can:cruise-create');
    Route::post('/cruise/store', 'CruiseController@store')->name('backend.cruise.store')->middleware('can:cruise-create');
    Route::get('/cruise/edit/{id}', 'CruiseController@edit')->name('backend.cruise.edit')->middleware('can:cruise-update');
    Route::put('/cruise/update/{id}', 'CruiseController@update')->name('backend.cruise.update')->middleware('can:cruise-update');
    Route::post('/cruise/destroy', 'CruiseController@destroy')->name('backend.cruise.destroy')->middleware('can:cruise-delete');
    Route::post('/cruise/{id}/storeItinerary', 'CruiseController@storeItinerary')->name('backend.cruise.store-itinerary')->middleware('can:cruise-create');
//    Route::put('/cruise/{id}/updateItinerary', 'CruiseController@updateItinerary')->name('backend.cruise.update-itinerary')->middleware('can:cruise-create');
    Route::delete('/cruise/{id}/destroyItinerary', 'CruiseController@destroyItinerary')->name('backend.cruise.destroy-itinerary')->middleware('can:cruise-create');
    Route::get('/{languageCode}/cruise', 'CruiseController@index')->name(Utilities::bindRouteNameMultiLanguage('backend.cruise.index'))->where('languageCode', $listLanguageCode)->middleware('can:cruise-read');
    Route::get('/{languageCode}/cruise/show/{id}', 'CruiseController@show')->name(Utilities::bindRouteNameMultiLanguage('backend.cruise.show'))->where('languageCode', $listLanguageCode)->middleware('can:cruise-read');
    Route::get('/{languageCode}/cruise/create', 'CruiseController@create')->name(Utilities::bindRouteNameMultiLanguage('backend.cruise.create'))->where('languageCode', $listLanguageCode)->middleware('can:cruise-create');
    Route::post('/{languageCode}/cruise/store', 'CruiseController@store')->name(Utilities::bindRouteNameMultiLanguage('backend.cruise.store'))->where('languageCode', $listLanguageCode)->middleware('can:cruise-create');
    Route::get('/{languageCode}/cruise/edit/{id}', 'CruiseController@edit')->name(Utilities::bindRouteNameMultiLanguage('backend.cruise.edit'))->where('languageCode', $listLanguageCode)->middleware('can:cruise-update');
    Route::put('/{languageCode}/cruise/update/{id}', 'CruiseController@update')->name(Utilities::bindRouteNameMultiLanguage('backend.cruise.update'))->where('languageCode', $listLanguageCode)->middleware('can:cruise-update');
    Route::post('/{languageCode}/cruise/destroy', 'CruiseController@destroy')->name(Utilities::bindRouteNameMultiLanguage('backend.cruise.destroy'))->where('languageCode', $listLanguageCode)->middleware('can:cruise-delete');
    Route::post('/{languageCode}/cruise/{id}/storeItinerary', 'CruiseController@storeItinerary')->name(Utilities::bindRouteNameMultiLanguage('backend.cruise.store-itinerary'))->where('languageCode', $listLanguageCode)->middleware('can:cruise-create');
//    Route::put('/{languageCode}/cruise/{id}/updateItinerary', 'CruiseController@updateItinerary')->name(Utilities::bindRouteNameMultiLanguage('backend.cruise.update-itinerary'))->where('languageCode', $listLanguageCode)->middleware('can:cruise-create');
    Route::delete('/{languageCode}/cruise/{id}/destroyItinerary', 'CruiseController@destroyItinerary')->name(Utilities::bindRouteNameMultiLanguage('backend.cruise.destroy-itinerary'))->where('languageCode', $listLanguageCode)->middleware('can:cruise-create');

    Route::get('/itinerary', 'ItineraryController@index')->name('backend.itinerary.index')->middleware('can:itinerary-read');
    Route::get('/itinerary/show/{id}', 'ItineraryController@show')->name('backend.itinerary.show')->middleware('can:itinerary-read');
    Route::get('/itinerary/create', 'ItineraryController@create')->name('backend.itinerary.create')->middleware('can:itinerary-create');
    Route::post('/itinerary/store', 'ItineraryController@store')->name('backend.itinerary.store')->middleware('can:itinerary-create');
    Route::get('/itinerary/edit/{id}', 'ItineraryController@edit')->name('backend.itinerary.edit')->middleware('can:itinerary-update');
    Route::put('/itinerary/update/{id}', 'ItineraryController@update')->name('backend.itinerary.update')->middleware('can:itinerary-update');
    Route::post('/itinerary/destroy', 'ItineraryController@destroy')->name('backend.itinerary.destroy')->middleware('can:itinerary-delete');
    Route::get('/{languageCode}/itinerary', 'ItineraryController@index')->name(Utilities::bindRouteNameMultiLanguage('backend.itinerary.index'))->where('languageCode', $listLanguageCode)->middleware('can:itinerary-read');
    Route::get('/{languageCode}/itinerary/show/{id}', 'ItineraryController@show')->name(Utilities::bindRouteNameMultiLanguage('backend.itinerary.show'))->where('languageCode', $listLanguageCode)->middleware('can:itinerary-read');
    Route::get('/{languageCode}/itinerary/create', 'ItineraryController@create')->name(Utilities::bindRouteNameMultiLanguage('backend.itinerary.create'))->where('languageCode', $listLanguageCode)->middleware('can:itinerary-create');
    Route::post('/{languageCode}/itinerary/store', 'ItineraryController@store')->name(Utilities::bindRouteNameMultiLanguage('backend.itinerary.store'))->where('languageCode', $listLanguageCode)->middleware('can:itinerary-create');
    Route::get('/{languageCode}/itinerary/edit/{id}', 'ItineraryController@edit')->name(Utilities::bindRouteNameMultiLanguage('backend.itinerary.edit'))->where('languageCode', $listLanguageCode)->middleware('can:itinerary-update');
    Route::put('/{languageCode}/itinerary/update/{id}', 'ItineraryController@update')->name(Utilities::bindRouteNameMultiLanguage('backend.itinerary.update'))->where('languageCode', $listLanguageCode)->middleware('can:itinerary-update');
    Route::post('/{languageCode}/itinerary/destroy', 'ItineraryController@destroy')->name(Utilities::bindRouteNameMultiLanguage('backend.itinerary.destroy'))->where('languageCode', $listLanguageCode)->middleware('can:itinerary-delete');
});
