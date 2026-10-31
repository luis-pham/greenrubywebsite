<?php

namespace App\Services\Content;

use App\Services\Locale\LocaleRegistry;
use App\Services\Publication\PublicationPolicy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Modules\BackEnd\Entities\AdConfig;
use Modules\BackEnd\Entities\AdLanguage;
use Modules\BackEnd\Entities\AppAmenity;
use Modules\BackEnd\Entities\AppCategory;
use Modules\BackEnd\Entities\AppGroup;
use Modules\BackEnd\Entities\AppMenuFrontEnd;
use Modules\BackEnd\Entities\AppPage;
use Modules\BackEnd\Entities\AppPageConfig;
use Modules\BackEnd\Entities\AppService;

/**
 * Imports reviewed-elsewhere translation drafts of CMS configuration text (phase 2B; i18n:import-drafts) into the
 * HIDDEN rows a new language already has (i18n:seed-locale, the translation matrix). Each draft names an English
 * source cell (cms-extract.py: table, row id, field, JSON path) and the English text it was translated from.
 *
 * Per draft: the English cell is read in the current database (changed or gone → conflict); the target cell of the
 * language is found through the existing links only (translation_meta, the "-{code}" config key, the page with the
 * same code, content_translations); no row is ever created here except, on request, a TranslatableFields draft
 * through TranslationFactory (never published). Writes go through the Eloquent models, so observers apply:
 * a published faq_group/article_category row gets a pending content_revision (ContentRevisionObserver), never a live
 * edit; a public target without revision support is refused. Publication flags, slugs, business columns,
 * translation links and workflow steps are never written. Every write is audited (editorial_audit_log
 * 'draft_import'). Idempotent: a target already holding the draft is skipped.
 */
final class DraftImporter
{
    public const STATUSES = ['created', 'updated', 'skipped', 'conflict', 'needs_review', 'missing_target'];
    public const IMPORTABLE = 'PENDING_REVIEW';
    public const NEVER = 'NEEDS_CONTEXT';
    public const AUDIT_ACTION = 'draft_import';

    /** Types whose published rows take a pending revision (ContentRevisionObserver through these models). */
    private const REVISION_TYPES = ['faq_group', 'article_category'];

    private const MODELS = [
        'faq_group' => AppGroup::class, 'group' => AppGroup::class, 'article_category' => AppCategory::class,
        'amenity' => AppAmenity::class, 'service' => AppService::class,
    ];

    private AdLanguage $language;
    private bool $apply;
    private bool $includeFlagged;
    private bool $createMissing;
    private ?int $actor;
    /** Dry run: TranslatableFields rows that would be created, "type:sourceId" => true. */
    private array $plannedCreates = [];

    public function __construct(AdLanguage $language, bool $apply, bool $includeFlagged = false, bool $createMissing = false, ?int $actor = null)
    {
        $this->language = $language;
        $this->apply = $apply;
        $this->includeFlagged = $includeFlagged;
        $this->createMissing = $createMissing;
        $this->actor = $actor;
    }

    /**
     * @param array $sources cms-config-source.json rows keyed by id
     * @param array $drafts  cms-config-ja-draft.json rows
     * @return array<int, array> one result per draft
     */
    public function run(array $sources, array $drafts): array
    {
        $results = [];
        foreach ($drafts as $draft) {
            $source = $sources[$draft['id'] ?? ''] ?? null;
            try {
                $result = $this->one($draft, $source);
            } catch (EditorialRefusal $e) {
                $result = ['status' => 'needs_review', 'reason' => 'refused by the editorial guard: ' . $e->getMessage()];
            } catch (\Throwable $e) {
                // The row's transaction is rolled back; the other rows go on.
                $result = ['status' => 'needs_review', 'reason' => 'error, nothing written: ' . mb_substr($e->getMessage(), 0, 300)];
            }
            $results[] = ['id' => (string) ($draft['id'] ?? ''), 'table' => $source['table'] ?? null, 'draft_status' => $draft['status'] ?? null] + $result;
        }

        return $results;
    }

