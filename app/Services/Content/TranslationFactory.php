<?php

namespace App\Services\Content;

use App\Services\Locale\LocaleRegistry;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\BackEnd\Entities\AdLanguage;
use Modules\BackEnd\Services\AppCategoryService;

/**
 * "Create Japanese translation" (phase 1E-b; plan §3 TranslationFactory). From a source row (normally English) it
 * creates, in one transaction, an UNPUBLISHED draft in the target language that holds:
 *   - the source's translatable text (TranslatableFields 'text'), as the text to translate;
 *   - presentation references (images, ordering) and child text rows (cabin rooms, itinerary days);
 *   - relations mapped to the target language (a cabin's cruise, an article's category, an FAQ's group); a required
 *     parent without a translation refuses the action ("translate the cruise first");
 *   - for an itinerary, one relation marker per translated cruise (start_at 1970-01-01: never a departure);
 *   - translation_meta (workflow draft, source row, source hash), and the content_translations link (attach, logged).
 * It never copies prices, departures, capacities or specifications as editable data: the few NOT NULL business
 * columns get a mirror of the source's values only to satisfy the schema; the Content API reads the price-list row
 * (BusinessFields) and every write to them is refused (BusinessFieldsObserver). Slugs are never copied: an editor
 * sets an ASCII slug. Nothing is published: publication needs the workflow (PublishGuardObserver).
 */
final class TranslationFactory
{
    /** NOT NULL business columns: mirrored from the source row (read-only, never shown for this language). */
    private const MIRROR = [
        'cruise' => ['capacity', 'total_floor', 'dimension_length', 'year_built'],
        'cabin' => ['capacity', 'area', 'discount_percent'],
        'itinerary' => ['duration', 'bay', 'start_time'],
        'experience' => ['start_time', 'end_time'],
        'service' => ['price'],
    ];

    /** NOT NULL slug columns of a draft that has none yet. */
    private const EMPTY_SLUG = ['article_category', 'faq_group', 'destination', 'article'];

