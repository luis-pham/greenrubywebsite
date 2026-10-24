<?php

namespace App\Services\Content;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\BackEnd\Constants\PageConfigConsts;
use Modules\BackEnd\Entities\AdLanguage;
use Modules\BackEnd\Helpers\Utilities;
use Modules\BackEnd\Services\AppPageService;
use Modules\FrontEnd\Constants\PageCodeConsts;
use Modules\FrontEnd\Helpers\FeUtils;
use Modules\FrontEnd\Services\AppCruiseItineraryService;
use Modules\FrontEnd\Services\AppPageConfigService;

/**
 * Generic page-config ("app_page_config") reader for the Content API.
 *
 * Same data as FeUtils::getPageConfigByCode, reshaped: keys lose the "{pageCode}-" prefix and are
 * camelCased; text editor values are sanitized HTML under "<name>Html"; images become "/upload/..." paths;
 * galleries become GalleryItem[]; sourceData pickers are hydrated into the same summaries the
 * dedicated endpoints return (admin order preserved).
 */
class PageConfigContentService
{
    /** Page codes served by GET /pages/{code} (homepage has its own builder). */
    public const PAGE_CODES = [
        PageCodeConsts::ABOUT,
        PageCodeConsts::GALLERY,
        'faq',
        PageCodeConsts::ARTICLE,
        PageCodeConsts::EXPERIENCE,
        PageCodeConsts::SERVICE,
        PageCodeConsts::CONTACT,
        PageCodeConsts::BOOKING,
        PageCodeConsts::LEGAL,
        PageCodeConsts::ITINERARY,
        PageCodeConsts::SAFETY_POLICIES,
        PageCodeConsts::TERMS_AND_CONDITIONS,
        PageCodeConsts::PRIVACY_POLICIES,
        PageCodeConsts::PAYMENT_METHODS,
        self::ECO_DASHBOARD,
        ...self::NEXT_ONLY_GUIDES,
    ];

    /** Blade pages rendered with robots "noindex, follow". */
    public const NO_INDEX_CODES = [
        PageCodeConsts::BOOKING,
        PageCodeConsts::LEGAL,
        PageCodeConsts::SAFETY_POLICIES,
        PageCodeConsts::TERMS_AND_CONDITIONS,
        PageCodeConsts::PRIVACY_POLICIES,
        PageCodeConsts::PAYMENT_METHODS,
    ];

    /** Next.js-only page (no Blade controller): SEO comes from app_page, null when empty so the frontend keeps its own copy. */
    public const ECO_DASHBOARD = 'eco-dashboard';

    /** Destinations hub and travel guides, also Next.js-only: empty SEO is returned as null like the eco dashboard. */
    public const NEXT_ONLY_GUIDES = ['destinations', 'guide-first-time', 'guide-transfer'];

    /** Pages with a show/hide switch (app_page.is_visible): the Next.js-only hubs. */
    public static function hideable(?string $code): bool
    {
        return in_array($code, self::NEXT_ONLY_GUIDES, true);
    }

    /** False only when the editor switched the page off for this language. */
    public static function visible(string $code, AdLanguage $language): bool
    {
        if (!self::hideable($code)) {
            return true;
        }
        $flag = DB::table('app_page')->where('code', $code)->where('language_id', $language->id)->value('is_visible');

        return $flag === null || (bool) $flag;
    }

    /** Codes of the pages switched off for this language (site payload: footer, llms.txt, guide links). */
    public static function hiddenCodes(AdLanguage $language): array
    {
        return DB::table('app_page')->whereIn('code', self::NEXT_ONLY_GUIDES)->where('language_id', $language->id)
            ->where('is_visible', false)->pluck('code')->values()->all();
    }

    /** GET /pages/{code} payload, or null for codes that are not exposed. */
    public static function page(string $code, AdLanguage $language): ?array
    {
        if (!in_array($code, self::PAGE_CODES, true)) {
            return null;
        }

        $data = [
            'code' => $code,
            'seo' => self::seo($code, $language),
            'noIndex' => in_array($code, self::NO_INDEX_CODES, true),
            'visible' => self::visible($code, $language),
            'config' => (object) self::decode($code, $language),
        ];

        // About & booking heroes use images of scheduled itineraries (AppCruiseItineraryService::getHeroBannerImages).
        if (in_array($code, [PageCodeConsts::ABOUT, PageCodeConsts::BOOKING], true)) {
            $data['heroImages'] = self::heroImages($language);
        }
        $data['updatedAt'] = self::updatedAt($code, $language);

        return $data;
    }

