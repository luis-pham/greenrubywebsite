<?php

namespace App\Services\Content;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Modules\BackEnd\Entities\AdLanguage;
use Modules\BackEnd\Entities\AppCabin;
use Modules\BackEnd\Entities\AppCabinPrice;
use Modules\BackEnd\Entities\AppCruise;
use Modules\BackEnd\Helpers\Utilities;
use Modules\FrontEnd\Helpers\FeHreflangUtils;
use Modules\FrontEnd\Services\AppCabinService as FeAppCabinService;

/**
 * Read-side queries/presenters for cabins exposed by the Content API.
 * "Accommodation" cabins = app_cabin rows whose app_group.slug is in config('backend.listAccommodationSlug');
 * everything else in app_cabin (restaurants, decks, ...) is a facility and never exposed here.
 */
class CabinContentService
{
    public static function accommodationQuery(int $languageId)
    {
        return AppCabin::query()
            ->select('app_cabin.*')
            ->join('app_group', 'app_group.id', '=', 'app_cabin.group_id')
            ->whereIn('app_group.slug', config('backend.listAccommodationSlug'))
            ->where('app_cabin.language_id', $languageId)
            ->with('cruise:id,name,language_id')
            ->orderBy('app_cabin.ord')
            ->orderBy('app_cabin.id');
    }

    public static function listAccommodation(int $languageId, ?int $cruiseId = null): Collection
    {
        $query = self::accommodationQuery($languageId);
        if ($cruiseId !== null) {
            $query->where('app_cabin.cruise_id', $cruiseId);
        }

        return $query->get();
    }

    public static function findAccommodation(int $id, int $languageId): ?AppCabin
    {
        return self::accommodationQuery($languageId)->where('app_cabin.id', $id)->first();
    }

    public static function slug(?string $name): string
    {
        return Utilities::convertToAlias((string) $name);
    }

    public static function fileLink(?string $path): ?string
    {
        $link = Utilities::getFileLink($path);

        return $link === '' ? null : $link;
    }

    public static function summary(AppCabin $cabin): array
    {
        return [
            'id' => (int) $cabin->id,
            'slug' => self::slug($cabin->name),
            'name' => $cabin->name,
            'summary' => $cabin->summary,
            'image' => self::fileLink($cabin->image_link),
            'view' => $cabin->view,
            'cabinClass' => $cabin->cabin_class,
            'capacity' => self::number($cabin->capacity),
            'area' => self::number($cabin->area),
            'cruise' => $cabin->cruise
                ? ['id' => (int) $cabin->cruise->id, 'name' => $cabin->cruise->name]
                : null,
        ];
    }

    public static function detail(AppCabin $cabin, AdLanguage $language): array
    {
        $cabinId = (int) $cabin->id;

        $gallery = FeAppCabinService::getFileById($cabinId)->map(fn ($file) => [
            'src' => self::fileLink($file->link),
            'alt' => trim((string) ($file->description ?? '')) !== '' ? $file->description : $cabin->name,
        ])->filter(fn ($g) => $g['src'] !== null)->values();

        $rooms = FeAppCabinService::getRoomById($cabinId)->map(fn ($room) => [
            'title' => $room->title,
            'description' => $room->description,
        ])->values();

        $amenities = FeAppCabinService::getAmenityById($cabinId)->map(fn ($a) => [
            'name' => $a->amenity_name,
            'icon' => self::fileLink($a->amenity_icon),
        ])->values();

        $suitableFor = $cabin->cabinSuitableAudiences()
            ->join('app_group', 'app_group.id', '=', 'app_cabin_suitable_audience.group_id')
            ->orderBy('app_cabin_suitable_audience.ord')
            ->get(['app_group.name'])
            ->map(fn ($g) => ['name' => $g->name])
            ->values();

        $cruise = $cabin->cruise;
        $otherCabins = $cruise
            ? self::listAccommodation((int) $language->id, (int) $cruise->id)
                ->filter(fn ($c) => (int) $c->id !== $cabinId)
                ->map(fn ($c) => self::summary($c))
                ->values()
            : collect();

        return [
            'id' => $cabinId,
            'slug' => self::slug($cabin->name),
            'name' => $cabin->name,
            'summary' => $cabin->summary,
            'contentHtml' => safe_html($cabin->content) ?? '',
            'image' => self::fileLink($cabin->image_link),
            'gallery' => $gallery,
            'view' => $cabin->view,
            'cabinClass' => $cabin->cabin_class,
            'capacity' => self::number($cabin->capacity),
            'area' => self::number($cabin->area),
            'overCapacity' => [
                'adult' => (int) $cabin->over_capacity_adult,
                'child6to12' => (int) $cabin->over_capacity_child_6_12,
                'child2to5' => (int) $cabin->over_capacity_child_2_5,
                'infant' => (int) $cabin->over_capacity_infant,
            ],
            'discountPercent' => self::number($cabin->discount_percent),
            'rooms' => $rooms,
            'amenities' => $amenities,
            'suitableFor' => $suitableFor,
            'pricing' => self::pricing($cabin, $language),
            'cruise' => $cruise
                ? ['id' => (int) $cruise->id, 'name' => $cruise->name, 'slug' => self::slug($cruise->name)]
                : null,
            'otherCabins' => $otherCabins,
            'alternate' => self::alternate($cabin, $language),
            'updatedAt' => self::isoDate($cabin->getRawOriginal('updated_at') ?: $cabin->getRawOriginal('created_at')),
        ];
    }

