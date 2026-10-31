<?php

namespace App\Services\Locale;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\BackEnd\Entities\AdLanguage;

/**
 * The one place that says which languages exist, which may be edited and which are public (12 §2.1, §5.1; phase 1B).
 *
 *   disabled  not editable, never public
 *   editing   editable in the admin, never public
 *   public    public once effectively public: en/vi (route_style 'legacy') always; any other language only while
 *             every web instance supports it (FrontendManifest), so editing ≠ public and a CMS flag alone can
 *             never publish a language the deployed website cannot render.
 *
 * Rows are cached (config i18n.registry_ttl) and flushed whenever an AdLanguage row is saved or deleted
 * (AppServiceProvider). Before the registry migration has run, en/vi count as public legacy languages and every
 * other row as disabled, which is what the site did then.
 */
final class LocaleRegistry
{
    public const STATUSES = ['disabled', 'editing', 'public'];
    private const LEGACY_CODES = ['en', 'vi'];
    private const CACHE_KEY = 'i18n.registry.v1';

    // Per application instance (one request, one job, one test), so nothing leaks between them.
    private const MEMO = 'i18n.registry.memo';
    private const MIGRATED = 'i18n.registry.migrated';

    /** Every ad_language row, ordered as the admin orders them. @return Collection<int, AdLanguage> */
    public static function all(): Collection
    {
        if (!app()->bound(self::MEMO)) {
            $rows = Cache::remember(self::cacheKey(), (int) config('i18n.registry_ttl', 300), fn () => DB::table('ad_language')
                ->get()->map(fn ($row) => (array) $row)
                ->sortBy(fn ($row) => [(int) ($row['ord'] ?? 0), (int) $row['id']])->values()->all());
            app()->instance(self::MEMO, AdLanguage::hydrate($rows));
        }

        return app(self::MEMO);
    }

    public static function flush(): void
    {
        app()->forgetInstance(self::MEMO);
        app()->forgetInstance(self::MIGRATED);
        Cache::forget(self::cacheKey());
    }

    /** Per database, so two databases sharing one cache store (clones, staging) never read each other's rows. */
    private static function cacheKey(): string
    {
        return self::CACHE_KEY . '.' . DB::connection()->getDatabaseName();
    }

    public static function byCode(?string $code): ?AdLanguage
    {
        $code = strtolower(trim((string) $code));

        return $code === '' ? null : self::all()->first(fn (AdLanguage $l) => strtolower((string) $l->code) === $code);
    }

    public static function byId(int $id): ?AdLanguage
    {
        return self::all()->first(fn (AdLanguage $l) => (int) $l->id === $id);
    }

    public static function status(AdLanguage $language): string
    {
        $status = $language->getAttribute('site_status');
        if (in_array($status, self::STATUSES, true)) {
            return $status;
        }

        return self::isLegacy($language) ? 'public' : 'disabled';
    }

    /** en/vi: today's URLs and SEO rules (no manifest dependency, no editorial workflow). */
    public static function isLegacy(AdLanguage $language): bool
    {
        $style = $language->getAttribute('route_style');
        if ($style !== null) {
            return $style === 'legacy';
        }

        return in_array(strtolower((string) $language->code), self::LEGACY_CODES, true);
    }

    public static function isEditable(AdLanguage $language): bool
    {
        return self::status($language) !== 'disabled';
    }

    /** Public status AND served by every web instance (legacy languages need no manifest). */
    public static function isEffectivePublic(AdLanguage $language, bool $freshManifest = false): bool
    {
        if (self::status($language) !== 'public') {
            return false;
        }

        if (self::isLegacy($language)) {
            return true;
        }

        return (bool) config('i18n.allow_public_new_locales') && FrontendManifest::supports((string) $language->code, $freshManifest);
    }

    /**
     * Phase 1C-5: a section (route family) of a language is publicly servable when the language is effectively
     * public and, for a new (intl) language, every web instance lists that section. en/vi: always.
     */
    public static function sectionReady(AdLanguage $language, string $section): bool
    {
        if (!self::isEffectivePublic($language)) {
            return false;
        }

        return self::isLegacy($language) || in_array($section, FrontendManifest::supportedSections((string) $language->code), true);
    }

    /** @return Collection<int, AdLanguage> */
    public static function editable(): Collection
    {
        return self::all()->filter(fn (AdLanguage $l) => self::isEditable($l))->values();
    }

    /** @return Collection<int, AdLanguage> */
    public static function effectivePublic(): Collection
    {
        return self::all()->filter(fn (AdLanguage $l) => self::isEffectivePublic($l))->values();
    }

    /**
     * What the website may link for intl languages right now: effectively public intl code => sections served by
     * every web instance (sorted). Empty while no intl language is public (the hold-back keeps it empty).
     */
    public static function intlReadiness(): array
    {
        $out = [];
        foreach (self::effectivePublic()->reject(fn (AdLanguage $l) => self::isLegacy($l)) as $language) {
            $sections = FrontendManifest::supportedSections((string) $language->code);
            sort($sections);
            $out[(string) $language->code] = $sections;
        }
        ksort($out);

        return $out;
    }

    public static function defaultLanguage(): ?AdLanguage
    {
        return self::all()->first(fn (AdLanguage $l) => (bool) $l->is_default);
    }

    /**
     * The language a public read (Content API) serves for ?lang=$code, or null when it must not be served.
     * Absent/empty → config i18n.content_api.missing_language (the legacy default, 'en').
     */
    public static function resolvePublic(?string $code): ?AdLanguage
    {
        $code = trim((string) $code);
        $language = self::byCode($code === '' ? (string) config('i18n.content_api.missing_language', 'en') : $code);

        return $language && self::isEffectivePublic($language) ? $language : null;
    }

    /** Code of the price-list language $code books through (ad_language.price_list_language_id), or null. */
    public static function priceListCode(string $code): ?string
    {
        $language = self::byCode($code);
        $id = $language?->getAttribute('price_list_language_id');

        return $id === null ? null : self::byId((int) $id)?->code;
    }

    /** True once the registry migration has run (columns present). */
    public static function migrated(): bool
    {
        if (!app()->bound(self::MIGRATED)) {
            app()->instance(self::MIGRATED, Schema::hasColumn('ad_language', 'site_status'));
        }

        return app(self::MIGRATED);
    }
}
