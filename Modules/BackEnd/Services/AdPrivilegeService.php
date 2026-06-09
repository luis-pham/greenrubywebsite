<?php
namespace Modules\BackEnd\Services;

use Modules\BackEnd\Entities\AdPrivilege;

class AdPrivilegeService
{
    public static function find($id)
    {
        return AdPrivilege::find($id);
    }

    public static function create($data)
    {
        $obj = new AdPrivilege();
        $obj->resource_id = array_key_exists('resource_id', $data) ? $data['resource_id'] : null;
        $obj->name = array_key_exists('name', $data) ? $data['name'] : null;
        $obj->alias = array_key_exists('alias', $data) ? $data['alias'] : null;
        $obj->ord = array_key_exists('ord', $data) ? $data['ord'] : null;
        $obj->save();
    }

    public static function update($data)
    {
        $obj = AdPrivilege::find($data['id']);
        if ($obj) {
            $obj->resource_id = array_key_exists('resource_id', $data) ? $data['resource_id'] : $obj->resource_id;
            $obj->name = array_key_exists('name', $data) ? $data['name'] : $obj->name;
            $obj->alias = array_key_exists('alias', $data) ? $data['alias'] : $obj->alias;
            $obj->ord = array_key_exists('ord', $data) ? $data['ord'] : $obj->ord;
            $obj->save();
        }
    }

    public static function delete($id)
    {
        if (is_array($id)) {
            AdPrivilege::destroy($id);
        } else {
            $obj = self::find($id);
            if ($obj) {
                $obj->delete();
            }
        }
    }

    public static function getAll()
    {
        return AdPrivilege::all();
    }

    public static function getPaging($param)
    {
        $list = new AdPrivilege();
        if (array_key_exists('keyword', $param)) {
            $list = $list->where('name', 'like', '%' . $param['keyword'] . '%')
                         ->orWhere('alias', 'like', '%' . $param['keyword'] . '%');
        }
        $list = $list->orderBy('id', 'desc');

        return $list->paginate(config('backend.paginationLimit'));
    }

    public static function getAllJoinAdResource()
    {
        return AdPrivilege::select('ad_resource.alias AS resource_alias', 'ad_privilege.alias AS privilege_alias')
            ->join('ad_resource', 'ad_resource.id', '=', 'ad_privilege.resource_id')
            ->get();
    }
}
