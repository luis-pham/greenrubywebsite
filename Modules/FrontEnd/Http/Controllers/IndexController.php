<?php
namespace Modules\FrontEnd\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\BackEnd\Helpers\Utilities;
use Modules\BackEnd\Services\AppCruiseService;
use Modules\BackEnd\Services\AppItineraryService;
use Modules\FrontEnd\Constants\PageCodeConsts;
use Modules\FrontEnd\Constants\PageConfigKeyConsts;
use Modules\FrontEnd\Helpers\FeLanguageUtils;
use Modules\FrontEnd\Helpers\FeUtils;
use Modules\FrontEnd\Services\AppCabinService;
use Modules\FrontEnd\Services\AppCruiseItineraryService;
use Modules\FrontEnd\Services\AppCruiseService as FeAppCruiseService;
use Modules\FrontEnd\Services\AppFileService;

class IndexController extends Controller
{
    private $baseView = 'frontend::index.';

    public function index(Request $request)
    {
        $language = FeLanguageUtils::getCurrentLanguage();
        $languageCode = $request->route('languageCode');

        $menuUrlActive = $languageCode
            ? route(Utilities::getRouteName('frontend.index'), ['languageCode' => $languageCode])
            : '/';

        $pageConfig = FeUtils::getPageConfigByCode(PageCodeConsts::HOMEPAGE, $language->id);

        // Bind cruise itinerary
        $listCruiseItinerary = $pageConfig[PageConfigKeyConsts::HOMEPAGE_CRUISE_ITINERARY];
        if (isset($listCruiseItinerary) && count($listCruiseItinerary) > 0) {
            $listCruiseId = [];
            for ($i = 0; $i < count($listCruiseItinerary); $i++) {
                if (!in_array($listCruiseItinerary[$i]->cruise_id, $listCruiseId)) {
                    $listCruiseId[] = $listCruiseItinerary[$i]->cruise_id;
                }
            }

            if (count($listCruiseId) > 0) {
                $listCruiseItineraryPrice = AppCabinService::getMinPriceByCruiseId($listCruiseId);
                if (count($listCruiseItineraryPrice) > 0) {
                    for ($i = 0; $i < count($listCruiseItinerary); $i++) {
                        $listCruiseItinerary[$i]->price = $listCruiseItineraryPrice
                            ->where('cruise_id', $listCruiseItinerary[$i]->cruise_id)
                            ->where('duration', $listCruiseItinerary[$i]->duration)
                            ->value('min_price');
                    }
                }
            }
        }

        // Bind cabin
        $listCabin = $pageConfig[PageConfigKeyConsts::HOMEPAGE_CABIN];
        if (isset($listCabin) && count($listCabin) > 0) {
            $listCabinId = [];
            for ($i = 0; $i < count($listCabin); $i++) {
                if (!in_array($listCabin[$i]->id, $listCabinId)) {
                    $listCabinId[] = $listCabin[$i]->id;
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

        // Bind exp activity
        $listExpActivity = $pageConfig[PageConfigKeyConsts::HOMEPAGE_EXP_ACTIVITY];
        if (isset($listExpActivity) && count($listExpActivity) > 0) {
            $listExpActivityId = [];
            for ($i = 0; $i < count($listExpActivity); $i++) {
                if (!in_array($listExpActivity[$i]->id, $listExpActivityId)) {
                    $listExpActivityId[] = $listExpActivity[$i]->id;
                }
            }

            $listFile = AppFileService::getByObjectId($listExpActivityId, config('backend.fileAttachObjectType.expActivity'));
            if (count($listFile) > 0) {
                for ($i = 0; $i < count($listExpActivity); $i++) {
                    $listExpActivity[$i]->file = $listFile
                        ->where('object_id', $listExpActivity[$i]->id)
                        ->values();
                }
            }
        }

        $selectBoxData = [];
        $selectBoxData['cruise'] = AppCruiseService::getAll($language->id)->sortBy('name');
        $selectBoxData['itinerary'] = AppItineraryService::getAll($language->id)->sortBy('name');

        $listCruiseName = AppCruiseService::getAll($language->id)->pluck('name')->toArray();
        $listAllCruise = AppCruiseService::getAll($language->id);

        $listCruiseByService = [];
        if (array_key_exists(PageConfigKeyConsts::HOMEPAGE_SERVICE, $pageConfig)) {
            $listService = $pageConfig[PageConfigKeyConsts::HOMEPAGE_SERVICE];
            $listServiceId = [];
            if (isset($listService) && count($listService) > 0) {
                for ($i = 0; $i < count($listService); $i++) {
                    if (!in_array($listService[$i]->id, $listServiceId)) {
                        $listServiceId[] = $listService[$i]->id;
                    }
                }
            }
            $data = FeAppCruiseService::getByServiceId($listServiceId, $language->id);
            for ($i = 0; $i < count($data); $i++) {
                if (!array_key_exists($data[$i]->service_id, $listCruiseByService)) {
                    $listCruiseByService[$data[$i]->service_id] = [];
                }
                $listCruiseByService[$data[$i]->service_id][] = $data[$i];
            }
        }

        $cruiseLatest = FeAppCruiseService::getLatest($language->id);

        $config = Utilities::getAllConfig($language);
        $canonicalUrl = FeUtils::frontendRoute('frontend.index', [], $languageCode);
        $seo = FeUtils::resolveHubSeo(
            PageCodeConsts::HOMEPAGE,
            $language,
            fn () => FeUtils::bindWebsiteTitle($config['website-name'], $config['website-slogan']),
            $config['website-description']
        );
        FeUtils::applyHubSeoMeta($seo, $canonicalUrl, $config);

        return view($this->baseView . __FUNCTION__, compact('menuUrlActive', 'pageConfig', 'selectBoxData', 'listCruiseByService', 'cruiseLatest', 'listCruiseName', 'listAllCruise'));
    }

    public function searchTour(Request $request)
    {
        $language = FeLanguageUtils::getCurrentLanguage();

        $param = [];
        if ($request->cruise_id) {
            $param['cruise_id'] = $request->cruise_id;
        }
        if ($request->itinerary_id) {
            $param['itinerary_id'] = $request->itinerary_id;
        }
        if ($request->date) {
            $param['start_at'] = Utilities::parseDateOnly($request->date);
        }
        if ($request->guest) {
            $param['guest'] = $request->guest;
        }

        $data = AppCruiseItineraryService::getCruiseItinerary($param, $language->id);
        
        try {
            return response()->json([
                'msg' => 'success',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'msg' => 'fail',
                'err' => $e->getMessage()
            ]);
        }
    }
}
