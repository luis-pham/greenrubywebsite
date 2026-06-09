<?php
namespace Modules\BackEnd\Services;

use Modules\BackEnd\Entities\AppPageSection;

class AppPageSectionService
{
    public static function find($id)
    {
        return AppPageSection::find($id);
    }

    public static function create($data)
    {
        $obj = new AppPageSection();
        $obj->page_id = array_key_exists('page_id', $data) ? $data['page_id'] : null;
        $obj->name = array_key_exists('name', $data) ? $data['name'] : null;
        $obj->ord = array_key_exists('ord', $data) ? $data['ord'] : null;
        $obj->save();

        return $obj->id;
    }

    public static function update($data)
    {
        $obj = AppPageSection::find($data['id']);
        if ($obj) {
            $obj->page_id = array_key_exists('page_id', $data) ? $data['page_id'] : $obj->page_id;
            $obj->name = array_key_exists('name', $data) ? $data['name'] : $obj->name;
            $obj->ord = array_key_exists('ord', $data) ? $data['ord'] : $obj->ord;
            $obj->save();
        }
    }

    public static function delete($id)
    {
        if (is_array($id)) {
            AppPageSection::destroy($id);
        } else {
            $obj = self::find($id);
            if ($obj) {
                $obj->delete();
            }
        }
    }

    public static function getAll()
    {
        return AppPageSection::all();
    }

    public static function getByPageId($pageId)
    {
        return AppPageSection::where('page_id', $pageId)->orderBy('ord')->get();
    }
}
