<?php

namespace App\Support;

class SafeRedirect
{
    /**
     * Validate a redirect target: relative paths on this app, or absolute URLs on APP_URL host.
     */
    public static function isAllowed(?string $url): bool
    {
        if ($url === null || $url === '') {
            return false;
        }

        $url = trim($url);

        if (str_starts_with($url, '//')) {
            return false;
        }

        if (str_starts_with($url, '/')) {
            return !str_starts_with($url, '//') && !str_contains($url, '..');
        }

        $parsed = parse_url($url);
        if ($parsed === false || empty($parsed['host'])) {
            return false;
        }

        $appUrl = parse_url(config('app.url', ''));
        if (empty($appUrl['host'])) {
            return false;
        }

        return strcasecmp($parsed['host'], $appUrl['host']) === 0;
    }

    /**
     * Normalize a safe relative or same-origin URL; returns null if disallowed.
     */
    public static function normalize(?string $url): ?string
    {
        if (!self::isAllowed($url)) {
            return null;
        }

        $url = trim($url);

        if (str_starts_with($url, '/')) {
            return $url;
        }

        return $url;
    }
}
