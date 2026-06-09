<?php
namespace Modules\BackEnd\Services;

use Modules\BackEnd\Entities\AppMenuFrontEnd;

class AppMenuFrontEndService
{
    public static function find($id, $languageId)
    {
        return AppMenuFrontEnd::where('id', $id)->where('language_id', $languageId)->first();
    }

    public static function create($data, $languageId)
    {
        $obj = new AppMenuFrontEnd();
        $obj->language_id = $languageId;
        $obj->code = array_key_exists('code', $data) ? $data['code'] : null;
        $obj->name = array_key_exists('name', $data) ? $data['name'] : null;
        $obj->description = array_key_exists('description', $data) ? $data['description'] : null;
        $obj->menu = array_key_exists('menu', $data) ? $data['menu'] : null;
        $obj->save();

        return $obj->id;
    }

    public static function update($data, $languageId)
    {
        $obj = self::find($data['id'], $languageId);
        if ($obj) {
            $obj->code = array_key_exists('code', $data) ? $data['code'] : $obj->code;
            $obj->name = array_key_exists('name', $data) ? $data['name'] : $obj->name;
            $obj->description = array_key_exists('description', $data) ? $data['description'] : $obj->description;
            $obj->menu = array_key_exists('menu', $data) ? $data['menu'] : $obj->menu;
            $obj->save();
        }
    }

    public static function delete($id, $languageId)
    {
        if (is_array($id)) {
            AppMenuFrontEnd::whereIn('id', $id)->where('language_id', $languageId)->delete();
        } else {
            $obj = self::find($id, $languageId);
            if ($obj) {
                $obj->delete();
            }
        }
    }

    public static function getAll($languageId)
    {
        return AppMenuFrontEnd::where('language_id', $languageId)->get();
    }

    public static function getPaging($param, $languageId)
    {
        $list = new AppMenuFrontEnd();
        if (array_key_exists('keyword', $param)) {
            $list = $list->where(function ($query) use ($param) {
                $query->where('code', 'like', '%' . $param['keyword'] . '%');
                $query->orWhere('name', 'like', '%' . $param['keyword'] . '%');
                $query->orWhere('description', 'like', '%' . $param['keyword'] . '%');
            });
        }
        $list = $list->where('language_id', $languageId);
        $list = $list->orderBy('id', 'desc');
        return $list->paginate(config('backend.paginationLimit'));
    }
    
    public static function getByCode($code, $languageId)
    {
        return AppMenuFrontEnd::where('code', $code)
            ->where('language_id', $languageId)
            ->first();
    }
}
