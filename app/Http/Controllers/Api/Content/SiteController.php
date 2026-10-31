<?php

namespace App\Http\Controllers\Api\Content;

use App\Http\Controllers\Controller;
use App\Services\Content\ContentMenuBuilder;
use App\Services\Content\PageConfigContentService;
use App\Services\Locale\IntlSitemap;
use App\Services\Locale\LocaleRegistry;
use Illuminate\Http\Request;
use Modules\BackEnd\Helpers\Utilities;

class SiteController extends Controller
{
    use ResolvesContentLanguage;

    public function show(Request $request)
    {
        $language = $this->resolveLanguage($request);

        // Only languages the public site serves (LocaleRegistry): an 'editing' language is never listed.
        $languages = LocaleRegistry::effectivePublic()->map(fn ($l) => [
            'code' => $l->code,
            'name' => $l->name,
            'isDefault' => (bool) $l->is_default,
        ])->values();

        $config = Utilities::getAllConfig($language);

        $payload = [
            'language' => ['code' => $language->code],
            'languages' => $languages,
            // Keys are the raw ad_config keys (with the "-{lang}" suffix stripped); object even when empty.
            'config' => (object) $config,
            'menus' => [
                'primary' => ContentMenuBuilder::build('primary-menu', $language),
                'footer' => ContentMenuBuilder::build('footer-menu', $language),
            ],
            // app_page codes switched off in the admin (destinations, travel guides): not linked anywhere.
            'hiddenPages' => PageConfigContentService::hiddenCodes($language),
        ];
        if (!LocaleRegistry::isLegacy($language)) {
            // Phase 2B: the section pages this language has (IntlSitemap::hubOpen), so the website links only to
            // pages that exist and sends the rest to English. Not sent for en/vi (their payload is unchanged).
            $payload['publicSections'] = IntlSitemap::publicSections($language);
        }

        return response()->json($payload);
    }
}
