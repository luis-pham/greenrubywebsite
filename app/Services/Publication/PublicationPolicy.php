<?php

namespace App\Services\Publication;

use App\Services\Locale\LocaleRegistry;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The one public-visibility rule per content row (12 §4, phase 1C-2). Pure: reads state, never writes.
 *
 * visible(row) = its language is effectively public (LocaleRegistry)
 *              ∧ its own flag says published (publish_state, is_published + publish_date, status, is_visible)
 *              ∧ its parent is not an unpublished row (cabin → cruise, FAQ → FAQ group, article → category).
 * Lists use PublishedScope (the same flags in SQL); detail checks and the resolver use isVisible().
 */
final class PublicationPolicy
{
    /** entity type (ContentTranslations::TYPES names + faq) => table and how "published" is stored. */
    public const TYPES = [
        'cruise' => ['table' => 'app_cruise', 'flag' => 'publish_state'],
        'cabin' => ['table' => 'app_cabin', 'flag' => 'publish_state'],
        'itinerary' => ['table' => 'app_itinerary', 'flag' => 'publish_state'],
        'experience' => ['table' => 'app_exp_activity', 'flag' => 'publish_state'],
        'faq' => ['table' => 'app_faq', 'flag' => 'publish_state'],
        'faq_group' => ['table' => 'app_group', 'flag' => 'publish_state'],
        'article_category' => ['table' => 'app_category', 'flag' => 'publish_state'],
        'article' => ['table' => 'app_article', 'flag' => 'is_published'],
        'destination' => ['table' => 'app_destination', 'flag' => 'status'],
        // Phase 1E-c: page-level publication (static pages of a new language; en/vi: PageConfigContentService).
        'page' => ['table' => 'app_page', 'flag' => 'is_visible'],
    ];

    /** table => parent table and foreign key (12 §4.1 parent rules). */
    public const PARENTS = [
        'app_cabin' => ['table' => 'app_cruise', 'key' => 'cruise_id'],
        'app_faq' => ['table' => 'app_group', 'key' => 'group_id'],
        'app_article' => ['table' => 'app_category', 'key' => 'category_id'],
    ];

    public static function hasColumn(string $table, string $column = 'publish_state'): bool
    {
        $key = "publication.has.$table.$column";
        if (!app()->bound($key)) {
            app()->instance($key, Schema::hasColumn($table, $column));
        }

        return app($key);
    }

    /** The row's own flag only (no locale, no parent). */
    public static function isPublishedFlag(string $type, object $row): bool
    {
        $flag = self::TYPES[$type]['flag'] ?? null;

        return match ($flag) {
            'publish_state' => !self::hasColumn(self::TYPES[$type]['table']) || ($row->publish_state ?? 'published') === 'published',
            'is_published' => (int) ($row->is_published ?? 0) === 1
                && (empty($row->publish_date) || strtotime((string) $row->publish_date) <= time()),
            'status' => ($row->status ?? null) === 'published',
            'is_visible' => (int) ($row->is_visible ?? 1) === 1,
            default => true,
        };
    }

    /** Public visibility of one row: effectively public language, own flag, parent not unpublished. */
    public static function isVisible(string $type, object $row): bool
    {
        $language = LocaleRegistry::byId((int) ($row->language_id ?? 0));
        if (!$language || !LocaleRegistry::isEffectivePublic($language)) {
            return false;
        }
        if (!self::isPublishedFlag($type, $row)) {
            return false;
        }
        $table = self::TYPES[$type]['table'] ?? null;
        $parent = $table ? (self::PARENTS[$table] ?? null) : null;
        if ($parent && !empty($row->{$parent['key']}) && self::hasColumn($parent['table'])) {
            $state = DB::table($parent['table'])->where('id', $row->{$parent['key']})->value('publish_state');
            if ($state !== null && $state !== 'published') {
                return false;
            }
        }

        return true;
    }

    /** The value that marks a row unpublished, per flag (used by PublishGuard). */
    public static function unpublishedAttributes(string $type): array
    {
        return match (self::TYPES[$type]['flag'] ?? null) {
            'publish_state' => ['publish_state' => 'unpublished'],
            'is_published' => ['is_published' => 0],
            'status' => ['status' => 'draft'],
            'is_visible' => ['is_visible' => 0],
            default => [],
        };
    }

    /**
     * Raw query builders (booking API) get the same rule as PublishedScope: during a public read, rows of these
     * tables must be published and their parent not unpublished. No-op outside a public read or before migration.
     */
    public static function wherePublished($query, array $tables)
    {
        if (!PublicationContext::isPublic()) {
            return $query;
        }
        foreach ($tables as $table) {
            if (!self::hasColumn($table)) {
                continue;
            }
            $query->where($table . '.publish_state', 'published');
            $parent = self::PARENTS[$table] ?? null;
            if ($parent && self::hasColumn($parent['table'])) {
                $query->whereNotExists(function ($sub) use ($table, $parent) {
                    $sub->selectRaw('1')->from($parent['table'] . ' as parent_pub')
                        ->whereColumn('parent_pub.id', $table . '.' . $parent['key'])
                        ->where('parent_pub.publish_state', '<>', 'published');
                });
            }
        }

        return $query;
    }

    /**
     * Same rule applied after the query, for result lists whose order must not change: an extra WHERE can change
     * MySQL's plan and with it the order of rows that tie on the ORDER BY columns (seen on the booking cabin list).
     * Removes rows of $table that are unpublished or whose parent ($parentKey) is unpublished.
     */
    public static function withoutUnpublished($rows, string $table, string $idKey = 'id', ?string $parentKey = null)
    {
        if (!PublicationContext::isPublic() || !self::hasColumn($table)) {
            return $rows;
        }
        $ids = collect($rows)->pluck($idKey)->filter()->unique()->values()->all();
        $hidden = $ids === [] ? [] : DB::table($table)->whereIn('id', $ids)->where('publish_state', '<>', 'published')->pluck('id')->map(fn ($id) => (int) $id)->all();
        $hiddenParents = [];
        $parent = self::PARENTS[$table] ?? null;
        if ($parentKey && $parent && self::hasColumn($parent['table'])) {
            $parentIds = collect($rows)->pluck($parentKey)->filter()->unique()->values()->all();
            $hiddenParents = $parentIds === [] ? [] : DB::table($parent['table'])->whereIn('id', $parentIds)->where('publish_state', '<>', 'published')->pluck('id')->map(fn ($id) => (int) $id)->all();
        }
        if ($hidden === [] && $hiddenParents === []) {
            return $rows;
        }

        return collect($rows)->reject(fn ($row) => in_array((int) data_get($row, $idKey), $hidden, true)
            || ($parentKey && in_array((int) data_get($row, $parentKey), $hiddenParents, true)))->values();
    }
}
