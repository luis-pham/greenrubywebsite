<?php
namespace Modules\BackEnd\Services;

use Illuminate\Support\Facades\DB;
use Modules\BackEnd\Entities\AppService;
use Modules\BackEnd\Entities\AppAmenity;
use Modules\BackEnd\Entities\AppCabinAmenity;
use Modules\BackEnd\Entities\AppCruiseAmenity;


class AppAmenityService
{
    public static function find($id, $languageId = null)
    {
        $query = AppAmenity::where('id', $id);
        if ($languageId !== null) {
            $query->where('language_id', $languageId);
        }
        return $query->first();
    }

    public static function create($data, $languageId)
    {
        DB::beginTransaction();
        try {
            $obj = new AppAmenity();
            $obj->language_id = $languageId;
            $obj->icon = array_key_exists('icon', $data) ? $data['icon'] : null;
            $obj->name = array_key_exists('name', $data) ? $data['name'] : null;
            $obj->description = array_key_exists('description', $data) ? $data['description'] : null;
            $obj->ord = array_key_exists('ord', $data) && $data['ord'] 
            ? $data['ord'] 
            : self::getNextOrder($languageId);
            $obj->save();
            DB::commit();

            return $obj->id;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public static function update($data, $languageId)
    {
        DB::beginTransaction();
        try {
            $obj = self::find($data['id'], $languageId);
            if ($obj) {
                $obj->icon = array_key_exists('icon', $data) ? $data['icon'] : $obj->icon;
                $obj->name = array_key_exists('name', $data) ? $data['name'] : $obj->name;
                $obj->description = array_key_exists('description', $data) ? $data['description'] : $obj->description;
                $obj->ord = array_key_exists('ord', $data) ? $data['ord'] : $obj->ord;
                $obj->save();
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public static function delete($id, $languageId)
    {
        if (is_array($id)) {
            AppAmenity::whereIn('id', $id)
                ->where('language_id', $languageId)
                ->delete();
            AppCabinAmenity::whereIn('amenity_id', $id)
                ->delete();
            AppCruiseAmenity::whereIn('amenity_id', $id)
                ->delete();
        } else {
            $obj = self::find($id, $languageId);
            if ($obj) {
                $obj->delete();
                AppCabinAmenity::where('amenity_id', $id)
                    ->delete();
                AppCruiseAmenity::where('amenity_id', $id)
                    ->delete();
            }
        }
    }

    public static function getAll($languageId)
    {
        return AppAmenity::where('language_id', $languageId)
            ->orderBy('ord')
            ->get();
    }



    public static function getPaging($param, $languageId)
    {
        $list = new AppAmenity();
        $list = $list->select('app_amenity.*');
       
        
        if (array_key_exists('keyword', $param)) {
            $list = $list->where(function($query) use ($param) {
                $query->where('app_amenity.name', 'like'    , '%' . $param['keyword'] . '%')
                      ->orWhere('app_amenity.description', 'like', '%' . $param['keyword'] . '%');
            });
        }
             
        $list = $list->where('app_amenity.language_id', $languageId);
        $list = $list->orderBy('ord');        
        return $list->paginate(config('backend.paginationLimit'));

    }
    public static function getNextOrder($languageId)
    {
        $ord = AppAmenity::where('language_id', $languageId)->max('ord');
        return $ord ? $ord + 1 : 1;
    }

    public static function saveOrder($list, $languageId)
    {
        DB::beginTransaction();
        try {
            foreach ($list as $key => $value) {
                $obj = self::find($key, $languageId);
                if ($obj) {
                    $obj->ord = $value;
                    $obj->save();
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            throw $e;
        }
    }

    public static function getIconList($languageId)
    {
        return AppAmenity::where('language_id', $languageId)
            ->orderBy('ord','desc')
            ->pluck('icon');    
    }

    public static function getById($id, $languageId)
    {
        if (is_array($id)) {
            return AppAmenity::whereIn('id', $id)
                ->where('language_id', $languageId)
                ->get();
        } else {
            return self::find($id, $languageId);
        }
    }

 
}