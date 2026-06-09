<?php

namespace Modules\FrontEnd\Services;

use Carbon\Carbon;
use Modules\BackEnd\Entities\AppPageConfig;
use Modules\BackEnd\Entities\AdConfig;
use Modules\FrontEnd\Constants\PageCodeConsts;
use Modules\FrontEnd\Constants\PageConfigKeyConsts;
use Modules\FrontEnd\Helpers\FeLanguageUtils;
use Modules\FrontEnd\Helpers\FeUtils;

class PageService
{
    public static function getLegalUpdatedAt($languageId = null)
    {
        $query = AppPageConfig::query()
            ->select('app_page_config.updated_at')
            ->join('app_page', 'app_page.id', '=', 'app_page_config.page_id')
            ->where('app_page.code', PageCodeConsts::LEGAL)
            ->whereIn('app_page_config.key', [
                PageConfigKeyConsts::LEGAL_SAFETY_POLICIES_CONTENT,
                PageConfigKeyConsts::LEGAL_TERMS_AND_CONDITIONS_CONTENT,
                PageConfigKeyConsts::LEGAL_PRIVACY_POLICIES_CONTENT,
                PageConfigKeyConsts::LEGAL_PAYMENT_METHODS_CONTENT,
            ]);

        if ($languageId) {
            $query = $query->where('app_page.language_id', $languageId);
        }

        $latestConfig = $query
            ->orderBy('app_page_config.updated_at', 'desc')
            ->first();

        return $latestConfig && $latestConfig->updated_at
            ? $latestConfig->updated_at
            : Carbon::now()->format('Y-m-d H:i:s');
    }

    public static function getAboutUpdatedAt($languageId = null)
    {
        $query = AppPageConfig::query()
            ->select('app_page_config.updated_at')
            ->join('app_page', 'app_page.id', '=', 'app_page_config.page_id')
            ->where('app_page.code', PageCodeConsts::ABOUT)
            ->whereIn('app_page_config.key', [
                PageConfigKeyConsts::ABOUT_US_BANNER,
                PageConfigKeyConsts::ABOUT_US_ECO_TITLE,
                PageConfigKeyConsts::ABOUT_US_ECO_DESCRIPTION,
                PageConfigKeyConsts::ABOUT_US_ECO_CONTENT,
                PageConfigKeyConsts::ABOUT_US_ECO_FEATURED_IMAGE,
                PageConfigKeyConsts::ABOUT_US_SUSTAINABILITY,
                PageConfigKeyConsts::ABOUT_US_ENVIROMENT_TITLE,
                PageConfigKeyConsts::ABOUT_US_ENVIROMENT_DESCRIPTION,
                PageConfigKeyConsts::ABOUT_US_ENVIROMENT_LIST,
                PageConfigKeyConsts::ABOUT_US_STATISTIC_TITLE,
                PageConfigKeyConsts::ABOUT_US_STATISTIC_DESCRIPTION,
                PageConfigKeyConsts::ABOUT_US_STATISTIC_WASTEWATER_TREATED,
                PageConfigKeyConsts::ABOUT_US_STATISTIC_REDUCED_ANNUALLY,
                PageConfigKeyConsts::ABOUT_US_STATISTIC_RENEWABLE_SOLAR_POWER,
                PageConfigKeyConsts::ABOUT_US_PARTNER_TITLE,
                PageConfigKeyConsts::ABOUT_US_PARTNER_DESCRIPTION,
                PageConfigKeyConsts::ABOUT_US_PARTNER_LIST,
                PageConfigKeyConsts::ABOUT_US_READY_TO_SAIL_DESCRIPTION,
                PageConfigKeyConsts::ABOUT_US_READY_TO_SAIL_CONTENT,
            ]);
        if ($languageId) {
            $query = $query->where('app_page.language_id', $languageId);
        }

        $latestConfig = $query
            ->orderBy('app_page_config.updated_at', 'desc')
            ->first();

        return $latestConfig && $latestConfig->updated_at
            ? $latestConfig->updated_at
            : Carbon::now()->format('Y-m-d H:i:s');
    }

    public static function getContactUpdatedAt($languageId = null)
    {
        $query = AdConfig::query()
            ->select('ad_config.updated_at')
            ->whereIn('ad_config.key', ['hotline', 'whatsapp', 'email', 'address']);

        if ($languageId) {
            $query = $query->where('ad_config.language_id', $languageId);
        }

        $latestConfig = $query
            ->orderBy('ad_config.updated_at', 'desc')
            ->first();

        return $latestConfig && $latestConfig->updated_at
            ? $latestConfig->updated_at
            : Carbon::now()->format('Y-m-d H:i:s');
    }
}