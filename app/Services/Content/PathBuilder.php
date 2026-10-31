<?php

namespace App\Services\Content;

use App\Services\Locale\LocaleRegistry;
use Modules\BackEnd\Entities\AdLanguage;

/**
 * Public URL paths, the PHP twin of greenruby-web src/lib/i18n.ts + src/views/blog/paths.ts (12 §5.2, phase 1C-3).
 *
 * en: no prefix, English segments. vi: /vi + Vietnamese segments (route_style 'legacy', unchanged). Any other
 * language (route_style 'intl'): /{url_prefix} + the ENGLISH segments (12 §7), never the Vietnamese ones that
 * Laravel's shared multi-language routes produce. Checked against scripts/i18n/paths.fixture.json (84 shapes)
 * by greenruby-web/scripts/i18n/cms/pathbuilder-check.php.
 */
final class PathBuilder
{
    /** Copy of `segments` in greenruby-web src/lib/i18n.ts (keep in sync; the fixture check fails otherwise). */
    public const SEGMENTS = [
        'cruise' => ['en' => 'cruise', 'vi' => 'du-thuyen'],
        'cabin' => ['en' => 'cabin', 'vi' => 'phong-nghi'],
        'itinerary' => ['en' => 'itinerary', 'vi' => 'hanh-trinh'],
        'experiences' => ['en' => 'experiences', 'vi' => 'hoat-dong-trai-nghiem'],
        'services' => ['en' => 'services', 'vi' => 'dich-vu'],
        'gallery' => ['en' => 'gallery', 'vi' => 'thu-vien'],
        'contact' => ['en' => 'contact', 'vi' => 'lien-he'],
        'aboutUs' => ['en' => 'about-us', 'vi' => 'gioi-thieu'],
        'faq' => ['en' => 'faq', 'vi' => 'cau-hoi-thuong-gap'],
        'blog' => ['en' => 'blog', 'vi' => 'blog'],
        'news' => ['en' => 'news', 'vi' => 'tin-tuc'],
        'booking' => ['en' => 'booking', 'vi' => 'booking'],
        'privacyPolicy' => ['en' => 'privacy-policy', 'vi' => 'chinh-sach-bao-mat'],
        'legal' => ['en' => 'legal', 'vi' => 'phap-ly'],
        'safetyPolicies' => ['en' => 'safety-policies', 'vi' => 'chinh-sach-an-toan'],
        'termsAndConditions' => ['en' => 'terms-and-conditions', 'vi' => 'dieu-khoan-dieu-kien'],
        'paymentMethods' => ['en' => 'payment-methods', 'vi' => 'phuong-thuc-thanh-toan'],
        'cookiePolicy' => ['en' => 'cookie-policy', 'vi' => 'chinh-sach-cookie'],
        'bookingTerms' => ['en' => 'booking-terms-and-conditions', 'vi' => 'dieu-khoan-dat-tour'],
        'ecoDashboard' => ['en' => 'eco-dashboard', 'vi' => 'bao-cao-sinh-thai'],
        'brandFacts' => ['en' => 'about-us/brand-facts', 'vi' => 'gioi-thieu/thong-tin-thuong-hieu'],
        'guides' => ['en' => 'guides', 'vi' => 'cam-nang'],
        'destinations' => ['en' => 'destinations', 'vi' => 'dia-diem-tham-quan'],
        'guideFirstTime' => ['en' => 'guides/first-time-halong-bay-cruise', 'vi' => 'cam-nang/lan-dau-di-du-thuyen-ha-long'],
        'guideTransfer' => ['en' => 'guides/hanoi-to-halong-bay', 'vi' => 'cam-nang/ha-noi-di-ha-long'],
    ];

    /** News category slugs per legacy language (blog/paths.ts NEWS_CATEGORY). */
    public const NEWS_CATEGORY = ['en' => 'news', 'vi' => 'tin-tuc'];

