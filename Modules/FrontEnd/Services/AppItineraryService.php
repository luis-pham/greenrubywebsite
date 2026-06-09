<?php

namespace Modules\FrontEnd\Services;

use Modules\BackEnd\Entities\AppItinerary;

class AppItineraryService
{
    public static function getEarliestItinerariesWithMinPriceAndOfBay($cruiseId)
    {
        $query = AppItinerary::query()
            ->select([
                'app_itinerary.id',
                'app_itinerary.name',
                'app_itinerary.destination',
                'app_itinerary.cover_link',
                'app_itinerary.duration',
                'app_cruise_itinerary.start_at',
            ])
            ->join('app_cruise_itinerary', 'app_itinerary.id', '=', 'app_cruise_itinerary.itinerary_id')
            ->where('app_cruise_itinerary.cruise_id', $cruiseId)
            ->where('app_cruise_itinerary.start_at', '>=', now()->startOfDay())
            ->with('itineraryActivities');

        $rs = $query
            ->orderBy('app_itinerary.duration')
            ->orderBy('app_cruise_itinerary.start_at')
            ->get();

        $rs = $rs->groupBy('duration')->map(fn($g) => $g->sortBy('start_at')->first());

        $listMinPrice = AppCabinService::getMinPriceByCruiseId($cruiseId);

        $rs->each(function ($item) use ($listMinPrice) {
           $item->min_price = $listMinPrice->where('duration',$item->duration)->first()?->min_price ?? 0;
        });

        return $rs;
    }
}
