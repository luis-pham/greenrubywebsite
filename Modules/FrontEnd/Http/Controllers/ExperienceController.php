<?php
namespace Modules\FrontEnd\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\BackEnd\Helpers\Utilities;
use Modules\FrontEnd\Helpers\FeLanguageUtils;
use Modules\BackEnd\Services\AppExpActivityService;
use Modules\BackEnd\Services\AppGroupService;
use Modules\FrontEnd\Helpers\FeUtils;
use Modules\FrontEnd\Constants\PageCodeConsts;
use Modules\FrontEnd\Constants\PageConfigKeyConsts;
use Modules\FrontEnd\Services\AppArticleService;


class ExperienceController extends Controller{
    private $baseView = 'frontend::experience.';

    public function index(Request $request)
    {
        $language = FeLanguageUtils::getCurrentLanguage();
        $languageCode = $request->route('languageCode');

        $pageConfig = FeUtils::getPageConfigByCode(PageCodeConsts::EXPERIENCE, $language->id);

        $listExperience = AppExpActivityService::getAll($language->id);
        $listExpFeatured = AppExpActivityService::getExpActivityFeatured($language->id);
        

        $listGroup = AppGroupService::getAll(config('backend.groupType.expActivity'), $language->id);

        $tabButtons = [];
        foreach (config('backend.groupTabType.expActivity', []) as $langKey => $tabValue) {
            $ids = $listGroup->where('tab', $tabValue)->pluck('id')->map(fn($id) => (string) $id)->values()->toArray();
            if (count($ids)) {
                $tabButtons[] = [
                    'ids'  => $ids,
                    'name' => __('backend::group.' . $langKey),
                ];
            }
        }

        $config = Utilities::getAllConfig($language);
        $title = FeUtils::bindWebsiteTitle($config['website-name'], $config['website-slogan']);
        $menuUrlActive = route(Utilities::getRouteName('frontend.experience.index'), ['languageCode' => $languageCode]);
        $url = route(Utilities::getRouteName('frontend.experience.index'), ['languageCode' => $languageCode]);
        
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

        return view($this->baseView . __FUNCTION__, compact('menuUrlActive','listExperience', 'listExpFeatured', 'listGroup', 'pageConfig', 'tabButtons'));
    }

    public function show(Request $request){
        $language = FeLanguageUtils::getCurrentLanguage();
        $languageCode = $request->route('languageCode');
        $pageConfig = FeUtils::getPageConfigByCode(PageCodeConsts::EXPERIENCE, $language->id);


        $slug = $request->route('slug');
        $id = $request->route('id');
        
        $obj = AppExpActivityService::findJoin($id, $language->id);
        if (!$obj) {
            return abort(404);
        }

        $obj->slug = Utilities::convertToAlias($obj->name);
        if ($obj->slug != $slug) {
            $route = route(Utilities::getRouteName('frontend.experience.show'), ['languageCode' => $languageCode, 'slug' => $obj->slug, 'id' => $obj->id]);
            return redirect($route, 301);
        }

        $listExperience = AppExpActivityService::getAll($language->id);
        $suitableAudiences = AppExpActivityService::getSuitableAudiences($id, $language->id);

        $galleryImages = AppExpActivityService::getActivityGallery($id);
        $listExperience = $listExperience->where('id', '!=', $obj->id)
                                         ->where('group_id', '=', $obj->group_id)->take(5);


        $config = Utilities::getAllConfig($language);
        $title = $obj->name;
        $title = FeUtils::bindWebsiteTitle($title, $config['website-name']);
        
        

        $url = route(Utilities::getRouteName('frontend.experience.show'), ['languageCode' => $languageCode, 'slug' => $obj->slug, 'id' => $obj->id]);

        \SEO::setTitle($title);
        \SEO::setDescription(strip_tags($obj->summary));
        \SEO::setCanonical($url);

        \OpenGraph::setSiteName($config['website-name']);
        \OpenGraph::setTitle($title);
        \OpenGraph::setUrl($url);
        \OpenGraph::addImage($obj->cover_link ? \URL::to('/') . $obj->cover_link : \URL::to('/') . config('frontend.organizationLogoSocial.url'), [
            'width' => config('frontend.organizationLogoSocial.width'),
            'height' => config('frontend.organizationLogoSocial.height'),
        ]);

        \TwitterCard::setType('summary');
        \TwitterCard::setTitle($title);
        \TwitterCard::setDescription(strip_tags($obj->summary));
        \TwitterCard::setUrl($url);
        \TwitterCard::setImage($obj->cover_link ? \URL::to('/') . $obj->cover_link : \URL::to('/') . config('frontend.organizationLogoSocial.url'));

        $menuUrlActive = route(Utilities::getRouteName('frontend.experience.index'), ['languageCode' => $languageCode]);

        return view($this->baseView . __FUNCTION__, compact('obj', 'listExperience', 'galleryImages', 'suitableAudiences', 'pageConfig', 'menuUrlActive'));
    }
}