    private function one(array $draft, ?array $source): array
    {
        if (!$source) {
            return $this->result('conflict', 'draft id not found in the source file');
        }
        if (($source['scope'] ?? 'in') !== 'in') {
            return $this->result('needs_review', 'source row is out of scope: ' . ($source['reason'] ?? ''));
        }
        $status = (string) ($draft['status'] ?? '');
        if ($status === self::NEVER) {
            return $this->result('needs_review', 'draft status NEEDS_CONTEXT: never imported (answer the context question first)');
        }
        if ($status !== self::IMPORTABLE && !$this->includeFlagged) {
            return $this->result('needs_review', "draft status {$status}: skipped (review it, or pass --include-flagged)");
        }
        $ja = (string) ($draft['ja'] ?? '');
        if (trim($ja) === '') {
            return $this->result('needs_review', 'empty draft');
        }

        $cell = $this->locate($source);
        if (isset($cell['status'])) {
            return $cell; // conflict or missing_target found while locating
        }
        return $this->write($cell, $source, $draft, $ja);
    }

    // ---------------------------------------------------------------------------------------------------------
    // Locating source and target cells

    /**
     * @return array either a final result (status, reason) or the cell:
     *   type, entity_id, model (target Model or null when it is still to be created), column, path, meta_type,
     *   meta_id, public (string reason or null), revision (bool), create (array|null)
     */
    private function locate(array $source): array
    {
        $table = (string) $source['table'];
        $rowId = (int) $source['row_id'];
        $field = (string) $source['field'];
        $path = (string) ($source['json_path'] ?? '');
        $en = (string) $source['en'];

        switch ($table) {
            case 'ad_config':
                $src = AdConfig::query()->find($rowId);
                if (!$src || (string) $src->key !== $field) {
                    return $this->result('conflict', "English config #{$rowId} ({$field}) no longer exists");
                }
                if ($c = $this->compareSource((string) $src->value, '', $en)) {
                    return $c;
                }
                $sourceCode = (string) (LocaleRegistry::byId((int) $src->language_id)->code ?? 'en');
                $suffix = '-' . strtolower($sourceCode);
                if (!str_ends_with(strtolower($field), $suffix)) {
                    return $this->result('missing_target', "{$field} is not a per-language key (no '{$suffix}' suffix): it is shared, nothing to translate per language");
                }
                $key = substr($field, 0, -strlen($suffix)) . '-' . strtolower((string) $this->language->code);
                $target = AdConfig::query()->where('key', $key)->first();
                if (!$target) {
                    return $this->result('missing_target', "no config key '{$key}': run php artisan i18n:seed-locale {$this->language->code}");
                }

                return $this->cell('ad_config', $target, 'value', '', null, null, $this->languagePublic());

            case 'app_menu_front_end':
                $code = str_starts_with($field, 'menu:') ? substr($field, 5) : $field;
                $src = AppMenuFrontEnd::query()->find($rowId);
                if (!$src || (string) $src->code !== $code) {
                    return $this->result('conflict', "English menu #{$rowId} ({$code}) no longer exists");
                }
                if ($c = $this->compareSource((string) $src->menu, $path, $en)) {
                    return $c;
                }
                $id = DB::table('translation_meta')->where('entity_type', 'menu')->where('source_entity_id', $rowId)
                    ->where('language_id', $this->language->id)->value('entity_id');
                $target = $id ? AppMenuFrontEnd::query()->find($id) : null;
                $target ??= AppMenuFrontEnd::query()->where('language_id', $this->language->id)->where('code', $code)->first();
                if (!$target) {
                    return $this->result('missing_target', "no {$this->language->code} menu '{$code}': run php artisan i18n:seed-locale {$this->language->code}");
                }
                if (!JsonPath::exists((string) $target->menu, $path)) {
                    return $this->result('missing_target', "menu item {$path} is not in the {$this->language->code} menu '{$code}' (menu edited since seeding): add it in the menu editor");
                }

                return $this->cell('menu', $target, 'menu', $path, 'menu', (int) $target->id, $this->languagePublic());

            case 'app_page':
                [$column, $code] = array_pad(explode(':', $field, 2), 2, '');
                $src = AppPage::query()->find($rowId);
                if (!$src || (string) $src->code !== $code || !in_array($column, ['title', 'seo_title', 'seo_description', 'description'], true)) {
                    return $this->result('conflict', "English page #{$rowId} ({$code}) no longer exists");
                }
                if ($c = $this->compareSource((string) $src->{$column}, '', $en)) {
                    return $c;
                }
                $target = AppPage::query()->where('language_id', $this->language->id)->where('code', $code)->first();
                if (!$target) {
                    return $this->result('missing_target', "no {$this->language->code} page '{$code}': run php artisan i18n:seed-locale {$this->language->code}");
                }

                return $this->cell('page', $target, $column, '', 'page', (int) $target->id, $this->pagePublic($target));

            case 'app_page_config':
                $src = AppPageConfig::query()->find($rowId);
                $page = $src ? AppPage::query()->find($src->page_id) : null;
                if (!$src || !$page || (string) $src->key !== $field) {
                    return $this->result('conflict', "English page config #{$rowId} ({$field}) no longer exists");
                }
                if ($c = $this->compareSource((string) $src->value, $path, $en)) {
                    return $c;
                }
                $targetPage = AppPage::query()->where('language_id', $this->language->id)->where('code', $page->code)->first();
                if (!$targetPage) {
                    return $this->result('missing_target', "no {$this->language->code} page '{$page->code}': run php artisan i18n:seed-locale {$this->language->code}");
                }
                $candidates = AppPageConfig::query()->where('page_id', $targetPage->id)->where('key', $field)->orderBy('id')->get();
                if ($candidates->count() > 1) {
                    $candidates = $candidates->filter(fn ($c) => (string) $c->ord === (string) $src->ord)->values();
                }
                if ($candidates->count() !== 1) {
                    return $candidates->isEmpty()
                        ? $this->result('missing_target', "no config '{$field}' on the {$this->language->code} page '{$page->code}' (added after seeding): add it in the page configuration")
                        : $this->result('needs_review', "several configs '{$field}' on the {$this->language->code} page '{$page->code}': ambiguous target");
                }
                $target = $candidates->first();
                if ($path !== '' && !JsonPath::exists((string) $target->value, $path)) {
                    return $this->result('missing_target', "list item {$path} is not in the {$this->language->code} config '{$field}' (list edited since seeding)");
                }

                return $this->cell('page_config', $target, 'value', $path, 'page', (int) $targetPage->id, $this->pagePublic($targetPage));

            case 'app_group':
                [$column, $kind] = array_pad(explode(':', $field, 2), 2, '');
                $src = AppGroup::withoutGlobalScopes()->find($rowId);
                $faq = (int) config('backend.groupType.faq', 1);
                if (!$src || ($kind === 'faq_group') !== ((int) $src->type === $faq)) {
                    return $this->result('conflict', "English group #{$rowId} ({$kind}) no longer exists");
                }
                if ($c = $this->compareSource((string) $src->{$column}, '', $en)) {
                    return $c;
                }
                if ($kind === 'faq_group') {
                    return $this->translatable('faq_group', $rowId, $column);
                }
                $id = TranslationFactory::structuralGroup($rowId, (int) $this->language->id);
                if (!$id) {
                    return $this->result('missing_target', "no {$this->language->code} structural group for #{$rowId}: run php artisan i18n:seed-locale {$this->language->code}");
                }
                $target = AppGroup::withoutGlobalScopes()->find($id);
                // Seeded unpublished since phase 2C (Workflow type 'group'). A published one (reviewed, or seeded before
                // 2C) is refused here: its text changes only through a reviewed revision (edit it in the admin).
                $public = $this->languagePublic() ?? (PublicationPolicy::isPublishedFlag('faq_group', (object) $target->getAttributes())
                    ? 'target is public (publish_state=published): a published structural group is edited in the admin (pending revision)' : null);

                return $this->cell('group', $target, $column, '', 'group', (int) $id, $public);

            case 'app_category':
                $src = AppCategory::withoutGlobalScopes()->find($rowId);
                if (!$src) {
                    return $this->result('conflict', "English category #{$rowId} no longer exists");
                }
                if ($c = $this->compareSource((string) $src->{$field}, '', $en)) {
                    return $c;
                }

                return $this->translatable('article_category', $rowId, $field);

            case 'app_amenity':
            case 'app_service':
                $type = $table === 'app_amenity' ? 'amenity' : 'service';
                $class = self::MODELS[$type];
                $src = $class::query()->find($rowId);
                if (!$src) {
                    return $this->result('conflict', "English {$type} #{$rowId} no longer exists");
                }
                if ($c = $this->compareSource((string) $src->{$field}, '', $en)) {
                    return $c;
                }

                return $this->translatable($type, $rowId, $field);
        }

        return $this->result('missing_target', "table {$table} is not supported by the importer");
    }

