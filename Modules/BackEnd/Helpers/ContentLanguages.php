<?php

namespace Modules\BackEnd\Helpers;

use App\Services\Locale\LocaleRegistry;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Lang;
use Modules\BackEnd\Services\AdLanguageService;

/**
 * Content languages in the admin (phase 1E-a). The content language (which rows an editor reads and writes:
 * /admincp/{code}/…, session "backend-language") is separate from the admin UI language (labels, Vietnamese by
 * default). Only editable languages (registry status editing or public) can be selected; disabled ones get no
 * admin routes. Before the registry migration this is every ad_language row, as before.
 */
final class ContentLanguages
{
    /** @return Collection<int, \Modules\BackEnd\Entities\AdLanguage> */
    public static function editable(): Collection
    {
        try {
            if (LocaleRegistry::migrated()) {
                return LocaleRegistry::editable()->values();
            }
        } catch (\Throwable $e) {
            // No database yet (package discovery, first install): fall back to the legacy list.
        }

        return AdLanguageService::getAll();
    }

    public static function isEditable($language): bool
    {
        return $language && self::editable()->contains(fn ($l) => (int) $l->id === (int) $language->id);
    }

    /** The admin UI language (labels). An admin user may set ui_locale; default Vietnamese. */
    public static function uiLocale(): string
    {
        $user = auth('admin')->user();
        $locale = $user ? ($user->getAttribute('ui_locale') ?: null) : null;

        return in_array($locale, ['vi', 'en'], true) ? $locale : 'vi';
    }

    /**
     * Locale for admin labels that historically followed the content language (EN content → English labels):
     * kept for en/vi, the admin UI language for any other content language (which has no admin label files).
     */
    public static function labelLocale(?string $contentCode): string
    {
        return $contentCode && Lang::hasForLocale('backend::group.onboard_activities', $contentCode) ? $contentCode : self::uiLocale();
    }

    /**
     * Where to land after switching the content language: the same admin section in the new language, never the
     * same record (record ids are per language row). Only /admincp paths are accepted.
     */
    public static function sectionPathFor(?string $currentPath, $target): string
    {
        $codes = self::editable()->pluck('code')->map(fn ($c) => strtolower((string) $c))->all();
        $segments = array_values(array_filter(explode('/', (string) parse_url((string) $currentPath, PHP_URL_PATH)), 'strlen'));
        if (($segments[0] ?? null) !== 'admincp') {
            $segments = ['admincp'];
        }
        array_shift($segments);
        if (isset($segments[0]) && in_array(strtolower($segments[0]), $codes, true)) {
            array_shift($segments); // current language prefix
        }
        $keep = [];
        foreach ($segments as $segment) {
            if (ctype_digit($segment) || in_array($segment, ['show', 'edit', 'create', 'detail', 'store', 'update', 'destroy'], true)) {
                break;
            }
            $keep[] = $segment;
        }
        $prefix = $target->is_default ? [] : [(string) $target->code];

        return '/' . implode('/', array_merge(['admincp'], $prefix, $keep));
    }
}
