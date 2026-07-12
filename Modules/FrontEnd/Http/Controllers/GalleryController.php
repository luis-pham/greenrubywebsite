<?php

namespace Modules\FrontEnd\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\BackEnd\Helpers\Utilities;
use Modules\BackEnd\Services\AppExpActivityService;
use Modules\FrontEnd\Constants\PageCodeConsts;
use Modules\FrontEnd\Helpers\FeLanguageUtils;
use Modules\FrontEnd\Helpers\FeUtils;
use Modules\FrontEnd\Services\GalleryService;

class GalleryController
{
    private string $baseView = "frontend::gallery.";
    public function index(Request $request){
        $language = FeLanguageUtils::getCurrentLanguage();
        $languageCode = $request->route('languageCode');

        $slug = $request->route('slug');
        $pageSize = config('frontend.paginationGalleryLimit');
        $page = $request->route('page');
        $page = $page < 1 ? 1 : $page;

        if ($slug) {
            $canonicalSlug = GalleryService::getCanonicalSlug($slug, $language->code);
            if (!$canonicalSlug) {
                abort(404);
            }

            if ($canonicalSlug !== $slug) {
                $redirectRoute = $page > 1
                    ? 'frontend.gallery.category.paginate'
                    : 'frontend.gallery.category';
                $redirectParams = ['slug' => $canonicalSlug];
                if ($page > 1) {
                    $redirectParams['page'] = $page;
                }

                return redirect(
                    FeUtils::frontendRoute($redirectRoute, $redirectParams, $languageCode),
                    301
                );
            }

            $slug = $canonicalSlug;
        }

        $galleries = GalleryService::getConfigGalleries($language->id,$slug);

        $galleries = collect($galleries)->each(function($item){
            $item->name = $item->title;
            $item->link = FeUtils::getImageLink($item->link);
            $item->thumbnail = FeUtils::getImageLink($item->thumbnail ?? $item->link);
            return $item;
        });

        $galleryFilters = GalleryService::getGalleryFilter($language->id);

        $paginated = new LengthAwarePaginator(
            $galleries->forPage($page, $pageSize),
            $galleries->count(),
            $pageSize,
            $page,
        );

        if($galleries->count() > 0 && $paginated->lastPage() < $page){
            abort(404);
        }

        $listExperience = AppExpActivityService::getExpActivityFeatured($language->id);
        $listExperience = $listExperience->map(function($item) use ($languageCode){
            $slug = Utilities::convertToAlias($item->name);
            $item->url = route(Utilities::getRouteName('frontend.experience.show'),['languageCode' => $languageCode,'slug' => $slug,'id' => $item->id]);
            return $item;
        });

        $config = Utilities::getAllConfig($language);

        if ($slug) {
            $hubCanonicalUrl = $page == 1
                ? FeUtils::frontendRoute('frontend.gallery.category', ['slug' => $slug], $languageCode)
                : FeUtils::frontendRoute('frontend.gallery.category.paginate', ['slug' => $slug, 'page' => $page], $languageCode);
        } else {
            $hubCanonicalUrl = $page == 1
                ? FeUtils::frontendRoute('frontend.gallery.index', [], $languageCode)
                : FeUtils::frontendRoute('frontend.gallery.index.paginate', ['page' => $page], $languageCode);
        }

        $hubSeo = FeUtils::resolveHubSeo(
            PageCodeConsts::GALLERY,
            $language,
            fn () => FeUtils::bindWebsiteTitle($config['website-name'], $config['website-slogan']),
            $config['website-description']
        );
        FeUtils::applyHubSeoMeta($hubSeo, $hubCanonicalUrl, $config);

        return view($this->baseView . __FUNCTION__, compact('paginated','listExperience','galleryFilters', 'hubSeo', 'hubCanonicalUrl'));
    }
}
