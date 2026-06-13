<?php

use Illuminate\Support\Facades\Route;
use Modules\BackEnd\Services\AdLanguageService;
use Modules\BackEnd\Helpers\Utilities;

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

Route::middleware('guest')->group(function() {
    Route::get('/thumbnail{link}', 'ImageController@thumbnail')->where('link', '(.*)')->name('frontend.image.thumbnail');

    Route::get('/sitemap.xml', 'SitemapController@index')->name('frontend.sitemap.index');
    Route::get('/sitemap-page.xml', 'SitemapController@page')->name('frontend.sitemap.page');
    Route::get('/sitemap-article.xml', 'SitemapController@article')->name('frontend.sitemap.article');
    Route::get('/sitemap-category-{type}.xml', 'SitemapController@category')->where('type', 'article')->name('frontend.sitemap.category');
    Route::get('/sitemap-experience.xml', 'SitemapController@experience')->name('frontend.sitemap.experience');
    Route::get('/sitemap-service.xml', 'SitemapController@service')->name('frontend.sitemap.service');
    Route::get('/sitemap-itinerary.xml', 'SitemapController@itinerary')->name('frontend.sitemap.itinerary');
    Route::get('/sitemap-cruise.xml', 'SitemapController@cruise')->name('frontend.sitemap.cruise');
    Route::get('/sitemap-category-faq.xml', 'SitemapController@faq')->name('frontend.sitemap.faq');
    Route::get('/sitemap-category-gallery.xml', 'SitemapController@gallery')->name('frontend.sitemap.gallery');
});

