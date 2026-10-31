<?php

namespace App\Services\Locale;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Which languages the deployed website can serve (08 §3, phase 1B). Pulled from every web instance
 * (config i18n.frontend_manifest.urls), never pushed, so the answer is always the serving builds' and not the last
 * container that happened to post. A language is supported only when EVERY instance answers with a valid manifest
 * that lists it as ready: a rolling deploy with old and new builds, an instance that is down, a malformed or
 * mis-versioned manifest, or no configured instance at all all mean "not supported" (fail closed).
 * Each instance's answer, failures included, is cached for `ttl` seconds.
 */
final class FrontendManifest
{
    /** Language codes every instance supports, or [] when any instance does not answer correctly. */
    public static function supportedCodes(bool $fresh = false): array
    {
        $urls = (array) config('i18n.frontend_manifest.urls', []);
        if ($urls === [] || !self::secret()) {
            return [];
        }
        $common = null;
        foreach ($urls as $url) {
            $instance = self::instance($url, $fresh);
            if (!$instance['ok']) {
                return [];
            }
            $common = $common === null ? $instance['ready'] : array_values(array_intersect($common, $instance['ready']));
        }

        return $common ?? [];
    }

    /**
     * Sections (route families: 'home', 'cruise', 'blog', …) that EVERY instance serves for $code; [] when any
     * instance fails, does not support the language, or predates the sections list (fail closed).
     */
    public static function supportedSections(string $code, bool $fresh = false): array
    {
        $code = strtolower($code);
        if (!in_array($code, self::supportedCodes($fresh), true)) {
            return [];
        }
        $common = null;
        foreach ((array) config('i18n.frontend_manifest.urls', []) as $url) {
            $list = self::instance($url, false)['sections'][$code] ?? [];
            $common = $common === null ? $list : array_values(array_intersect($common, $list));
        }

        return $common ?? [];
    }

    public static function supports(string $code, bool $fresh = false): bool
    {
        return in_array(strtolower($code), self::supportedCodes($fresh), true);
    }

    /** @return array<int, array{url: string, ok: bool, buildId: ?string, ready: string[], error: ?string, fetchedAt: string}> */
    public static function instances(bool $fresh = false): array
    {
        return array_map(fn ($url) => self::instance($url, $fresh), (array) config('i18n.frontend_manifest.urls', []));
    }

    public static function flush(): void
    {
        foreach ((array) config('i18n.frontend_manifest.urls', []) as $url) {
            Cache::forget(self::key($url));
        }
    }

    /** Validates one manifest payload. @return array{0: ?string, 1: string[], 2: ?string, 3?: array<string, string[]>} [buildId, ready codes, error, sections by code] */
    public static function parse($payload): array
    {
        if (!is_array($payload) || ($payload['schema'] ?? null) !== (int) config('i18n.frontend_manifest.schema', 1)) {
            return [null, [], 'schema_mismatch'];
        }
        if (!is_string($payload['buildId'] ?? null) || $payload['buildId'] === '' || !is_array($payload['locales'] ?? null)) {
            return [null, [], 'malformed'];
        }
        $ready = [];
        $sections = [];
        foreach ($payload['locales'] as $locale) {
            if (!is_array($locale) || !is_string($locale['code'] ?? null)) {
                return [null, [], 'malformed'];
            }
            if (($locale['routes'] ?? false) === true && ($locale['dictionary'] ?? false) === true) {
                $code = strtolower($locale['code']);
                $ready[] = $code;
                // Phase 1C-5: the section routes the build serves for this locale; absent (older web) = none known.
                $list = $locale['sections'] ?? [];
                $sections[$code] = is_array($list) ? array_values(array_unique(array_filter($list, 'is_string'))) : [];
            }
        }

        return [$payload['buildId'], array_values(array_unique($ready)), null, $sections];
    }

    private static function instance(string $url, bool $fresh): array
    {
        $load = function () use ($url) {
            $result = ['url' => $url, 'ok' => false, 'buildId' => null, 'ready' => [], 'sections' => [], 'error' => null, 'fetchedAt' => now()->toIso8601String()];
            try {
                $response = Http::timeout((float) config('i18n.frontend_manifest.timeout', 1.0))
                    ->acceptJson()
                    ->withHeaders(['X-Manifest-Key' => (string) self::secret()])
                    ->get($url);
                if (!$response->successful()) {
                    $result['error'] = 'http_' . $response->status();
                } else {
                    [$buildId, $ready, $error, $sections] = self::parse($response->json()) + [3 => []];
                    $result = array_merge($result, ['ok' => $error === null, 'buildId' => $buildId, 'ready' => $ready, 'sections' => $sections, 'error' => $error]);
                }
            } catch (\Throwable $e) {
                $result['error'] = 'unreachable';
            }
            if (!$result['ok']) {
                Log::warning('Frontend manifest unavailable: new languages treated as not ready', ['url' => $url, 'error' => $result['error']]);
            }

            return $result;
        };
        if ($fresh) {
            Cache::forget(self::key($url));
        }

        return Cache::remember(self::key($url), (int) config('i18n.frontend_manifest.ttl', 60), $load);
    }

    private static function key(string $url): string
    {
        return 'i18n.frontend-manifest.' . sha1($url);
    }

    private static function secret(): ?string
    {
        $secret = config('i18n.frontend_manifest.secret');

        return is_string($secret) && $secret !== '' ? $secret : null;
    }
}