    /**
     * Hub SEO like the Blade controllers: app_page.seo_title / seo_description, else the controller default.
     * Titles never carry the " | {website-name}" suffix the Blade site appends (the frontend adds the brand).
     */
    public static function seo(string $code, AdLanguage $language, ?string $defaultTitle = null, ?string $defaultDescription = null): array
    {
        $config = Utilities::getAllConfig($language);
        $locale = $language->code;
        $nextOnly = $code === self::ECO_DASHBOARD || in_array($code, self::NEXT_ONLY_GUIDES, true);
        $defaultTitle ??= match (true) {
            $nextOnly => '',
            $code === PageCodeConsts::ARTICLE => 'Blog',
            $code === 'faq' => __('frontend::faq.page_title', [], $locale),
            $code === PageCodeConsts::LEGAL => __('frontend::page.page_legal_title', [], $locale),
            $code === PageCodeConsts::SAFETY_POLICIES => __('frontend::page.page_safety_policies_title', [], $locale),
            $code === PageCodeConsts::TERMS_AND_CONDITIONS => __('frontend::page.page_terms_and_conditions_title', [], $locale),
            $code === PageCodeConsts::PRIVACY_POLICIES => $locale === 'en' ? 'Privacy Policy' : __('frontend::page.page_privacy_policies_title', [], $locale),
            $code === PageCodeConsts::PAYMENT_METHODS => __('frontend::page.page_payment_methods_title', [], $locale),
            default => FeUtils::bindWebsiteTitle($config['website-name'] ?? '', $config['website-slogan'] ?? ''),
        };
        $defaultDescription ??= $nextOnly ? '' : (($code === PageCodeConsts::PRIVACY_POLICIES && $locale === 'en')
            ? 'How Green Ruby Cruises collects, uses, and protects your personal information. GDPR compliant.'
            : (string) ($config['website-description'] ?? ''));

        $seo = FeUtils::resolveHubSeo($code, $language, fn () => $defaultTitle, $defaultDescription);

        return [
            'title' => ItineraryContentService::clean($seo['title']),
            'description' => ItineraryContentService::clean($seo['description']),
        ];
    }

    /** Decoded, camelCased page config (see class doc). */
    public static function decode(string $code, AdLanguage $language): array
    {
        $out = [];
        foreach (AppPageConfigService::getByPageCode($code, (int) $language->id) as $row) {
            $name = self::keyName($code, (string) $row->key);
            $value = $row->value;

            switch ((int) $row->type) {
                case (int) config('backend.configInput.texteditor'):
                    $out[$name . 'Html'] = self::html($value);
                    break;
                case (int) config('backend.configInput.image'):
                    $out[$name] = CabinContentService::fileLink(ItineraryContentService::clean($value));
                    break;
                case (int) config('backend.configInput.gallery'):
                    $out[$name] = self::gallery(json_decode((string) $value));
                    break;
                case (int) config('backend.configInput.sourceData'):
                    $out[$name] = self::sourceData(json_decode((string) $value, true), (string) $row->list_value, $language);
                    break;
                default:
                    $out[$name] = ItineraryContentService::clean(is_string($value) ? str_replace("\r\n", "\n", $value) : $value);
                    break;
            }
        }

        return $out;
    }

    /** "about-us-eco-title" (page "about-us") => "ecoTitle". */
    public static function keyName(string $code, string $key): string
    {
        $stripped = str_starts_with($key, $code . '-') ? substr($key, strlen($code) + 1) : $key;

        return Str::camel($stripped);
    }

    /** Page-config gallery JSON ([{id,title,link,thumbnail,extension,description,is_360}]) => GalleryItem[]. */
    public static function gallery($items): array
    {
        return collect(is_iterable($items) ? $items : [])
            ->map(fn ($item) => self::galleryItem($item))
            ->filter()
            ->values()
            ->all();
    }

    public static function galleryItem($item): ?array
    {
        $field = fn (string $key) => is_array($item) ? ($item[$key] ?? null) : (is_object($item) ? ($item->{$key} ?? null) : null);
        $src = CabinContentService::fileLink(ItineraryContentService::clean($field('link')));
        if ($src === null) {
            return null;
        }

        $extension = strtolower((string) ($field('extension') ?: pathinfo(parse_url($src, PHP_URL_PATH) ?: '', PATHINFO_EXTENSION)));
        $type = (bool) $field('is_360')
            ? '360'
            : (in_array($extension, ['mp4', 'webm', 'mov', 'm4v', 'ogg'], true) ? 'video' : 'image');
        $title = ItineraryContentService::clean($field('title') ?? $field('name'));
        // Media-library uploads default their title to the file name ("img-75-550x824.png"): not a caption.
        if ($title !== null && preg_match('/\.(jpe?g|png|gif|webp|avif|svg|bmp|mp4|webm|mov|m4v|ogg)$/i', $title)) {
            $title = null;
        }
        $description = ItineraryContentService::clean($field('description'));

        return [
            'src' => $src,
            'thumbnail' => CabinContentService::fileLink(ItineraryContentService::clean($field('thumbnail'))) ?? $src,
            'title' => $title,
            'description' => $description,
            'alt' => $description ?? $title,
            'type' => $type,
        ];
    }

