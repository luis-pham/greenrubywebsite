<?php

namespace App\Services\Publication;

use App\Services\Content\PageConfigContentService;
use App\Services\Locale\LocaleRegistry;
use Illuminate\Support\Facades\DB;
use Modules\BackEnd\Entities\AdLanguage;

/**
 * Page-level publication (phase 1E-c). Static pages (services, gallery, about us, contact) have no content rows
 * of their own; their app_page row is their publication: for a new language the page is public only when that
 * row exists and is_visible = 1, which the workflow guards (PublishGuardObserver, type 'page'). Registering a
 * language or deploying its route never exposes a page. en/vi keep today's rule (PageConfigContentService::visible:
 * only the hideable hubs can be switched off). The Content API, the intl sitemap and the web gate all read this.
 */
final class PagePublication
{
    /** Web section => app_page code, for the sections whose publication is the page itself. */
    public const SECTION_PAGES = ['services' => 'service', 'gallery' => 'gallery', 'aboutUs' => 'about-us', 'contact' => 'contact'];

    public static function visible(string $code, AdLanguage $language): bool
    {
        if (self::previewed($code, $language)) {
            return true; // an authenticated preview of exactly this page (1E hardening)
        }
        if (LocaleRegistry::isLegacy($language)) {
            return PageConfigContentService::visible($code, $language);
        }

        return (int) DB::table('app_page')->where('code', $code)->where('language_id', $language->id)->value('is_visible') === 1;
    }

    /** True when the current read is an authenticated preview of this page row. */
    public static function previewed(string $code, AdLanguage $language): bool
    {
        if (!\App\Services\Preview\Preview::active()) {
            return false;
        }
        $id = DB::table('app_page')->where('code', $code)->where('language_id', $language->id)->value('id');

        return $id !== null && \App\Services\Preview\Preview::allows('app_page', $id);
    }

    /** The page whose publication decides $section for an intl language, or null (content-driven section). */
    public static function pageForSection(?string $section): ?string
    {
        return $section === null ? null : (self::SECTION_PAGES[$section] ?? null);
    }
}
