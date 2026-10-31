<?php

namespace App\Services\Publication;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Public reads only see published rows of the types that gained `publish_state` in phase 1C-2 (cruise, cabin,
 * itinerary, experience, FAQ, FAQ group, category), plus the parent rules of 12 §4.1: a cabin of an unpublished
 * cruise and a FAQ of an unpublished group are hidden too (an orphan with no parent row is not). Every existing row defaults to 'published', so EN/VI
 * output does not change. Inactive outside a public read (PublicationContext) and before the migration has run.
 */
final class PublishedScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        if (!PublicationContext::isPublic() || !PublicationPolicy::hasColumn($model->getTable())) {
            return;
        }
        $preview = \App\Services\Preview\Preview::active();
        if ($preview && $preview->table === $model->getTable()) {
            // Phase 1E-d: an authenticated preview also sees its one entity, whatever its own flag.
            $builder->where(fn ($q) => $q->where($model->qualifyColumn('publish_state'), 'published')->orWhere($model->qualifyColumn('id'), (int) $preview->entity_id));
        } else {
            $builder->where($model->qualifyColumn('publish_state'), 'published');
        }
        if (PublicationContext::storedSlugsRequired() && in_array($model->getTable(), ['app_cruise', 'app_cabin', 'app_itinerary', 'app_exp_activity'], true)
            && PublicationPolicy::hasColumn($model->getTable(), 'slug')) {
            $builder->whereNotNull($model->qualifyColumn('slug'))->where($model->qualifyColumn('slug'), '<>', '');
        }

        // Hidden only when the parent exists AND is unpublished: orphans (e.g. a cabin with cruise_id 0) keep
        // today's behaviour.
        $parent = PublicationPolicy::PARENTS[$model->getTable()] ?? null;
        if ($parent && PublicationPolicy::hasColumn($parent['table'])) {
            $builder->whereNotExists(function ($sub) use ($model, $parent) {
                $sub->selectRaw('1')->from($parent['table'] . ' as parent_pub')
                    ->whereColumn('parent_pub.id', $model->qualifyColumn($parent['key']))
                    ->where('parent_pub.publish_state', '<>', 'published');
            });
        }
    }
}
