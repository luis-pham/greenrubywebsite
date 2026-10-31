<?php

namespace App\Services\Publication;

use App\Services\Locale\LocaleRegistry;
use Illuminate\Support\Facades\DB;

/**
 * Publication of the rows that the Content API reads through raw joins, pivots and page-config pickers, where
 * PublishedScope does not reach (phase 2C):
 *   amenity  app_amenity.publish_state (2026_11_07_000001)
 *   service  app_service.status (1 = published; TranslationFactory creates 0)
 *   group    app_group.publish_state of a structural group (cabin facility type, experience tab, service group,
 *            suitable audience) seeded by `i18n:seed-locale` as unpublished
 * Applies ONLY to rows of a new (intl) language during a public read: en/vi rows are never filtered, and every
 * method returns its input unchanged (same object, no query) when nothing is hidden, so en/vi output stays
 * byte-identical. An unpublished row is dropped from lists; an unpublished group's name is never shown.
 */
final class IntlVisibility
{
    /** type => [table, flag column, published value] */
    public const TYPES = [
        'amenity' => ['app_amenity', 'publish_state', 'published'],
        'service' => ['app_service', 'status', 1],
        'group' => ['app_group', 'publish_state', 'published'],
    ];

    /** True when rows of this language are filtered now: a public read of a new (non en/vi) language. */
    public static function applies($languageId): bool
    {
        if (!PublicationContext::isPublic() || !$languageId) {
            return false;
        }
        $language = LocaleRegistry::byId((int) $languageId);

        return $language !== null && !LocaleRegistry::isLegacy($language);
    }

    /**
     * $rows without the unpublished rows of an intl language (rows carry language_id and, ideally, the flag).
     * Returns $rows itself when none is hidden.
     */
    public static function keep(string $type, $rows)
    {
        if (!PublicationContext::isPublic() || !self::flagExists($type)) {
            return $rows;
        }
        $hidden = [];
        foreach ($rows as $key => $row) {
            if (self::applies(data_get($row, 'language_id')) && !self::published($type, $row)) {
                $hidden[] = $key;
            }
        }
        if ($hidden === []) {
            return $rows;
        }

        return collect($rows)->except($hidden)->values();
    }

    /**
     * Ids among $ids of $type rows that are hidden for a public read of $languageId (an intl language). Empty, with
     * no query, for en/vi or outside a public read.
     */
    public static function hiddenIds(string $type, iterable $ids, $languageId): array
    {
        if (!self::applies($languageId) || !self::flagExists($type)) {
            return [];
        }
        $ids = collect($ids)->filter()->map(fn ($id) => (int) $id)->unique()->values()->all();
        if ($ids === []) {
            return [];
        }
        [$table, $column, $value] = self::TYPES[$type];

        return DB::table($table)->whereIn('id', $ids)->where(fn ($q) => $q->where($column, '<>', $value)->orWhereNull($column))
            ->pluck('id')->map(fn ($id) => (int) $id)->all();
    }

    /** $rows without those whose $idKey is hidden (rows of joins that lack the flag, e.g. a cabin's amenities). */
    public static function keepIds(string $type, $rows, string $idKey, $languageId)
    {
        $hidden = self::hiddenIds($type, collect($rows)->pluck($idKey), $languageId);
        if ($hidden === []) {
            return $rows;
        }

        return collect($rows)->reject(fn ($row) => in_array((int) data_get($row, $idKey), $hidden, true))->values();
    }

    /** The row's own flag; read from the table when the row does not carry it. */
    private static function published(string $type, $row): bool
    {
        [$table, $column, $value] = self::TYPES[$type];
        $flag = data_get($row, $column);
        if ($flag === null) {
            $flag = DB::table($table)->where('id', (int) data_get($row, 'id'))->value($column);
        }

        return is_int($value) ? (int) $flag === $value : (string) $flag === $value;
    }

    private static function flagExists(string $type): bool
    {
        [$table, $column] = self::TYPES[$type];

        return PublicationPolicy::hasColumn($table, $column);
    }
}
