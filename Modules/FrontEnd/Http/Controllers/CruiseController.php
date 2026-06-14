<?php

namespace Modules\FrontEnd\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\BackEnd\Helpers\Utilities;
use Modules\BackEnd\Services\AppGroupService;
use Modules\BackEnd\Services\AppTestimonialService;
use Modules\FrontEnd\Helpers\FeLanguageUtils;
use Modules\FrontEnd\Helpers\FeUtils;
use Modules\FrontEnd\Services\AppCabinService;
use Modules\FrontEnd\Services\AppCruiseService;
use Modules\FrontEnd\Services\AppItineraryService;
use Modules\BackEnd\Entities\AppExpActivity;

class CruiseController extends Controller
{
    private string $baseView = 'frontend::cruise.';
    public function show(Request $request) {
        $language = FeLanguageUtils::getCurrentLanguage();
        $languageCode = $request->route('languageCode');

        $slug = $request->route('slug');
        $id = $request->route('id');

        $obj = AppCruiseService::findByIdJoin($id,$language->id);

        if(!$obj)
            abort(404);
        else{
            $obj->slug = Utilities::convertToAlias($obj->name);
            if ($obj->slug !== $slug) {
                $showRouteName = $languageCode
                    ? Utilities::bindRouteNameMultiLanguage('frontend.cruise.show')
                    : 'frontend.cruise.show';
                $redirectParams = [
                    'slug' => $obj->slug,
                    'id' => $id,
                ];
                if ($languageCode) {
                    $redirectParams['languageCode'] = $languageCode;
                }

                return redirect(route($showRouteName, $redirectParams));
            }
        }

        $listTestimonial = AppTestimonialService::getPaging([],$language->id);

        $groupItinerary = AppItineraryService::getEarliestItinerariesWithMinPriceAndOfBay($id)->sortKeys();

        $listCabinType = AppGroupService::getAll(config('backend.groupType.cabin'),$language->id);

        $listCabin = $obj->cabins;
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

        if (isset($listCabin) && count($listCabin) > 0) {
            $listCabin->load('group');
        }

        $listInclusiveService = $obj->cruiseServices;

        $listExpActivity = AppExpActivity::with('group')
        ->where(
            'language_id',
            $obj->language_id ?? $language->id ?? 1
        )
        ->where(function($query) use ($obj) {
            $query
                ->where('cruise_id', $obj->id)
                ->orWhereNull('cruise_id');
        })
        ->orderBy('cruise_id', 'desc')
        ->orderBy('id', 'asc')
        ->get();

        $menuUrlActive = '#';

        $config = Utilities::getAllConfig($language);
        $title = FeUtils::formatGreenRubyMenuName($obj->seo_title ? $obj->seo_title : $obj->name);
        $title = FeUtils::bindWebsiteTitle($title, $config['website-name']);
        $url = route(Utilities::getRouteName('frontend.cruise.show'), [
            'languageCode' => $languageCode,
            'slug' => $obj->slug,
            'id' => $id
        ]);

        \SEO::setTitle($title);
        \SEO::setDescription($obj->seo_description ? $obj->seo_description : strip_tags($obj->lead));

        \OpenGraph::setSiteName($config['website-name']);
        \OpenGraph::setTitle($title);
        \OpenGraph::setUrl($url);
        if ($obj->image_link) {
            $image = FeUtils::getThumbnail(['link' => $obj->image_link, 'w' => 1200, 'h' => 630]);
            \OpenGraph::addImage($image);
        }

        \TwitterCard::setType('summary');
        \TwitterCard::setTitle($title);
        \SEO::setDescription($obj->seo_description ? $obj->seo_description : strip_tags($obj->lead));
        \TwitterCard::setUrl($url);
        \TwitterCard::setImage($obj->image_link ? \URL::to('/') . $obj->image_link : \URL::to('/') . config('frontend.organizationLogoSocial.url'));
        \SEO::setCanonical($url);

        return view($this->baseView . __FUNCTION__,compact(
            'menuUrlActive',
            'obj',
            'listTestimonial',
            'listCabin',
            'listInclusiveService',
            'groupItinerary',
            'listExpActivity'
        ));
    }
}
