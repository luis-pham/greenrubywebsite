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
}
