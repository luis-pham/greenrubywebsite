<?php

namespace App\Observers;

use App\Services\Content\BusinessFields;
use Illuminate\Database\Eloquent\Model;
use RuntimeException;

/**
 * Text-only languages never write business data (phase 1E-c; I-9, I-10). For a row whose language has no price list
 * of its own (ja → en), any change to a business column (prices, capacities, specifications, times; see
 * TranslatableFields) is refused, whatever the path: admin forms, editor API, tinker, commands. Its values are never
 * read publicly either: the Content API overlays the price-list row's (BusinessFields::overlay). en/vi: no effect.
 */
class BusinessFieldsObserver
{
    public const MODELS = [
        \Modules\BackEnd\Entities\AppCruise::class => 'cruise',
        \Modules\BackEnd\Entities\AppCabin::class => 'cabin',
        \Modules\BackEnd\Entities\AppItinerary::class => 'itinerary',
        \Modules\BackEnd\Entities\AppExpActivity::class => 'experience',
        \Modules\BackEnd\Entities\AppService::class => 'service',
    ];

    public function updating(Model $model): void
    {
        $type = self::MODELS[get_class($model)];
        $forbidden = BusinessFields::forbiddenChanges($type, (int) $model->getAttribute('language_id'), $model->getDirty());
        if ($forbidden !== []) {
            throw new \App\Services\Content\EditorialRefusal('Không sửa được ' . implode(', ', $forbidden) . ' ở ngôn ngữ này: giá trị lấy từ bản EN (sửa ở bản EN).');
        }
    }
}
