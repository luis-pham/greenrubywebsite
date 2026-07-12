<?php

namespace Modules\FrontEnd\Services;

use Carbon\Carbon;
use Modules\BackEnd\Entities\AppPageConfig;
use Modules\BackEnd\Services\AdLanguageService;
use Modules\FrontEnd\Constants\PageCodeConsts;
use Modules\FrontEnd\Constants\PageConfigKeyConsts;
use Modules\FrontEnd\Helpers\FeLanguageUtils;
use Modules\FrontEnd\Helpers\FeUtils;

class GalleryService
{


    private static function slugMap(): array
    {
        return [
            PageConfigKeyConsts::GALLERY_FILE_ACTIVITIES => [
                'en' => 'activities',
                'vi' => 'hoat-dong',
            ],
            PageConfigKeyConsts::GALLERY_FILE_CABINS_SUITES => [
                'en' => 'cabins-suites',
                'vi' => 'cabin-suite',
            ],
            PageConfigKeyConsts::GALLERY_FILE_NATURE_BAY_VIEWS => [
                'en' => 'nature-bay-views',
                'vi' => 'thien-nhien-va-vinh',
            ],
            PageConfigKeyConsts::GALLERY_FILE_GUESTS_MOMENTS => [
                'en' => 'guests-moments',
                'vi' => 'khoang-khac-du-khach',
            ],
            PageConfigKeyConsts::GALLERY_FILE => [
                'en' => 'onboard-life',
                'vi' => 'cuoc-song-tren-tau',
            ],
        ];
    }

    private static function currentLanguageCode(): string
    {
        return FeLanguageUtils::getCurrentLanguage()->code ?? 'en';
    }

    private static function resolveConfigKey(?string $slug): ?string
    {
        if (!$slug) {
            return null;
        }

        foreach (self::slugMap() as $configKey => $slugs) {
            if (in_array($slug, $slugs, true)) {
                return $configKey;
            }
        }

        return null;
    }

    public static function resolveConfigKeyFromSlug(?string $slug): ?string
    {
        return self::resolveConfigKey($slug);
    }

    public static function getLocalizedSlugForConfigKey(string $configKey, string $langCode): ?string
    {
        $map = self::slugMap();
        if (!isset($map[$configKey])) {
            return null;
        }

        return $map[$configKey][$langCode] ?? $map[$configKey]['en'] ?? null;
    }

    public static function getCanonicalSlug(?string $anySlug, string $langCode): ?string
    {
        $configKey = self::resolveConfigKey($anySlug);
        if (!$configKey) {
            return null;
        }

        return self::getLocalizedSlugForConfigKey($configKey, $langCode);
    }

    public static function getConfigGalleries($languageId, ?string $slug): array
    {
        $pageConfig = FeUtils::getPageConfigByCode(PageCodeConsts::GALLERY, $languageId);
        $rs = $pageConfig[PageConfigKeyConsts::GALLERY_FILE_VR_360];
        $configKey = self::resolveConfigKey($slug);
        $extra = match ($configKey) {
            PageConfigKeyConsts::GALLERY_FILE              => $pageConfig[PageConfigKeyConsts::GALLERY_FILE],
            PageConfigKeyConsts::GALLERY_FILE_ACTIVITIES   => $pageConfig[PageConfigKeyConsts::GALLERY_FILE_ACTIVITIES],
            PageConfigKeyConsts::GALLERY_FILE_CABINS_SUITES => $pageConfig[PageConfigKeyConsts::GALLERY_FILE_CABINS_SUITES],
            PageConfigKeyConsts::GALLERY_FILE_GUESTS_MOMENTS => $pageConfig[PageConfigKeyConsts::GALLERY_FILE_GUESTS_MOMENTS],
            PageConfigKeyConsts::GALLERY_FILE_NATURE_BAY_VIEWS => $pageConfig[PageConfigKeyConsts::GALLERY_FILE_NATURE_BAY_VIEWS],
            default => array_merge(
                $pageConfig[PageConfigKeyConsts::GALLERY_FILE],
                $pageConfig[PageConfigKeyConsts::GALLERY_FILE_ACTIVITIES],
                $pageConfig[PageConfigKeyConsts::GALLERY_FILE_CABINS_SUITES],
                $pageConfig[PageConfigKeyConsts::GALLERY_FILE_GUESTS_MOMENTS],
                $pageConfig[PageConfigKeyConsts::GALLERY_FILE_NATURE_BAY_VIEWS],
            )
        };

        return array_merge($rs, $extra);
    }

    public static function getGalleryFilter($languageId = null): array
    {
        $pageConfig = FeUtils::getPageConfigByCode(PageCodeConsts::GALLERY, $languageId);
        if ($languageId) {
            $langCode = AdLanguageService::getAll()->firstWhere('id', $languageId)->code ?? 'en';
        } else {
            $langCode = self::currentLanguageCode();
        }
        $map = self::slugMap();
        return collect(array_keys($pageConfig))
            ->filter(fn($item) => !str_ends_with($item, "-vr-360"))
            ->mapWithKeys(function ($item) use ($map, $langCode) {
                $friendlySlug = $map[$item][$langCode] ?? $map[$item]['en'] ?? $item;
                return [$friendlySlug => __('frontend::page.' . $item)];
            })
            ->toArray();
    }

    public static function getGalleryUpdatedAt($languageId = null)
    {
        $query = AppPageConfig::query()
            ->select('app_page_config.updated_at')
            ->join('app_page', 'app_page.id', '=', 'app_page_config.page_id')
            ->where('app_page.code', PageCodeConsts::GALLERY)
            ->whereIn('app_page_config.key', [
                PageConfigKeyConsts::GALLERY_FILE,
                PageConfigKeyConsts::GALLERY_FILE_CABINS_SUITES,
                PageConfigKeyConsts::GALLERY_FILE_ACTIVITIES,
                PageConfigKeyConsts::GALLERY_FILE_NATURE_BAY_VIEWS,
                PageConfigKeyConsts::GALLERY_FILE_GUESTS_MOMENTS,
                PageConfigKeyConsts::GALLERY_FILE_VR_360,
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
}
