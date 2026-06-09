<?php

namespace Modules\FrontEnd\Services;

use DB;
use Modules\BackEnd\Entities\AppCabin;
use Modules\BackEnd\Entities\AppCruise;
use Modules\BackEnd\Entities\AppCruiseItinerary;
use Modules\BackEnd\Entities\AppItinerary;

class AppCruiseItineraryService
{
    public static function findAllReservable($languageId){
        return AppCruiseItinerary::with(['cruise', 'itinerary'])
            ->whereHas('cruise', function($query) use ($languageId){
                $query->where('language_id', $languageId);
            })
            ->reservable()
            ->get();
    }

    public static function getScheduledItineraries($languageId = null)
    {
        $subQuery1 = DB::table('app_cruise_itinerary as aci')
            ->select('aci.cruise_id', 'aci.itinerary_id', DB::raw('MIN(aci.start_at) as nearest_start_at'))
            ->where('aci.start_at', '>=', DB::raw('CURRENT_DATE()'))
            ->groupBy('aci.cruise_id', 'aci.itinerary_id');

        $subQuery2 = DB::table('app_cabin as cab')
            ->select('cab.cruise_id', 'acp.duration', DB::raw('MIN(acp.price) AS min_price'))
            ->join('app_cabin_price as acp', 'cab.id', '=', 'acp.cabin_id')
            ->join('app_group as ag', 'cab.group_id', '=', 'ag.id')
            ->whereIn('ag.slug', config('backend.listAccommodationSlug'))
            ->groupBy('cab.cruise_id', 'acp.duration');

        $itinerary = DB::table('app_itinerary as ai')
            ->where('ai.language_id', $languageId);

        $subQuery1Wrapped = DB::table(DB::raw("({$subQuery1->toSql()}) as sub_1_1"))
            ->selectRaw('cruise_id, itinerary_id, nearest_start_at');

        $rankedSql = DB::table(DB::raw("({$itinerary->toSql()}) as ai"))
            ->select([
                'ai.id',
                'ai.name',
                'ai.destination',
                'ai.bay',
                'ai.image_link',
                'ai.description',
                'ai.duration',
                'ai.updated_at',
                'ai.created_at',
                'ac.id as cruise_id',
                'ac.name as cruise_name',
                'sub_1.nearest_start_at',
                DB::raw('MIN(sub_2.min_price) AS price'),
                DB::raw('ROW_NUMBER() OVER (PARTITION BY ai.id ORDER BY MIN(sub_2.min_price) ASC) AS rn'),
            ])
            ->leftJoinSub($subQuery1Wrapped, 'sub_1', 'ai.id', '=', 'sub_1.itinerary_id')
            ->leftJoin('app_cruise as ac', 'ac.id', '=', 'sub_1.cruise_id')
            ->leftJoinSub($subQuery2, 'sub_2', function ($join) {
                $join->on('ai.duration', '=', 'sub_2.duration')
                    ->on('sub_1.cruise_id', '=', 'sub_2.cruise_id');
            })
            ->groupBy('ai.id', 'ai.duration', 'ai.updated_at', 'ai.created_at', 'ac.id', 'ac.name', 'sub_1.nearest_start_at');

        $bindings = array_merge(
            $itinerary->getBindings(),
            $subQuery1->getBindings(),
            $subQuery2->getBindings()
        );

        return DB::table(DB::raw("({$rankedSql->toSql()}) as ranked"))
            ->whereRaw('rn = 1')
            ->orderByRaw('nearest_start_at IS NULL ASC')
            ->orderBy('nearest_start_at')
            ->setBindings($bindings)
            ->get();
    }

    public static function findByIdsJoin($cruiseId,$itineraryId,$languageId){
        $cruise = AppCruise::with(['cabins'])
            ->where('id', $cruiseId)
            ->where('language_id', $languageId)
            ->first();

        $itinerary = AppItinerary::with([
            'itineraryServices',
            'itineraryDays',
            'itineraryDays.itineraryDayDetails',
            'galleryImages',
        ])
            ->where('id', $itineraryId)
            ->first();

        $pivot = AppCruiseItinerary::where('cruise_id', $cruiseId)
            ->where('itinerary_id', $itineraryId)
            ->whereDate('start_at', '>=', DB::raw('CURRENT_DATE()'))
            ->get();

        if (count($pivot) > 0) {
            $pivot->each(function ($item) use ($itinerary,$cruise) {
                $item->setRelation('cruise', $cruise);
                $item->setRelation('itinerary', $itinerary);
            });
            return collect($pivot);
        }

        $fakeRecord = new AppCruiseItinerary();
        $fakeRecord->cruise_id = $cruiseId;
        $fakeRecord->itinerary_id = $itineraryId;
        $fakeRecord->setRelation('cruise', $cruise);
        $fakeRecord->setRelation('itinerary', $itinerary);

        return collect([$fakeRecord]);
    }

    public static function getCruiseItinerary($param, $languageId)
    {
        $query = new AppItinerary();
        $query = $query->select('app_cruise_itinerary.*');
        $query = $query->join('app_cruise_itinerary', 'app_cruise_itinerary.itinerary_id', '=', 'app_itinerary.id');
        $query = $query->join('app_cruise', 'app_cruise.id', '=', 'app_cruise_itinerary.cruise_id');
        $query = $query->where('app_itinerary.language_id', $languageId);
        $query = $query->where('start_at', '>=', date('Y-m-d'));
        if (array_key_exists('cruise_id', $param)) {
            $query = $query->where('app_cruise_itinerary.cruise_id', $param['cruise_id']);
        }
        if (array_key_exists('itinerary_id', $param)) {
            $query = $query->where('app_cruise_itinerary.itinerary_id', $param['itinerary_id']);
        }
        if (array_key_exists('start_at', $param)) {
            $query = $query->where('app_cruise_itinerary.start_at', $param['start_at']);
        }
        if (array_key_exists('guest', $param)) {
            $listCruiseId = AppCabin::select('cruise_id', DB::raw('COUNT(*) as total_capacity'))
                ->where('language_id', $languageId)
                ->groupBy('cruise_id')
                ->having('total_capacity', '>=', $param['guest'])
                ->pluck('cruise_id');
            $query = $query->whereIn('app_cruise_itinerary.cruise_id', $listCruiseId);
        }
        return $query->get();
    }
}
