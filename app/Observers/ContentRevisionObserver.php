<?php

namespace App\Observers;

use App\Services\Content\ChildRevisions;
use App\Services\Content\RevisionStore;
use App\Services\Content\Workflow;
use App\Services\Publication\PublicationPolicy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Published text of a workflow-controlled language never changes unreviewed (12 §2.5, §4.2; phase 1E-d). When a
 * PUBLISHED row of such a language is saved with changed text or presentation fields (TranslatableFields text +
 * copy), the changes go into its open content_revision and the live row keeps its approved values; the workflow
 * reviews the revision and applies it on approval (Workflow). Publication flags, slugs (SlugService) and en/vi rows
 * are not intercepted. Phase 2C: static pages (title/SEO and configuration values) and seeded structural groups too.
 */
class ContentRevisionObserver
{
    public function updating(Model $model): void
    {
        if (!PublishGuardObserver::controlled($model)) {
            return;
        }
        $type = self::type($model);
        $columns = Workflow::revisionColumns($type);
        if ($columns === [] || !DB::getSchemaBuilder()->hasTable('content_revision')) {
            return;
        }
        $changes = array_intersect_key($model->getDirty(), array_flip($columns));
        if ($changes === []) {
            return;
        }
        $actor = auth('admin')->id();
        $published = $type === 'group'
            ? ($model->getOriginal('publish_state') ?? 'published') === 'published'
            : PublicationPolicy::isPublishedFlag($type, (object) $model->getOriginal());
        if (!$published) {
            // Unpublished drafts are edited directly; remember who wrote the text (self-approval rule, Workflow).
            DB::table('translation_meta')->where('entity_type', $type)->where('entity_id', $model->getKey())
                ->update(['translated_by' => $actor, 'updated_at' => now()]);

            return;
        }
        // Throws when another editor holds the open revision: the save is refused, nothing is overwritten.
        RevisionStore::merge($type, (int) $model->getKey(), $changes, [], $actor === null ? null : (int) $actor);
        foreach (array_keys($changes) as $column) {
            $model->setAttribute($column, $model->getOriginal($column));
        }
    }

    /**
     * The workflow type of a row: PublishGuardObserver's, except a structural app_group (not an FAQ group), whose
     * workflow type is 'group' (seeded by i18n:seed-locale; phase 2C). Static pages ('page') revise their title/SEO
     * text here and their configuration values through pageConfigUpdating().
     */
    public static function type(Model $model): string
    {
        $type = PublishGuardObserver::type($model);
        if ($type === 'faq_group' && (int) $model->getAttribute('type') !== (int) config('backend.groupType.faq', 1)) {
            return 'group';
        }

        return $type;
    }

    /**
     * app_page_config of a PUBLISHED page of a workflow-controlled language (phase 2C, 12 §2.5): a changed value goes
     * into the page's pending revision and the live value is kept (ChildRevisions::pageConfig). en/vi pages and
     * unpublished pages are edited directly, as before.
     */
    public static function pageConfigUpdating(Model $config): void
    {
        if (!$config->isDirty('value') || !$config->getAttribute('page_id')) {
            return;
        }
        if (ChildRevisions::pageConfig((int) $config->getAttribute('page_id'), (int) $config->getKey(), $config->getAttribute('value'))) {
            $config->setAttribute('value', $config->getOriginal('value'));
        }
    }
}
