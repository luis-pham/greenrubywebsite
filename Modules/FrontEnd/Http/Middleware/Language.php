<?php

namespace Modules\FrontEnd\Http\Middleware;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;
use Modules\BackEnd\Services\AdLanguageService;
use Modules\BackEnd\Helpers\Utilities;
use Modules\FrontEnd\Helpers\FeLanguageUtils;

class Language
{
    public function handle($request, \Closure $next)
    {
        $defaultLanguage = AdLanguageService::getDefaultLanguage();
        $currentLanguage = FeLanguageUtils::getCurrentLanguage();
        $routeName = $request->route()->getName();
        if ($routeName == 'frontend.index' && $currentLanguage == null) {
            $languageCode = $this->getPreferredLanguage();
            $currentLanguage = AdLanguageService::findByCode($languageCode);
            if (!$currentLanguage) {
                $currentLanguage = $defaultLanguage;
            }
            if (!$currentLanguage->is_default) {
                return redirect()->route(Utilities::bindRouteNameMultiLanguage('frontend.index'), ['languageCode' => $languageCode]);
            }

            FeLanguageUtils::setCurrentLanguage($currentLanguage);
            App::setLocale($currentLanguage->code);
            
            return $next($request);
        }

        $languageCode = $request->route('languageCode');
        if (!$languageCode) {
            $firstSegment = $request->segment(1);
            if ($firstSegment) {
                $languageBySegment = AdLanguageService::findByCode($firstSegment);
                if ($languageBySegment) {
                    $languageCode = $languageBySegment->code;
                }
            }
        }
        if (!$languageCode) {
            $languageCode = $defaultLanguage ? $defaultLanguage->code : config('app.locale');
        }

        if (!$currentLanguage || $currentLanguage->code != $languageCode) {
            $language = AdLanguageService::findByCode($languageCode);
            FeLanguageUtils::setCurrentLanguage($language);
            $currentLanguage = $language;
        }
        App::setLocale($currentLanguage->code);

        return $next($request);
    }

    private function getPreferredLanguage()
    {
        $languageCode = request()->getPreferredLanguage() ?? config('app.locale');
        if (Str::startsWith($languageCode, 'en')) {
            $languageCode = 'en';
        }

        return $languageCode;
    }
}
