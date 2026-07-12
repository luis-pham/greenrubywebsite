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
        $route = $request->route();
        $routeName = $route ? $route->getName() : null;
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
            if (!$language) {
                $language = $defaultLanguage;
            }
            FeLanguageUtils::setCurrentLanguage($language);
            $currentLanguage = $language;
        }
        if ($currentLanguage) {
            App::setLocale($currentLanguage->code);
        }

        return $next($request);
    }

    private function getPreferredLanguage()
    {
        $preferred = request()->getPreferredLanguage() ?? config('app.locale');
        $preferred = strtolower((string) $preferred);

        $availableCodes = AdLanguageService::getAll()->pluck('code')->filter()->values()->all();
        foreach ($availableCodes as $code) {
            $code = strtolower((string) $code);
            if ($preferred === $code || Str::startsWith($preferred, $code . '-') || Str::startsWith($preferred, $code . '_')) {
                return $code;
            }
        }

        if (Str::startsWith($preferred, 'en')) {
            return in_array('en', $availableCodes, true) ? 'en' : ($availableCodes[0] ?? config('app.locale'));
        }

        $default = AdLanguageService::getDefaultLanguage();

        return $default?->code ?? config('app.locale');
    }
}
