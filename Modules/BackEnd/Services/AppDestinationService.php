<?php
namespace Modules\BackEnd\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\BackEnd\Entities\AppDestination;
use Modules\BackEnd\Entities\AppItinerary;

class AppDestinationService
{
    /** Form fields copied as-is onto the row. */
    private const FIELDS = [
        'name', 'heading', 'bay', 'category', 'summary', 'content', 'latitude', 'longitude', 'coords_source',
        'image_link', 'image_alt', 'image_credit', 'status', 'editor_notes', 'fact_checked_at',
        'seo_title', 'seo_description', 'ord',
    ];

    public static function find($id, $languageId = null)
    {
        $query = AppDestination::where('id', $id);
        if ($languageId !== null) {
            $query->where('language_id', $languageId);
        }
        return $query->first();
    }

    public static function create($data, $languageId)
    {
        return DB::transaction(function () use ($data, $languageId) {
            $obj = new AppDestination();
            $obj->language_id = $languageId;
            self::fill($obj, $data, $languageId);
            if (!$obj->ord) {
                $obj->ord = self::getNextOrder($languageId);
            }
            $obj->created_at = now(); // BaseModel only stamps rows saved by a logged-in admin
            $obj->save();
            self::syncItineraries($obj->id, $data['itinerary_ids'] ?? [], $languageId);

            return $obj->id;
        });
    }

    public static function update($data, $languageId)
    {
        DB::transaction(function () use ($data, $languageId) {
            $obj = self::find($data['id'], $languageId);
            if (!$obj) {
                return;
            }
            self::fill($obj, $data, $languageId);
            $obj->updated_at = now(); // also for CLI imports (sitemap lastmod)
            $obj->save();
            self::syncItineraries($obj->id, $data['itinerary_ids'] ?? [], $languageId);
        });
    }

    public static function delete($id, $languageId)
    {
        $ids = AppDestination::whereIn('id', (array) $id)->where('language_id', $languageId)->pluck('id');
        DB::table('app_destination_itinerary')->whereIn('destination_id', $ids)->delete();
        DB::table('content_translations')->where('entity_type', 'destination')->whereIn('entity_id', $ids)->delete();
        AppDestination::whereIn('id', $ids)->delete();
    }

    public static function getPaging($param, $languageId)
    {
        $list = AppDestination::where('language_id', $languageId);
        if (!empty($param['keyword'])) {
            $list->where(function ($query) use ($param) {
                $query->where('name', 'like', '%' . $param['keyword'] . '%')
                    ->orWhere('slug', 'like', '%' . $param['keyword'] . '%');
            });
        }
        foreach (['bay', 'category', 'status'] as $field) {
            if (!empty($param[$field])) {
                $list->where($field, $param[$field]);
            }
        }

        return $list->orderBy('ord')->orderBy('id')->get();
    }

    public static function saveOrder($list, $languageId)
    {
        DB::transaction(function () use ($list, $languageId) {
            foreach ($list as $id => $ord) {
                AppDestination::where('id', $id)->where('language_id', $languageId)->update(['ord' => (int) $ord]);
            }
        });
    }

    public static function getNextOrder($languageId)
    {
        return (int) AppDestination::where('language_id', $languageId)->max('ord') + 1;
    }

    /** Itineraries of the language, for the "planned Green Ruby stop" multi-select: id => name. */
    public static function itineraryOptions($languageId): array
    {
        return AppItinerary::where('language_id', $languageId)->orderBy('id')->pluck('name', 'id')->all();
    }

    public static function itineraryIds($destinationId): array
    {
        return DB::table('app_destination_itinerary')->where('destination_id', $destinationId)->pluck('itinerary_id')->map(fn ($id) => (int) $id)->all();
    }

    /** URL slug: lowercase ASCII words joined by "-" (Vietnamese diacritics removed, đ -> d). */
    public static function slugify(?string $value): string
    {
        return Str::slug(str_replace(['đ', 'Đ'], 'd', (string) $value));
    }

    public static function slugTaken(string $slug, $languageId, $exceptId = null): bool
    {
        return AppDestination::where('language_id', $languageId)
            ->where('slug', $slug)
            ->when($exceptId, fn ($q) => $q->where('id', '!=', $exceptId))
            ->exists();
    }

    private static function fill(AppDestination $obj, array $data, $languageId): void
    {
        foreach (self::FIELDS as $field) {
            if (array_key_exists($field, $data)) {
                $obj->{$field} = $data[$field] === '' ? null : $data[$field];
            }
        }
        $obj->slug = self::slugify(($data['slug'] ?? '') ?: $obj->name);
        $obj->coords_verified = !empty($data['coords_verified']) && $obj->latitude !== null && $obj->longitude !== null;
        $obj->facts = self::rows($data['facts'] ?? [], ['label', 'value']);
        $obj->faq = self::rows($data['faq'] ?? [], ['q', 'a']);
        $obj->sources = self::rows($data['sources'] ?? [], ['title', 'url']);
    }

    /** Repeater rows from the form, trimmed; rows missing any field are dropped. */
    private static function rows($rows, array $keys): array
    {
        $out = [];
        foreach (is_array($rows) ? $rows : [] as $row) {
            $clean = [];
            foreach ($keys as $key) {
                $clean[$key] = trim((string) ($row[$key] ?? ''));
            }
            if (!in_array('', $clean, true)) {
                $out[] = $clean;
            }
        }

        return $out;
    }

    private static function syncItineraries($destinationId, $itineraryIds, $languageId): void
    {
        $valid = AppItinerary::where('language_id', $languageId)->whereIn('id', array_map('intval', (array) $itineraryIds))->pluck('id');
        DB::table('app_destination_itinerary')->where('destination_id', $destinationId)->delete();
        DB::table('app_destination_itinerary')->insert($valid->map(fn ($id) => ['destination_id' => $destinationId, 'itinerary_id' => $id])->all());
    }
}
