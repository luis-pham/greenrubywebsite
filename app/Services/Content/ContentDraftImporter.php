<?php

namespace App\Services\Content;

use App\Services\Locale\LocaleRegistry;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Modules\BackEnd\Entities\AdLanguage;
use Modules\BackEnd\Entities\AppCabin;
use Modules\BackEnd\Entities\AppCruise;
use Modules\BackEnd\Entities\AppDestination;
use Modules\BackEnd\Entities\AppExpActivity;
use Modules\BackEnd\Entities\AppFaq;
use Modules\BackEnd\Entities\AppGroup;
use Modules\BackEnd\Entities\AppItinerary;

/**
 * Imports translation drafts of ENTITY content (phase 2C; `php artisan i18n:import-content`) into a new language's
 * translations: cruises, cabins (+ room text), itineraries (+ programme lines: app_itinerary_day_detail, as copied by
 * TranslationFactory::copyItinerary), destinations, experiences, FAQ groups and FAQ items. Draft file (schema 1):
 *
 *   {"schema":1,"language":"ja","items":[{"type":"cabin","en_id":24,"slug":"imperial-suite","fields":{"name":"…"},
 *     "source_hash":{"name":"<sha1 of the English value>"},"children":[{"table":"app_cabin_room","en_id":12,
 *     "fields":{"title":"…"},"source_hash":{"title":"…"}}],"status":"PENDING_REVIEW","risk":[…],"flags":[…]}]}
 *
 * Per item, all or nothing (one transaction): the English row and every drafted English value are re-read and hashed
 * (sha1; a difference is a conflict and nothing of the item is written); the target is the linked translation
 * (content_translations), or with --create-missing a new UNPUBLISHED draft made by TranslationFactory. Only
 * TranslatableFields 'text' columns and child title/description are written, never business, copy, structure,
 * links, publication or workflow. An unpublished draft target is written through its model (observers apply); a
 * published target only gets a pending content_revision (RevisionStore), the live text stays as approved. A target
 * in review, or holding text that is neither the English copy nor the draft (edited by someone), is not touched.
 * The proposed slug is set only on an unpublished row that has none, through SlugService (validated, audited).
 * Statuses: PENDING_REVIEW is imported; COMMERCIAL / LEGAL / NEEDS_CONTEXT statuses only with --include-flagged;
 * BLOCKED (status or risk) never. Every write is audited (editorial_audit_log 'draft_import'). Idempotent.
 */
final class ContentDraftImporter
{
    public const STATUSES = DraftImporter::STATUSES;
    public const AUDIT_ACTION = DraftImporter::AUDIT_ACTION;
    public const IMPORTABLE = 'PENDING_REVIEW';
    public const FLAGGED = ['COMMERCIAL', 'LEGAL', 'NEEDS_CONTEXT'];
    public const BLOCKED = 'BLOCKED';

    public const MODELS = [
        'cruise' => AppCruise::class, 'cabin' => AppCabin::class, 'itinerary' => AppItinerary::class, 'destination' => AppDestination::class,
        'experience' => AppExpActivity::class, 'faq_group' => AppGroup::class, 'faq' => AppFaq::class,
    ];

    /** type => the child table whose text travels with it (TranslatableFields children / ITINERARY_DAY_TEXT). */
    public const CHILDREN = ['cabin' => 'app_cabin_room', 'itinerary' => 'app_itinerary_day_detail'];
    public const CHILD_TEXT = ['title', 'description'];

    private AdLanguage $language;
    private bool $apply;
    private bool $includeFlagged;
    private bool $createMissing;
    private ?int $actor;
    /** Dry run: translations that would be created, "type:sourceId" => true. */
    private array $plannedCreates = [];

    public function __construct(AdLanguage $language, bool $apply, bool $includeFlagged = false, bool $createMissing = false, ?int $actor = null)
    {
        $this->language = $language;
        $this->apply = $apply;
        $this->includeFlagged = $includeFlagged;
        $this->createMissing = $createMissing;
        $this->actor = $actor;
    }

