<?php

namespace App\Services\Locale;

use App\Services\Content\ContentTranslations;
use App\Services\Content\DestinationContentService;
use App\Services\Content\PathBuilder;
use App\Services\Content\TranslationResolver;
use App\Services\Publication\PublicationContext;
use App\Services\Publication\PublicationPolicy;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\BackEnd\Entities\AdLanguage;
use Modules\BackEnd\Entities\AppArticle;
use Modules\BackEnd\Entities\AppCruise;
use Modules\BackEnd\Entities\AppDestination;
use Modules\BackEnd\Entities\AppExpActivity;
use Modules\BackEnd\Entities\AppItinerary;

/**
 * Sitemap entries of new (intl) languages, phase 1C-5. Built only from what is publicly served — never from the
 * mere existence of an ad_language row or from Laravel's shared multi-language routes:
 *
 * - detail URLs: rows that TranslationResolver accepts as members (PublicationPolicy-visible, valid stored slug,
 *   section served by every web instance), at their PathBuilder path, with the resolver's alternates;
 * - hubs (home, experiences, itinerary, blog, news, faq, destinations) and blog/FAQ categories: only when the
 *   section is served AND at least one published, visible item of that language would be listed there; categories
 *   and FAQ groups carry GroupAlternates (1E hardening: only where the EN/VI pair stays exactly as it is);
 * - static pages, gallery and services: not listed for intl languages until page-level publication exists (1E).
 *
 * Empty for every kind while no intl language is effectively public, so EN/VI sitemaps are untouched.
 */
final class IntlSitemap
{
    /** @return array<int, array{loc: string, lastmod: string, changefreq: string, priority: string, alternates: array, lang: string, group: string}> */
    public static function entries(string $kind): array
    {
        $languages = LocaleRegistry::effectivePublic()->reject(fn (AdLanguage $l) => LocaleRegistry::isLegacy($l))->values();
        if ($languages->isEmpty()) {
            return [];
        }

        return PublicationContext::asPublic(function () use ($kind, $languages) {
            $out = [];
            foreach ($languages as $language) {
                foreach (self::forLanguage($kind, $language) as $entry) {
                    $out[] = $entry;
                }
            }

            return $out;
        });
    }

    private static function forLanguage(string $kind, AdLanguage $language): array
    {
        $id = (int) $language->id;

        return match ($kind) {
            'cruise' => self::details('cruise', AppCruise::where('language_id', $id)->orderBy('id')->get()),
            'cabin' => self::details('cabin', \Modules\BackEnd\Entities\AppCabin::whereIn('id', ContentTranslations::items('cabin', $id)->pluck('id'))->orderBy('id')->get()),
            'itinerary' => self::details('itinerary', AppItinerary::where('language_id', $id)->orderBy('id')->get()),
            'experience' => self::details('experience', AppExpActivity::where('language_id', $id)->orderBy('id')->get()),
            // The destinations hub sits in the destination sitemap, beside its EN/VI twins (the reciprocity pass in
            // PublicLocaleGate works per sitemap file and needs them to build the hub group).
            'destination' => array_merge(self::hubs($language, ['destinations']), self::details('destination', AppDestination::where('language_id', $id)->where('status', 'published')->orderBy('id')->get())),
            'article' => self::details('article', AppArticle::where('language_id', $id)->where('is_published', 1)->orderBy('id')->get()),
            'category' => self::categories($language),
            'faq' => self::faq($language),
            // Static pages sit in the sitemap of their EN/VI twins (the hub group is built per sitemap file).
            'page' => array_merge(self::hubs($language, ['home', 'experiences', 'itinerary', 'blog', 'news']), self::staticPages($language, ['aboutUs', 'contact'])),
            'service' => self::staticPages($language, ['services']),
            'gallery' => self::staticPages($language, ['gallery']),
            default => [],
        };
    }

