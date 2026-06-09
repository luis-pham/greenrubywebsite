<?php
namespace Modules\FrontEnd\Services;

use Illuminate\Support\Facades\DB;
use Modules\BackEnd\Entities\AppCabin;
use Modules\BackEnd\Entities\AppCabinAmenity;
use Modules\BackEnd\Entities\AppCabinRoom;
use Modules\BackEnd\Entities\AppFile;

class AppCabinService
{
    public static function getMinPriceByCruiseId($cruiseId)
    {
        $list = new AppCabin();
        $list = $list->select('cruise_id', 'duration', DB::raw('MIN(price) AS min_price'));
        $list = $list->join('app_cabin_price', 'app_cabin_price.cabin_id', '=', 'app_cabin.id');
        $list = $list->join('app_group','app_group.id', '=', 'app_cabin.group_id');
        $list = $list->whereIn('app_group.slug', config('backend.listAccommodationSlug'));
        if (is_array($cruiseId)) {
            $list = $list->whereIn('cruise_id', $cruiseId);
        } else {
            $list = $list->where('cruise_id', $cruiseId);
        }
        $list = $list->groupBy('cruise_id', 'duration');
        return $list->get();
    }

    public static function getAmenityById($id)
    {
        $list = new AppCabinAmenity();
        $list = $list->select('cabin_id', 'amenity_id', 'app_cabin_amenity.ord', DB::raw('app_amenity.name AS amenity_name, app_amenity.icon AS amenity_icon'));
        $list = $list->join('app_amenity', 'app_amenity.id', '=', 'app_cabin_amenity.amenity_id');
        if (is_array($id)) {
            $list = $list->whereIn('cabin_id', $id);
        } else {
            $list = $list->where('cabin_id', $id);
        }
        $list = $list->orderBy('app_cabin_amenity.cabin_id');
        $list = $list->orderBy('app_cabin_amenity.ord');
        return $list->get();
    }

    public static function getFileById($id)
    {
        $list = new AppFile();
        $list = $list->select(DB::raw('app_file.*, app_file_attach.object_id, app_file_attach.ord'));
        $list = $list->join('app_file_attach', 'app_file.id', '=', 'app_file_attach.file_id');
        $list = $list->where('object_type', config('backend.fileAttachObjectType.cabin'));
        if (is_array($id)) {
            $list = $list->whereIn('object_id', $id);
        } else {
            $list = $list->where('object_id', $id);
        }
        $list = $list->orderBy('object_id');
        $list = $list->orderBy('ord');
        return $list->get();
    }

    public static function getRoomById($id)
    {
        $list = new AppCabinRoom();
        if (is_array($id)) {
            $list = $list->whereIn('cabin_id', $id);
        } else {
            $list = $list->where('cabin_id', $id);
        }
        $list = $list->orderBy('ord');
        return $list->get();
    }

    public static function getCountRoomById($id)
    {
        $list = new AppCabinRoom();
        $list = $list->select(DB::raw('cabin_id, title, COUNT(*) AS count_room'));
        if (is_array($id)) {
            $list = $list->whereIn('cabin_id', $id);
        } else {
            $list = $list->where('cabin_id', $id);
        }
        $list = $list->groupBy('cabin_id', 'title');
        $list = $list->orderBy('cabin_id');
        //$list = $list->orderBy('title');
        return $list->get();
    }
}
