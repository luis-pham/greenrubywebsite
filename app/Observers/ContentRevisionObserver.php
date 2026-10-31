<?php

namespace App\Observers;

use App\Services\Content\TranslatableFields;
use App\Services\Content\RevisionStore;
use App\Services\Publication\PublicationPolicy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Published text of a workflow-controlled language never changes unreviewed (12 §2.5, §4.2; phase 1E-d). When a
 * PUBLISHED row of such a language is saved with changed text or presentation fields (TranslatableFields text +
 * copy), the changes go into its open content_revision and the live row keeps its approved values; the workflow
 * reviews the revision and applies it on approval (Workflow). Publication flags, slugs (SlugService) and en/vi rows
 * are not intercepted.
 */
class ContentRevisionObserver
{
    public function updating(Model $model): void
    {
        if (!PublishGuardObserver::controlled($model)) {
            return;
        }
        $type = PublishGuardObserver::type($model);
        if (!TranslatableFields::has($type) || !DB::getSchemaBuilder()->hasTable('content_revision')) {
            return;
        }
        $meta = TranslatableFields::of($type);
        $changes = array_intersect_key($model->getDirty(), array_flip(array_merge($meta['text'], $meta['copy'])));
        if ($changes === []) {
            return;
        }
        $actor = auth('admin')->id();
        if (!PublicationPolicy::isPublishedFlag($type, (object) $model->getOriginal())) {
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
}
