<?php

namespace Modules\FrontEnd\Helpers;

use Illuminate\Support\Facades\Session;
use Modules\BackEnd\Services\AdLanguageService;

class FeLanguageUtils
{
    public static function getCurrentLanguage()
    {
        $languageData = Session::get('frontend-language');
        return $languageData ? json_decode($languageData) : null;
    }

    public static function setCurrentLanguage(&$language)
    {
        if (!$language) {
            $language = AdLanguageService::getDefaultLanguage();
        }
        Session::put('frontend-language', json_encode($language, JSON_UNESCAPED_UNICODE));
    }

    public static function clearCurrentLanguage()
    {
        Session::forget('frontend-language');
    }

    public static function getRouteLanguageCode(): ?string
    {
        $languageCode = \Route::current()?->parameter('languageCode');
        if ($languageCode) {
            return $languageCode;
        }

        $language = self::getCurrentLanguage();
        if ($language && !$language->is_default) {
            return $language->code;
        }

        return null;
    }

    public static function getRouteLanguageParams(): array
    {
        $languageCode = self::getRouteLanguageCode();

        return $languageCode ? ['languageCode' => $languageCode] : [];
    }
}
