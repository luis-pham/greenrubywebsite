<?php
namespace Modules\BackEnd\Services;

use Modules\BackEnd\Entities\AdLanguage;

class AdLanguageService
{
    public static function find($id)
    {
        return AdLanguage::find($id);
    }

    public static function findByCode($code)
    {
        return AdLanguage::where('code', $code)->first();
    }

    public static function create($data)
    {
        $obj = new AdLanguage();
        $obj->code = array_key_exists('code', $data) ? $data['code'] : null;
        $obj->name = array_key_exists('name', $data) ? $data['name'] : null;
        $obj->short_name = array_key_exists('short_name', $data) ? $data['short_name'] : null;
        $obj->image_link = array_key_exists('image_link', $data) ? $data['image_link'] : null;
        $obj->currency_format = array_key_exists('currency_format', $data) ? $data['currency_format'] : null;
        $obj->is_default = array_key_exists('is_default', $data) ? $data['is_default'] : null;
        $obj->ord = array_key_exists('ord', $data) ? $data['ord'] : null;
        $obj->save();

        return $obj->id;
    }

    public static function update($data)
    {
        $obj = AdLanguage::find($data['id']);
        if ($obj) {
            $obj->code = array_key_exists('code', $data) ? $data['code'] : $obj->code;
            $obj->name = array_key_exists('name', $data) ? $data['name'] : $obj->name;
            $obj->short_name = array_key_exists('short_name', $data) ? $data['short_name'] : $obj->short_name;
            $obj->image_link = array_key_exists('image_link', $data) ? $data['image_link'] : $obj->image_link;
            $obj->currency_format = array_key_exists('currency_format', $data) ? $data['currency_format'] : $obj->currency_format;
            $obj->is_default = array_key_exists('is_default', $data) ? $data['is_default'] : $obj->is_default;
            $obj->ord = array_key_exists('ord', $data) ? $data['ord'] : $obj->ord;
            $obj->save();
        }
    }

    public static function delete($id)
    {
        if (is_array($id)) {
            AdLanguage::destroy($id);
        } else {
            $obj = self::find($id);
            if ($obj) {
                $obj->delete();
            }
        }
    }

    public static function getAll()
    {
        return AdLanguage::orderBy('ord', 'asc')->get();
    }

    public static function getDefaultLanguage()
    {
        return AdLanguage::where('is_default', true)->first();
    }
}
