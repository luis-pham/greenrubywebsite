<?php

namespace App\Services\Publication;

/**
 * Marks the current request as a public read (Content API, Laravel sitemaps and legacy public routes, booking API).
 * Only then does PublishedScope hide unpublished rows; the admin, the editor API, queues and the CLI always see
 * every row. Bound per application instance, so nothing leaks between requests, jobs or tests.
 */
final class PublicationContext
{
    private const KEY = 'publication.public-context';

    public static function enterPublic(): void
    {
        app()->instance(self::KEY, true);
    }

    public static function leave(): void
    {
        app()->forgetInstance(self::KEY);
        app()->forgetInstance(self::KEY . '.slugs');
    }

    public static function isPublic(): bool
    {
        return app()->bound(self::KEY) && app(self::KEY) === true;
    }

    /**
     * Phase 1C-5: the current public read is for a new (intl) language: rows without a stored slug have no URL and
     * are hidden (lists and details). Never set for en/vi, whose legacy rows may still rely on a derived slug.
     */
    public static function requireStoredSlugs(): void
    {
        app()->instance(self::KEY . '.slugs', true);
    }

    public static function storedSlugsRequired(): bool
    {
        return app()->bound(self::KEY . '.slugs') && app(self::KEY . '.slugs') === true;
    }

    /** Runs $callback as a public read (tests, CLI checks), restoring the previous state afterwards. */
    public static function asPublic(callable $callback)
    {
        $was = self::isPublic();
        self::enterPublic();
        try {
            return $callback();
        } finally {
            if (!$was) {
                self::leave();
            }
        }
    }
}