    /** @return array{id: int, type: string, language: string} */
    public static function create(string $type, int $sourceId, AdLanguage $target, ?int $actor = null, ?string $reason = null): array
    {
        $meta = TranslatableFields::of($type);
        $table = $meta['table'];
        $source = DB::table($table)->where('id', $sourceId)->first();
        if (!$source) {
            throw new \InvalidArgumentException("Không tìm thấy {$meta['label']} #{$sourceId}.");
        }
        $targetId = (int) $target->id;
        if ((int) $source->language_id === $targetId) {
            throw new \RuntimeException('Bản gốc đã ở ngôn ngữ này.');
        }
        if (LocaleRegistry::isLegacy($target) || !LocaleRegistry::isEditable($target)) {
            throw new \RuntimeException("Không tạo bản dịch tự động sang {$target->code}: chỉ cho ngôn ngữ mới đang biên tập (EN/VI giữ cách ghép cũ).");
        }
        if ($existing = self::existingTranslation($type, $sourceId, $targetId)) {
            throw new \RuntimeException("{$meta['label']} #{$sourceId} đã có bản {$target->code} (#{$existing}).");
        }
        if ($meta['grouped'] && !ContentTranslations::isLinked($type, $sourceId)) {
            throw new \RuntimeException("{$meta['label']} #{$sourceId} chưa thuộc nhóm bản dịch nào: ghép EN/VI (hoặc đánh dấu \"không có bản dịch\") trước.");
        }

        $values = ['language_id' => $targetId];
        foreach ($meta['text'] as $column) {
            $values[$column] = $source->{$column} ?? null;
        }
        foreach ($meta['copy'] as $column) {
            $values[$column] = $source->{$column} ?? null;
        }
        foreach (self::MIRROR[$type] ?? [] as $column) {
            $values[$column] = $source->{$column} ?? null;
        }
        foreach ($meta['parents'] as $column => $parent) {
            $values[$column] = self::parentIn($parent, $source->{$column} ?? null, $target, $meta['label']);
        }
        if ($meta['slug']) {
            $values['slug'] = in_array($type, self::EMPTY_SLUG, true) ? '' : null;
        }
        $values += self::unpublished($type);
        $now = now();
        if (DB::getSchemaBuilder()->hasColumn($table, 'created_at')) {
            $values += ['created_at' => $now, 'created_by' => $actor ?? 0, 'updated_at' => $now, 'updated_by' => $actor ?? 0];
        }
        if ($type === 'article') {
            $values['publish_date'] = $now;
        }

        $id = DB::transaction(function () use ($type, $meta, $table, $source, $sourceId, $target, $targetId, $values, $actor, $reason, $now) {
            $id = $type === 'article_category' ? self::createCategory($source, $target, $values) : (int) DB::table($table)->insertGetId($values);
            foreach ($meta['children'] as $childTable => $child) {
                foreach (DB::table($childTable)->where($child['key'], $sourceId)->orderBy('id')->get() as $row) {
                    $copy = [$child['key'] => $id];
                    foreach (array_merge($child['text'], $child['copy']) as $column) {
                        $copy[$column] = $row->{$column} ?? null;
                    }
                    DB::table($childTable)->insert($copy + ['created_at' => $now, 'created_by' => $actor ?? 0, 'updated_at' => $now, 'updated_by' => $actor ?? 0]);
                }
            }
            if ($type === 'itinerary') {
                self::copyItinerary($sourceId, $id, $target, $actor, $now);
            }
            if ($meta['grouped']) {
                ContentTranslations::attach($type, $sourceId, $id, $actor, $reason ?? "Tạo bản dịch {$target->code} từ #{$sourceId}");
            }
            DB::table('translation_meta')->insert([
                'entity_type' => $type, 'entity_id' => $id, 'language_id' => $targetId, 'workflow' => 'draft',
                'source_entity_id' => $sourceId, 'source_language_id' => (int) $source->language_id,
                'source_hash' => Freshness::hash($type, $sourceId), 'source_hash_at' => $now,
                'translated_by' => $actor, 'updated_at' => $now,
            ]);

            return $id;
        });
        Log::notice('Translation draft created', ['type' => $type, 'source' => $sourceId, 'id' => $id, 'language' => $target->code, 'actor' => $actor]);

        return ['id' => $id, 'type' => $type, 'language' => (string) $target->code];
    }

    /** The target-language row already made from (or linked with) $sourceId, if any. */
    public static function existingTranslation(string $type, int $sourceId, int $targetId): ?int
    {
        if (TranslatableFields::of($type)['grouped']) {
            return ContentTranslations::isLinked($type, $sourceId) ? ContentTranslations::linkedId($type, $sourceId, $targetId) : null;
        }
        $id = DB::table('translation_meta')->where('entity_type', $type)->where('source_entity_id', $sourceId)->where('language_id', $targetId)->value('entity_id');

        return $id === null ? null : (int) $id;
    }

    /** The target-language translation of a parent row, or null when optional and missing. */
    private static function parentIn(array $parent, $sourceParentId, AdLanguage $target, string $label): ?int
    {
        if (!$sourceParentId) {
            return null;
        }
        if ($parent['type'] === 'group') {
            $id = self::structuralGroup((int) $sourceParentId, (int) $target->id);
            if ($id === null && $parent['required']) {
                throw new \RuntimeException("Chưa có nhóm #{$sourceParentId} ở {$target->code}: chạy php artisan i18n:seed-locale {$target->code} trước.");
            }

            return $id;
        }
        $id = self::existingTranslation($parent['type'], (int) $sourceParentId, (int) $target->id);
        if ($id === null && $parent['required']) {
            $parentLabel = TranslatableFields::of($parent['type'])['label'];
            throw new \RuntimeException("Tạo bản {$target->code} của {$parentLabel} #{$sourceParentId} trước, rồi mới tạo {$label}.");
        }

        return $id;
    }

