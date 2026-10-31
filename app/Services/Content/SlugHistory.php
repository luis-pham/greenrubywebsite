<?php

namespace App\Services\Content;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Old slugs of slug-only URLs (destination, article, article category, FAQ group), phase 1C-3. A lookup always
 * lands on the row's CURRENT slug, so redirects are one hop: no chains, and no loops even after several renames.
 * A slug that is live on a row is resolved by the normal lookup first, so history never shadows a live URL.
 */
final class SlugHistory
{
    /** The current row (id + slug) that once used $oldSlug, or null. */
    public static function movedRow(string $type, int $languageId, string $oldSlug): ?object
    {
        if ($oldSlug === '' || !Schema::hasTable('app_slug_history')) {
            return null;
        }
        $table = SlugValidator::TYPES[$type]['table'] ?? null;
        $entry = DB::table('app_slug_history')->where('entity_type', $type)->where('language_id', $languageId)
            ->where('old_slug', $oldSlug)->orderByDesc('id')->first();
        if (!$table || !$entry) {
            return null;
        }
        $row = DB::table($table)->where('id', $entry->entity_id)->where('language_id', $languageId)->first();

        return $row && trim((string) $row->slug) !== '' && $row->slug !== $oldSlug ? $row : null;
    }

    public static function movedSlug(string $type, int $languageId, string $oldSlug): ?string
    {
        return self::movedRow($type, $languageId, $oldSlug)?->slug;
    }
}
