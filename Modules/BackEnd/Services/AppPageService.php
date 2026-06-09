<?php
namespace Modules\BackEnd\Services;

use Modules\BackEnd\Entities\AppPage;

class AppPageService
{
    public static function find($id, $languageId)
    {
        return AppPage::where('id', $id)->where('language_id', $languageId)->first();
    }

    public static function create($data, $languageId)
    {
        $obj = new AppPage();
        $obj->language_id = $languageId;
        $obj->code = array_key_exists('code', $data) ? $data['code'] : null;
        $obj->title = array_key_exists('title', $data) ? $data['title'] : null;
        $obj->description = array_key_exists('description', $data) ? $data['description'] : null;
        $obj->seo_title = array_key_exists('seo_title', $data) ? $data['seo_title'] : null;
        $obj->seo_description = array_key_exists('seo_description', $data) ? $data['seo_description'] : null;
        $obj->save();

        return $obj->id;
    }

    public static function update($data, $languageId)
    {
        $obj = self::find($data['id'], $languageId);
        if ($obj) {
            $obj->code = array_key_exists('code', $data) ? $data['code'] : $obj->code;
            $obj->title = array_key_exists('title', $data) ? $data['title'] : $obj->title;
            $obj->description = array_key_exists('description', $data) ? $data['description'] : $obj->description;
            $obj->seo_title = array_key_exists('seo_title', $data) ? $data['seo_title'] : $obj->seo_title;
            $obj->seo_description = array_key_exists('seo_description', $data) ? $data['seo_description'] : $obj->seo_description;
            $obj->save();
        }
    }

    public static function delete($id, $languageId)
    {
        if (is_array($id)) {
            AppPage::whereIn('id', $id)->where('language_id', $languageId)->delete();
        } else {
            $obj = self::find($id, $languageId);
            if ($obj) {
                $obj->delete();
            }
        }
    }

    public static function getAll($languageId)
    {
        return AppPage::where('language_id', $languageId)->get();
    }

    public static function getPaging($param, $languageId)
    {
        $list = new AppPage();
        if (array_key_exists('keyword', $param)) {
            $list = $list->where(function ($query) use ($param) {
                $query->where('code', 'like', '%' . $param['keyword'] . '%');
                $query->orWhere('title', 'like', '%' . $param['keyword'] . '%');
                $query->orWhere('description', 'like', '%' . $param['keyword'] . '%');
            });
        }
        $list = $list->where('language_id', $languageId);
        $list = $list->orderBy('id', 'desc');
        return $list->paginate(config('backend.paginationLimit'));
    }
    
    public static function getByCode($code, $languageId)
    {
        return AppPage::where('code', $code)
            ->where('language_id', $languageId)
            ->first();
    }
}
