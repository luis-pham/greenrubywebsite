<?php

namespace App\Observers;

use App\Services\Content\Slugs;
use App\Services\Locale\LocaleRegistry;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

/**
 * Cruise, cabin, itinerary, experience (phase 1C-3): the slug is generated once, when the row is created, from its
 * name (Latin only; a Japanese name gives none and the row cannot be published until an editor sets one). Renaming
 * never touches it; `php artisan content:slug` changes it deliberately, with history and redirects.
 */
class SlugObserver
{
    public const MODELS = [
        \Modules\BackEnd\Entities\AppCruise::class,
        \Modules\BackEnd\Entities\AppCabin::class,
        \Modules\BackEnd\Entities\AppItinerary::class,
        \Modules\BackEnd\Entities\AppExpActivity::class,
    ];

    public function creating(Model $model): void
    {
        if (!self::hasSlug($model) || trim((string) $model->getAttribute('slug')) !== '') {
            return;
        }
        // Only en/vi derive a slug from the name. Other languages need an editor-chosen, meaningful Latin slug:
        // a Japanese name would derive nothing, or only its digits ("グリーンルビー1" → "1").
        $language = LocaleRegistry::byId((int) $model->getAttribute('language_id'));
        if (!$language || !LocaleRegistry::isLegacy($language)) {
            return;
        }
        $slug = Slugs::derive((string) $model->getAttribute('name'));
        $model->setAttribute('slug', $slug === '' ? null : $slug);
    }

    private static function hasSlug(Model $model): bool
    {
        $key = 'slugs.column.' . $model->getTable();
        if (!app()->bound($key)) {
            app()->instance($key, Schema::hasColumn($model->getTable(), 'slug'));
        }

        return app($key);
    }
}