    /**
     * Hydrates a sourceData picker exactly like FeUtils::getPageConfigSourceDataValue (same queries, admin
     * order, missing/unpublished ids dropped) and presents each row like the matching endpoint does.
     */
    public static function sourceData($ids, string $type, AdLanguage $language): array
    {
        if (!is_array($ids) || $ids === []) {
            return [];
        }
        $rows = collect(FeUtils::getPageConfigSourceDataValue($ids, $type, (int) $language->id));

        return match ($type) {
            PageConfigConsts::SOURCE_DATA_TYPE_ARTICLE => $rows->map(fn ($a) => ArticleContentService::summary($a))->values()->all(),
            PageConfigConsts::SOURCE_DATA_TYPE_FAQ => FaqContentService::presentMany($rows, $language),
            PageConfigConsts::SOURCE_DATA_TYPE_CRUISE => $rows->map(fn ($c) => CruiseContentService::summary($c))->values()->all(),
            PageConfigConsts::SOURCE_DATA_TYPE_CABIN => $rows->map(fn ($c) => CabinContentService::summary($c))->values()->all(),
            PageConfigConsts::SOURCE_DATA_TYPE_SERVICE => ServiceContentService::presentMany($rows, $language),
            PageConfigConsts::SOURCE_DATA_TYPE_EXP_ACTIVITY => ExperienceContentService::presentMany($rows, $language),
            PageConfigConsts::SOURCE_DATA_TYPE_AMENITY => $rows->map(fn ($a) => [
                'id' => (int) $a->id,
                'name' => $a->name,
                'description' => ItineraryContentService::plain($a->description),
                'icon' => CabinContentService::fileLink($a->icon),
            ])->values()->all(),
            PageConfigConsts::SOURCE_DATA_TYPE_CRUISE_ITINERARY => $rows->map(fn ($i) => ItineraryContentService::summary(
                $i,
                (int) $i->cruise_id,
                $i->cruise_name,
                null,
                $language,
                null
            ))->values()->all(),
            default => [],
        };
    }

    /** Images of scheduled itineraries (Blade hero), without the bundled placeholder. */
    public static function heroImages(AdLanguage $language, int $limit = 2): array
    {
        return collect(AppCruiseItineraryService::getHeroBannerImages((int) $language->id, $limit))
            ->filter(fn ($img) => str_starts_with((string) $img->link, '/'))
            ->map(fn ($img) => [
                'src' => CabinContentService::fileLink($img->link),
                'alt' => ItineraryContentService::clean($img->alt),
            ])
            ->values()
            ->all();
    }

    /** Latest change of the page row or any of its config rows (null if never saved). */
    public static function updatedAt(string $code, AdLanguage $language): ?string
    {
        $page = AppPageService::getByCode($code, (int) $language->id);
        if (!$page) {
            return null;
        }
        $configAt = DB::table('app_page_config')->where('page_id', $page->id)->max('updated_at');
        $pageAt = $page->getRawOriginal('updated_at') ?: $page->getRawOriginal('created_at');
        $latest = collect([$configAt, $pageAt])->filter()->map(fn ($d) => Carbon::parse($d))->max();

        return $latest ? CabinContentService::isoDate($latest->format('Y-m-d H:i:s')) : null;
    }

    /** Sanitized HTML, null when empty. */
    public static function html($value): ?string
    {
        $value = ItineraryContentService::clean($value);
        if ($value === null) {
            return null;
        }

        return ItineraryContentService::clean(safe_html($value));
    }

    /** Drops config keys a page payload already exposes at the top level. */
    public static function without(array $config, array $keys): array
    {
        return array_diff_key($config, array_flip($keys));
    }

    public static function paginate(int $page, int $perPage, int $total): array
    {
        return [
            'page' => $page,
            'perPage' => $perPage,
            'total' => $total,
            'lastPage' => max(1, (int) ceil($total / max(1, $perPage))),
        ];
    }

    public static function pageNumber($value): int
    {
        return ctype_digit((string) $value) && (int) $value > 0 ? (int) $value : 1;
    }

    /** " - Page N" suffix the Blade listings add to titles for page > 1. */
    public static function pageSuffix(?string $title, int $page, AdLanguage $language): ?string
    {
        if ($title === null || $page <= 1) {
            return $title;
        }

        return $title . ' - ' . __('frontend::common.page', [], $language->code) . ' ' . $page;
    }
}
