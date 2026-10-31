<?php

namespace App\Services\Content;

use App\Services\Locale\IntlSitemap;
use App\Services\Locale\LocaleRegistry;
use Illuminate\Support\Facades\DB;
use Modules\BackEnd\Entities\AdLanguage;

/**
 * Menu URLs of a new language (phase 2B). Its menus are seeded copies of the English menus, whose URLs point at
 * English rows; prefixing them with /ja (what the legacy localisation does) links to pages that do not exist.
 * Each English URL is mapped instead:
 *   - a detail page (cruise, cabin, itinerary, experience, destination, article) → the linked row of the language,
 *     at TranslationResolver's path, when it is public;
 *   - a section page (itineraries, experiences, blog, FAQ, services, …) → the language's section page when it is
 *     public (IntlSitemap::hubOpen: same rules as its sitemap entry);
 *   - anything else, or a page that is not public in the language → the English URL, never a 404.
 * en/vi menus are not affected.
 */
final class IntlMenuUrls
{
    /** First path segment of an English URL → section (PathBuilder::SEGMENTS 'en'). */
    private const SECTIONS = [
        'itinerary' => 'itinerary', 'experiences' => 'experiences', 'destinations' => 'destinations', 'blog' => 'blog',
        'news' => 'news', 'faq' => 'faq', 'services' => 'services', 'gallery' => 'gallery', 'about-us' => 'aboutUs', 'contact' => 'contact',
        'guides' => 'guides',
    ];

    /** Travel guide pages (English path below /guides → section, PathBuilder::SEGMENTS). */
    private const GUIDES = ['guides/first-time-halong-bay-cruise' => 'guideFirstTime', 'guides/hanoi-to-halong-bay' => 'guideTransfer'];

    /** Detail URL patterns of the English site: type, regex, group holding the row id. */
    private const DETAILS = [
        ['cruise', '#^/cruise/[a-z0-9-]+-(\d+)\.html$#'],
        ['cabin', '#^/cabin/[a-z0-9-]+-(\d+)\.html$#'],
        ['itinerary', '#^/itinerary/[a-z0-9-]+-\d+-(\d+)\.html$#'],
        ['experience', '#^/experiences/[a-z0-9-]+-(\d+)\.html$#'],
    ];

    /** @param string $englishUrl a site-relative URL of the English menu (ContentMenuBuilder output for English) */
    public static function resolve(string $englishUrl, AdLanguage $language): string
    {
        if ($englishUrl === '' || str_starts_with($englishUrl, '#') || str_starts_with($englishUrl, 'javascript:') || !str_starts_with($englishUrl, '/')) {
            return $englishUrl; // anchors, scripts, external links
        }
        $parts = parse_url($englishUrl);
        $path = '/' . trim($parts['path'] ?? '/', '/');
        $suffix = (isset($parts['query']) ? '?' . $parts['query'] : '') . (isset($parts['fragment']) ? '#' . $parts['fragment'] : '');

        $target = self::target($path, $language);

        return $target === null ? $englishUrl : $target . $suffix;
    }

    /** The language's public path for an English path, or null (keep the English URL). */
    private static function target(string $path, AdLanguage $language): ?string
    {
        if ($path === '/') {
            return IntlSitemap::hubOpen($language, 'home') ? PathBuilder::home($language) : null;
        }
        foreach (self::DETAILS as [$type, $pattern]) {
            if (preg_match($pattern, $path, $m)) {
                return self::detail($type, (int) $m[1], $language);
            }
        }
        $segments = explode('/', trim($path, '/'));
        if ($segments[0] === 'destinations' && count($segments) === 2) {
            $id = DB::table('app_destination')->where('language_id', LocaleRegistry::defaultLanguage()->id)->where('slug', $segments[1])->value('id');

            return $id ? self::detail('destination', (int) $id, $language) : null;
        }
        if ($segments[0] === 'blog' && count($segments) === 3 && str_ends_with($segments[2], '.html')) {
            $id = DB::table('app_article as a')->join('app_category as c', 'c.id', '=', 'a.category_id')
                ->where('a.language_id', LocaleRegistry::defaultLanguage()->id)->where('c.slug', $segments[1])->where('a.slug', substr($segments[2], 0, -5))->value('a.id');

            return $id ? self::detail('article', (int) $id, $language) : null;
        }
        if ($segments[0] === 'faq' && count($segments) === 2) {
            return self::faqGroup($segments[1], $language);
        }
        if (isset(self::GUIDES[trim($path, '/')])) {
            $section = self::GUIDES[trim($path, '/')];

            return IntlSitemap::hubOpen($language, $section) ? PathBuilder::section($language, $section) : null;
        }
        if (count($segments) === 1 && isset(self::SECTIONS[$segments[0]])) {
            $section = self::SECTIONS[$segments[0]];

            return IntlSitemap::hubOpen($language, $section) ? PathBuilder::section($language, $section) : null;
        }

        return null; // category/group pages, legal, booking…: the English page
    }

    /** An English FAQ group page → the language's group page, while it is public and lists a question. */
    private static function faqGroup(string $englishSlug, AdLanguage $language): ?string
    {
        $type = config('backend.groupType.faq');
        $id = DB::table('app_group')->where('language_id', LocaleRegistry::defaultLanguage()->id)->where('type', $type)->where('slug', $englishSlug)->value('id');
        $linked = $id ? ContentTranslations::linkedId('faq_group', (int) $id, (int) $language->id) : null;
        $group = $linked ? \Modules\BackEnd\Entities\AppGroup::where('id', $linked)->where('publish_state', 'published')->first() : null;
        if (!$group || !preg_match('/^[a-z0-9]+(-[a-z0-9]+)*$/', (string) $group->slug) || !IntlSitemap::hubOpen($language, 'faq')
            || !\Modules\BackEnd\Entities\AppFaq::where('group_id', $group->id)->exists()) {
            return null;
        }

        return PathBuilder::faqGroup($language, $group->slug);
    }

    private static function detail(string $type, int $englishId, AdLanguage $language): ?string
    {
        $id = ContentTranslations::linkedId($type, $englishId, (int) $language->id);
        $row = $id ? DB::table(TranslatableFields::of($type)['table'])->where('id', $id)->first() : null;

        return $row ? TranslationResolver::selfPath($type, $row) : null;
    }
}
