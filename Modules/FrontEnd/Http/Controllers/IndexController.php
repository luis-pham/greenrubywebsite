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
        $belowFoldData = $this->buildBelowFoldData($pageConfig, $language);
        $selectBoxData = $belowFoldData['selectBoxData'];
        $listCruiseByService = $belowFoldData['listCruiseByService'];
        $cruiseLatest = $belowFoldData['cruiseLatest'];
        $listCruiseName = $belowFoldData['listCruiseName'];
        $listAllCruise = $belowFoldData['listAllCruise'];
        $pageConfig = $belowFoldData['pageConfig'];

        $config = Utilities::getAllConfig($language);
        $canonicalUrl = FeUtils::frontendRoute('frontend.index', [], $languageCode);
        $seo = FeUtils::resolveHubSeo(
            PageCodeConsts::HOMEPAGE,
            $language,
            fn () => FeUtils::bindWebsiteTitle($config['website-name'], $config['website-slogan']),
            $config['website-description']
        );
        FeUtils::applyHubSeoMeta($seo, $canonicalUrl, $config);

        $criticalCssPath = 'assets/frontend/css/critical-home.css';

        return view($this->baseView . 'index', compact(
            'menuUrlActive',
            'pageConfig',
            'selectBoxData',
            'listCruiseByService',
            'cruiseLatest',
            'listCruiseName',
            'listAllCruise',
            'criticalCssPath'
        ));
    }

    public function belowFold(Request $request)
    {
        $language = FeLanguageUtils::getCurrentLanguage();
        $languageCode = $request->route('languageCode');

        $pageConfig = FeUtils::getPageConfigByCode(PageCodeConsts::HOMEPAGE, $language->id);
        $data = $this->buildBelowFoldData($pageConfig, $language);

        return response()
            ->view($this->baseView . 'partials.below-fold', array_merge($data, [
                'languageCode' => $languageCode,
                'pageConfig' => $data['pageConfig'],
            ]))
            ->header('Content-Type', 'text/html; charset=UTF-8');
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

    /**
     * Heavy homepage bindings used by below-fold sections.
     */
    private function buildBelowFoldData(array $pageConfig, $language): array
    {
        $listCruiseItinerary = $pageConfig[PageConfigKeyConsts::HOMEPAGE_CRUISE_ITINERARY] ?? null;
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
            $pageConfig[PageConfigKeyConsts::HOMEPAGE_CRUISE_ITINERARY] = $listCruiseItinerary;
        }

        $listCabin = $pageConfig[PageConfigKeyConsts::HOMEPAGE_CABIN] ?? null;
        if (isset($listCabin) && count($listCabin) > 0) {
            $listCabinId = [];
            for ($i = 0; $i < count($listCabin); $i++) {
                if (!in_array($listCabin[$i]->id, $listCabinId)) {
                    $listCabinId[] = $listCabin[$i]->id;
                }
            }

            $listCabinRoom = AppCabinService::getRoomById($listCabinId);
            if (count($listCabinRoom) > 0) {
                for ($i = 0; $i < count($listCabin); $i++) {
                    $listCabin[$i]->room = $listCabinRoom
                        ->where('cabin_id', $listCabin[$i]->id)
                        ->values();
                }
            }
            $pageConfig[PageConfigKeyConsts::HOMEPAGE_CABIN] = $listCabin;
        }

        $listExpActivity = $pageConfig[PageConfigKeyConsts::HOMEPAGE_EXP_ACTIVITY] ?? null;
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
            $pageConfig[PageConfigKeyConsts::HOMEPAGE_EXP_ACTIVITY] = $listExpActivity;
        }

        $selectBoxData = [];
        $listAllCruise = AppCruiseService::getAll($language->id);
        $selectBoxData['cruise'] = $listAllCruise->sortBy('name');
        $selectBoxData['itinerary'] = AppItineraryService::getAll($language->id)->sortBy('name');

        $listCruiseName = $listAllCruise->pluck('name')->toArray();

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
            if (count($listServiceId) > 0) {
                $data = FeAppCruiseService::getByServiceId($listServiceId, $language->id);
                for ($i = 0; $i < count($data); $i++) {
                    if (!array_key_exists($data[$i]->service_id, $listCruiseByService)) {
                        $listCruiseByService[$data[$i]->service_id] = [];
                    }
                    $listCruiseByService[$data[$i]->service_id][] = $data[$i];
                }
            }
        }

        $cruiseLatest = FeAppCruiseService::getLatest($language->id);

        return [
            'pageConfig' => $pageConfig,
            'selectBoxData' => $selectBoxData,
            'listCruiseByService' => $listCruiseByService,
            'cruiseLatest' => $cruiseLatest,
            'listCruiseName' => $listCruiseName,
            'listAllCruise' => $listAllCruise,
        ];
    }

}
