<?php

namespace App\Http\Controllers\Api\Content;

use Illuminate\Http\Request;
use Modules\BackEnd\Entities\AdLanguage;
use Modules\BackEnd\Services\AdLanguageService;

trait ResolvesContentLanguage
{
    /**
     * Resolve ?lang=en|vi (or a given code) to an AdLanguage row.
     * Missing or unknown codes fall back to "en", then to the default language.
     */
    protected function resolveLanguage(Request $request, ?string $code = null): AdLanguage
    {
        $code = strtolower(trim((string) ($code ?? $request->query('lang', 'en'))));

        $language = $code !== '' ? AdLanguageService::findByCode($code) : null;
        if (!$language) {
            $language = AdLanguageService::findByCode('en') ?: AdLanguageService::getDefaultLanguage();
        }

        abort_if(!$language, 503, 'No language configured');

        return $language;
    }

    protected function notFound()
    {
        return response()->json(['error' => 'not_found'], 404);
    }
}
