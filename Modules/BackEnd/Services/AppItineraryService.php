<?php
namespace Modules\BackEnd\Services;

use Illuminate\Support\Facades\DB;
use Modules\BackEnd\Entities\AppExpActivity;
use Modules\BackEnd\Entities\AppItinerary;
use Modules\BackEnd\Entities\AppItineraryDay;
use Modules\BackEnd\Entities\AppService;

class AppItineraryService
{
    public static function find($id, $languageId)
    {
        $query = AppItinerary::where('id', $id);
        if ($languageId !== null) {
            $query->where('language_id', $languageId);
        }
        return $query->first();
    }

    public static function getAll($languageId,$params = [])
    {
        $query = AppItinerary::query();

        if ($languageId !== null) {
            $query->where('language_id', $languageId);
        }

        if(array_key_exists('name', $params) && $params['name'] != null) {
            $query->where('name', 'like', '%'.$params['name'].'%');
        }
        if(array_key_exists('duration', $params) && $params['duration'] != null) {
            $query->where('duration', $params['duration']);
        }
        return $query->get();
    }

    public static function getAllJoin($languageId){
        $pivot = DB::table('app_cruise_itinerary as aci')
            ->select(
                'aci.cruise_id',
                'aci.itinerary_id',
                DB::raw('MIN(aci.start_at) AS nearest_start_at')
            )
            ->whereDate('aci.start_at', '>=', DB::raw('CURRENT_DATE()'))
            ->groupBy('aci.cruise_id', 'aci.itinerary_id');

        $query = DB::table('app_itinerary as ai')
            ->joinSub($pivot, 'sub_aci', function ($join) {
                $join->on('sub_aci.itinerary_id', '=', 'ai.id');
            })
            ->join('app_cruise as ac', 'ac.id', '=', 'sub_aci.cruise_id')
            ->select(
                'ai.*',
                'sub_aci.cruise_id',
                'sub_aci.itinerary_id'
            );

        if ($languageId !== null) {
            $query->where('ai.language_id', $languageId);
            $query->where('ac.language_id', $languageId);
        }

        return $query->get();
    }

    public static function getPaging($pageSize,$languageId){
        $query = AppItinerary::query();
        if ($languageId !== null) {
            $query->where('language_id', $languageId);
        }

        return $query->paginate($pageSize);
    }
    public static function create($data) {
        return DB::transaction(function () use ($data) {
            $listServiceId = $data['listServiceId'] ?? [];
            $listActivityId = $data['listActivityId'] ?? [];

            $listService = AppService::WhereIn('id', $listServiceId)->get();
            $listActivity = AppExpActivity::WhereIn('id', $listActivityId)->get();
            $listItineraryDay = $data['itinerary_days'] ?? [];
            $listImageGallery = $data['image_gallery'] ?? [];

            $itinerary = AppItinerary::create($data);

            if(!empty($listService)){
                $pivotData = [];
                foreach ($listService as $index => $service){
                    $pivotData[$service->id] = [
                        'ord' => $index + 1,
                    ];
                }
                $itinerary->itineraryServices()->sync($pivotData);
            }

            if(!empty($listActivity)){
                $pivotData = [];
                foreach ($listActivity as $index => $activity){
                    $pivotData[$activity->id] = [
                        'ord' => $index + 1,
                    ];
                }
                $itinerary->itineraryActivities()->sync($pivotData);
            }

            if(!empty($listItineraryDay)){
                collect($listItineraryDay)->each(function($day,$idx) use($itinerary){
                    $day['day'] = $idx+1;
                    $dayEntity = $itinerary->itineraryDays()->create($day);

                    $sorted = collect($day['itinerary_day_details'])->sortBy('time');

                    $sorted->each(function($detail,$idx) use($dayEntity){
                        $detail['ord'] = $idx + 1;
                        $dayEntity->itineraryDayDetails()->create($detail);
                    });
                });
            }

            if(!empty($listImageGallery)){
                $pivotData = [];
                foreach ($listImageGallery as $index => $gallery){
                    $pivotData[$gallery['id']] = [
                        'ord' => $index + 1,
                    ];
                }

                $itinerary->syncGalleryImages($pivotData);
            }

            return $itinerary->id;
        });
    }

