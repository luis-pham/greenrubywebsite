<?php
namespace Modules\FrontEnd\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\BackEnd\Helpers\Utilities;
use Modules\FrontEnd\Helpers\FeLanguageUtils;
use Modules\FrontEnd\Helpers\FeUtils;

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
        $title = FeUtils::bindWebsiteTitle($config['website-name'], $config['website-slogan']);

        \SEO::setTitle($title);
        \SEO::setDescription($config['website-description']);
        \SEO::setCanonical(route(Utilities::getRouteName('frontend.booking'), ['languageCode' => $languageCode]));

        \OpenGraph::setSiteName($config['website-name']);
        \OpenGraph::setTitle($title);
        \OpenGraph::setUrl(route(Utilities::getRouteName('frontend.booking'), ['languageCode' => $languageCode]));
        \OpenGraph::addImage(\URL::to('/') . config('frontend.organizationLogoSocial.url'), [
            'width' => config('frontend.organizationLogoSocial.width'),
            'height' => config('frontend.organizationLogoSocial.height'),
        ]);

        \TwitterCard::setType('summary');
        \TwitterCard::setTitle($title);
        \TwitterCard::setDescription($config['website-description']);
        \TwitterCard::setUrl(route(Utilities::getRouteName('frontend.booking'), ['languageCode' => $languageCode]));
        \TwitterCard::setImage(\URL::to('/') . config('frontend.organizationLogoSocial.url'));

        $languageCode = $languageCode ?? $language->code ?? 'vi';
        return view($this->baseView . 'index', compact('menuUrlActive', 'languageCode'));
    }
}

