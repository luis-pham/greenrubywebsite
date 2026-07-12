<?php

namespace Modules\FrontEnd\Helpers;

use Illuminate\Support\Facades\Route;
use Modules\BackEnd\Entities\AppCategory;
use Modules\BackEnd\Entities\AppCruise;
use Modules\BackEnd\Entities\AppExpActivity;
use Modules\BackEnd\Entities\AppGroup;
use Modules\BackEnd\Entities\AppItinerary;
use Modules\BackEnd\Helpers\Utilities;
use Modules\BackEnd\Services\AdLanguageService;
use Modules\BackEnd\Services\AppCategoryService;
use Modules\BackEnd\Services\AppExpActivityService;
use Modules\BackEnd\Services\AppGroupService;
use Modules\FrontEnd\Services\AppArticleService;
use Modules\FrontEnd\Services\AppCruiseItineraryService;
use Modules\FrontEnd\Services\AppCruiseService;
use Modules\FrontEnd\Services\GalleryService;

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

        $sourceLanguageId = FeLanguageUtils::getCurrentLanguage()?->id;

        return self::getAlternateLinksForRoute($baseName, $route->parameters(), $sourceLanguageId);
    }

    public static function getAlternateLinksForRoute(string $baseRouteName, array $params = [], ?int $sourceLanguageId = null): array
    {
        $baseName = preg_replace('/\|multi-language$/', '', $baseRouteName);
        if (!str_starts_with($baseName, 'frontend.')) {
            return [];
        }

        $defaultLanguage = AdLanguageService::getDefaultLanguage();
        $languages = AdLanguageService::getAll();
        $links = [];

        foreach ($languages as $language) {
            $resolvedParams = self::resolveParams($baseName, $params, $language, $sourceLanguageId);
            if ($resolvedParams === null) {
                continue;
            }

            $name = $language->is_default ? $baseName : Utilities::bindRouteNameMultiLanguage($baseName);

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

    private static function resolveParams(string $baseName, array $params, $language, ?int $sourceLanguageId = null): ?array
    {
        unset($params['languageCode']);

        if (!$language->is_default) {
            $params['languageCode'] = $language->code;
        }

        return match ($baseName) {
            'frontend.cruise.show' => self::resolveCruiseShow($params, $language, $sourceLanguageId),
            'frontend.itinerary.show' => self::resolveItineraryShow($params, $language, $sourceLanguageId),
            'frontend.experience.show' => self::resolveExperienceShow($params, $language, $sourceLanguageId),
            'frontend.article.show' => self::resolveArticleShow($params, $language, $sourceLanguageId),
            'frontend.article.category' => self::resolveArticleCategory($params, $language, $sourceLanguageId),
            'frontend.faq.category' => self::resolveFaqCategory($params, $language, $sourceLanguageId),
            'frontend.gallery.category' => self::resolveGalleryCategory($params, $language, $sourceLanguageId),
            default => $params,
        };
    }

    private static function resolveSourceLanguage(?int $sourceLanguageId)
    {
        if ($sourceLanguageId) {
            return AdLanguageService::getAll()->firstWhere('id', $sourceLanguageId);
        }

        return FeLanguageUtils::getCurrentLanguage();
    }

    private static function resolveCruiseShow(array $params, $language, ?int $sourceLanguageId = null): ?array
    {
        if (empty($params['id'])) {
            return null;
        }

        $sourceLanguage = self::resolveSourceLanguage($sourceLanguageId);
        $cruise = self::findCruiseCounterpart((int) $params['id'], $sourceLanguage?->id, $language);
        if (!$cruise) {
            return null;
        }

        $params['id'] = $cruise->id;
        $params['slug'] = Utilities::convertToAlias($cruise->name);

        return $params;
    }

    private static function findCruiseCounterpart(int $sourceCruiseId, ?int $sourceLanguageId, $targetLanguage): ?AppCruise
    {
        $sameId = AppCruiseService::findByIdJoin($sourceCruiseId, $targetLanguage->id);
        if ($sameId) {
            return $sameId;
        }

        $sourceCruise = null;
        if ($sourceLanguageId) {
            $sourceCruise = AppCruiseService::findByIdJoin($sourceCruiseId, $sourceLanguageId);
        }
        if (!$sourceCruise) {
            $sourceCruise = AppCruise::where('id', $sourceCruiseId)->first();
        }
        if (!$sourceCruise) {
            return null;
        }

        $bay = FeCruiseUtils::getBayForCruise($sourceCruise->name);

        return AppCruise::where('language_id', $targetLanguage->id)
            ->get()
            ->first(fn (AppCruise $cruise) => FeCruiseUtils::getBayForCruise($cruise->name) === $bay);
    }

    private static function resolveItineraryShow(array $params, $language, ?int $sourceLanguageId = null): ?array
    {
        if (empty($params['itinerary_id'])) {
            return null;
        }

        $sourceItinerary = AppItinerary::where('id', $params['itinerary_id'])->first();
        if (!$sourceItinerary) {
            return null;
        }

        $targetItinerary = AppItinerary::where('language_id', $language->id)
            ->where('duration', $sourceItinerary->duration)
            ->where('bay', $sourceItinerary->bay)
            ->orderBy('id')
            ->first();

        if (!$targetItinerary) {
            return null;
        }

        $canonical = AppCruiseItineraryService::resolveCanonicalShowParams($targetItinerary->id, $language->id);
        if (!$canonical) {
            return null;
        }

        unset($params['slug'], $params['cruise_id'], $params['itinerary_id']);

        return array_merge($params, $canonical);
    }

    private static function resolveExperienceShow(array $params, $language, ?int $sourceLanguageId = null): ?array
    {
        if (empty($params['id'])) {
            return null;
        }

        $sourceLanguage = self::resolveSourceLanguage($sourceLanguageId);
        $experience = self::findExperienceCounterpart((int) $params['id'], $sourceLanguage?->id, $language);
        if (!$experience) {
            return null;
        }

        $params['id'] = $experience->id;
        $params['slug'] = Utilities::convertToAlias($experience->name);

        return $params;
    }

    private static function findExperienceCounterpart(int $sourceId, ?int $sourceLanguageId, $targetLanguage): ?AppExpActivity
    {
        $sameId = AppExpActivityService::getById($sourceId, $targetLanguage->id);
        if ($sameId) {
            return $sameId;
        }

        $source = null;
        if ($sourceLanguageId) {
            $source = AppExpActivityService::getById($sourceId, $sourceLanguageId);
        }
        if (!$source) {
            $source = AppExpActivity::where('id', $sourceId)->first();
        }
        if (!$source) {
            return null;
        }

        $sourceImage = self::normalizeAssetPath($source->image_link);
        if (self::isPlaceholderAsset($sourceImage)) {
            return null;
        }

        return AppExpActivity::where('language_id', $targetLanguage->id)
            ->where('duration', $source->duration)
            ->get()
            ->first(function (AppExpActivity $item) use ($sourceImage) {
                $itemImage = self::normalizeAssetPath($item->image_link);
                if (self::isPlaceholderAsset($itemImage)) {
                    return false;
                }

                return $itemImage === $sourceImage;
            });
    }

    private static function normalizeAssetPath(?string $path): string
    {
        $path = trim((string) $path);
        if ($path === '') {
            return '';
        }

        $path = preg_replace('#^https?://[^/]+#i', '', $path) ?? $path;

        return ltrim($path, '/');
    }

    private static function isPlaceholderAsset(string $path): bool
    {
        if ($path === '') {
            return true;
        }

        $blank = self::normalizeAssetPath(config('frontend.imageBlank'));
        if ($blank !== '' && $path === $blank) {
            return true;
        }

        return str_ends_with($path, '/blank.gif') || str_ends_with($path, 'blank.gif');
    }

    private static function resolveArticleCategory(array $params, $language, ?int $sourceLanguageId = null): ?array
    {
        if (empty($params['slug'])) {
            return null;
        }

        $sourceLanguage = self::resolveSourceLanguage($sourceLanguageId);
        if (!$sourceLanguage) {
            return null;
        }

        $articleType = config('backend.categoryType.article');
        $sourceCategory = AppCategoryService::getBySlug($params['slug'], $articleType, $sourceLanguage->id);
        if (!$sourceCategory || $sourceCategory->slug === 'root') {
            return null;
        }

        $targetCategory = self::findArticleCategoryCounterpart($sourceCategory, $language->id);
        if (!$targetCategory) {
            return null;
        }

        $params['slug'] = $targetCategory->slug;

        return $params;
    }

    /**
     * Article categories are nested-set rows per language (ids often differ).
     * Pair by same id first, then by mirrored tree position (lvl + lft).
     */
    public static function findArticleCategoryCounterpart($sourceCategory, int $targetLanguageId)
    {
        $articleType = config('backend.categoryType.article');

        $sameId = AppCategoryService::find($sourceCategory->id, $articleType, $targetLanguageId);
        if ($sameId && $sameId->slug !== 'root') {
            return $sameId;
        }

        return AppCategory::where('type', $articleType)
            ->where('language_id', $targetLanguageId)
            ->where('lvl', $sourceCategory->lvl)
            ->where('lft', $sourceCategory->lft)
            ->where('slug', '!=', 'root')
            ->orderBy('id')
            ->first();
    }

    private static function resolveFaqCategory(array $params, $language, ?int $sourceLanguageId = null): ?array
    {
        if (empty($params['slug'])) {
            return null;
        }

        $sourceLanguage = self::resolveSourceLanguage($sourceLanguageId);
        if (!$sourceLanguage) {
            return null;
        }

        $faqType = config('backend.groupType.faq');
        $sourceGroup = AppGroupService::getBySlug($params['slug'], $faqType, $sourceLanguage->id);
        if (!$sourceGroup || $sourceGroup->slug === 'root') {
            return null;
        }

        $targetGroup = self::findFaqGroupCounterpart($sourceGroup, $language->id);
        if (!$targetGroup) {
            return null;
        }

        $params['slug'] = $targetGroup->slug;

        return $params;
    }

    /**
     * FAQ groups are separate rows per language (different ids). Pair by shared ord.
     */
    public static function findFaqGroupCounterpart($sourceGroup, int $targetLanguageId)
    {
        $faqType = config('backend.groupType.faq');

        $sameId = AppGroupService::find($sourceGroup->id, $faqType, $targetLanguageId);
        if ($sameId && $sameId->slug !== 'root') {
            return $sameId;
        }

        if ($sourceGroup->ord === null) {
            return null;
        }

        return AppGroup::where('type', $faqType)
            ->where('language_id', $targetLanguageId)
            ->where('ord', $sourceGroup->ord)
            ->where('slug', '!=', 'root')
            ->orderBy('id')
            ->first();
    }

    private static function resolveGalleryCategory(array $params, $language, ?int $sourceLanguageId = null): ?array
    {
        if (empty($params['slug'])) {
            return null;
        }

        $canonicalSlug = GalleryService::getCanonicalSlug($params['slug'], $language->code);
        if (!$canonicalSlug) {
            return null;
        }

        $params['slug'] = $canonicalSlug;

        return $params;
    }

    private static function resolveArticleShow(array $params, $language, ?int $sourceLanguageId = null): ?array
    {
        $sourceLanguage = self::resolveSourceLanguage($sourceLanguageId);

        if (!$sourceLanguage) {
            return null;
        }
        $sourceArticle = null;

        if (!empty($params['categorySlug']) && !empty($params['articleSlug'])) {
            $sourceArticle = AppArticleService::findByCategoryAndSlug(
                $params['categorySlug'],
                $params['articleSlug'],
                $sourceLanguage->id
            );
        }

        if (!$sourceArticle && !empty($params['id'])) {
            $sourceArticle = AppArticleService::findJoin($params['id'], $sourceLanguage->id);
        }

        if (!$sourceArticle) {
            return null;
        }

        $article = AppArticleService::findJoin($sourceArticle->id, $language->id);
        if (!$article) {
            return null;
        }

        unset($params['id'], $params['slug']);
        $params['categorySlug'] = $article->category_slug;
        $params['articleSlug'] = FeArticleUtils::getArticleSlug($article);

        return $params;
    }
}
