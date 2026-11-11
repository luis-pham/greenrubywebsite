<?php

namespace App\Payments;

use App\Services\Locale\LocaleRegistry;
use App\Services\Publication\PublicationPolicy;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Modules\BackEnd\Entities\AdLanguage;

/**
 * Display text of the booking API for a new (intl) language. Those pages book the English inventory rows
 * (PriceList::inventoryLanguageCode), so names and descriptions came back in English. Each English row's published
 * translation (translation_meta) now supplies the text; ids, prices, capacities and everything stored on a booking
 * stay the English row's. No-op for en/vi, an unknown language, or one that is not effectively public.
 */
final class BookingDisplayText
{
    /** entity type => table and the text columns shown by the booking API */
    private const TEXT = [
        'itinerary' => ['app_itinerary', ['name', 'destination']],
        'cruise' => ['app_cruise', ['name']],
        'cabin' => ['app_cabin', ['name', 'summary', 'view']],
        'service' => ['app_service', ['name', 'description']],
        'experience' => ['app_exp_activity', ['name']],
    ];

    /** The intl display language of a request (?lang=), or null when the English text stays. */
    public static function language(?string $code): ?AdLanguage
    {
        $language = LocaleRegistry::byCode(strtolower((string) $code));
        if (!$language || LocaleRegistry::isLegacy($language) || !LocaleRegistry::isEffectivePublic($language)) {
            return null;
        }

        return $language;
    }

    /** English id => translated row (published only) of the given type. */
    public static function rows(AdLanguage $language, string $type, array $englishIds): array
    {
        [$table, $columns] = self::TEXT[$type];
        $ids = array_values(array_unique(array_filter(array_map('intval', $englishIds))));
        if ($ids === []) {
            return [];
        }
        $map = DB::table('translation_meta')->where('entity_type', $type)->where('language_id', $language->id)
            ->whereIn('source_entity_id', $ids)->pluck('entity_id', 'source_entity_id');
        if ($map->isEmpty()) {
            return [];
        }
        $select = array_merge(['id', 'language_id'], $columns, array_filter(['publish_state', 'status', 'cruise_id'], fn ($c) => PublicationPolicy::hasColumn($table, $c)));
        $rows = DB::table($table)->whereIn('id', $map->values())->get($select)->keyBy('id');
        $out = [];
        foreach ($map as $englishId => $id) {
            $row = $rows->get($id);
            if ($row && self::published($type, $row)) {
                $out[(int) $englishId] = $row;
            }
        }

        return $out;
    }

    private static function published(string $type, object $row): bool
    {
        if (isset(PublicationPolicy::TYPES[$type])) {
            return PublicationPolicy::isVisible($type, $row);
        }

        return !property_exists($row, 'status') || (int) $row->status === 1; // services: status 1 = active
    }

    /** Non-empty translated text, else the English value. */
    public static function text(?object $row, string $column, $fallback)
    {
        $value = $row->$column ?? null;

        return is_string($value) && trim($value) !== '' ? $value : $fallback;
    }

    /** "2 jours / 1 nuit", "2 días / 1 noche", "1泊2日"; English for a language without its own form. */
    public static function durationLabel(int $duration, AdLanguage $language): string
    {
        if ($duration <= 0) {
            return '';
        }
        $nights = $duration - 1;

        return match (strtolower((string) $language->code)) {
            'fr' => $duration . "\u{00a0}jour" . ($duration > 1 ? 's' : '') . ($nights > 0 ? ' / ' . $nights . "\u{00a0}nuit" . ($nights > 1 ? 's' : '') : ''),
            'es' => $duration . ' día' . ($duration > 1 ? 's' : '') . ($nights > 0 ? ' / ' . $nights . ' noche' . ($nights > 1 ? 's' : '') : ''),
            'de' => $duration . ($duration > 1 ? ' Tage' : ' Tag') . ($nights > 0 ? ' / ' . $nights . ($nights > 1 ? ' Nächte' : ' Nacht') : ''),
            'ja' => $nights > 0 ? "{$nights}泊{$duration}日" : "{$duration}日",
            default => $duration . ' Day' . ($duration > 1 ? 's' : '') . ($nights > 0 ? ' / ' . $nights . ' Night' . ($nights > 1 ? 's' : '') : ''),
        };
    }

    /** Itinerary list items (booking/itineraries, suggest-by-*): name, cruise name, destination, duration label. */
    public static function itineraries(?string $code, Collection $items): Collection
    {
        $language = self::language($code);
        if (!$language || $items->isEmpty()) {
            return $items;
        }
        $itineraries = self::rows($language, 'itinerary', $items->pluck('itinerary_id')->all());
        $cruises = self::rows($language, 'cruise', $items->pluck('cruise_id')->all());

        return $items->map(function (array $item) use ($itineraries, $cruises, $language) {
            $itinerary = $itineraries[$item['itinerary_id']] ?? null;
            $item['name'] = self::text($itinerary, 'name', $item['name']);
            $item['cruise_name'] = self::text($cruises[$item['cruise_id']] ?? null, 'name', $item['cruise_name']);
            if ($itinerary && is_string($itinerary->destination) && ($list = json_decode($itinerary->destination, true)) && is_array($list)) {
                $item['destination'] = implode(', ', $list);
            }
            $item['duration_label'] = self::durationLabel((int) $item['duration'], $language);

            return $item;
        });
    }

    /** Cabin items: name, summary, view, cruise name. */
    public static function cabins(?string $code, Collection $items): Collection
    {
        $language = self::language($code);
        if (!$language || $items->isEmpty()) {
            return $items;
        }
        $cabins = self::rows($language, 'cabin', $items->pluck('id')->all());
        $cruises = self::rows($language, 'cruise', $items->pluck('cruise_id')->all());

        return $items->map(function (array $item) use ($cabins, $cruises) {
            $cabin = $cabins[$item['id']] ?? null;
            foreach (['name', 'summary', 'view'] as $column) {
                $item[$column] = self::text($cabin, $column, $item[$column]);
            }
            $item['cruise_name'] = self::text($cruises[$item['cruise_id']] ?? null, 'name', $item['cruise_name']);

            return $item;
        });
    }

    /** Optional service items: name, description. */
    public static function amenities(?string $code, Collection $items): Collection
    {
        $language = self::language($code);
        if (!$language || $items->isEmpty()) {
            return $items;
        }
        $services = self::rows($language, 'service', $items->pluck('id')->all());

        return $items->map(function (array $item) use ($services) {
            $service = $services[$item['id']] ?? null;
            $item['name'] = self::text($service, 'name', $item['name']);
            $item['description'] = self::text($service, 'description', $item['description']);

            return $item;
        });
    }

    /** Itinerary detail: activity names and the destination list. */
    public static function detail(?string $code, int $itineraryId, array $activities, array $destinations): array
    {
        $language = self::language($code);
        if (!$language) {
            return [$activities, $destinations];
        }
        $experiences = self::rows($language, 'experience', array_column($activities, 'id'));
        foreach ($activities as &$activity) {
            $activity['name'] = self::text($experiences[$activity['id']] ?? null, 'name', $activity['name']);
        }
        unset($activity);
        $itinerary = self::rows($language, 'itinerary', [$itineraryId])[$itineraryId] ?? null;
        if ($itinerary && is_string($itinerary->destination) && ($list = json_decode($itinerary->destination, true)) && is_array($list) && $list !== []) {
            $destinations = array_values($list);
        }

        return [$activities, $destinations];
    }
}
