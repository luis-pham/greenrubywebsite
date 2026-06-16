<?php

namespace Modules\FrontEnd\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\BackEnd\Helpers\Utilities;
use Modules\BackEnd\Services\AppGroupService;
use Modules\BackEnd\Services\AppTestimonialService;
use Modules\FrontEnd\Constants\PageCodeConsts;
use Modules\FrontEnd\Helpers\FeLanguageUtils;
use Modules\FrontEnd\Helpers\FeUtils;
use Modules\FrontEnd\Services\AppCabinService;
use Modules\FrontEnd\Services\AppCruiseItineraryService;
use Modules\FrontEnd\Services\AppCruiseService;
use Modules\FrontEnd\Services\AppFaqService;
use Modules\FrontEnd\Services\AppServiceService;

class ItineraryController extends Controller
{
    private $baseView = "frontend::itinerary.";

    public function index(Request $request) {
        $language = FeLanguageUtils::getCurrentLanguage();
        $languageCode = $request->route('languageCode');

        $listItinerary = AppCruiseItineraryService::getScheduledItineraries($language->id);

        $listItineraryYetScheduled = [];

        $listItinerary->each(function($item) use(&$listItineraryYetScheduled){
           if(!$item->price){
               $listItineraryYetScheduled[] = $item;
           }
        });

        if(count($listItineraryYetScheduled) > 0){
            $listCruise = AppCruiseService::getAll($language->id);
            $listCruiseId = $listCruise->pluck('id')->toArray();
            $listMinPrice = AppCabinService::getMinPriceByCruiseId($listCruiseId);

            $cheapestByDuration = $listMinPrice->groupBy('duration')->map(function($group){
                return $group->sortBy('min_price')->first();
            });

            foreach($listItineraryYetScheduled as $item){
                $matchedPrice = $cheapestByDuration->where('duration', $item->duration)->first();
                if($matchedPrice){
                    $matchedCruise = $listCruise->where('id', $matchedPrice->cruise_id)->first();
                    $item->price = $matchedPrice->min_price;
                    $item->cruise_id = $matchedCruise->id;
                    $item->cruise_name = $matchedCruise->name;
                }

                if(!$item->price || !$item->cruise_id || !$item->cruise_name){
                    $listItinerary->filter(fn($i) => $i->id !== $item->id)->values();
                }
            }
        }

        $listItinerary = $listItinerary->map(function($item){
            $item->image_link = FeUtils::getImageLink($item->image_link);
            return $item;
        });

        $listInclusiveService = AppServiceService::getAllByType(config('backend.appServiceType.inclusive'),$language->id);

        $listInclusiveService = $listInclusiveService->map(function($service){
            $service->icon = Utilities::getFileLink($service->image_link);
            return $service;
        });

        $listFaq = AppFaqService::getPaging([],$language->id);

        $menuUrlActive = route(Utilities::getRouteName('frontend.itinerary.index'), ['languageCode' => $languageCode]);

        $listHeroBannerImages = AppCruiseItineraryService::getHeroBannerImages($language->id, 2);
        $listBanner = [];
        foreach ($listHeroBannerImages as $index => $image) {
            $banner = new \stdClass();
            $banner->link = $image->link;
            $banner->title = $index === 0 ? __('frontend::itineraryIndex.section_cover_title') : '';
            $banner->description = $index === 0 ? __('frontend::itineraryIndex.section_cover_description') : '';
            $listBanner[] = $banner;
        }

        $config = Utilities::getAllConfig($language);
        $hubCanonicalUrl = FeUtils::frontendRoute('frontend.itinerary.index', [], $languageCode);
        $hubSeo = FeUtils::resolveHubSeo(
            PageCodeConsts::ITINERARY,
            $language,
            fn () => FeUtils::bindWebsiteTitle($config['website-name'], $config['website-slogan']),
            $config['website-description']
        );
        FeUtils::applyHubSeoMeta($hubSeo, $hubCanonicalUrl, $config);

        return view($this->baseView . __FUNCTION__, compact('menuUrlActive', 'listItinerary', 'listInclusiveService', 'listFaq', 'listBanner', 'hubSeo', 'hubCanonicalUrl'));
    }

