<?php
namespace Modules\FrontEnd\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\BackEnd\Helpers\Utilities;
use Modules\FrontEnd\Constants\PageCodeConsts;
use Modules\FrontEnd\Helpers\FeLanguageUtils;
use Modules\FrontEnd\Helpers\FeUtils;
use Modules\FrontEnd\Services\AppCruiseItineraryService;

class BookingController extends Controller
{
    private $baseView = 'frontend::booking.';

    public function index(Request $request)
    {
        $language = FeLanguageUtils::getCurrentLanguage();
        $languageCode = $request->route('languageCode');

        $menuUrlActive = $languageCode
            ? route(Utilities::getRouteName('frontend.booking'), ['languageCode' => $languageCode])
            : route('frontend.booking');

        $config = Utilities::getAllConfig($language);
        $hubCanonicalUrl = FeUtils::frontendRoute('frontend.booking', [], $languageCode);
        $hubSeo = FeUtils::resolveHubSeo(
            PageCodeConsts::BOOKING,
            $language,
            fn () => FeUtils::bindWebsiteTitle($config['website-name'], $config['website-slogan']),
            $config['website-description']
        );
        FeUtils::applyHubSeoMeta($hubSeo, $hubCanonicalUrl, $config);
        FeUtils::applyNoIndexFollowMeta();

        $listHeroBannerImages = AppCruiseItineraryService::getHeroBannerImages($language->id, 2);
        $listBanner = [];
        foreach ($listHeroBannerImages as $index => $image) {
            $banner = new \stdClass();
            $banner->link = $image->link;
            $banner->title = $index === 0 ? __('frontend::booking.step1_title') : '';
            $banner->description = $index === 0 ? __('frontend::booking.step1_subtitle') : '';
            $listBanner[] = $banner;
        }

        $languageCode = $languageCode ?? $language->code ?? 'vi';
        return view($this->baseView . 'index', compact('menuUrlActive', 'languageCode', 'listBanner', 'hubSeo', 'hubCanonicalUrl'));
    }
}

