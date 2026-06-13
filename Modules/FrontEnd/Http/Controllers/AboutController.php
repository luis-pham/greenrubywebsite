<?php
namespace Modules\FrontEnd\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\BackEnd\Helpers\Utilities;
use Modules\BackEnd\Services\AppExpActivityService;
use Modules\FrontEnd\Constants\PageCodeConsts;
use Modules\FrontEnd\Constants\PageConfigKeyConsts;
use Modules\FrontEnd\Helpers\FeLanguageUtils;
use Modules\FrontEnd\Helpers\FeUtils;
use Modules\FrontEnd\Services\AppCruiseItineraryService;

class AboutController extends Controller
{
    private $baseView = 'frontend::about.';

    public function index(Request $request)
    {
        $language = FeLanguageUtils::getCurrentLanguage();
        $languageCode = $request->route('languageCode');

        $pageConfig = FeUtils::getPageConfigByCode(PageCodeConsts::ABOUT, $language->id);
        $listStoryImages = AppCruiseItineraryService::getHeroBannerImages($language->id, 2);
        $listExpFeatured = AppExpActivityService::getExpActivityFeatured($language->id);
        $config = Utilities::getAllConfig($language);
        $title = FeUtils::bindWebsiteTitle($config['website-name'], $config['website-slogan']);
        $url = route(Utilities::getRouteName('frontend.about.index'), ['languageCode' => $languageCode]);

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

        return view($this->baseView . __FUNCTION__, compact('pageConfig', 'listStoryImages', 'listExpFeatured', 'config', 'url'));
    }

   
}