    /** @return array<int, array> one result per item */
    public function run(array $items): array
    {
        $results = [];
        foreach ($items as $item) {
            $id = ($item['type'] ?? '?') . ':' . ($item['en_id'] ?? '?');
            try {
                $result = $this->one((array) $item);
            } catch (EditorialRefusal $e) {
                $result = $this->result('needs_review', 'refused by the editorial guard: ' . $e->getMessage());
            } catch (\Throwable $e) {
                // The item's transaction is rolled back; the other items go on.
                $result = $this->result('needs_review', 'error, nothing written: ' . mb_substr($e->getMessage(), 0, 300));
            }
            $results[] = ['id' => $id, 'type' => $item['type'] ?? null, 'en_id' => $item['en_id'] ?? null, 'draft_status' => $item['status'] ?? null] + $result;
        }

        return $results;
    }

    private function one(array $item): array
    {
        $type = (string) ($item['type'] ?? '');
        if (!isset(self::MODELS[$type])) {
            return $this->result('needs_review', "type '{$type}' is not imported (" . implode(', ', array_keys(self::MODELS)) . ')');
        }
        if ($gate = $this->statusGate($item)) {
            return $gate;
        }
        $meta = TranslatableFields::of($type);
        $enId = (int) ($item['en_id'] ?? 0);
        $source = DB::table($meta['table'])->where('id', $enId)->first();
        if (!$source) {
            return $this->result('conflict', "English {$type} #{$enId} no longer exists");
        }
        if ((int) $source->language_id !== (int) LocaleRegistry::defaultLanguage()->id) {
            return $this->result('needs_review', "{$type} #{$enId} is not a row of the default (source) language");
        }

        // Fields: only translatable text; anything else is reported and ignored (never written).
        $fields = [];
        $ignored = [];
        foreach ((array) ($item['fields'] ?? []) as $column => $value) {
            if (!in_array($column, $meta['text'], true)) {
                $ignored[] = $column;
                continue;
            }
            $fields[$column] = self::text($value);
        }
        $notes = $ignored === [] ? [] : ['ignored (not translatable text): ' . implode(', ', $ignored)];
        $hashes = (array) ($item['source_hash'] ?? []);

        // Source check: every drafted English value must hash as when it was translated.
        $conflicts = [];
        foreach ($fields as $column => $value) {
            if (!$this->sameSource($source->{$column} ?? null, $hashes[$column] ?? null)) {
                $conflicts[] = $column;
            }
        }
        $children = [];
        foreach ((array) ($item['children'] ?? []) as $i => $child) {
            $parsed = $this->child($type, $enId, (array) $child);
            if (isset($parsed['status'])) {
                return $parsed + ['notes' => $notes];
            }
            foreach ($parsed['conflicts'] as $column) {
                $conflicts[] = "{$parsed['table']}#{$parsed['en_id']}.{$column}";
            }
            $children[] = $parsed;
        }
        if ($conflicts !== []) {
            return $this->result('conflict', 'English source changed since the draft (sha1 differs): re-translate ' . implode(', ', $conflicts)) + ['notes' => $notes];
        }
        if ($fields === [] && $children === [] && empty($item['slug'])) {
            return $this->result('needs_review', 'nothing to import (no translatable field)') + ['notes' => $notes];
        }

        // Target: the linked translation, or a new unpublished draft (--create-missing).
        $targetId = TranslationFactory::existingTranslation($type, $enId, (int) $this->language->id);
        $create = false;
        if (!$targetId) {
            $key = "{$type}:{$enId}";
            if (!$this->createMissing) {
                return $this->result('missing_target', "no {$this->language->code} translation of {$type} #{$enId}: create it in the translation matrix (or pass --create-missing)") + ['notes' => $notes];
            }
            if (!$this->apply && isset($this->plannedCreates[$key])) {
                return $this->result('skipped', 'duplicate item: already planned above') + ['notes' => $notes];
            }
            if ($refusal = $this->factoryRefusal($type, $enId)) {
                return $this->result('missing_target', "cannot create the {$this->language->code} {$type} of #{$enId}: {$refusal}") + ['notes' => $notes];
            }
            $create = true;
        }

        if (!$this->apply) {
            return $create ? $this->plan($type, $enId, $fields, $children, $item, $notes) : $this->write($type, (int) $targetId, $source, $fields, $children, $item, $notes, false);
        }

        try {
            return DB::transaction(function () use ($create, $type, $enId, $targetId, $source, $fields, $children, $item, $notes) {
                if ($create) {
                    $created = TranslationFactory::create($type, $enId, $this->language, $this->actor, 'i18n:import-content');
                    $targetId = (int) $created['id'];
                    EditorialAudit::record($type, $targetId, 'translation_created', null, 'draft', $this->actor, null, "i18n:import-content from #{$enId}");
                }
                $out = $this->write($type, (int) $targetId, $source, $fields, $children, $item, $notes, $create);
                if ($create && $out['status'] !== 'created') {
                    throw new ImportRollback($out); // all or nothing: no half-imported new draft is left behind
                }

                return $out;
            });
        } catch (ImportRollback $e) {
            unset($e->result['target']);

            return $e->result;
        }
    }

