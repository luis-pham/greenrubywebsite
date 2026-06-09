<?php

namespace Modules\FrontEnd\Services;
use DB;
use Modules\BackEnd\Entities\AppCruise;
use Modules\BackEnd\Entities\AppCruiseItinerary;

class AppCruiseService
{
    public static function getAll($languageId){
        return AppCruise::where('language_id', $languageId)->get();
    }


    public static function findByIdJoin($id,$languageId){
        return AppCruise::with([
            'cabins',
            'cruiseAmenities',
            'cruiseServices',
            'galleryImages',
            'cabins.cabinRooms'
        ])
            ->where('id',$id)
            ->where('language_id',$languageId)
            ->first();
    }

    public static function getLatest($languageId)
    {
        return AppCruise::where('language_id', $languageId)
            ->orderBy('id', 'desc')
            ->first();
    }

    public static function getByServiceId($serviceId, $languageId)
    {
        $query = new AppCruise();
        $query = $query->select('app_cruise.*', 'service_id');
        $query = $query->distinct(['cruise_id', 'service_id']);
        $query = $query->join('app_cruise_itinerary', 'app_cruise.id', '=', 'app_cruise_itinerary.cruise_id');
        $query = $query->join('app_itinerary_service', 'app_cruise_itinerary.itinerary_id', '=', 'app_itinerary_service.itineraries_id');
        if (is_array($serviceId)) {
            $query->whereIn('app_itinerary_service.service_id', $serviceId);
        } else {
            $query->where('app_itinerary_service.service_id', $serviceId);
        }
        $query = $query->where('app_cruise.language_id', $languageId);

        return $query->get();
    }

    public static function getLatestUpdate($languageId = null)
    {
        $query = AppCruise::select('app_cruise.*', DB::raw('IFNULL(app_cruise.updated_at, app_cruise.created_at) AS `lastmod`'));

        if ($languageId) {
            $query = $query->where('app_cruise.language_id', $languageId);
        }
        $query = $query->orderBy('updated_at', 'desc');
        return $query->first();
    }
}
