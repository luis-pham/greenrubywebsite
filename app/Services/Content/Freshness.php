<?php

namespace App\Services\Content;

use Illuminate\Support\Facades\DB;

/**
 * Source freshness of a translation (12 §3 "Freshness", phase 1E-b). A translation records the hash of its source's
 * translatable text (TranslatableFields) when it is created or confirmed; it is "outdated" when the source's text
 * hashes differently now. Freshness never changes publication or workflow: an outdated published translation stays
 * live until someone deliberately unpublishes it.
 */
final class Freshness
{
    /** sha256 of a row's translatable text, including its child rows (cabin rooms, itinerary days). */
    public static function hash(string $type, int $id): ?string
    {
        $meta = TranslatableFields::of($type);
        $row = DB::table($meta['table'])->where('id', $id)->first();
        if (!$row) {
            return null;
        }
        $data = [];
        foreach ($meta['text'] as $column) {
            $data[$column] = self::normalize($row->{$column} ?? null);
        }
        foreach ($meta['children'] as $table => $child) {
            $data[$table] = DB::table($table)->where($child['key'], $id)->orderBy('ord')->orderBy('id')->get()
                ->map(fn ($c) => array_map(fn ($column) => self::normalize($c->{$column} ?? null), array_combine($child['text'], $child['text'])))
                ->all();
        }
        if ($type === 'itinerary') {
            $data['days'] = self::itineraryDays($id);
        }

        return hash('sha256', json_encode($data, JSON_UNESCAPED_UNICODE));
    }

    /**
     * 'legacy' (en/vi row without translation_meta), 'current', 'outdated', or 'source_missing'.
     */
    public static function status(string $type, int $id): string
    {
        $meta = DB::table('translation_meta')->where('entity_type', $type)->where('entity_id', $id)->first();
        if (!$meta || !$meta->source_entity_id) {
            return 'legacy';
        }
        $now = self::hash($type, (int) $meta->source_entity_id);
        if ($now === null) {
            return 'source_missing';
        }

        return hash_equals((string) $meta->source_hash, $now) ? 'current' : 'outdated';
    }

    /** "Reviewed against the current source": records the source's current hash. */
    public static function confirm(string $type, int $id, ?int $actor = null): void
    {
        $meta = DB::table('translation_meta')->where('entity_type', $type)->where('entity_id', $id)->first();
        if (!$meta || !$meta->source_entity_id) {
            throw new \RuntimeException("{$type} #{$id} has no source to compare with.");
        }
        DB::table('translation_meta')->where('entity_type', $type)->where('entity_id', $id)->update([
            'source_hash' => self::hash($type, (int) $meta->source_entity_id), 'source_hash_at' => now(),
            'translated_by' => $actor ?? $meta->translated_by, 'updated_at' => now(),
        ]);
    }

    /** Day number => detail lines (time, title, description), the text an itinerary translation carries. */
    public static function itineraryDays(int $itineraryId): array
    {
        $out = [];
        foreach (DB::table('app_itinerary_day')->where('itinerary_id', $itineraryId)->orderBy('day')->orderBy('id')->get() as $day) {
            $out[] = DB::table('app_itinerary_day_detail')->where('itinerary_day_id', $day->id)->orderBy('ord')->orderBy('id')->get()
                ->map(fn ($d) => array_map(fn ($c) => self::normalize($d->{$c} ?? null), array_combine(TranslatableFields::ITINERARY_DAY_TEXT, TranslatableFields::ITINERARY_DAY_TEXT)))
                ->all();
        }

        return $out;
    }

    private static function normalize($value): string
    {
        return is_string($value) ? trim(str_replace("\r\n", "\n", $value)) : (string) ($value ?? '');
    }
}
