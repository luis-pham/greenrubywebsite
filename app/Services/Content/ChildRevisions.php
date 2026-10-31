<?php

namespace App\Services\Content;

use App\Services\Locale\LocaleRegistry;
use App\Services\Publication\PublicationPolicy;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Child text of a PUBLISHED translation (1E hardening): cabin room titles/descriptions and itinerary programme lines
 * go into the pending revision with the parent's fields; the public version stays as approved. Only text: the
 * structure (number and order of rooms, days, lines and their times) belongs to the price-list row and cannot be
 * changed from a published translation. en/vi and unpublished drafts keep editing their rows directly.
 * Each method returns true when it handled the change (the caller must then not write the child rows).
 */
final class ChildRevisions
{
    public const TABLES = ['cabin' => 'app_cabin_room', 'itinerary' => 'app_itinerary_day_detail'];

    /** @param string[] $titles @param array $descriptions positional, as the cabin form posts them */
    public static function cabinRooms(int $cabinId, array $titles, array $descriptions): bool
    {
        if (!self::intercepts('cabin', $cabinId)) {
            return false;
        }
        $live = DB::table('app_cabin_room')->where('cabin_id', $cabinId)->orderBy('ord')->orderBy('id')->get();
        $new = [];
        foreach ($titles as $i => $title) {
            if (trim((string) $title) !== '') {
                $new[] = ['title' => (string) $title, 'description' => $descriptions[$i] ?? null];
            }
        }
        if (count($new) !== $live->count()) {
            throw new \App\Services\Content\EditorialRefusal('Bản dịch đã xuất bản: không thêm/bớt phòng ở đây (số phòng theo bản gốc). Chỉ sửa chữ.');
        }
        $changes = [];
        foreach ($live->values() as $i => $room) {
            if ((string) $room->title !== $new[$i]['title'] || (string) $room->description !== (string) $new[$i]['description']) {
                $changes[(int) $room->id] = $new[$i];
            }
        }
        if ($changes) {
            RevisionStore::merge('cabin', $cabinId, [], ['app_cabin_room' => $changes], self::actor());
        }

        return true;
    }

    /** @param array $days as the itinerary form posts them: [{id, itinerary_day_details: [{id, time, title, description}]}] */
    public static function itineraryDays(int $itineraryId, array $days): bool
    {
        if (!self::intercepts('itinerary', $itineraryId)) {
            return false;
        }
        $live = DB::table('app_itinerary_day')->where('itinerary_id', $itineraryId)->orderBy('day')->orderBy('id')->get();
        $refuse = fn () => throw new \App\Services\Content\EditorialRefusal('Bản dịch đã xuất bản: không đổi số ngày, số dòng hay giờ của chương trình ở đây (theo bản gốc). Chỉ sửa chữ.');
        if (count($days) !== $live->count()) {
            $refuse();
        }
        $changes = [];
        foreach (array_values($days) as $i => $day) {
            $liveDay = $live[$i];
            $liveDetails = DB::table('app_itinerary_day_detail')->where('itinerary_day_id', $liveDay->id)->get()->keyBy('id');
            $posted = array_values($day['itinerary_day_details'] ?? []);
            if ((int) ($day['id'] ?? 0) !== (int) $liveDay->id || count($posted) !== $liveDetails->count()) {
                $refuse();
            }
            foreach ($posted as $detail) {
                $row = $liveDetails->get((int) ($detail['id'] ?? 0));
                if (!$row || substr((string) $row->time, 0, 5) !== substr((string) ($detail['time'] ?? ''), 0, 5)) {
                    $refuse();
                }
                $title = (string) ($detail['title'] ?? '');
                $description = (string) ($detail['description'] ?? '');
                if ((string) $row->title !== $title || (string) $row->description !== $description) {
                    $changes[(int) $row->id] = ['title' => $title, 'description' => $description];
                }
            }
        }
        if ($changes) {
            RevisionStore::merge('itinerary', $itineraryId, [], ['app_itinerary_day_detail' => $changes], self::actor());
        }

        return true;
    }

    /** Writes a revision's child text into the live rows (only rows that belong to the entity, only title/description). */
    public static function apply(string $type, int $id, array $children): void
    {
        $table = self::TABLES[$type] ?? null;
        foreach (($table ? ($children[$table] ?? []) : []) as $rowId => $values) {
            $belongs = $type === 'cabin'
                ? DB::table('app_cabin_room')->where('id', $rowId)->where('cabin_id', $id)->exists()
                : DB::table('app_itinerary_day_detail')->join('app_itinerary_day', 'app_itinerary_day.id', '=', 'app_itinerary_day_detail.itinerary_day_id')
                    ->where('app_itinerary_day_detail.id', $rowId)->where('app_itinerary_day.itinerary_id', $id)->exists();
            if ($belongs) {
                DB::table($table)->where('id', $rowId)->update(array_intersect_key($values, array_flip(['title', 'description'])) + ['updated_at' => now()]);
            }
        }
    }

    /** True for a published row of a workflow-controlled language (revisions table present). */
    private static function intercepts(string $type, int $id): bool
    {
        if (!DB::getSchemaBuilder()->hasTable('content_revision')) {
            return false;
        }
        $row = DB::table(TranslatableFields::of($type)['table'])->where('id', $id)->first();
        $language = $row ? LocaleRegistry::byId((int) $row->language_id) : null;
        if (!$language || LocaleRegistry::isLegacy($language) || in_array($language->getAttribute('workflow_controlled'), [0, '0', false], true)) {
            return false;
        }

        return PublicationPolicy::isPublishedFlag($type, $row);
    }

    private static function actor(): ?int
    {
        $id = auth('admin')->id();

        return $id === null ? null : (int) $id;
    }
}
