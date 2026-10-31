<?php

namespace App\Http\Middleware;

use App\Services\Content\IntlMenuUrls;
use App\Services\Locale\LocaleRegistry;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\BackEnd\Entities\AdLanguage;

/**
 * Content API answers for a new language (2026-11-01): rich text drafted from English keeps the English internal
 * links (href="/itinerary/…", "/blog/…", "/destinations/…"). Each one is mapped like a menu URL (IntlMenuUrls): to the
 * language's own page when it is public, else the English page, never to a 404. Only href attributes holding a
 * site-relative path are touched, and only in answers for a non-legacy language: en/vi payloads are passed through
 * byte for byte.
 */
class IntlContentLinks
{
    private const HREF = '/href=(\\\\?["\'])(\/(?!\/)[^"\'\\\\]*)\1/';

    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
        $code = $request->query('lang');
        if (!$response instanceof JsonResponse || $response->getStatusCode() !== 200 || !is_string($code)) {
            return $response;
        }
        $language = LocaleRegistry::byCode($code);
        if (!$language || LocaleRegistry::isLegacy($language)) {
            return $response;
        }
        $prefix = rtrim(\App\Services\Content\PathBuilder::home($language), '/');
        $resolved = [];
        $data = $response->getData(true);
        array_walk_recursive($data, function (&$value) use ($language, $prefix, &$resolved) {
            if (is_string($value) && str_contains($value, 'href=')) {
                $value = self::localize($value, $language, $prefix, $resolved);
            }
        });

        return $response->setData($data);
    }

    /** $html with every English site-relative href mapped for $language (memoised per request in $resolved). */
    public static function localize(string $html, AdLanguage $language, string $prefix, array &$resolved = []): string
    {
        return preg_replace_callback(self::HREF, function (array $m) use ($language, $prefix, &$resolved) {
            [$all, $quote, $path] = $m;
            if ($prefix !== '' && ($path === $prefix || str_starts_with($path, $prefix . '/') || str_starts_with($path, $prefix . '#'))) {
                return $all; // already the language's own URL
            }

            return 'href=' . $quote . ($resolved[$path] ??= IntlMenuUrls::resolve($path, $language)) . $quote;
        }, $html) ?? $html;
    }
}