    /**
     * Prices are hidden entirely (fromPrice null, empty matrix) unless config('frontend.showPublicPrices'),
     * consistent with FeUtils::formatDisplayCurrency on the Blade site.
     */
    public static function pricing(AppCabin $cabin, AdLanguage $language): array
    {
        $showPublic = (bool) config('frontend.showPublicPrices', false);
        $currency = strtolower((string) $language->code) === 'vi' ? 'VND' : 'USD';

        if (!$showPublic) {
            return ['showPublic' => false, 'currency' => $currency, 'fromPrice' => null, 'matrix' => []];
        }

        $prices = AppCabinPrice::where('cabin_id', $cabin->id)
            ->orderBy('duration')
            ->orderBy('guest')
            ->get(['duration', 'guest', 'price']);

        $positive = $prices->filter(fn ($p) => (float) $p->price > 0);
        $atCapacity = $positive->filter(fn ($p) => (float) $p->guest === (float) $cabin->capacity);
        $from = $atCapacity->isNotEmpty() ? $atCapacity->min('price') : $positive->min('price');

        return [
            'showPublic' => true,
            'currency' => $currency,
            'fromPrice' => $from === null ? null : self::number($from),
            'matrix' => $prices->map(fn ($p) => [
                'duration' => (int) $p->duration,
                'guest' => (int) $p->guest,
                'price' => self::number($p->price),
            ])->values(),
        ];
    }

    /**
     * Conservative cross-language counterpart: counterpart cruise via FeHreflangUtils::findCruiseCounterpart
     * (same id in target language, else same bay), then the single accommodation cabin on that cruise with
     * the same `ord`. Returns null if no unique match (or the source ord is itself shared on its cruise).
     */
    public static function alternate(AppCabin $cabin, AdLanguage $language): ?array
    {
        $target = AdLanguage::where('id', '!=', $language->id)
            ->whereIn('code', ['en', 'vi'])
            ->orderBy('ord')
            ->first();
        if (!$target || !$cabin->cruise_id) {
            return null;
        }

        try {
            $targetCruise = FeHreflangUtils::findCruiseCounterpart((int) $cabin->cruise_id, (int) $language->id, $target);
        } catch (\Throwable) {
            return null;
        }
        if (!$targetCruise instanceof AppCruise) {
            return null;
        }

        $sameOrdSource = self::accommodationQuery((int) $language->id)
            ->where('app_cabin.cruise_id', $cabin->cruise_id)
            ->where('app_cabin.ord', $cabin->ord)
            ->count();
        if ($sameOrdSource !== 1) {
            return null;
        }

        $matches = self::accommodationQuery((int) $target->id)
            ->where('app_cabin.cruise_id', $targetCruise->id)
            ->where('app_cabin.ord', $cabin->ord)
            ->limit(2)
            ->get();
        if ($matches->count() !== 1) {
            return null;
        }

        return ['lang' => $target->code, 'id' => (int) $matches->first()->id];
    }

    private static function isoDate($value): ?string
    {
        if (!$value) {
            return null;
        }
        try {
            return Carbon::parse($value, config('app.timezone'))->toIso8601String();
        } catch (\Throwable) {
            return null;
        }
    }

    private static function number($value)
    {
        if ($value === null) {
            return null;
        }
        $f = (float) $value;

        return floor($f) === $f ? (int) $f : $f;
    }
}
