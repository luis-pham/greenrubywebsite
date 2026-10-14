<?php

namespace App\Services\Content;

use Illuminate\Support\Str;
use Modules\BackEnd\Services\AppMenuFrontEndService;
use Modules\FrontEnd\Helpers\FeUtils;

/**
 * Builds a frontend menu tree for the Content API, mirroring the URL processing done for the
 * Blade layout in FrontEndServiceProvider::getMenu() (FeUtils::localizeMenuUrl +
 * normalizeMenuPath), but returning site-root-relative URLs instead of absolute ones.
 */
class ContentMenuBuilder
{
    public static function build(string $code, $language): array
    {
        $obj = AppMenuFrontEndService::getByCode($code, $language->id);
        if (!$obj || !$obj->menu) {
            return [];
        }

        $items = json_decode($obj->menu);
        if (!is_array($items)) {
            return [];
        }

        $ids = [];
        foreach ($items as $item) {
            $ids[(string) ($item->id ?? '')] = true;
        }

        $flat = [];
        foreach ($items as $item) {
            $parentId = $item->parent_id ?? null;
            // Orphans become top-level, same as the Blade menu processing.
            if ($parentId !== null && $parentId !== '' && !isset($ids[(string) $parentId])) {
                $parentId = null;
            }
            $flat[] = [
                'id' => (string) ($item->id ?? ''),
                'parentId' => ($parentId === null || $parentId === '') ? null : (string) $parentId,
                'name' => (string) ($item->name ?? ''),
                'url' => self::processUrl($item->url ?? '', $language),
                'target' => ($item->target ?? '') ?: '_self',
            ];
        }

        return self::tree($flat, null);
    }

    private static function tree(array $flat, ?string $parentId): array
    {
        $out = [];
        foreach ($flat as $item) {
            if ($item['parentId'] === $parentId) {
                $out[] = [
                    'name' => $item['name'],
                    'url' => $item['url'],
                    'target' => $item['target'],
                    'children' => self::tree($flat, $item['id']),
                ];
            }
        }

        return $out;
    }

    private static function processUrl(?string $url, $language): string
    {
        $url = (string) $url;
        if ($url === '') {
            return '';
        }
        if (Str::startsWith($url, ['javascript:', '#'])) {
            return $url;
        }

        $url = FeUtils::localizeMenuUrl($url, $language);

        $path = FeUtils::getAbsoluteUrl($url);
        if ($path) {
            $normalized = FeUtils::normalizeMenuPath($path);
            if ($normalized !== $path) {
                $url = str_replace($path, $normalized, $url);
            }
        }

        return self::toRelative($url);
    }

    /**
     * Strip scheme+host for URLs pointing at this site (APP_URL host or the current request host);
     * external URLs are returned untouched.
     */
    private static function toRelative(string $url): string
    {
        $parts = parse_url($url);
        if ($parts === false || empty($parts['host'])) {
            return $url;
        }

        $ownHosts = array_filter([
            parse_url((string) config('app.url'), PHP_URL_HOST),
            request()->getHost(),
        ]);
        if (!in_array(strtolower($parts['host']), array_map('strtolower', $ownHosts), true)) {
            return $url;
        }

        $relative = ($parts['path'] ?? '') ?: '/';
        if (!empty($parts['query'])) {
            $relative .= '?' . $parts['query'];
        }
        if (!empty($parts['fragment'])) {
            $relative .= '#' . $parts['fragment'];
        }

        return $relative;
    }
}
