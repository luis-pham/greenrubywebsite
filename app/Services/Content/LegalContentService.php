<?php

namespace App\Services\Content;

use Modules\BackEnd\Entities\AdLanguage;
use Modules\FrontEnd\Constants\PageCodeConsts;
use Modules\FrontEnd\Constants\PageConfigKeyConsts;
use Modules\FrontEnd\Helpers\FeUtils;
use Modules\FrontEnd\Services\PageService;

/**
 * GET /legal: everything the five legal URLs of the Blade site render (PageController):
 *  - /legal: accordion of the "legal" page config *-content keys (empty sections skipped by Blade);
 *  - /safety-policies, /terms-and-conditions, /payment-methods: their own page code's "*-content" key;
 *  - /privacy-policy: a hard-coded English Blade template; "privacy-policies-content" is exposed here.
 * All five are "noindex, follow" and show the "article-popular" sidebar.
 */
class LegalContentService
{
    public static function build(AdLanguage $language): array
    {
        $languageId = (int) $language->id;
        $locale = $language->code;
        $legal = FeUtils::getPageConfigByCode(PageCodeConsts::LEGAL, $languageId);

        $section = fn (string $titleKey, string $configKey) => [
            'title' => __('frontend::page.' . $titleKey, [], $locale),
            'html' => PageConfigContentService::html($legal[$configKey] ?? null),
        ];

        $page = function (string $code, string $configKey, string $title) use ($language, $languageId) {
            $config = FeUtils::getPageConfigByCode($code, $languageId);
            $seo = PageConfigContentService::seo($code, $language, $title);

            return [
                'code' => $code,
                'title' => $title,
                'seo' => $seo,
                'html' => PageConfigContentService::html($config[$configKey] ?? null),
                'updatedAt' => PageConfigContentService::updatedAt($code, $language),
            ];
        };

        return [
            'seo' => PageConfigContentService::seo(PageCodeConsts::LEGAL, $language),
            'title' => __('frontend::page.page_legal_title', [], $locale),
            'noIndex' => true,
            'sections' => [
                'safety' => $section('page_safety_policies_title', PageConfigKeyConsts::LEGAL_SAFETY_POLICIES_CONTENT),
                'terms' => $section('page_terms_and_conditions_title', PageConfigKeyConsts::LEGAL_TERMS_AND_CONDITIONS_CONTENT),
                'privacy' => $section('page_privacy_policies_title', PageConfigKeyConsts::LEGAL_PRIVACY_POLICIES_CONTENT),
                'paymentMethods' => $section('page_payment_methods_title', PageConfigKeyConsts::LEGAL_PAYMENT_METHODS_CONTENT),
            ],
            'pages' => [
                'safetyPolicies' => $page(PageCodeConsts::SAFETY_POLICIES, PageConfigKeyConsts::SAFETY_POLICIES_CONTENT,
                    __('frontend::page.page_safety_policies_title', [], $locale)),
                'termsAndConditions' => $page(PageCodeConsts::TERMS_AND_CONDITIONS, PageConfigKeyConsts::TERMS_AND_CONDITIONS_CONTENT,
                    __('frontend::page.page_terms_and_conditions_title', [], $locale)),
                // Blade hard-codes the English title "Privacy Policy" for both languages.
                'privacyPolicy' => $page(PageCodeConsts::PRIVACY_POLICIES, PageConfigKeyConsts::PRIVACY_POLICIES_CONTENT,
                    $locale === 'en' ? 'Privacy Policy' : __('frontend::page.page_privacy_policies_title', [], $locale)),
                'paymentMethods' => $page(PageCodeConsts::PAYMENT_METHODS, PageConfigKeyConsts::PAYMENT_METHODS_CONTENT,
                    __('frontend::page.page_payment_methods_title', [], $locale)),
            ],
            'popularArticles' => ArticleContentService::popular($language),
            'updatedAt' => CabinContentService::isoDate(PageService::getLegalUpdatedAt($languageId)),
        ];
    }
}
