<?php

namespace App\Services\Seo;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * IndexNow (Bing, Yandex, Seznam, Naver...): tells search engines which public URLs changed.
 * The key file is served by nginx at {site}/{key}.txt (see config/services.php "indexnow").
 *
 * The change list comes from our own sitemaps (url => lastmod), so it covers every content type,
 * scheduled articles and direct DB edits without hooking each admin save.
 */
class IndexNow
{
    /** Protocol limit per POST. */
    public const MAX_URLS = 10000;

    /**
     * <loc> => <lastmod> ('' when absent) of a urlset, or of the child sitemaps of a sitemapindex.
     *
     * @return array<string, string>
     */
    public static function parseSitemap(string $xml): array
    {
        $out = [];
        if (!preg_match_all('#<(?:url|sitemap)>(.*?)</(?:url|sitemap)>#s', $xml, $blocks)) {
            return $out;
        }
        foreach ($blocks[1] as $block) {
            if (!preg_match('#<loc>\s*(.*?)\s*</loc>#s', $block, $loc)) {
                continue;
            }
            preg_match('#<lastmod>\s*(.*?)\s*</lastmod>#s', $block, $mod);
            $out[html_entity_decode($loc[1], ENT_QUOTES | ENT_XML1)] = $mod[1] ?? '';
        }

        return $out;
    }

    public static function isIndex(string $xml): bool
    {
        return str_contains($xml, '<sitemapindex');
    }

    /**
     * URLs to submit: new, lastmod changed, or gone from the sitemap (engines then recrawl and drop them).
     *
     * @param array<string, string> $old
     * @param array<string, string> $new
     * @return string[]
     */
    public static function changes(array $old, array $new): array
    {
        $changed = [];
        foreach ($new as $url => $mod) {
            if (!array_key_exists($url, $old) || $old[$url] !== $mod) {
                $changed[] = $url;
            }
        }
        foreach (array_keys(array_diff_key($old, $new)) as $url) {
            $changed[] = $url;
        }

        return $changed;
    }

    /** Keeps only absolute URLs on the configured host (the protocol rejects the whole batch otherwise). */
    public static function onHost(array $urls, string $host): array
    {
        return array_values(array_filter($urls, fn ($u) => strcasecmp((string) parse_url($u, PHP_URL_HOST), $host) === 0));
    }

    /** Fetches the sitemap index and every child sitemap. Null when any fetch fails, so state is never half-updated. */
    public function crawl(): ?array
    {
        $site = rtrim((string) config('services.indexnow.site'), '/');
        $index = $this->fetch($site . '/sitemap.xml');
        if ($index === null) {
            return null;
        }
        if (!self::isIndex($index)) {
            return self::parseSitemap($index);
        }

        $urls = [];
        foreach (array_keys(self::parseSitemap($index)) as $child) {
            $xml = $this->fetch($child);
            if ($xml === null) {
                return null;
            }
            $urls += self::parseSitemap($xml);
        }

        return $urls;
    }

    /** True when the engine accepted the batch (200 OK, or 202 while it is still verifying the key). */
    public function submit(array $urls): bool
    {
        $key = config('services.indexnow.key');
        $site = rtrim((string) config('services.indexnow.site'), '/');
        $host = (string) parse_url($site, PHP_URL_HOST);
        $urls = self::onHost($urls, $host);
        if (!$key || $urls === []) {
            return (bool) $key;
        }

        foreach (array_chunk($urls, self::MAX_URLS) as $batch) {
            try {
                $res = Http::timeout(20)->asJson()->post(config('services.indexnow.endpoint'), [
                    'host' => $host,
                    'key' => $key,
                    'keyLocation' => $site . '/' . $key . '.txt',
                    'urlList' => $batch,
                ]);
            } catch (\Throwable $e) {
                Log::warning('IndexNow unreachable', ['error' => $e->getMessage()]);

                return false;
            }
            if (!in_array($res->status(), [200, 202], true)) {
                Log::warning('IndexNow rejected', ['status' => $res->status(), 'body' => mb_substr($res->body(), 0, 300), 'count' => count($batch)]);

                return false;
            }
        }

        return true;
    }

    private function fetch(string $url): ?string
    {
        try {
            $res = Http::timeout(30)->get($url);

            return $res->successful() ? $res->body() : null;
        } catch (\Throwable $e) {
            Log::warning('IndexNow sitemap fetch failed', ['url' => $url, 'error' => $e->getMessage()]);

            return null;
        }
    }
}