    /**
     * null when the item may be imported; else the needs_review result. The item's status decides (flags and risk
     * are per-field review questions, reported, not gates); BLOCKED in the status, the risk list or a flag's risk is
     * never imported.
     */
    private function statusGate(array $item): ?array
    {
        $status = strtoupper(trim((string) ($item['status'] ?? '')));
        $risks = array_merge((array) ($item['risk'] ?? []), array_map(fn ($f) => is_array($f) ? ($f['risk'] ?? '') : $f, (array) ($item['flags'] ?? [])));
        $risks = array_map(fn ($r) => strtoupper(is_scalar($r) ? (string) $r : ''), $risks);
        if (str_contains($status, self::BLOCKED) || in_array(self::BLOCKED, $risks, true)) {
            return $this->result('needs_review', 'BLOCKED: never imported');
        }
        $flagged = array_values(array_filter(self::FLAGGED, fn ($word) => str_contains($status, $word)));
        if ($status !== self::IMPORTABLE && $flagged === []) {
            return $this->result('needs_review', "unknown draft status '{$status}': not imported");
        }
        if ($flagged !== [] && !$this->includeFlagged) {
            return $this->result('needs_review', "draft status {$status}: review it, or pass --include-flagged");
        }

        return null;
    }

    /** One drafted child row, checked against its English row: table, en_id, fields, conflicts; or a final result. */
    private function child(string $type, int $enId, array $child): array
    {
        $table = (string) ($child['table'] ?? '');
        if ((self::CHILDREN[$type] ?? null) !== $table) {
            return $this->result('needs_review', "child table '{$table}' is not imported for {$type}" . (isset(self::CHILDREN[$type]) ? ' (only ' . self::CHILDREN[$type] . ')' : ''));
        }
        $childId = (int) ($child['en_id'] ?? 0);
        $row = DB::table($table)->where('id', $childId)->first();
        $belongs = $row && ($type === 'cabin'
            ? (int) $row->cabin_id === $enId
            : DB::table('app_itinerary_day')->where('id', $row->itinerary_day_id)->where('itinerary_id', $enId)->exists());
        if (!$belongs) {
            return $this->result('conflict', "English {$table} #{$childId} of {$type} #{$enId} no longer exists");
        }
        $fields = [];
        $conflicts = [];
        foreach ((array) ($child['fields'] ?? []) as $column => $value) {
            if (!in_array($column, self::CHILD_TEXT, true)) {
                continue; // times, order and structure belong to the price-list row
            }
            $fields[$column] = self::text($value);
            if (!$this->sameSource($row->{$column} ?? null, ((array) ($child['source_hash'] ?? []))[$column] ?? null)) {
                $conflicts[] = $column;
            }
        }

        return ['table' => $table, 'en_id' => $childId, 'fields' => $fields, 'conflicts' => $conflicts, 'english' => $row];
    }

