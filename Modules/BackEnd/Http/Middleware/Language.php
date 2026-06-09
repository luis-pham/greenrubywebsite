<?php

namespace Modules\BackEnd\Http\Middleware;

use Modules\BackEnd\Helpers\LanguageUtils;
use Modules\BackEnd\Services\AdLanguageService;

class Language
{
    public function handle($request, \Closure $next)
    {
        $defaultLanguage = AdLanguageService::getDefaultLanguage();
        $languageCode = $request->route('languageCode') ?: $defaultLanguage->code;
        $currentLanguage = LanguageUtils::getCurrentLanguage();
        if (!$currentLanguage || $currentLanguage->code != $languageCode) {
            $language = AdLanguageService::findByCode($languageCode);
            LanguageUtils::setCurrentLanguage($language);
        }

        return $next($request);
    }
}