    /** A TranslatableFields type: target through TranslationFactory::existingTranslation (content_translations / translation_meta). */
    private function translatable(string $type, int $sourceId, string $column): array
    {
        if (!in_array($column, TranslatableFields::text($type), true)) {
            return $this->result('needs_review', "{$column} is not a translatable text column of {$type}");
        }
        $id = TranslationFactory::existingTranslation($type, $sourceId, (int) $this->language->id);
        if (!$id) {
            $fix = "create the {$this->language->code} {$type} of #{$sourceId} in the translation matrix (or pass --create-missing)";
            if (!$this->createMissing) {
                return $this->result('missing_target', "no {$this->language->code} translation of {$type} #{$sourceId}: {$fix}");
            }
            $refusal = $this->factoryRefusal($type, $sourceId);
            if ($refusal !== null) {
                return $this->result('missing_target', "cannot create the {$this->language->code} {$type} of #{$sourceId}: {$refusal}");
            }

            return ['type' => $type, 'entity_id' => null, 'model' => null, 'column' => $column, 'path' => '', 'meta_type' => $type,
                'meta_id' => null, 'public' => null, 'revision' => in_array($type, self::REVISION_TYPES, true), 'create' => [$type, $sourceId]];
        }
        $class = self::MODELS[$type];
        $target = $class::withoutGlobalScopes()->find($id);
        if (!$target) {
            return $this->result('missing_target', "{$type} #{$id} is linked but missing");
        }
        $revision = in_array($type, self::REVISION_TYPES, true);
        $public = $this->languagePublic();
        // Amenities without the 2026_11_07 flag are hidden while the language is not public (no flag to check).
        $flagged = $type !== 'amenity' || PublicationPolicy::hasColumn('app_amenity');
        if ($public === null && !$revision && $flagged && TranslationMatrix::published($type, (object) $target->getAttributes())) {
            $public = 'target is public (published) and ' . $type . ' has no revision workflow';
        }

        return $this->cell($type, $target, $column, '', $type, (int) $id, $public, $revision);
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
                : TranslationFactory::existingTranslation($parent['type'], (int) $parentId, (int) $this->language->id);
            if (!$found) {
                return "its parent {$parent['type']} #{$parentId} has no {$this->language->code} row yet";
            }
        }