    /** Dry run of a creation: TranslationFactory copies the English text, so every drafted value differs. */
    private function plan(string $type, int $enId, array $fields, array $children, array $item, array $notes): array
    {
        $this->plannedCreates["{$type}:{$enId}"] = true;
        $changed = array_keys($fields);
        $slug = trim((string) ($item['slug'] ?? ''));
        if ($slug !== '' && !preg_match(SlugValidator::PATTERN, $slug)) {
            $notes[] = "proposed slug '{$slug}' is not a valid ASCII slug: not set";
        }

        return $this->result('created', "would create the {$this->language->code} {$type} draft of #{$enId} (TranslationFactory, unpublished) and write "
            . count($changed) . ' field(s) and ' . count($children) . ' child row(s)' . ($slug !== '' ? ", slug '{$slug}'" : ''))
            + ['fields' => $changed, 'children' => count($children), 'notes' => $notes];
    }

    private function write(string $type, int $targetId, object $source, array $fields, array $children, array $item, array $notes, bool $created): array
    {
        $class = self::MODELS[$type];
        /** @var Model $model */
        $model = $class::withoutGlobalScopes()->find($targetId);
        if (!$model) {
            return $this->result('missing_target', "{$type} #{$targetId} is linked but missing") + ['notes' => $notes];
        }
        $base = ['target' => ['type' => $type, 'id' => $targetId]];
        $row = (object) $model->getAttributes();
        $published = TranslationMatrix::published($type, $row);
        $meta = DB::table('translation_meta')->where('entity_type', $type)->where('entity_id', $targetId)->first();
        $revision = $published ? RevisionStore::open($type, $targetId) : null;
        if ($published) {
            if ($revision && $revision->workflow !== 'draft') {
                return $base + $this->result('needs_review', "open revision #{$revision->id} is in step {$revision->workflow}: writing would send it back to draft") + ['notes' => $notes];
            }
            $owner = $revision ? RevisionStore::owner($revision) : null;
            if ($owner !== null && $owner !== $this->actor) {
                return $base + $this->result('needs_review', "open revision #{$revision->id} is held by user #{$owner}") + ['notes' => $notes];
            }
        } elseif ($meta && $meta->workflow !== 'draft') {
            return $base + $this->result('needs_review', "target is in workflow step {$meta->workflow}: only draft targets are imported") + ['notes' => $notes];
        }
        $pending = $revision ? (array) json_decode((string) $revision->fields, true) : [];

        // Field changes: the effective value (pending revision, else live) is the English copy → write the draft.
        $changes = [];
        $edited = [];
        foreach ($fields as $column => $draft) {
            // Raw column text (JSON columns are cast to arrays/objects by the models; drafts are JSON text).
            $effective = array_key_exists($column, $pending) ? $pending[$column] : ($row->{$column} ?? null);
            if (self::same($effective, $draft)) {
                continue;
            }
            if (trim((string) $effective) !== '' && !self::same($effective, $source->{$column} ?? null)) {
                $edited[] = $column;
                continue;
            }
            $changes[$column] = $draft;
        }

        // Child changes, mapped by position (TranslationFactory copies children in the English order).
        $childChanges = [];
        if ($children !== []) {
            $map = $this->childMap($type, (int) $source->id, $targetId);
            if ($map === null) {
                return $base + $this->result('needs_review', "the {$this->language->code} " . self::CHILDREN[$type] . ' rows do not match the English structure (count or times differ): fix them in the admin') + ['notes' => $notes];
            }
            $pendingChildren = (array) ($pending['_children'][self::CHILDREN[$type]] ?? []);
            foreach ($children as $child) {
                $jaId = $map[$child['en_id']] ?? null;
                $jaRow = $jaId ? DB::table($child['table'])->where('id', $jaId)->first() : null;
                if (!$jaRow) {
                    return $base + $this->result('needs_review', "no {$this->language->code} row for {$child['table']} #{$child['en_id']}") + ['notes' => $notes];
                }
                foreach ($child['fields'] as $column => $draft) {
                    $effective = $pendingChildren[$jaId][$column] ?? $jaRow->{$column};
                    if (self::same($effective, $draft)) {
                        continue;
                    }
                    if (trim((string) $effective) !== '' && !self::same($effective, $child['english']->{$column} ?? null)) {
                        $edited[] = "{$child['table']}#{$jaId}.{$column}";
                        continue;
                    }
                    $childChanges[$jaId][$column] = $draft;
                }
            }
        }
        if ($edited !== []) {
            return $base + $this->result('conflict', "the {$this->language->code} target already has other text (edited, not the English copy): not overwritten: " . implode(', ', $edited)) + ['notes' => $notes];
        }

        // Slug: only on an unpublished row without one (an editor's choice is never replaced).
        $slug = trim((string) ($item['slug'] ?? ''));
        $setSlug = null;
        if ($slug !== '') {
            $current = SlugService::current($type, $row);
            if ($published) {
                $notes[] = $current === $slug ? 'slug unchanged' : "row is published: slug '{$current}' kept (change it with content:slug)";
            } elseif ($current === $slug) {
                // already set
            } elseif ($current !== '') {
                $notes[] = "row already has slug '{$current}': proposed '{$slug}' not set";
            } elseif ($error = SlugService::check($type, $row, $slug)) {
                $notes[] = "proposed slug '{$slug}' refused: {$error}";
            } else {
                $setSlug = $slug;
            }
        }

        if ($changes === [] && $childChanges === [] && $setSlug === null) {
            return $base + ($created ? $this->result('created', 'created (nothing differed from the draft)') : $this->result('skipped', 'unchanged (target already holds the draft)')) + ['notes' => $notes];
        }
        $summary = count($changes) . ' field(s), ' . count($childChanges) . ' child row(s)' . ($setSlug !== null ? ", slug '{$setSlug}'" : '');
        $status = $created ? 'created' : 'updated';
        if (!$this->apply) {
            return $base + $this->result($status, ($published ? 'would open/extend a pending revision (row is published): ' : 'would update the unpublished draft: ') . $summary)
                + ['fields' => array_keys($changes), 'children' => count($childChanges), 'notes' => $notes];
        }

        $revisionId = null;
        if ($published) {
            // The live text stays as approved: fields and child text go into one pending revision.
            $revisionId = RevisionStore::merge($type, $targetId, $changes, $childChanges ? [self::CHILDREN[$type] => $childChanges] : [], $this->actor);
        } else {
            if ($changes !== []) {
                // Through the model: the observers (business-field guard, translator stamp) apply. Raw values, so a
                // JSON column keeps the draft's exact text (a cast would re-encode it).
                $model->setRawAttributes(array_merge($model->getAttributes(), $changes));
                $model->save();
            }
            foreach ($childChanges as $jaId => $values) {
                DB::table(self::CHILDREN[$type])->where('id', $jaId)->update($values + ['updated_at' => now(), 'updated_by' => $this->actor ?? 0]);
            }
            DB::table('translation_meta')->where('entity_type', $type)->where('entity_id', $targetId)
                ->update(['translated_by' => $this->actor, 'updated_at' => now()]);
            if ($setSlug !== null) {
                SlugService::change($type, $targetId, $setSlug, 'i18n:import-content: proposed ASCII slug of the draft', $this->actor);
            }
        }
        EditorialAudit::record($type, $targetId, self::AUDIT_ACTION, $published ? 'published' : 'draft', $published ? 'revision' : $status, $this->actor,
            $revisionId, "content:{$type}:{$source->id} " . $summary);

        return $base + $this->result($status, ($published ? "pending revision #{$revisionId} (row is published; live text unchanged): " : ($created ? 'created the unpublished draft and wrote ' : 'updated the unpublished draft: ')) . $summary)
            + ['fields' => array_keys($changes), 'children' => count($childChanges), 'notes' => $notes] + ($revisionId ? ['revision_id' => $revisionId] : []);
    }

