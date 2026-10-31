<?php

namespace App\Services\Content;

use App\Services\Locale\LocaleRegistry;
use App\Services\Publication\PublicationPolicy;
use Illuminate\Support\Facades\DB;
use Modules\BackEnd\Entities\AdLanguage;

/**
 * The N-language translation matrix (phase 1E-b): one row per source entity (default language), one column per
 * other editable language. Three separate dimensions per cell (12 §3): publication (is it live?), workflow (where is
 * it in review?) and freshness (has the source changed since?). en/vi keep their legacy meaning: no workflow, the
 * displayed VI counterpart is what the site shows (explicit link, else the legacy heuristic, marked as such).
 */
final class TranslationMatrix
{
    public const STATES = ['missing', 'draft', 'translation_review', 'seo_review', 'legal_review', 'approved', 'published', 'unpublished'];

    /** @return array{source: AdLanguage, languages: AdLanguage[], rows: array, counts: array} */
    public static function build(string $type, array $filters = []): array
    {
        $meta = TranslatableFields::of($type);
        $source = LocaleRegistry::defaultLanguage();
        $languages = LocaleRegistry::editable()->filter(fn (AdLanguage $l) => (int) $l->id !== (int) $source->id)->values()->all();
        if (!empty($filters['language'])) {
            $languages = array_values(array_filter($languages, fn ($l) => $l->code === $filters['language']));
        }
        $rows = [];
        $counts = [];
        foreach (self::items($type, (int) $source->id) as $item) {
            $cells = [];
            foreach ($languages as $language) {
                $cell = self::cell($type, (int) $item->id, $source, $language);
                $cells[$language->code] = $cell;
                $counts[$language->code][$cell->state] = ($counts[$language->code][$cell->state] ?? 0) + 1;
                if ($cell->outdated) {
                    $counts[$language->code]['outdated'] = ($counts[$language->code]['outdated'] ?? 0) + 1;
                }
            }
            if (!empty($filters['state']) && !collect($cells)->contains(fn ($c) => $c->state === $filters['state'])) {
                continue;
            }
            if (!empty($filters['outdated']) && !collect($cells)->contains(fn ($c) => $c->outdated)) {
                continue;
            }
            $rows[] = (object) ['item' => $item, 'cells' => $cells, 'linked' => $meta['grouped'] && ContentTranslations::isLinked($type, (int) $item->id)];
        }

        return ['source' => $source, 'languages' => $languages, 'rows' => $rows, 'counts' => $counts];
    }

    /** Source rows: id + display name. */
    public static function items(string $type, int $languageId)
    {
        if (TranslatableFields::of($type)['grouped']) {
            return ContentTranslations::items($type, $languageId);
        }
        $meta = TranslatableFields::of($type);
        $name = $meta['text'][0];

        return DB::table($meta['table'])->where('language_id', $languageId)->orderBy('id')
            ->get(['id', "$name as name"])->map(fn ($r) => (object) ['id' => (int) $r->id, 'name' => strip_tags((string) $r->name)]);
    }

    /** One cell: the translation in $language of source row $id, with its three dimensions. */
    public static function cell(string $type, int $id, AdLanguage $source, AdLanguage $language): object
    {
        $meta = TranslatableFields::of($type);
        $heuristic = false;
        if ($meta['grouped'] && LocaleRegistry::isLegacy($language) && LocaleRegistry::isLegacy($source)) {
            $targetId = ContentTranslations::effectiveCounterpartId($type, $id, $source, $language);
            $heuristic = $targetId !== null && !ContentTranslations::isLinked($type, $id);
        } else {
            $targetId = TranslationFactory::existingTranslation($type, $id, (int) $language->id);
        }
        if (!$targetId) {
            return (object) ['id' => null, 'state' => 'missing', 'workflow' => null, 'published' => false, 'outdated' => false, 'heuristic' => false, 'pending' => 0, 'name' => null];
        }
        $row = DB::table($meta['table'])->where('id', $targetId)->first();
        $published = $row ? self::published($type, $row) : false;
        $tm = DB::table('translation_meta')->where('entity_type', $type)->where('entity_id', $targetId)->first();
        $workflow = $tm->workflow ?? null;
        $state = $published ? 'published' : ($workflow ?? 'unpublished');
        $pending = RevisionStore::open($type, (int) $targetId) ? 1 : 0;

        return (object) [
            'id' => $targetId, 'state' => $state, 'workflow' => $workflow, 'published' => $published,
            'outdated' => $tm && Freshness::status($type, $targetId) === 'outdated', 'heuristic' => $heuristic, 'pending' => $pending,
            'name' => $row ? strip_tags((string) ($row->{$meta['text'][0]} ?? '')) : null,
        ];
    }

    /** The row's own publication flag (services: status; amenities: publish_state, always before 2026_11_07_000001). */
    public static function published(string $type, object $row): bool
    {
        return match ($type) {
            'service' => (int) ($row->status ?? 0) === 1,
            'amenity' => !PublicationPolicy::hasColumn('app_amenity') || ($row->publish_state ?? 'published') === 'published',
            default => PublicationPolicy::isPublishedFlag($type, $row),
        };
    }
}
