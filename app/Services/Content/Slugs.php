<?php

namespace App\Services\Content;

use Modules\BackEnd\Helpers\Utilities;

/**
 * Stable slugs (owner decision #4, phase 1C-3). Cruise, cabin, itinerary and experience URLs used to be derived
 * from the current name, so a rename changed the URL. Now the slug is stored once (backfilled with exactly that
 * derivation) and a rename keeps it; only a deliberate `content:slug` change moves a URL (with history + redirect).
 * Readers use of(): the stored slug, else today's derivation, so a row without a stored slug behaves as before.
 */
final class Slugs
{
    /** The public slug of a row (object or array) with `slug` and a name field. */
    public static function of($row, string $nameField = 'name'): string
    {
        $stored = trim((string) data_get($row, 'slug', ''));
        if ($stored !== '') {
            return $stored;
        }
        // Rows of languages other than en/vi never get a derived slug: '' (no URL) until an editor sets one.
        $languageId = data_get($row, 'language_id');
        $language = $languageId === null ? null : \App\Services\Locale\LocaleRegistry::byId((int) $languageId);
        if ($language && !\App\Services\Locale\LocaleRegistry::isLegacy($language)) {
            return '';
        }

        return self::derive((string) data_get($row, $nameField, ''));
    }

    /**
     * The slug of row $id in $table, for callers that only hold a snapshot (page-config lists, a cruise name passed
     * around): the row's stored slug when it exists, else today's derivation of the name they have.
     */
    public static function forId(string $table, $id, string $fallbackName): string
    {
        $key = "slugs.row.$table." . (int) $id;
        if (!app()->bound($key)) {
            $row = (int) $id > 0 ? \Illuminate\Support\Facades\DB::table($table)->where('id', (int) $id)->first() : null;
            app()->instance($key, ['row' => $row ? (array) $row : null]); // wrapped: the container cannot hold null
        }
        $row = app($key)['row'];

        return $row ? self::of($row + ['name' => $fallbackName]) : self::derive($fallbackName);
    }

    /** The stored slug only (languages other than en/vi never fall back to a derivation). */
    public static function stored($row): string
    {
        return trim((string) data_get($row, 'slug', ''));
    }

    /** Today's derivation: Vietnamese accents removed, ASCII words joined by hyphens; '' for a Japanese name. */
    public static function derive(string $name): string
    {
        return Utilities::convertToAlias($name);
    }
}
