<?php
namespace Modules\BackEnd\Services;

use Modules\BackEnd\Entities\AppSeoKeyword;

class AppSeoKeywordService
{
    public static function find($id, $languageId)
    {
        return AppSeoKeyword::where('id', $id)->where('language_id', $languageId)->first();
    }

    public static function create($data, $languageId)
    {
        $obj = new AppSeoKeyword();
        $obj->keyword = array_key_exists('keyword', $data) ? $data['keyword'] : null;
        $obj->link = array_key_exists('link', $data) ? $data['link'] : null;
        $obj->description = array_key_exists('description', $data) ? $data['description'] : null;
        $obj->language_id = $languageId;
        $obj->save();

        return $obj->id;
    }

    public static function update($data, $languageId)
    {
        $obj = self::find($data['id'], $languageId);
        if ($obj) {
            $obj->keyword = array_key_exists('keyword', $data) ? $data['keyword'] : $obj->keyword;
            $obj->link = array_key_exists('link', $data) ? $data['link'] : $obj->link;
            $obj->description = array_key_exists('description', $data) ? $data['description'] : $obj->description;
            $obj->save();
        }
    }

    public static function delete($id, $languageId)
    {
        if (is_array($id)) {
            AppSeoKeyword::whereIn('id', $id)->where('language_id', $languageId)->delete();
        } else {
            $obj = self::find($id, $languageId);
            if ($obj) {
                $obj->delete();
            }
        }
    }

    public static function getAll($languageId)
    {
        return AppSeoKeyword::where('language_id', $languageId)->get();
    }

    public static function getPaging($param, $languageId)
    {
        $list = new AppSeoKeyword();
        if (array_key_exists('keyword', $param)) {
            $list = $list->where('keyword', 'like', '%' . $param['keyword'] . '%');
            $list = $list->orWhere('description', 'like', '%' . $param['keyword'] . '%');
        }
        $list = $list->where('language_id', $languageId);
        return $list->paginate(config('backend.paginationLimit'));
    }
}