    private static function details(string $type, $rows): array
    {
        $entries = [];
        foreach ($rows as $row) {
            $path = TranslationResolver::selfPath($type, $row);
            if ($path === null) {
                continue;
            }
            $alternates = TranslationResolver::alternates($type, $row);
            $entries[] = self::entry($path, $row, 'weekly', '0.7', $alternates, "$type:" . $row->id);
        }

        return $entries;
    }

    /** Blog categories with at least one listed article of the language (empty categories are noindex). */
    private static function categories(AdLanguage $language): array
    {
        $entries = [];
        $type = config('backend.categoryType.article');
        $cats = \Modules\BackEnd\Entities\AppCategory::where('language_id', $language->id)->where('type', $type)->where('slug', '!=', 'root')->get();
        foreach ($cats as $cat) {
            $section = TranslationResolver::section('article_category', $cat->slug);
            $articles = AppArticle::where('language_id', $language->id)->where('category_id', $cat->id)->where('is_published', 1)->get()
                ->filter(fn ($a) => TranslationResolver::selfPath('article', $a) !== null);
            if (!preg_match('/^[a-z0-9]+(-[a-z0-9]+)*$/', (string) $cat->slug) || $articles->isEmpty() || !LocaleRegistry::sectionReady($language, $section)) {
                continue;
            }
            $alternates = \App\Services\Content\GroupAlternates::for('article_category', $cat);
            $entries[] = self::entry(PathBuilder::blogCategory($language, $cat->slug), $articles->sortByDesc('updated_at')->first(), 'weekly', '0.6', $alternates, "category:{$cat->id}");
        }

        return $entries;
    }

    /** The FAQ hub and FAQ groups, each only with at least one visible FAQ of the language. */
    private static function faq(AdLanguage $language): array
    {
        if (!LocaleRegistry::sectionReady($language, 'faq')) {
            return [];
        }
        $faqType = config('backend.groupType.faq');
        $groups = \Modules\BackEnd\Entities\AppGroup::where('language_id', $language->id)->where('type', $faqType)->where('slug', '!=', 'root')->get();
        $entries = [];
        $any = null;
        foreach ($groups as $group) {
            $faq = \Modules\BackEnd\Entities\AppFaq::where('group_id', $group->id)->orderByDesc('id')->first();
            if (!$faq || !preg_match('/^[a-z0-9]+(-[a-z0-9]+)*$/', (string) $group->slug)) {
                continue;
            }
            $any = $any ?? $faq;
            $alternates = \App\Services\Content\GroupAlternates::for('faq_group', $group);
            $entries[] = self::entry(PathBuilder::faqGroup($language, $group->slug), $group, 'monthly', '0.5', $alternates, "faq_group:{$group->id}");
        }
        if ($any) {
            array_unshift($entries, self::entry(PathBuilder::section($language, 'faq'), $any, 'weekly', '0.8', [], 'hub:faq'));
        }

        return $entries;
    }

    /**
     * Static pages (services, gallery, about us, contact; phase 1E-c): listed only when the page row is published
     * for the language (PagePublication) and every web instance serves the section. Legal pages, booking and guides
     * are never listed for a new language.
     */
    private static function staticPages(AdLanguage $language, array $sections): array
    {
        $entries = [];
        foreach (array_intersect_key(\App\Services\Publication\PagePublication::SECTION_PAGES, array_flip($sections)) as $section => $code) {
            if (!LocaleRegistry::sectionReady($language, $section) || !\App\Services\Publication\PagePublication::visible($code, $language)) {
                continue;
            }
            $row = DB::table('app_page')->where('code', $code)->where('language_id', $language->id)->first();
            $entries[] = self::entry(PathBuilder::section($language, $section), $row, 'monthly', '0.6', [], "hub:$section");
        }

        return $entries;
    }

