<?php

namespace Modules\BackEnd\Helpers;

use Illuminate\Support\Facades\Session;
use Modules\BackEnd\Services\AdLanguageService;

class LanguageUtils
{
    public static function getCurrentLanguage()
    {
        $languageData = Session::get('backend-language');
        return $languageData ? json_decode($languageData) : null;
    }

    public static function setCurrentLanguage(&$language)
    {
        if (!$language) {
            $language = AdLanguageService::getDefaultLanguage();
        }
        Session::put('backend-language', json_encode($language, JSON_UNESCAPED_UNICODE));
    }

    public static function clearCurrentLanguage()
    {
        Session::forget('backend-language');
    }
}
