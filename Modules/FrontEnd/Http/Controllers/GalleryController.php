<?php

namespace Modules\FrontEnd\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\BackEnd\Helpers\Utilities;
use Modules\BackEnd\Services\AppExpActivityService;
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
        $title = FeUtils::bindWebsiteTitle($config['website-name'], $config['website-slogan']);

        if ($slug) {
            $url = $page == 1
                ? route(Utilities::getRouteName('frontend.gallery.category'), ['languageCode' => $languageCode, 'slug' => $slug])
                : route(Utilities::getRouteName('frontend.gallery.category.paginate'), ['languageCode' => $languageCode, 'slug' => $slug, 'page' => $page]);
        } else {
            $url = $page == 1
                ? route(Utilities::getRouteName('frontend.gallery.index'), ['languageCode' => $languageCode])
                : route(Utilities::getRouteName('frontend.gallery.index.paginate'), ['languageCode' => $languageCode, 'page' => $page]);
        }


        \SEO::setTitle($title);
        \SEO::setDescription($config['website-description']);
        \SEO::setCanonical($url);

        \OpenGraph::setSiteName($config['website-name']);
        \OpenGraph::setTitle($title);
        \OpenGraph::setUrl($url);
        \OpenGraph::addImage(\URL::to('/') . config('frontend.organizationLogoSocial.url'), [
            'width' => config('frontend.organizationLogoSocial.width'),
            'height' => config('frontend.organizationLogoSocial.height'),
        ]);

        \TwitterCard::setType('summary');
        \TwitterCard::setTitle($title);
        \TwitterCard::setDescription($config['website-description']);
        \TwitterCard::setUrl($url);
        \TwitterCard::setImage(\URL::to('/') . config('frontend.organizationLogoSocial.url'));

        return view($this->baseView . __FUNCTION__, compact('paginated','listExperience','galleryFilters'));
    }
}
