<?php

namespace App\Services\Content;

use Illuminate\Support\Collection;
use Modules\BackEnd\Entities\AdLanguage;
use Modules\BackEnd\Services\AppGroupService;
use Modules\FrontEnd\Constants\PageCodeConsts;

/**
 * GET /services. The Blade /services page lists only the services picked in the "service" page config
 * (key "service-service"), grouped by their service group (app_group type service, admin order).
 */
class ServiceContentService
{
    public static function index(AdLanguage $language): array
    {
        $groups = self::groups($language);
        $config = PageConfigContentService::decode(PageCodeConsts::SERVICE, $language);
        $items = $config['service'] ?? [];

        return [
            'seo' => PageConfigContentService::seo(PageCodeConsts::SERVICE, $language),
            'page' => (object) PageConfigContentService::without($config, ['service']),
            'groups' => $groups->map(fn ($g) => self::groupRef($g))->values(),
            'items' => $items,
            'currency' => ItineraryContentService::currency($language),
        ];
    }

    /** @param iterable $rows app_service rows */
    public static function presentMany($rows, AdLanguage $language): array
    {
        $groups = self::groups($language);

        return collect($rows)->map(fn ($s) => self::present($s, $groups))->values()->all();
    }

    public static function present($s, Collection $groups): array
    {
        $showPublic = (bool) config('frontend.showPublicPrices', false);
        $optional = (int) $s->type === (int) config('backend.appServiceType.exclusive');
        $group = $groups->get($s->group_id);

        return [
            'id' => (int) $s->id,
            'name' => $s->name,
            'description' => ItineraryContentService::plain($s->description),
            'image' => CabinContentService::fileLink($s->image_link),
            'type' => $optional ? 'optional' : 'included',
            'price' => $showPublic && $optional && (float) $s->price > 0 ? CabinContentService::number($s->price) : null,
            'group' => $group ? self::groupRef($group) : null,
        ];
    }

    public static function groups(AdLanguage $language): Collection
    {
        return AppGroupService::getAll(config('backend.groupType.service'), (int) $language->id)
            ->filter(fn ($g) => $g->slug !== 'root')
            ->keyBy('id');
    }

    protected static function groupRef($group): array
    {
        return ['id' => (int) $group->id, 'slug' => $group->slug, 'name' => $group->name];
    }
}