    public static function home($language): string
    {
        return self::prefix($language) ?: '/';
    }

    public static function section($language, string $section): string
    {
        return self::prefix($language) . '/' . self::segment($language, $section);
    }

    public static function cruise($language, string $slug, int $id): string
    {
        return self::section($language, 'cruise') . "/{$slug}-{$id}.html";
    }

    public static function cabin($language, string $slug, int $id): string
    {
        return self::section($language, 'cabin') . "/{$slug}-{$id}.html";
    }

    public static function itinerary($language, string $slug, int $cruiseId, int $itineraryId): string
    {
        return self::section($language, 'itinerary') . "/{$slug}-{$cruiseId}-{$itineraryId}.html";
    }

    public static function experience($language, string $slug, int $id): string
    {
        return self::section($language, 'experiences') . "/{$slug}-{$id}.html";
    }

    public static function destination($language, string $slug): string
    {
        return self::section($language, 'destinations') . '/' . $slug;
    }

    public static function blogCategory($language, string $categorySlug): string
    {
        return in_array($categorySlug, self::NEWS_CATEGORY, true)
            ? self::section($language, 'news')
            : self::section($language, 'blog') . '/' . $categorySlug;
    }

    public static function article($language, string $categorySlug, string $slug): string
    {
        return in_array($categorySlug, self::NEWS_CATEGORY, true)
            ? self::section($language, 'news') . "/{$slug}.html"
            : self::section($language, 'blog') . "/{$categorySlug}/{$slug}.html";
    }

    public static function faqGroup($language, string $groupSlug): string
    {
        return self::section($language, 'faq') . '/' . $groupSlug;
    }

    public static function galleryCategory($language, string $categorySlug): string
    {
        return self::section($language, 'gallery') . '/' . $categorySlug;
    }

    /** '' for the default language, '/vi' for vi, '/{url_prefix}' for every other language. */
    public static function prefix($language): string
    {
        $language = self::language($language);
        if (!$language || (bool) $language->is_default) {
            return '';
        }
        $prefix = trim((string) ($language->getAttribute('url_prefix') ?? ''), '/');

        return '/' . ($prefix !== '' ? $prefix : strtolower((string) $language->code));
    }

    /** The Vietnamese segment for vi; the English one for en and every intl language. */
    public static function segment($language, string $section): string
    {
        $language = self::language($language);
        $code = $language && LocaleRegistry::isLegacy($language) ? strtolower((string) $language->code) : 'en';

        return self::SEGMENTS[$section][$code] ?? self::SEGMENTS[$section]['en'];
    }

    /**
     * Rewrites a path that Laravel's shared multi-language routes built for an intl language (which carry the
     * Vietnamese segments, e.g. /ja/cau-hoi-thuong-gap) to this builder's form (/ja/faq). Paths of legacy
     * languages and paths without a known segment are returned unchanged.
     */
    public static function normalizeIntlPath(string $path, $language): string
    {
        $language = self::language($language);
        if (!$language || LocaleRegistry::isLegacy($language)) {
            return $path;
        }
        $prefix = self::prefix($language);
        if ($prefix === '' || !str_starts_with($path . '/', $prefix . '/')) {
            return $path;
        }
        $rest = ltrim(substr($path, strlen($prefix)), '/');
        $vi = self::SEGMENTS;
        uasort($vi, fn ($a, $b) => strlen($b['vi']) <=> strlen($a['vi'])); // longest first: gioi-thieu/thong-tin… before gioi-thieu
        foreach ($vi as $segments) {
            if ($rest === $segments['vi'] || str_starts_with($rest, $segments['vi'] . '/')) {
                return $prefix . '/' . $segments['en'] . substr($rest, strlen($segments['vi']));
            }
        }

        return $path;
    }

    private static function language($language): ?AdLanguage
    {
        return $language instanceof AdLanguage ? $language : LocaleRegistry::byCode((string) $language);
    }
}