    /** English child id => target child id, by position; null when the structures differ. */
    private function childMap(string $type, int $enId, int $targetId): ?array
    {
        $en = $this->childRows($type, $enId);
        $ja = $this->childRows($type, $targetId);
        if (count($en) !== count($ja)) {
            return null;
        }
        $map = [];
        foreach ($en as $i => $row) {
            if ($type === 'itinerary' && substr((string) $row->time, 0, 5) !== substr((string) $ja[$i]->time, 0, 5)) {
                return null;
            }
            $map[(int) $row->id] = (int) $ja[$i]->id;
        }

        return $map;
    }

    private function childRows(string $type, int $parentId): array
    {
        if ($type === 'cabin') {
            return DB::table('app_cabin_room')->where('cabin_id', $parentId)->orderBy('ord')->orderBy('id')->get()->all();
        }

        return DB::table('app_itinerary_day_detail')->join('app_itinerary_day', 'app_itinerary_day.id', '=', 'app_itinerary_day_detail.itinerary_day_id')
            ->where('app_itinerary_day.itinerary_id', $parentId)
            ->orderBy('app_itinerary_day.day')->orderBy('app_itinerary_day.id')->orderBy('app_itinerary_day_detail.ord')->orderBy('app_itinerary_day_detail.id')
            ->get(['app_itinerary_day_detail.id', 'app_itinerary_day_detail.time'])->all();
    }

