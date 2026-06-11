<?php
namespace Modules\BackEnd\Services;

use App\Support\HtmlSanitizer;
use Modules\BackEnd\Entities\AppPageConfig;

class AppPageConfigService
{
    public static function find($id)
    {
        return AppPageConfig::find($id);
    }

    public static function create($data)
    {
        $obj = new AppPageConfig();
        $obj->page_id = array_key_exists('page_id', $data) ? $data['page_id'] : null;
        $obj->section_id = array_key_exists('section_id', $data) ? $data['section_id'] : null;
        $obj->label = array_key_exists('label', $data) ? $data['label'] : null;
        $obj->key = array_key_exists('key', $data) ? $data['key'] : null;
        $obj->value = array_key_exists('value', $data) ? self::sanitizeValue($data['type'] ?? null, $data['value']) : null;
        $obj->list_value = array_key_exists('list_value', $data) ? $data['list_value'] : null;
        $obj->type = array_key_exists('type', $data) ? $data['type'] : null;
        $obj->ord = array_key_exists('ord', $data) ? $data['ord'] : null;
        $obj->save();

        return $obj->id;
    }

    public static function update($data)
    {
        $obj = AppPageConfig::find($data['id']);
        if ($obj) {
            $obj->page_id = array_key_exists('page_id', $data) ? $data['page_id'] : $obj->page_id;
            $obj->section_id = array_key_exists('section_id', $data) ? $data['section_id'] : $obj->section_id;
            $obj->label = array_key_exists('label', $data) ? $data['label'] : $obj->label;
            $obj->key = array_key_exists('key', $data) ? $data['key'] : $obj->key;
            $obj->value = array_key_exists('value', $data)
                ? self::sanitizeValue($obj->type, $data['value'])
                : $obj->value;
            $obj->list_value = array_key_exists('list_value', $data) ? $data['list_value'] : $obj->list_value;
            $obj->type = array_key_exists('type', $data) ? $data['type'] : $obj->type;
            $obj->ord = array_key_exists('ord', $data) ? $data['ord'] : $obj->ord;
            $obj->save();
        }
    }

    public static function delete($id)
    {
        if (is_array($id)) {
            AppPageConfig::destroy($id);
        } else {
            $obj = self::find($id);
            if ($obj) {
                $obj->delete();
            }
        }
    }

    public static function getAll()
    {
        return AppPageConfig::all();
    }

    public static function getByPageId($pageId)
    {
        return AppPageConfig::where('page_id', $pageId)->orderBy('ord')->get();
    }

    public static function getByPageIdAndKey($pageId, $key)
    {
        $list = AppPageConfig::where('page_id', $pageId);
        if (is_array($key)) {
            $list = $list->whereIn('key', $key)->get();
        } else {
            $list = $list->where('key', $key)->first();
        }
        return $list;
    }

    protected static function sanitizeValue($type, $value): ?string
    {
        if ((int) $type !== (int) config('backend.configInput.texteditor')) {
            return $value;
        }

        return HtmlSanitizer::clean($value);
    }
}
