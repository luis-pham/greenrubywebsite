<?php

namespace App\Http\Controllers\Api\Content;

use App\Services\Locale\LocaleRegistry;
use App\Services\Publication\PublicationContext;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\BackEnd\Entities\AdLanguage;

trait ResolvesContentLanguage
{
    /**
     * Resolve ?lang (or a given code) to an effectively public language (LocaleRegistry, phase 1B).
     *
     * Compatibility policy (config/i18n.php, greenruby-web docs/i18n-audit/phase-1/1b-report.md):
     * - absent or empty ?lang → 'en', the legacy default;
     * - a language that is not effectively public (unknown, disabled, editing, or not yet supported by the
     *   deployed website) → 404 `unknown_language`, so it can never be served English under its own code;
     *   `content_api.unknown_language = legacy_en` restores the old English fallback (logged);
     * - $lenient (writes such as the contact form) → English, logged, so a guest's message is never lost.
     */
    protected function resolveLanguage(Request $request, ?string $code = null, bool $lenient = false): AdLanguage
    {
        $raw = $code ?? $request->query('lang');
        $requested = is_string($raw) ? $raw : null;
        // Phase 1E-d: an authenticated preview reads its own language even before it is public (editing).
        if (($previewLanguageId = \App\Services\Preview\Preview::languageId()) && $requested !== null) {
            $previewLanguage = LocaleRegistry::byId($previewLanguageId);
            if ($previewLanguage && strtolower($requested) === strtolower((string) $previewLanguage->code) && !LocaleRegistry::isEffectivePublic($previewLanguage)) {
                if (!LocaleRegistry::isLegacy($previewLanguage)) {
                    PublicationContext::requireStoredSlugs();
                    // Other unpublished pages of the language stay hidden inside a preview (only the previewed one opens).
                    $page = \App\Services\Publication\PagePublication::pageForSection(self::requestSection($request));
                    if ($page !== null && !\App\Services\Publication\PagePublication::visible($page, $previewLanguage)) {
                        throw new HttpResponseException(response()->json(['error' => 'page_not_published'], 404));
                    }
                }

                return $previewLanguage;
            }
        }
        $language = $raw === null || is_string($raw) ? LocaleRegistry::resolvePublic($requested) : null;
        if ($language) {
            if (!LocaleRegistry::isLegacy($language)) {
                PublicationContext::requireStoredSlugs(); // phase 1C-5: no URL without a stored slug
                // Phase 1C-5: a new language is only served for the sections every web instance serves for it.
                $section = self::requestSection($request);
                if ($section !== null && !LocaleRegistry::sectionReady($language, $section)) {
                    Log::info('Content API: section not served for language', ['lang' => $language->code, 'section' => $section, 'path' => $request->path()]);
                    throw new HttpResponseException(response()->json(['error' => 'section_not_served'], 404));
                }
                // Phase 1E-c: a static page of a new language is public only once its page row is published.
                $page = \App\Services\Publication\PagePublication::pageForSection($section);
                if ($page !== null && !\App\Services\Publication\PagePublication::visible($page, $language)) {
                    throw new HttpResponseException(response()->json(['error' => 'page_not_published'], 404));
                }
            }

            return $language;
        }

        if (!$lenient && config('i18n.content_api.unknown_language', 'not_found') !== 'legacy_en') {
            Log::info('Content API: language not served', ['lang' => $requested, 'path' => $request->path()]);
            throw new HttpResponseException(response()->json(['error' => 'unknown_language'], 404));
        }
        Log::warning('Content API: language not served, answered in English', ['lang' => $requested, 'path' => $request->path()]);
        $fallback = LocaleRegistry::byCode('en') ?? LocaleRegistry::defaultLanguage();
        abort_if(!$fallback, 503, 'No language configured');

        return $fallback;
    }

    /** Content API route => frontend section (route family) it feeds; null = language-level (site, locales, contact). */
    private static function requestSection(Request $request): ?string
    {
        $name = (string) optional($request->route())->getName();
        $news = fn ($slug) => in_array($slug, \App\Services\Content\PathBuilder::NEWS_CATEGORY, true);

        return match (true) {
            str_starts_with($name, 'api.content.cruises.') => 'cruise',
            str_starts_with($name, 'api.content.cabins.') => 'cabin',
            str_starts_with($name, 'api.content.itineraries.') => 'itinerary',
            str_starts_with($name, 'api.content.experiences.') => 'experiences',
            str_starts_with($name, 'api.content.destinations.') => 'destinations',
            str_starts_with($name, 'api.content.articles.') => $news($request->route('categorySlug') ?? $request->query('category')) ? 'news' : 'blog',
            $name === 'api.content.faqs.index' => 'faq',
            $name === 'api.content.gallery.index' => 'gallery',
            $name === 'api.content.services.index' => 'services',
            $name === 'api.content.legal' => 'legal',
            $name === 'api.content.pages.show' => match ((string) $request->route('code')) {
                'homepage' => 'home', 'about-us' => 'aboutUs', 'contact' => 'contact', 'faq' => 'faq', 'gallery' => 'gallery',
                'service' => 'services', 'itinerary' => 'itinerary', 'exp-activity' => 'experiences', 'article' => 'blog',
                'destinations' => 'destinations', 'guide-first-time' => 'guideFirstTime', 'guide-transfer' => 'guideTransfer',
                'eco-dashboard' => 'ecoDashboard', 'booking' => 'booking',
                default => 'legal',
            },
            default => null,
        };
    }

    /** Trimmed string query parameter, null when absent/empty/not a string. */
    protected function stringQuery(Request $request, string $key): ?string
    {
        $value = $request->query($key);
        if (!is_string($value)) {
            return null;
        }
        $value = trim($value);

        return $value === '' ? null : $value;
    }

    protected function notFound()
    {
        return response()->json(['error' => 'not_found'], 404);
    }
}
