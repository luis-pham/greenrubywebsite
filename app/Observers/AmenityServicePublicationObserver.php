<?php

namespace App\Observers;

use App\Services\Content\EditorialRefusal;
use App\Services\Publication\PublicationPolicy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Publication of amenities (publish_state, 2026_11_07_000001) and services (status) in a language under editorial
 * workflow (phase 2C; same rule as PublishGuardObserver for the other types): a row created in such a language
 * starts unpublished, and a save that turns it published needs its translation_meta workflow 'approved'. The
 * workflow's own publish action (Workflow::setPublished) writes the flag after that check. en/vi rows: untouched.
 */
class AmenityServicePublicationObserver
{
    /** model class => [type, flag column, published value, unpublished value] */
    public const MODELS = [
        \Modules\BackEnd\Entities\AppAmenity::class => ['amenity', 'publish_state', 'published', 'unpublished'],
        \Modules\BackEnd\Entities\AppService::class => ['service', 'status', 1, 0],
    ];

    public function creating(Model $model): void
    {
        [, $column, , $unpublished] = self::MODELS[get_class($model)];
        if (PublishGuardObserver::controlled($model) && PublicationPolicy::hasColumn($model->getTable(), $column)) {
            $model->setAttribute($column, $unpublished);
        }
    }

    public function updating(Model $model): void
    {
        [$type, $column, $published] = self::MODELS[get_class($model)];
        if (!PublishGuardObserver::controlled($model) || !PublicationPolicy::hasColumn($model->getTable(), $column)) {
            return;
        }
        $is = fn ($value) => is_int($published) ? (int) $value === $published : (string) $value === $published;
        if ($is($model->getOriginal($column)) || !$is($model->getAttribute($column))) {
            return; // not a transition to published
        }
        $workflow = DB::table('translation_meta')->where('entity_type', $type)->where('entity_id', $model->getKey())->value('workflow');
        if ($workflow !== 'approved') {
            throw new EditorialRefusal("Cannot publish {$type} #{$model->getKey()}: its translation is not approved (workflow: " . ($workflow ?? 'none') . ').');
        }
    }
}
