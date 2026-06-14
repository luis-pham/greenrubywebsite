<?php

namespace Modules\FrontEnd\Services;

use Modules\BackEnd\Entities\AppCruise;
use Modules\BackEnd\Entities\AppItinerary;
use Modules\FrontEnd\Helpers\FeCruiseUtils;
use Modules\FrontEnd\Helpers\FeUtils;

class AppItineraryService
{
    public static function getEarliestItinerariesWithMinPriceAndOfBay($cruiseId)
    {
        $cruise = AppCruise::query()->find($cruiseId);
        if (!$cruise) {
            return collect();
        }

        $bay = FeCruiseUtils::getBayForCruise($cruise->name);

        $rs = AppItinerary::query()
            ->select([
                'app_itinerary.id',
                'app_itinerary.name',
                'app_itinerary.destination',
                'app_itinerary.cover_link',
                'app_itinerary.image_link',
                'app_itinerary.duration',
                'app_itinerary.bay',
            ])
            ->where('app_itinerary.language_id', $cruise->language_id)
            ->where('app_itinerary.bay', $bay)
            ->orderBy('app_itinerary.duration')
            ->orderBy('app_itinerary.id')
            ->get();

        $rs = $rs->groupBy('duration')->map(fn($g) => $g->first());

        $listMinPrice = AppCabinService::getMinPriceByCruiseId($cruiseId);

        $rs->each(function ($item) use ($listMinPrice, $cruise) {
            $minPrice = $listMinPrice->where('duration', $item->duration)->first()?->min_price ?? 0;
            $item->min_price = $minPrice;
            $item->price = $minPrice;
            $item->cruise_id = $cruise->id;
            $item->cruise_name = FeUtils::formatGreenRubyMenuName($cruise->name);
        });

        return $rs;
    }
}
