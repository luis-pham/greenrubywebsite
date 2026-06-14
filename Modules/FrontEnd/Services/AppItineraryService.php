<?php

namespace Modules\FrontEnd\Services;

use Modules\BackEnd\Entities\AppCruise;
use Modules\BackEnd\Entities\AppItinerary;
use Modules\FrontEnd\Helpers\FeCruiseUtils;

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
                'app_itinerary.duration',
            ])
            ->where('app_itinerary.language_id', $cruise->language_id)
            ->where('app_itinerary.bay', $bay)
            ->with('itineraryActivities')
            ->orderBy('app_itinerary.duration')
            ->orderBy('app_itinerary.id')
            ->get();

        $rs = $rs->groupBy('duration')->map(fn($g) => $g->first());

        $listMinPrice = AppCabinService::getMinPriceByCruiseId($cruiseId);

        $rs->each(function ($item) use ($listMinPrice) {
           $item->min_price = $listMinPrice->where('duration',$item->duration)->first()?->min_price ?? 0;
        });

        return $rs;
    }
}
