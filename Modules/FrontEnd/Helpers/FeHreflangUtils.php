<?php

namespace Modules\FrontEnd\Helpers;

use Illuminate\Support\Facades\Route;
use Modules\BackEnd\Entities\AppArticle;
use Modules\BackEnd\Entities\AppItinerary;
use Modules\BackEnd\Helpers\Utilities;
use Modules\BackEnd\Services\AdLanguageService;
use Modules\BackEnd\Services\AppExpActivityService;
use Modules\FrontEnd\Services\AppCruiseService;

class FeHreflangUtils
{
    private const SKIP_ROUTE_PREFIXES = [
        'frontend.sitemap.',
        'frontend.image.',
        'frontend.api.',
        'frontend.cookie.',
        'api.',
    ];

    public static function getAlternateLinks(): array
    {
        $route = Route::current();
        if (!$route || !$route->getName()) {
            return [];
        }

        $routeName = $route->getName();
        foreach (self::SKIP_ROUTE_PREFIXES as $prefix) {
            if (str_starts_with($routeName, $prefix)) {
                return [];
            }
        }

        $baseName = preg_replace('/\|multi-language$/', '', $routeName);
        if (!str_starts_with($baseName, 'frontend.')) {
            return [];
        }

        $defaultLanguage = AdLanguageService::getDefaultLanguage();
        $languages = AdLanguageService::getAll();
        $currentParams = $route->parameters();
        $links = [];

        foreach ($languages as $language) {
            $isDefault = (bool) $language->is_default;
            $resolvedParams = self::resolveParams($baseName, $currentParams, $language);
            if ($resolvedParams === null) {
                continue;
            }

            $name = $isDefault ? $baseName : Utilities::bindRouteNameMultiLanguage($baseName);

            try {
                $url = route($name, $resolvedParams);
            } catch (\Throwable) {
                continue;
            }

            $links[] = ['hreflang' => $language->code, 'url' => $url];
        }

        if (count($links) > 1) {
            $defaultLink = collect($links)->firstWhere('hreflang', $defaultLanguage->code);
            if ($defaultLink) {
                $links[] = ['hreflang' => 'x-default', 'url' => $defaultLink['url']];
            }
        }

        return $links;
    }

    private static function resolveParams(string $baseName, array $params, $language): ?array
    {
        unset($params['languageCode']);

        if (!$language->is_default) {
            $params['languageCode'] = $language->code;
        }

        return match ($baseName) {
            'frontend.cruise.show' => self::resolveCruiseShow($params, $language),
            'frontend.itinerary.show' => self::resolveItineraryShow($params, $language),
            'frontend.experience.show' => self::resolveExperienceShow($params, $language),
            'frontend.article.show' => self::resolveArticleShow($params, $language),
            default => $params,
        };
    }

    private static function resolveCruiseShow(array $params, $language): ?array
    {
        if (empty($params['id'])) {
            return null;
        }

        $cruise = AppCruiseService::findByIdJoin($params['id'], $language->id);
        if (!$cruise) {
            return null;
        }

        $params['slug'] = Utilities::convertToAlias($cruise->name);

        return $params;
    }

    private static function resolveItineraryShow(array $params, $language): ?array
    {
        if (empty($params['cruise_id']) || empty($params['itinerary_id'])) {
            return null;
        }

        $cruise = AppCruiseService::findByIdJoin($params['cruise_id'], $language->id);
        if (!$cruise) {
            return null;
        }

        $itinerary = AppItinerary::where('id', $params['itinerary_id'])
            ->where('language_id', $language->id)
            ->first()
            ?? AppItinerary::where('id', $params['itinerary_id'])->first();

        if (!$itinerary) {
            return null;
        }

        $params['slug'] = Utilities::convertToAlias($itinerary->name);

        return $params;
    }

    private static function resolveExperienceShow(array $params, $language): ?array
    {
        if (empty($params['id'])) {
            return null;
        }

        $experience = AppExpActivityService::getById($params['id'], $language->id);
        if (!$experience) {
            return null;
        }

        $params['slug'] = Utilities::convertToAlias($experience->name);

        return $params;
    }

    private static function resolveArticleShow(array $params, $language): ?array
    {
        if (empty($params['id'])) {
            return null;
        }

        $article = AppArticle::where('id', $params['id'])
            ->where('language_id', $language->id)
            ->first();

        if (!$article) {
            return null;
        }

        $params['slug'] = Utilities::convertToAlias($article->title);

        return $params;
    }
}