    /** Home and section hubs (of $sections), each only when served and not empty for this language. */
    private static function hubs(AdLanguage $language, array $sections): array
    {
        $entries = [];
        foreach (array_intersect(self::HUBS, $sections) as $section) {
            if (!LocaleRegistry::sectionReady($language, $section) || !($row = self::hubEvidence($language, $section))) {
                continue;
            }
            $path = $section === 'home' ? PathBuilder::home($language) : PathBuilder::section($language, $section);
            $entries[] = self::entry($path, $row, 'weekly', $section === 'home' ? '1.0' : '0.8', [], "hub:$section");
        }

        return $entries;
    }

    /** Hubs that are public only while they list at least one public item of the language. */
    private const HUBS = ['home', 'experiences', 'itinerary', 'blog', 'news', 'destinations'];

    /** The first public item a hub of $language would list (its evidence of content), or null. */
    private static function hubEvidence(AdLanguage $language, string $section)
    {
        $id = (int) $language->id;
        $listed = fn (string $type, $rows) => collect($rows)->first(fn ($row) => TranslationResolver::selfPath($type, $row) !== null);
        $articles = fn (string $hub) => $listed('article', AppArticle::where('language_id', $id)->where('is_published', 1)->get()
            ->filter(fn ($a) => TranslationResolver::section('article', DB::table('app_category')->where('id', $a->category_id)->value('slug')) === $hub));

        return match ($section) {
            'home' => $listed('cruise', AppCruise::where('language_id', $id)->get()),
            'experiences' => $listed('experience', AppExpActivity::where('language_id', $id)->get()),
            'itinerary' => $listed('itinerary', AppItinerary::where('language_id', $id)->get()),
            'blog', 'news' => $articles($section),
            'destinations' => DestinationContentService::hubVisible($language)
                ? $listed('destination', AppDestination::where('language_id', $id)->where('status', 'published')->get()) : null,
            default => null,
        };
    }

    /** Section pages a new language can link to (hubOpen), for the website's own links (Content API /site). */
    public const LINKABLE = ['home', 'itinerary', 'experiences', 'destinations', 'blog', 'news', 'faq', 'services', 'gallery', 'aboutUs', 'contact', 'booking'];

    /** @return string[] the LINKABLE sections that are public for $language */
    public static function publicSections(AdLanguage $language): array
    {
        return array_values(array_filter(self::LINKABLE, fn (string $section) => self::hubOpen($language, $section)));
    }

    /**
     * Whether a section page of a new language is public, by the same rules as its sitemap entry: hubs list
     * something; the FAQ has a group with a question; static pages are published for the language. Used to link only
     * to pages that exist (phase 2B: Japanese menus).
     */
    public static function hubOpen(AdLanguage $language, string $section): bool
    {
        if (!LocaleRegistry::sectionReady($language, $section)) {
            return false;
        }

        if ($section === 'booking') {
            return true; // the reservation form has no content of its own: served = public
        }

        return PublicationContext::asPublic(function () use ($language, $section) {
            if (in_array($section, self::HUBS, true)) {
                return self::hubEvidence($language, $section) !== null;
            }
            if ($section === 'faq') {
                $groups = \Modules\BackEnd\Entities\AppGroup::where('language_id', $language->id)->where('type', config('backend.groupType.faq'))->where('slug', '!=', 'root')->pluck('id');

                return \Modules\BackEnd\Entities\AppFaq::whereIn('group_id', $groups)->exists();
            }
            $code = \App\Services\Publication\PagePublication::SECTION_PAGES[$section] ?? null;

            return $code !== null && \App\Services\Publication\PagePublication::visible($code, $language);
        });
    }

    private static function entry(string $path, $row, string $changefreq, string $priority, array $alternates, string $group): array
    {
        $stamp = data_get($row, 'updated_at') ?: data_get($row, 'created_at');

        return [
            'loc' => url($path),
            'lastmod' => $stamp ? Carbon::parse($stamp)->format('Y-m-d\TH:i:sP') : '',
            'changefreq' => $changefreq,
            'priority' => $priority,
            'alternates' => array_map(fn ($a) => ['hreflang' => $a['lang'], 'url' => url($a['path'])], $alternates),
            'group' => $group,
        ];
    }
}
