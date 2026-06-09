<?php
namespace Modules\BackEnd\Services;

use Modules\BackEnd\Entities\AdRole;

class AdRoleService
{
    public static function find($id)
    {
        return AdRole::find($id);
    }

    public static function create($data)
    {
        $obj = new AdRole();
        $obj->name = array_key_exists('name', $data) ? $data['name'] : null;
        $obj->save();

        return $obj->id;
    }

    public static function update($data)
    {
        $obj = self::find($data['id']);
        if ($obj) {
            $obj->name = array_key_exists('name', $data) ? $data['name'] : $obj->name;
            $obj->save();
        }
    }

    public static function delete($id)
    {
        if (is_array($id)) {
            AdRole::destroy($id);
        } else {
            $obj = self::find($id);
            if ($obj) {
                $obj->delete();
            }
        }
    }

    public static function getAll()
    {
        return AdRole::all();
    }

    public static function getPaging($param)
    {
        $list = new AdRole();
        if (array_key_exists('keyword', $param)) {
            $list = $list->where('name', 'like', '%' . $param['keyword'] . '%');
        }
        $list = $list->orderBy('id', 'desc');
        return $list->paginate(config('backend.paginationLimit'));
    }
}