        return null;
    }

    private function cell(string $type, Model $target, string $column, string $path, ?string $metaType, ?int $metaId, ?string $public, bool $revision = false): array
    {
        return ['type' => $type, 'entity_id' => (int) $target->getKey(), 'model' => $target, 'column' => $column, 'path' => $path,
            'meta_type' => $metaType, 'meta_id' => $metaId, 'public' => $public, 'revision' => $revision, 'create' => null];
    }

    /** Conflict when the current English text differs from the text the draft was translated from. */
    private function compareSource(?string $value, string $path, string $en): ?array
    {
        if ($path !== '') {
            if (!JsonPath::exists((string) $value, $path)) {
                return $this->result('conflict', "English item {$path} no longer exists");
            }
            $value = JsonPath::get((string) $value, $path);
        }
        if (trim((string) $value) !== trim($en)) {
            return $this->result('conflict', 'English source changed since the 2026-10-29 export: re-translate (now: "' . mb_substr(trim((string) $value), 0, 80) . '")');
        }

        return null;
    }

    private function languagePublic(): ?string
    {
        return LocaleRegistry::status($this->language) === 'public' ? "target is public (language {$this->language->code} is public)" : null;
    }

    private function pagePublic(AppPage $page): ?string
    {
        return $this->languagePublic() ?? ((int) $page->is_visible === 1 ? 'target is public (page is visible) and pages have no revision workflow' : null);
    }

    // ---------------------------------------------------------------------------------------------------------
    // Writing

    private function write(array $cell, array $source, array $draft, string $ja): array
    {
        $base = ['target' => ['type' => $cell['type'], 'id' => $cell['entity_id'], 'column' => $cell['column'], 'path' => $cell['path'] ?: null]];
        $en = (string) $source['en'];

        if ($cell['create']) {
            [$type, $sourceId] = $cell['create'];
            $key = "{$type}:{$sourceId}";
            if (!$this->apply) {
                $first = !isset($this->plannedCreates[$key]);
                $this->plannedCreates[$key] = true;

                return $base + $this->result($first ? 'created' : 'updated', $first
                    ? "would create the {$this->language->code} {$type} draft of #{$sourceId} (TranslationFactory, unpublished) and write the draft"
                    : "would write into the {$this->language->code} {$type} draft created above");
            }

            return DB::transaction(function () use ($cell, $type, $sourceId, $draft, $ja, $base) {
                try {
                    $created = TranslationFactory::create($type, $sourceId, $this->language, $this->actor, 'i18n:import-drafts');
                } catch (\Throwable $e) {
                    return $base + $this->result('missing_target', "TranslationFactory could not create the {$this->language->code} {$type} of #{$sourceId} (nothing written): " . mb_substr($e->getMessage(), 0, 200));
                }
                EditorialAudit::record($type, (int) $created['id'], 'translation_created', null, 'draft', $this->actor, null, "i18n:import-drafts from #{$sourceId}");
                $model = self::MODELS[$type]::withoutGlobalScopes()->findOrFail($created['id']);
                $cell = array_merge($cell, ['entity_id' => (int) $created['id'], 'model' => $model, 'meta_id' => (int) $created['id']]);
                $base['target']['id'] = (int) $created['id'];
                $out = $this->save($cell, $draft, $ja, 'created');

                return $base + $out;
            });
        }

        // Workflow: only a draft is written. Text under review or approved would change behind its reviewers.
        $meta = $cell['meta_type'] && $cell['meta_id'] ? DB::table('translation_meta')->where('entity_type', $cell['meta_type'])->where('entity_id', $cell['meta_id'])->first() : null;
        $published = $cell['revision'] && PublicationPolicy::isPublishedFlag($cell['type'], (object) $cell['model']->getAttributes());
        $openRevision = $published ? RevisionStore::open($cell['type'], (int) $cell['entity_id']) : null;

        $current = $this->current($cell);
        $pending = null;
        if ($openRevision) {
            $fields = (array) json_decode((string) $openRevision->fields, true);
            if (array_key_exists($cell['column'], $fields)) {
                $pending = (string) $fields[$cell['column']];
            }
        }
        $effective = $pending ?? $current;
        if (trim((string) $effective) === trim($ja)) {
            return $base + $this->result('skipped', $pending !== null ? "unchanged (pending revision #{$openRevision->id} already holds the draft)" : 'unchanged (target already holds the draft)');
        }
        if ($cell['public'] !== null) {
            return $base + $this->result('needs_review', $cell['public']);
        }
        if ($published) {
            // A published row's text changes only through its revision (opened on save if none is open).
            if ($openRevision && $openRevision->workflow !== 'draft') {
                return $base + $this->result('needs_review', "open revision #{$openRevision->id} is in step {$openRevision->workflow}: writing would send it back to draft");
            }
            $owner = $openRevision ? RevisionStore::owner($openRevision) : null;
            if ($owner !== null && $owner !== $this->actor) {
                return $base + $this->result('needs_review', "open revision #{$openRevision->id} is held by user #{$owner}");
            }
        } elseif ($meta && $meta->workflow !== 'draft') {
            return $base + $this->result('needs_review', "target is in workflow step {$meta->workflow}: only draft targets are imported");
        }
        if (trim((string) $effective) !== '' && trim((string) $effective) !== trim((string) $source['en'])) {
            return $base + $this->result('conflict', 'the ' . $this->language->code . ' target already has other text (edited, not the English copy): not overwritten (now: "' . mb_substr(trim((string) $effective), 0, 80) . '")');
        }
        if (!$this->apply) {
            return $base + $this->result('updated', $published ? 'would open/extend a pending revision (row is published)' : 'would update the unpublished target');
        }

        return DB::transaction(fn () => $base + $this->save($cell, $draft, $ja, 'updated'));
    }

    /** The target cell's current live value. */
    private function current(array $cell): ?string
    {
        $value = $cell['model']->getAttribute($cell['column']);
        if ($cell['path'] !== '') {
            return JsonPath::get((string) $value, $cell['path']);
        }

        return $value === null ? null : (string) $value;
    }

    /** Saves through the model (observers), then translation_meta.translated_by and the audit entry. */
    private function save(array $cell, array $draft, string $ja, string $status): array
    {
        /** @var Model $model */
        $model = $cell['model'];
        $column = $cell['column'];
        $value = $cell['path'] !== '' ? JsonPath::set((string) $model->getAttribute($column), $cell['path'], $ja) : $ja;
        $published = $cell['revision'] && PublicationPolicy::isPublishedFlag($cell['type'], (object) $model->getAttributes());
        $model->setAttribute($column, $value);
        $model->save();

        $revisionId = null;
        if ($published) {
            $revisionId = RevisionStore::open($cell['type'], (int) $cell['entity_id'])?->id;
            if ($revisionId === null) {
                throw new \RuntimeException("{$cell['type']} #{$cell['entity_id']}: published row saved without a pending revision; aborting");
            }
        } elseif ($cell['meta_type'] && $cell['meta_id']) {
            DB::table('translation_meta')->where('entity_type', $cell['meta_type'])->where('entity_id', $cell['meta_id'])
                ->update(['translated_by' => $this->actor, 'updated_at' => now()]);
        }
        EditorialAudit::record($cell['type'], (int) $cell['entity_id'], self::AUDIT_ACTION, $published ? 'published' : 'draft',
            $published ? 'revision' : $status, $this->actor, $revisionId === null ? null : (int) $revisionId, (string) $draft['id']);

        return $this->result($status, $published ? "pending revision #{$revisionId} (row is published; live text unchanged)" : ($status === 'created'
            ? "created the {$this->language->code} draft (TranslationFactory, " . ($cell['type'] === 'amenity' && !PublicationPolicy::hasColumn('app_amenity') ? 'amenities have no publication flag before 2026_11_07: hidden while the language is not public' : 'unpublished') . ') and wrote the draft'
            : 'updated the unpublished target'))
            + ($revisionId ? ['revision_id' => (int) $revisionId] : []);
    }

    private function result(string $status, string $reason): array
    {
        return ['status' => $status, 'reason' => $reason];
    }
}
