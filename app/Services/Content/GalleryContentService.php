<?php

namespace App\Services\Content;

use Modules\BackEnd\Entities\AdLanguage;
use Modules\BackEnd\Services\AppExpActivityService;
use Modules\FrontEnd\Constants\PageCodeConsts;
use Modules\FrontEnd\Helpers\FeUtils;
use Modules\FrontEnd\Services\GalleryService;

/**
 * GET /gallery. Mirrors GalleryController@index + GalleryService: categories are the page-config gallery
 * keys with fixed per-language slugs; every listing starts with the VR/360 items, then the category's
 * items (or all categories for "all"); pages of config('frontend.paginationGalleryLimit').
 */
class GalleryContentService
{
    /**
     * Returns null when the category slug is unknown or the page is past the end (Blade 404).
     * A slug of the other language is accepted; "canonicalCategorySlug" then differs from the request
     * (the Blade site 301s to it).
     */
    public static function index(AdLanguage $language, ?string $slug, int $page): ?array
    {
        $languageId = (int) $language->id;
        $canonical = null;
        if ($slug !== null && $slug !== '') {
            $canonical = GalleryService::getCanonicalSlug($slug, $language->code);
            if (!$canonical) {
                return null;
            }
        }

        $items = collect(GalleryService::getConfigGalleries($languageId, $canonical))
            ->map(fn ($item) => self::item($item))
            ->filter()
            ->values();

        $perPage = (int) config('frontend.paginationGalleryLimit', 20);
        $pagination = PageConfigContentService::paginate($page, $perPage, $items->count());
        if ($items->count() > 0 && $page > $pagination['lastPage']) {
            return null;
        }

        return [
            'seo' => PageConfigContentService::seo(PageCodeConsts::GALLERY, $language),
            'categories' => self::categories($language),
            'canonicalCategorySlug' => $canonical,
            'items' => $items->forPage($page, $perPage)->values(),
            'pagination' => $pagination,
            'featuredExperiences' => ExperienceContentService::presentMany(
                AppExpActivityService::getExpActivityFeatured($languageId),
                $language
            ),
        ];
    }

    /** Filter tabs (GalleryService::getGalleryFilter), with the number of items in each category (VR items excluded). */
    public static function categories(AdLanguage $language): array
    {
        $config = FeUtils::getPageConfigByCode(PageCodeConsts::GALLERY, (int) $language->id);

        return collect($config)
            ->filter(fn ($value, $key) => self::isCategoryKey($key))
            ->map(fn ($value, $key) => [
                'slug' => GalleryService::getLocalizedSlugForConfigKey($key, $language->code) ?? $key,
                'name' => __('frontend::page.' . $key, [], $language->code),
                'count' => count(PageConfigContentService::gallery($value)),
                // "SEO danh mục" section of the gallery page config; null = frontend default.
                'seo' => [
                    'title' => ItineraryContentService::clean($config['seo-title-' . $key] ?? null),
                    'description' => ItineraryContentService::clean($config['seo-description-' . $key] ?? null),
                ],
            ])
            ->values()
            ->all();
    }

    /** Gallery page config also holds the per-category SEO textboxes ("seo-title-…"): only "gallery-file…" keys are categories. */
    public static function isCategoryKey(string $key): bool
    {
        return str_starts_with($key, 'gallery-file') && !str_ends_with($key, '-vr-360');
    }

    /** Blade caption is the file "title"; alt prefers the description. */
    protected static function item($item): ?array
    {
        $g = PageConfigContentService::galleryItem($item);
        if (!$g) {
            return null;
        }

        return [
            'src' => $g['src'],
            'thumbnail' => $g['thumbnail'],
            'title' => $g['title'],
            'alt' => $g['alt'],
            'type' => $g['type'],
        ];
    }
}