    public static function update($id, $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $itinerary = AppItinerary::findOrFail($id);

            $itinerary->update($data);

            $listServiceId = $data['listServiceId'] ?? [];
            $listActivityId = $data['listActivityId'] ?? [];
            $listItineraryDay = $data['itinerary_days'] ?? [];
            $listImageGallery = $data['image_gallery'] ?? [];

            $listService = AppService::WhereIn('id', $listServiceId)->get();
            $listActivity = AppExpActivity::WhereIn('id', $listActivityId)->get();

            if(!empty($listService)){
                $pivotData = [];
                foreach ($listService as $index => $service){
                    $pivotData[$service->id] = [
                        'ord' => $index + 1,
                    ];
                }
                $itinerary->itineraryServices()->sync($pivotData);
            }
            else {
                $itinerary->itineraryServices()->sync([]);
            }

            if(!empty($listActivity)){
                $pivotData = [];
                foreach ($listActivity as $index => $activity){
                    $pivotData[$activity->id] = [
                        'ord' => $index + 1,
                    ];
                }
                $itinerary->itineraryActivities()->sync($pivotData);
            }
            else {
                $itinerary->itineraryActivities()->sync([]);
            }

            if(!empty($listItineraryDay)){
                $existingDayIds = [];

                collect($listItineraryDay)->each(function($day, $idx) use($itinerary, &$existingDayIds){
                    $dayData = array_merge($day, ['day' => $idx + 1]);

                    // Update or create day
                    if(isset($day['id'])) {
                        $dayEntity = $itinerary->itineraryDays()->find($day['id']);
                        $dayEntity->update($dayData);
                    } else {
                        $dayEntity = $itinerary->itineraryDays()->create($dayData);
                    }

                    $existingDayIds[] = $dayEntity->id;
                    $existingDetailIds = [];

                    $sorted = collect($day['itinerary_day_details'] ?? [])->sortBy('time')->values();
                    $sorted->each(function($detail, $idx) use($dayEntity, &$existingDetailIds){
                        $detailData = array_merge($detail, ['ord' => $idx + 1]);

                        // Update or create detail
                        if(isset($detail['id'])) {
                            $detailEntity = $dayEntity->itineraryDayDetails()->find($detail['id']);
                            $detailEntity->update($detailData);
                            $existingDetailIds[] = $detailEntity->id;
                        } else {
                            $detailEntity = $dayEntity->itineraryDayDetails()->create($detailData);
                            $existingDetailIds[] = $detailEntity->id;
                        }
                    });

                    // Delete removed details
                    $dayEntity->itineraryDayDetails()->whereNotIn('id', $existingDetailIds)->delete();
                });

                // Delete removed days
                $itinerary->itineraryDays()->whereNotIn('id', $existingDayIds)->delete();
            }

            if(!empty($listImageGallery)){
                $pivotData = [];
                foreach ($listImageGallery as $index => $gallery){
                    $pivotData[$gallery['id']] = [
                        'ord' => $index + 1,
                    ];
                }

                $itinerary->syncGalleryImages($pivotData);
            }
            else{
                $itinerary->galleryImages()->sync([]);
            }
        });
    }

    public static function findJoin($id,$languageId){
        return AppItinerary
            ::with([
                'itineraryServices',
                'itineraryActivities',
                'itineraryDays',
                'itineraryDays.itineraryDayDetails',
                'galleryImages'
            ])
            ->where('id', $id)
            ->where('language_id', $languageId)
            ->first();
    }

    public static function delete($id){
        if (is_array($id)) {
            // Fetch all first → events fire for each
            $itineraries = AppItinerary::whereIn('id', $id)->get();
            foreach ($itineraries as $itinerary) {
                $itinerary->delete();           // ← this triggers events
            }
        } else {
            $itinerary = AppItinerary::find($id);
            $itinerary?->delete();           // ← triggers events

        }
    }
    public static function getLatestUpdate($param, $languageId = null)
    {
        $query = AppItinerary::select('app_itinerary.*', DB::raw('IFNULL(app_itinerary.updated_at, app_itinerary.created_at) AS `lastmod`'));
        if ($languageId) {
            $query = $query->where('app_itinerary.language_id', $languageId);
        }
        $query = $query->orderBy('updated_at', 'desc');
        return $query->first();
    }
}
