<?php
namespace Modules\BackEnd\Services;

use Illuminate\Support\Facades\DB;
use Modules\BackEnd\Entities\AppAmenity;
use Modules\BackEnd\Entities\AppCruise;
use Modules\BackEnd\Entities\AppCruiseItinerary;
use Modules\BackEnd\Entities\AppItinerary;
use Modules\BackEnd\Entities\AppService;

class AppCruiseService
{
    public static function find($id, $languageId)
    {
        $query = AppCruise::where('id', $id);
        if ($languageId !== null) {
            $query->where('language_id', $languageId);
        }
        return $query->first();
    }

    public static function getAll($languageId = null)
    {
        $query = AppCruise::query();
        if ($languageId !== null) {
            $query->where('language_id', $languageId);
        }
        return $query->get();
    }

    public static function getAllJoinItinerary($languageId){
        $query = AppCruise::with('itineraries');
        if ($languageId !== null) {
            $query->where('language_id', $languageId);
        }
        return $query->get();
    }

    public static function getPaging($pageSize,$languageId){
        $query = AppCruise::query();
        if ($languageId !== null) {
            $query->where('language_id', $languageId);
        }

        return $query->paginate($pageSize);
    }

    public static function create($data) {
        return DB::transaction(function () use ($data) {
            $listAmenityId = $data['listAmenityId'] ?? [];
            $listServiceId = $data['listServiceId'] ?? [];

            $listAmenity = AppAmenity::WhereIn('id', $listAmenityId)->get();
            $listService = AppService::WhereIn('id', $listServiceId)->get();
            $listImageGallery = $data['image_gallery'] ?? [];
            $cruise = AppCruise::create($data);

            if(!empty($listAmenity)){
                $pivotData = [];
                foreach ($listAmenity as $index => $amenity){
                    $pivotData[$amenity->id] = [
                        'ord' => $index + 1,
                    ];
                }
                $cruise->cruiseAmenities()->sync($pivotData);
            }

            if(!empty($listService)){
                $pivotData = [];
                foreach ($listService as $index => $service){
                    $pivotData[$service->id] = [
                        'ord' => $index + 1,
                    ];
                }
                $cruise->cruiseServices()->sync($pivotData);
            }

            if(!empty($listImageGallery)){
                $pivotData = [];
                foreach ($listImageGallery as $index => $gallery){
                    $pivotData[$gallery['id']] = [
                        'ord' => $index + 1,
                    ];
                }

                $cruise->syncGalleryImages($pivotData);
            }

            return $cruise->id;
        });
    }

    public static function update($id, $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $cruise = AppCruise::findOrFail($id);

            // Update main cruise fields (exclude relationship data)
            $cruise->update($data);

            // Handle amenities sync (same logic as create)
            $listAmenityId = $data['listAmenityId'] ?? [];
            $listImageGallery = $data['image_gallery'] ?? [];
            $listServiceId = $data['listServiceId'] ?? [];

            if (!empty($listAmenityId)) {
                $listAmenity = AppAmenity::whereIn('id', $listAmenityId)->get();

                $pivotData = [];
                foreach ($listAmenity as $index => $amenity) {
                    $pivotData[$amenity->id] = [
                        'ord' => $index + 1,
                    ];
                }

                $cruise->cruiseAmenities()->sync($pivotData);
            } else {
                $cruise->cruiseAmenities()->sync([]);
            }

            if(!empty($listServiceId)){
                $listService = AppService::WhereIn('id', $listServiceId)->get();

                $pivotData = [];
                foreach ($listService as $index => $service){
                    $pivotData[$service->id] = [
                        'ord' => $index + 1,
                    ];
                }
                $cruise->cruiseServices()->sync($pivotData);
            }
            else{
                $cruise->cruiseServices()->sync([]);
            }

            if(!empty($listImageGallery)){
                $pivotData = [];
                foreach ($listImageGallery as $index => $gallery){
                    $pivotData[$gallery['id']] = [
                        'ord' => $index + 1,
                    ];
                }

                $cruise->syncGalleryImages($pivotData);
            }
            else{
                $cruise->galleryImages()->sync([]);
            }
        });
    }

    public static function findJoin($id,$languageId){
        return AppCruise::with(['cruiseAmenities','galleryImages'])
            ->where('id', $id)
            ->where('language_id', $languageId)
            ->first();
    }
    public static function getAllJoin($languageId = null)
    {
        $query = AppCruise::query()
            ->from('app_cruise')
            ->select(
                'app_cruise.*',
                'app_itinerary.*',
                DB::raw('app_cruise_itinerary.start_at AS start_at')
            )
            ->leftJoin('app_cruise_itinerary', 'app_cruise_itinerary.cruise_id', '=', 'app_cruise.id')
            ->leftJoin('app_itinerary', 'app_itinerary.id', '=', 'app_cruise_itinerary.itinerary_id');

        if ($languageId !== null) {
            $query->where('app_cruise.language_id', $languageId);
        }

        return $query->get();
    }
    public static function delete($id){
        if (is_array($id)) {
            $cruises = AppCruise::whereIn('id', $id)->get();
            foreach ($cruises as $cruise) {
                $cruise->delete();           // ← this triggers events
            }
        } else {
            $cruise = AppCruise::find($id);
            $cruise?->delete();
        }
    }

    public static function storeItinerary($id,$languageId,$data){
        return DB::transaction(function () use ($id,$languageId,$data){
            $cruise = AppCruiseService::find($id,$languageId);
            $cruise->itineraries()->attach($data['itinerary_id'],['start_at' => $data['start_at']]);
        });
   }

   public static function updateItinerary($id,$languageId,$data){
        return DB::transaction(function () use ($id,$languageId,$data){
            $cruise = AppCruiseService::find($id,$languageId);
            $cruise->itineraries()->sync($data);
        });
   }

   public static function deleteItinerary($id,$languageId,$data){
        DB::transaction(function () use ($id,$languageId,$data){
            AppCruiseItinerary::query()
                ->where('cruise_id', $id)
                ->where('itinerary_id', $data['itinerary_id'])
                ->where('start_at',$data['start_at'])
                ->delete();
        });
   }
}
