<?php
namespace Modules\FrontEnd\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\BackEnd\Helpers\Utilities;
use Modules\BackEnd\Services\AppPageService;
use Modules\FrontEnd\Constants\PageCodeConsts;
use Modules\FrontEnd\Constants\PageConfigKeyConsts;
use Modules\FrontEnd\Helpers\FeLanguageUtils;
use Modules\FrontEnd\Helpers\FeUtils;

class PageController extends Controller
{
    private $baseView = 'frontend::page.';

    public function legal(Request $request)
    {
        $language = FeLanguageUtils::getCurrentLanguage();
        $languageCode = $request->route('languageCode');

        $pageConfig = FeUtils::getPageConfigByCode(PageCodeConsts::LEGAL, $language->id);
        $pageTitle = __('frontend::page.page_legal_title');
        $pageUrl = route(Utilities::getRouteName('frontend.page.legal'), ['languageCode' => $languageCode]);
        
        $listArticlePopular = [];
        $pageArticle = AppPageService::getByCode(PageCodeConsts::ARTICLE, $language->id);
        if ($pageArticle) {
            $pageArticleConfig = FeUtils::getPageConfigByCode(PageCodeConsts::ARTICLE, $language->id);
            $listArticlePopular = $pageArticleConfig[PageConfigKeyConsts::ARTICLE_POPULAR] ?? [];
        }
        
        $lastBreadcrumb[] = [
            'name' => $pageTitle,
            'url' => $pageUrl
        ];
        $listBreadcrumb = FeUtils::bindBreadcrumb($lastBreadcrumb, $languageCode);

        $config = Utilities::getAllConfig($language);
        $title = FeUtils::bindWebsiteTitle($pageTitle, $config['website-name']);

        \SEO::setTitle($title);
        \SEO::setCanonical($pageUrl);

        \OpenGraph::setSiteName($config['website-name']);
        \OpenGraph::setTitle($pageTitle);
        \OpenGraph::setUrl($pageUrl);

        \TwitterCard::setType('summary');
        \TwitterCard::setTitle($title);
        \TwitterCard::setDescription($config['website-description']);
        \TwitterCard::setUrl($pageUrl);
        \TwitterCard::setImage(\URL::to('/') . config('frontend.organizationLogoSocial.url'));

        return view($this->baseView . __FUNCTION__, compact('pageTitle', 'pageConfig', 'pageUrl', 'listArticlePopular', 'listBreadcrumb'));
    }

    public function safetyPolicies(Request $request)
    {
        return $this->show($request, 'frontend.page.safety-policies', __('frontend::page.page_safety_policies_title'), PageCodeConsts::SAFETY_POLICIES, PageConfigKeyConsts::SAFETY_POLICIES_CONTENT);
    }

    public function termsAndConditions(Request $request)
    {
        return $this->show($request, 'frontend.page.terms-and-conditions', __('frontend::page.page_terms_and_conditions_title'), PageCodeConsts::TERMS_AND_CONDITIONS, PageConfigKeyConsts::TERMS_AND_CONDITIONS_CONTENT);
    }

    public function privacyPolicy(Request $request)
    {
        $language = FeLanguageUtils::getCurrentLanguage();
        $languageCode = $request->route('languageCode');

        $pageTitle = 'Privacy Policy';
        $pageUrl = route(Utilities::getRouteName('frontend.page.privacy-policy'), ['languageCode' => $languageCode]);

        $lastBreadcrumb[] = [
            'name' => $pageTitle,
            'url' => $pageUrl
        ];
        $listBreadcrumb = FeUtils::bindBreadcrumb($lastBreadcrumb, $languageCode);

        $config = Utilities::getAllConfig($language);
        $title = FeUtils::bindWebsiteTitle($pageTitle, $config['website-name']);
        $metaDescription = 'How Green Ruby Cruises collects, uses, and protects your personal information. GDPR compliant.';

        \SEO::setTitle($title);
        \SEO::setDescription($metaDescription);
        \SEO::setCanonical($pageUrl);

        \OpenGraph::setSiteName($config['website-name']);
        \OpenGraph::setTitle($pageTitle);
        \OpenGraph::setDescription($metaDescription);
        \OpenGraph::setUrl($pageUrl);

        \TwitterCard::setType('summary');
        \TwitterCard::setTitle($title);
        \TwitterCard::setDescription($metaDescription);
        \TwitterCard::setUrl($pageUrl);
        \TwitterCard::setImage(\URL::to('/') . config('frontend.organizationLogoSocial.url'));

        return view($this->baseView . 'privacy-policy', compact('pageTitle', 'pageUrl', 'listBreadcrumb'));
    }

    public function paymentMethods(Request $request)
    {
        return $this->show($request, 'frontend.page.payment-methods', __('frontend::page.page_payment_methods_title'), PageCodeConsts::PAYMENT_METHODS, PageConfigKeyConsts::PAYMENT_METHODS_CONTENT);
    }

    private function show(Request $request, $routeName, $pageTitle, $pageCode, $pageConfigKeyContent)
    {
        $language = FeLanguageUtils::getCurrentLanguage();
        $languageCode = $request->route('languageCode');

        $pageConfig = FeUtils::getPageConfigByCode($pageCode, $language->id);

        $pageContent = isset($pageConfig[$pageConfigKeyContent]) ? $pageConfig[$pageConfigKeyContent] : '';
        $pageUrl = route(Utilities::getRouteName($routeName), ['languageCode' => $languageCode]);
        
        $listArticlePopular = [];
        $pageArticle = AppPageService::getByCode(PageCodeConsts::ARTICLE, $language->id);
        if ($pageArticle) {
            $pageArticleConfig = FeUtils::getPageConfigByCode(PageCodeConsts::ARTICLE, $language->id);
            $listArticlePopular = $pageArticleConfig[PageConfigKeyConsts::ARTICLE_POPULAR] ?? [];
        }
        
        $lastBreadcrumb[] = [
            'name' => $pageTitle,
            'url' => $pageUrl
        ];
        $listBreadcrumb = FeUtils::bindBreadcrumb($lastBreadcrumb, $languageCode);

        $config = Utilities::getAllConfig($language);
        $title = FeUtils::bindWebsiteTitle($pageTitle, $config['website-name']);

        \SEO::setTitle($title);
        \SEO::setCanonical($pageUrl);

        \OpenGraph::setSiteName($config['website-name']);
        \OpenGraph::setTitle($pageTitle);
        \OpenGraph::setUrl($pageUrl);

        \TwitterCard::setType('summary');
        \TwitterCard::setTitle($title);
        \TwitterCard::setDescription($config['website-description']);
        \TwitterCard::setUrl($pageUrl);
        \TwitterCard::setImage(\URL::to('/') . config('frontend.organizationLogoSocial.url'));

        return view($this->baseView . 'show', compact('pageTitle', 'pageContent', 'pageUrl', 'listArticlePopular', 'listBreadcrumb'));
    }
}