    /** Why TranslationFactory would refuse to create the draft now (checked without writing), or null. */
    private function factoryRefusal(string $type, int $sourceId): ?string
    {
        if ($refusal = EditorialActor::refusal($type, $this->actor)) {
            return $refusal;
        }
        $meta = TranslatableFields::of($type);
        if ($meta['grouped'] && !ContentTranslations::isLinked($type, $sourceId)) {
            return "#{$sourceId} has no translation group: link EN/VI first";
        }
        foreach ($meta['parents'] as $column => $parent) {
            $parentId = DB::table($meta['table'])->where('id', $sourceId)->value($column);
            if (!$parentId || !$parent['required']) {
                continue;
            }
            $found = $parent['type'] === 'group' ? TranslationFactory::structuralGroup((int) $parentId, (int) $this->language->id)
                : (TranslationFactory::existingTranslation($parent['type'], (int) $parentId, (int) $this->language->id)
                    ?? (isset($this->plannedCreates["{$parent['type']}:{$parentId}"]) ? -1 : null));
            if (!$found) {
                return $parent['type'] === 'group'
                    ? "its group #{$parentId} has no {$this->language->code} row: run php artisan i18n:seed-locale {$this->language->code}"
                    : "its parent {$parent['type']} #{$parentId} has no {$this->language->code} row yet (import or create it first)";
            }
        }

        return null;
    }

    /** sha1 of the current English value equals the draft's source hash (raw or trimmed; null = ''). */
    private function sameSource($english, $hash): bool
    {
        if (!is_string($hash) || $hash === '') {
            return false;
        }
        $value = (string) ($english ?? '');

        return hash_equals(strtolower($hash), sha1($value)) || hash_equals(strtolower($hash), sha1(trim($value)));
    }

    private static function same($a, $b): bool
    {
        return trim((string) ($a ?? '')) === trim((string) ($b ?? ''));
    }

    private static function text($value): string
    {
        return is_string($value) ? $value : (string) json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    private function result(string $status, string $reason): array
    {
        return ['status' => $status, 'reason' => $reason];
    }
}

/** Rolls back an item whose new draft could not take the draft text (ContentDraftImporter, internal). */
final class ImportRollback extends \RuntimeException
{
    public function __construct(public array $result)
    {
        parent::__construct($result['reason'] ?? 'rolled back');
    }
}