Route::middleware(['guest', 'language.frontend'])->group(function() {
    $listLanguage = AdLanguageService::getAll();
    $listLanguageCode = implode('|', $listLanguage->where('is_default', false)->pluck('code')->toArray());

    Route::get('', 'IndexController@index')->name('frontend.index');
    Route::get('/{languageCode}', 'IndexController@index')->where('languageCode', $listLanguageCode)->name(Utilities::bindRouteNameMultiLanguage('frontend.index'));

    Route::get('/booking', 'BookingController@index')->name('frontend.booking');
    Route::get('/{languageCode}/booking', 'BookingController@index')->where('languageCode', $listLanguageCode)->name(Utilities::bindRouteNameMultiLanguage('frontend.booking'));

    Route::post('/api/search-tour', 'IndexController@searchTour')->name('frontend.index.search-tour');
    Route::post('/api/{languageCode}/search-tour', 'IndexController@searchTour')->where('languageCode', $listLanguageCode)->name(Utilities::bindRouteNameMultiLanguage('frontend.index.search-tour'));

    Route::get('/api/cabins/getById', 'ApiCabinController@getById')->where('languageCode', $listLanguageCode)->name('frontend.api.cabin.getById');
    Route::get('/api/{languageCode}/cabins/getById', 'ApiCabinController@getById')->where('languageCode', $listLanguageCode)->name(Utilities::bindRouteNameMultiLanguage('frontend.api.cabin.getById'));

    Route::get('/api/services/getById', 'ServiceController@getById')->where('languageCode', $listLanguageCode)->name('frontend.api.service.getById');
    Route::get('/api/{languageCode}/services/getById', 'ServiceController@getById')->where('languageCode', $listLanguageCode)->name(Utilities::bindRouteNameMultiLanguage('frontend.api.service.getById'));

    Route::get('/api/exp-activities/getById', 'PublicDataController@expActivityGetById')->name('api.expActivity.getById');
    Route::get('/api/{languageCode}/exp-activities/getById', 'PublicDataController@expActivityGetById')->where('languageCode', $listLanguageCode)->name(Utilities::bindRouteNameMultiLanguage('api.expActivity.getById'));

    Route::post('/api/cookie/consent', 'CookieController@consent')->name('frontend.cookie.consent');

    Route::get('/itinerary','ItineraryController@index')->name('frontend.itinerary.index');
    Route::get('/{languageCode}/hanh-trinh','ItineraryController@index')->where('languageCode',$listLanguageCode)->name(Utilities::bindRouteNameMultiLanguage('frontend.itinerary.index'));
    Route::get('/itinerary/{slug}-{cruise_id}-{itinerary_id}.html','ItineraryController@show')
        ->where('slug','[A-Za-z0-9_\-]+')
        ->name('frontend.itinerary.show');
    Route::get('/{languageCode}/hanh-trinh/{slug}-{cruise_id}-{itinerary_id}.html','ItineraryController@show')
        ->where('languageCode',$listLanguageCode)
        ->where('slug','[A-Za-z0-9_\-]+')
        ->name(Utilities::bindRouteNameMultiLanguage('frontend.itinerary.show'));

    Route::get('/cruise/{slug}-{id}.html','CruiseController@show')
        ->where('slug','[A-Za-z0-9_\-]+')
        ->name('frontend.cruise.show');
    Route::get('/{languageCode}/du-thuyen/{slug}-{id}.html','CruiseController@show')
        ->where('languageCode',$listLanguageCode)
        ->where('slug','[A-Za-z0-9_\-]+')
        ->name(Utilities::bindRouteNameMultiLanguage('frontend.cruise.show'));

    Route::get('/gallery','GalleryController@index')->name('frontend.gallery.index');
    Route::get('/{languageCode}/thu-vien','GalleryController@index')->where('languageCode',$listLanguageCode)->name(Utilities::bindRouteNameMultiLanguage('frontend.gallery.index'));
    Route::get('/gallery/page-{page}','GalleryController@index')->name('frontend.gallery.index.paginate');
    Route::get('/{languageCode}/thu-vien/trang-{page}','GalleryController@index')->where('languageCode',$listLanguageCode)->name(Utilities::bindRouteNameMultiLanguage('frontend.gallery.index.paginate'));

    Route::get('/gallery/{slug}','GalleryController@index')->name('frontend.gallery.category');
    Route::get('/{languageCode}/thu-vien/{slug}','GalleryController@index')->where('languageCode',$listLanguageCode)->name(Utilities::bindRouteNameMultiLanguage('frontend.gallery.category'));
    Route::get('/gallery/{slug}/page-{page}','GalleryController@index')->name('frontend.gallery.category.paginate');
    Route::get('/{languageCode}/thu-vien/{slug}/trang-{page}','GalleryController@index')->where('languageCode',$listLanguageCode)->name(Utilities::bindRouteNameMultiLanguage('frontend.gallery.category.paginate'));

    Route::get('contact','ContactController@index')->name('frontend.contact.index');
    Route::get('/{languageCode}/lien-he','ContactController@index')->where('languageCode',$listLanguageCode)->name(Utilities::bindRouteNameMultiLanguage('frontend.contact.index'));
    Route::post('/contact', 'ContactController@request')->middleware('throttle:5,1')->name('frontend.contact.request');
    Route::post('/{languageCode}/lien-he', 'ContactController@request')->middleware('throttle:5,1')->where('languageCode',$listLanguageCode)->name(Utilities::bindRouteNameMultiLanguage('frontend.contact.request'));

    Route::redirect('/experience', '/experiences', 301);
    Route::get('/experiences', 'ExperienceController@index')->name('frontend.experience.index');
    Route::get('/{languageCode}/hoat-dong-trai-nghiem', 'ExperienceController@index')->where('languageCode', $listLanguageCode)->name(Utilities::bindRouteNameMultiLanguage('frontend.experience.index'));

    Route::redirect('/service', '/services', 301);
    Route::get('/services', 'ServiceController@index')->name('frontend.service.index');
    Route::get('/{languageCode}/dich-vu', 'ServiceController@index')->where('languageCode', $listLanguageCode)->name(Utilities::bindRouteNameMultiLanguage('frontend.service.index'));

    Route::get('/experience/{slug}-{id}.html', function (string $slug, $id) {
        return redirect()->route('frontend.experience.show', ['slug' => $slug, 'id' => $id], 301);
    })->where('slug', '[A-Za-z0-9_\-]+');
    Route::get('/experiences/{slug}-{id}.html', 'ExperienceController@show')->where('slug', '[A-Za-z0-9_\-]+')->name('frontend.experience.show');
    Route::get('/{languageCode}/hoat-dong-trai-nghiem/{slug}-{id}.html', 'ExperienceController@show')->where('languageCode', $listLanguageCode)->where('slug', '[A-Za-z0-9_\-]+')->name(Utilities::bindRouteNameMultiLanguage('frontend.experience.show'));

    Route::get('/about-us', 'AboutController@index')->name('frontend.about.index');
    Route::get('/{languageCode}/gioi-thieu', 'AboutController@index')->where('languageCode', $listLanguageCode)->name(Utilities::bindRouteNameMultiLanguage('frontend.about.index'));


    Route::get('/faq', 'FaqController@index')->name('frontend.faq.index');
    Route::get('/faq/page-{page}', 'FaqController@index')->name('frontend.faq.index.paginate');
    Route::get('/{languageCode}/cau-hoi-thuong-gap', 'FaqController@index')->where('languageCode', $listLanguageCode)->name(Utilities::bindRouteNameMultiLanguage('frontend.faq.index'));
    Route::get('/{languageCode}/cau-hoi-thuong-gap/trang-{page}', 'FaqController@index')->where('languageCode', $listLanguageCode)->name(Utilities::bindRouteNameMultiLanguage('frontend.faq.index.paginate'));

    Route::get('/faq/{slug}', 'FaqController@category')->name('frontend.faq.category');
    Route::get('/faq/{slug}/page-{page}', 'FaqController@category')->name('frontend.faq.category.paginate');
    Route::get('/{languageCode}/cau-hoi-thuong-gap/{slug}', 'FaqController@category')->where('languageCode', $listLanguageCode)->name(Utilities::bindRouteNameMultiLanguage('frontend.faq.category'));
    Route::get('/{languageCode}/cau-hoi-thuong-gap/{slug}/trang-{page}', 'FaqController@category')->where('languageCode', $listLanguageCode)->name(Utilities::bindRouteNameMultiLanguage('frontend.faq.category.paginate'));

    Route::get('/blog/{slug}-{id}.html', 'ArticleController@show')->where('slug', '[A-Za-z0-9_\-]+')->name('frontend.article.show');
    Route::get('/{languageCode}/blog/{slug}-{id}.html', 'ArticleController@show')->where('languageCode', $listLanguageCode)->where('slug', '[A-Za-z0-9_\-]+')->name(Utilities::bindRouteNameMultiLanguage('frontend.article.show'));

    Route::get('/blog', 'ArticleController@index')->name('frontend.article.index');
    Route::get('/blog/page-{page}', 'ArticleController@index')->name('frontend.article.index.paginate');
    Route::get('/{languageCode}/blog', 'ArticleController@index')->where('languageCode', $listLanguageCode)->name(Utilities::bindRouteNameMultiLanguage('frontend.article.index'));
    Route::get('/{languageCode}/blog/trang-{page}', 'ArticleController@index')->where('languageCode', $listLanguageCode)->name(Utilities::bindRouteNameMultiLanguage('frontend.article.index.paginate'));

    Route::get('/blog/{slug}', 'ArticleController@category')->name('frontend.article.category');
    Route::get('/blog/{slug}/page-{page}', 'ArticleController@category')->name('frontend.article.category.paginate');
    Route::get('/{languageCode}/blog/{slug}', 'ArticleController@category')->where('languageCode', $listLanguageCode)->name(Utilities::bindRouteNameMultiLanguage('frontend.article.category'));
    Route::get('/{languageCode}/blog/{slug}/trang-{page}', 'ArticleController@category')->where('languageCode', $listLanguageCode)->name(Utilities::bindRouteNameMultiLanguage('frontend.article.category.paginate'));

    Route::get('/legal', 'PageController@legal')->name('frontend.page.legal');
    Route::get('/{languageCode}/phap-ly', 'PageController@legal')->where('languageCode', $listLanguageCode)->name(Utilities::bindRouteNameMultiLanguage('frontend.page.legal'));
    Route::get('/safety-policies', 'PageController@safetyPolicies')->name('frontend.page.safety-policies');
    Route::get('/{languageCode}/chinh-sach-an-toan', 'PageController@safetyPolicies')->where('languageCode', $listLanguageCode)->name(Utilities::bindRouteNameMultiLanguage('frontend.page.safety-policies'));
    Route::get('/terms-and-conditions', 'PageController@termsAndConditions')->name('frontend.page.terms-and-conditions');
    Route::get('/{languageCode}/dieu-khoan-dieu-kien', 'PageController@termsAndConditions')->where('languageCode', $listLanguageCode)->name(Utilities::bindRouteNameMultiLanguage('frontend.page.terms-and-conditions'));
    Route::get('/privacy-policy', 'PageController@privacyPolicy')->name('frontend.page.privacy-policy');
    Route::get('/{languageCode}/chinh-sach-bao-mat', 'PageController@privacyPolicy')->where('languageCode', $listLanguageCode)->name(Utilities::bindRouteNameMultiLanguage('frontend.page.privacy-policy'));
    Route::get('/payment-methods', 'PageController@paymentMethods')->name('frontend.page.payment-methods');
    Route::get('/{languageCode}/phuong-thuc-thanh-toan', 'PageController@paymentMethods')->where('languageCode', $listLanguageCode)->name(Utilities::bindRouteNameMultiLanguage('frontend.page.payment-methods'));

});
