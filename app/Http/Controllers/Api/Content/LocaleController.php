<?php

namespace App\Http\Controllers\Api\Content;

use App\Http\Controllers\Controller;
use App\Services\Locale\FrontendManifest;
use App\Services\Locale\LocaleRegistry;
use Illuminate\Http\Request;
use Modules\BackEnd\Entities\AdLanguage;

/**
 * GET /api/v1/content/locales (phase 1B; contract in greenruby-web docs/i18n-audit/phase-1/1b-report.md §2).
 * Default: the effectively public languages only. ?scope=editable adds the languages being prepared ('editing'),
 * for editor tooling; disabled languages are never listed.
 */
class LocaleController extends Controller
{
    public function index(Request $request)
    {
        $editable = $request->query('scope') === 'editable';
        $languages = $editable ? LocaleRegistry::editable() : LocaleRegistry::effectivePublic();
        $default = LocaleRegistry::defaultLanguage();

        return response()->json([
            'default' => $default && LocaleRegistry::isEffectivePublic($default) ? $default->code : null,
            'locales' => $languages->map(fn (AdLanguage $l) => self::describe($l))->values(),
        ]);
    }

    public static function describe(AdLanguage $language): array
    {
        $priceList = $language->getAttribute('price_list_language_id');

        return [
            'code' => $language->code,
            'hreflang' => $language->getAttribute('hreflang') ?? $language->code,
            'nativeName' => $language->getAttribute('native_name') ?? $language->name,
            'englishName' => $language->getAttribute('english_name'),
            'urlPrefix' => $language->getAttribute('url_prefix'),
            'routeStyle' => LocaleRegistry::isLegacy($language) ? 'legacy' : 'intl',
            'ogLocale' => $language->getAttribute('og_locale'),
            'intlTag' => $language->getAttribute('intl_tag'),
            'direction' => $language->getAttribute('direction') ?? 'ltr',
            'isDefault' => (bool) $language->is_default,
            'priceList' => $priceList === null ? null : LocaleRegistry::byId((int) $priceList)?->code,
            'xDefaultPriority' => $language->getAttribute('xdefault_priority') === null ? null : (int) $language->getAttribute('xdefault_priority'),
            'status' => LocaleRegistry::status($language),
            'effectivePublic' => LocaleRegistry::isEffectivePublic($language),
            // Phase 1D-b (additive): the sections every web instance serves for an effectively public intl language
            // (LocaleRegistry::sectionReady), so each instance's route gate agrees with the CMS; null = all (en/vi).
            'sections' => LocaleRegistry::isLegacy($language) ? null
                : (LocaleRegistry::isEffectivePublic($language) ? array_values(FrontendManifest::supportedSections((string) $language->code)) : []),
        ];
    }
}
