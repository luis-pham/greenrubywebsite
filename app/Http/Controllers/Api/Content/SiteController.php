<?php

namespace App\Http\Controllers\Api\Content;

use App\Http\Controllers\Controller;
use App\Services\Content\ContentMenuBuilder;
use Illuminate\Http\Request;
use Modules\BackEnd\Helpers\Utilities;
use Modules\BackEnd\Services\AdLanguageService;

class SiteController extends Controller
{
    use ResolvesContentLanguage;

    public function show(Request $request)
    {
        $language = $this->resolveLanguage($request);

        $languages = AdLanguageService::getAll()->map(fn ($l) => [
            'code' => $l->code,
            'name' => $l->name,
            'isDefault' => (bool) $l->is_default,
        ])->values();

        $config = Utilities::getAllConfig($language);

        return response()->json([
            'language' => ['code' => $language->code],
            'languages' => $languages,
            // Keys are the raw ad_config keys (with the "-{lang}" suffix stripped); object even when empty.
            'config' => (object) $config,
            'menus' => [
                'primary' => ContentMenuBuilder::build('primary-menu', $language),
                'footer' => ContentMenuBuilder::build('footer-menu', $language),
            ],
        ]);
    }
}