    /**
     * The target language's structural group matching a source group: the one seeded from it (translation_meta),
     * else the same type and slug (intl languages reuse the English slugs of structural groups).
     */
    public static function structuralGroup(int $sourceGroupId, int $targetLanguageId): ?int
    {
        $seeded = DB::table('translation_meta')->where('entity_type', 'group')->where('source_entity_id', $sourceGroupId)
            ->where('language_id', $targetLanguageId)->value('entity_id');
        if ($seeded) {
            return (int) $seeded;
        }
        $source = DB::table('app_group')->where('id', $sourceGroupId)->first();
        $id = $source ? DB::table('app_group')->where('language_id', $targetLanguageId)->where('type', $source->type)->where('slug', $source->slug)->value('id') : null;

        return $id === null ? null : (int) $id;
    }

    /** Categories are a nested set per language: inserted through the admin service, under the translated parent. */
    private static function createCategory(object $source, AdLanguage $target, array $values): int
    {
        $parentId = null;
        $root = DB::table('app_category')->where('id', $source->parent_id)->first();
        if ($root && $root->slug !== 'root') {
            $parentId = self::parentIn(['type' => 'article_category', 'required' => true], (int) $root->id, $target, 'chuyên mục con');
        }
        // AppCategoryService stamps created_by from the signed-in user (BaseModel): the CLI must name one (--actor).
        if (!\Auth::user()) {
            $actor = (int) ($values['created_by'] ?? 0);
            if (!$actor) {
                throw new \RuntimeException('Tạo chuyên mục cần một người dùng quản trị: chạy lệnh với --actor=<ad_user id> (creating an article category needs --actor).');
            }
            EditorialActor::signIn($actor);
        }
        $id = (int) AppCategoryService::create(['parent_id' => $parentId] + array_intersect_key($values, array_flip(['name', 'slug', 'icon', 'description', 'seo_title', 'seo_description'])), $source->type, (int) $target->id);
        DB::table('app_category')->where('id', $id)->update(['publish_state' => 'unpublished']);

        return $id;
    }

    /** Days and their detail lines (text), and one cruise relation marker per translated cruise. */
    private static function copyItinerary(int $sourceId, int $newId, AdLanguage $target, ?int $actor, $now): void
    {
        $stamp = ['created_at' => $now, 'created_by' => $actor ?? 0, 'updated_at' => $now, 'updated_by' => $actor ?? 0];
        foreach (DB::table('app_itinerary_day')->where('itinerary_id', $sourceId)->orderBy('day')->orderBy('id')->get() as $day) {
            $dayId = (int) DB::table('app_itinerary_day')->insertGetId(['itinerary_id' => $newId, 'day' => $day->day] + $stamp);
            foreach (DB::table('app_itinerary_day_detail')->where('itinerary_day_id', $day->id)->orderBy('ord')->orderBy('id')->get() as $detail) {
                DB::table('app_itinerary_day_detail')->insert([
                    'itinerary_day_id' => $dayId, 'time' => $detail->time, 'title' => $detail->title,
                    'description' => $detail->description, 'ord' => $detail->ord,
                ] + $stamp);
            }
        }
        // Which ship sails it (the URL and the inventory link need it). Departures stay on the price-list rows: the
        // marker date is in the past, so it is never listed or bookable (PaymentPlan refuses past dates).
        $cruises = DB::table('app_cruise_itinerary')->where('itinerary_id', $sourceId)->distinct()->pluck('cruise_id');
        foreach ($cruises as $cruiseId) {
            $translated = self::existingTranslation('cruise', (int) $cruiseId, (int) $target->id);
            if ($translated) {
                DB::table('app_cruise_itinerary')->insert(['cruise_id' => $translated, 'itinerary_id' => $newId, 'start_at' => self::RELATION_MARKER]);
            }
        }
    }

    public const RELATION_MARKER = '1970-01-01';

    /** The unpublished flag per type (PublicationPolicy; services: status 0; amenities: publish_state once migrated). */
    private static function unpublished(string $type): array
    {
        return match ($type) {
            'service' => ['status' => 0],
            'amenity' => \App\Services\Publication\PublicationPolicy::hasColumn('app_amenity') ? ['publish_state' => 'unpublished'] : [],
            default => \App\Services\Publication\PublicationPolicy::unpublishedAttributes($type),
        };
    }
}
