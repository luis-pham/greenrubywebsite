<?php
namespace Modules\FrontEnd\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\BackEnd\Helpers\Utilities;
use Modules\FrontEnd\Helpers\FeLanguageUtils;
use Modules\BackEnd\Services\AppGroupService;
use Modules\FrontEnd\Services\AppServiceService as FeAppServiceService;
use Modules\BackEnd\Services\AppServiceService;
use Modules\BackEnd\Services\AppAmenityService;
use Modules\BackEnd\Services\AppCabinService;
use Modules\FrontEnd\Helpers\FeUtils;
use Modules\FrontEnd\Constants\PageCodeConsts;
use Modules\FrontEnd\Constants\PageConfigKeyConsts;

class ServiceController extends Controller{
    private $baseView = 'frontend::service.';

    public function index(Request $request)
    {
        $language = FeLanguageUtils::getCurrentLanguage();
        $languageCode = $request->route('languageCode');

        $pageConfig = FeUtils::getPageConfigByCode(PageCodeConsts::SERVICE, $language->id);

        // $listService = AppServiceService::getByType(2, $language->id);
        $listAmenity = AppAmenityService::getAll($language->id);
        $listGroup = AppGroupService::getAll(config('backend.groupType.service'), $language->id)
        ->pluck('name', 'id');

        $menuUrlActive = route(Utilities::getRouteName('frontend.service.index'), ['languageCode' => $languageCode]);

        $config = Utilities::getAllConfig($language);
        $title = FeUtils::bindWebsiteTitle($config['website-name'], $config['website-slogan']);
        $url = route(Utilities::getRouteName('frontend.service.index'), ['languageCode' => $languageCode]);
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
        
        return view($this->baseView . __FUNCTION__, compact('menuUrlActive', 'listAmenity', 'listGroup', 'pageConfig'));
    }
    public function getById(Request $request){
        $language = FeLanguageUtils::getCurrentLanguage();

        $id = $request->id;
        $obj = AppServiceService::findJoin($id, $language->id);
        if (!$obj) {
            return response()->json([
                'msg' => 'fail',
                'err' => 'Cabin not found'
            ], 404);
        }

        try {
            $listFile = FeAppServiceService::getFileById($id);
            for ($i = 0; $i < count($listFile); $i++) {
                $listFile[$i]->link = FeUtils::getImageLink($listFile[$i]->link);
            }
            $obj->file = $listFile;

            if (!empty($obj->image_link)) {
                $obj->image_link = FeUtils::getImageLink($obj->image_link);
            }

            $obj->price_formatted = FeUtils::formatDisplayCurrency($obj->price);

            return response()->json([
                'msg' => 'success',
                'data' => $obj
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'msg' => 'fail',
                'err' => $e->getMessage()
            ]);
        }
    }
}