    public function show(Request $request) {
        $language = FeLanguageUtils::getCurrentLanguage();
        $languageCode = $request->route('languageCode');

        $slug = $request->route('slug');
        $cruiseId = $request->route('cruise_id');
        $itineraryId = $request->route('itinerary_id');

        $itineraries = AppCruiseItineraryService::findByIdsJoin($cruiseId,$itineraryId,$language->id);

        if(count($itineraries) === 0)
            abort(404);

        $obj = $itineraries->first();
        $obj->slug = Utilities::convertToAlias($obj->itinerary->name);

        $showRouteName = $languageCode
            ? Utilities::bindRouteNameMultiLanguage('frontend.itinerary.show')
            : 'frontend.itinerary.show';

        if ($obj->slug !== $slug) {
            $redirectParams = [
                'slug' => $obj->slug,
                'cruise_id' => $cruiseId,
                'itinerary_id' => $itineraryId,
            ];
            if ($languageCode) {
                $redirectParams['languageCode'] = $languageCode;
            }

            return redirect(route($showRouteName, $redirectParams));
        }
        $obj->price = AppCabinService::getMinPriceByCruiseId($cruiseId)
                        ->where('duration',$obj->itinerary->duration)
                        ->first()?->min_price ?? 0;

        $listTestimonial = AppTestimonialService::getPaging([],$language->id);

        $listCabinType = AppGroupService::getAll(config('backend.groupType.cabin'),$language->id);

        $listCabin = $obj->cruise->cabins;
        if (isset($listCabin) && count($listCabin) > 0) {
            $listCabinId = [];
            for ($i = 0; $i < count($listCabin); $i++) {
                $cabin = $listCabin[$i];
                if (!in_array($cabin->id, $listCabinId)) {
                    $listCabinId[] = $cabin->id;
                }
                $group = $listCabinType->first(fn($item) => $item->id == $cabin->group_id);
                if($group){
                    $cabin->slug = $group->slug;
                }
            }

            // $listCabinRoomCount = AppCabinService::getCountRoomById($listCabinId);
            // if (count($listCabinRoomCount) > 0) {
            //     for ($i = 0; $i < count($listCabin); $i++) {
            //         $listCabin[$i]->room_count = $listCabinRoomCount
            //             ->where('cabin_id', $listCabin[$i]->id)
            //             //->sortBy('title')
            //             ->values();
            //     }
            // }

            $listCabinRoom = AppCabinService::getRoomById($listCabinId);
            if (count($listCabinRoom) > 0) {
                for ($i = 0; $i < count($listCabin); $i++) {
                    $listCabin[$i]->room = $listCabinRoom
                        ->where('cabin_id', $listCabin[$i]->id)
                        ->values();
                }
            }
        }

//        $obj->itinerary->galleryImages = $obj->itinerary->galleryImages->map(function ($item) {
//            if($item->link)$item->link = $this->bindImage($item->link);
//            if($item->thumbnail) $item->thumbnail = $this->bindImage($item->thumbnail);
//            return $item;
//        });
        $menuUrlActive = route(Utilities::getRouteName('frontend.itinerary.index'), ['languageCode' => $languageCode]);

        $config = Utilities::getAllConfig($language);
        $itinerary = $obj->itinerary;
        $seoTitle = trim((string) ($itinerary->seo_title ?? ''));
        $title = $seoTitle !== ''
            ? $seoTitle
            : FeUtils::bindWebsiteTitle($itinerary->name, $config['website-name']);

        $seoDescription = trim((string) ($itinerary->seo_description ?? ''));
        $description = $seoDescription !== ''
            ? $seoDescription
            : strip_tags($itinerary->description ?? '');

        $urlParams = [
            'slug' => $obj->slug,
            'cruise_id' => $cruiseId,
            'itinerary_id' => $itineraryId,
        ];
        if ($languageCode) {
            $urlParams['languageCode'] = $languageCode;
        }
        $url = route($showRouteName, $urlParams);

        $lastBreadCrumb = [
            [
                'name' => __('frontend::common.itinerary'),
                'url' => route(Utilities::getRouteName('frontend.itinerary.index'), ['languageCode' => $languageCode]),
            ],
            [
                'name' => $obj->itinerary->name,
                'url' => $url,
            ],
        ];
        $listBreadCrumb = FeUtils::bindBreadcrumb($lastBreadCrumb, $languageCode);

        \SEO::setTitle($title);
        \SEO::setDescription($description);

        \OpenGraph::setSiteName($config['website-name']);
        \OpenGraph::setTitle($title);
        \OpenGraph::setDescription($description);
        \OpenGraph::setUrl($url);
        if ($obj->image_link) {
            $image = FeUtils::getThumbnail(['link' => $obj->image_link, 'w' => 1200, 'h' => 630]);
            \OpenGraph::addImage($image);
        }

        \TwitterCard::setType('summary');
        \TwitterCard::setTitle($title);
        \TwitterCard::setDescription($description);
        \TwitterCard::setUrl($url);
        \TwitterCard::setImage($obj->image_link ? \URL::to('/') . $obj->image_link : \URL::to('/') . config('frontend.organizationLogoSocial.url'));
        \SEO::setCanonical($url);

        return view($this->baseView . __FUNCTION__,compact('menuUrlActive','obj','listTestimonial','listCabin','listBreadCrumb','itineraries'));
    }

    private function bindImage($img){
        if(!$img) return "";
        $extension = pathinfo($img,PATHINFO_EXTENSION);
        if(in_array($extension,config('backend.fileTypeImage'),true) && $extension !== "svg"){
            $filePath = Utilities::getFileLink($img);
            $filePath = ltrim($filePath, '/');
            $h = \Image::make($filePath)->height() * 9/16;
            $w = \Image::make($filePath)->width() * 9/16;
            return FeUtils::getThumbnail(['link' => $img,'w' => $w ,'h' => $h]);
        }
        return $img;
    }
}
