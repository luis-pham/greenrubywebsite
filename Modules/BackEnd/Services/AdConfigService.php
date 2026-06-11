<?php
namespace Modules\BackEnd\Services;

use App\Support\HtmlSanitizer;
use Modules\BackEnd\Entities\AdConfig;

class AdConfigService
{
    public static function find($id)
    {
        return AdConfig::find($id);
    }

    public static function create($data)
    {
        $obj = new AdConfig();
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
        $obj = AdConfig::find($data['id']);
        if ($obj) {
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
            AdConfig::destroy($id);
        } else {
            $obj = self::find($id);
            if ($obj) {
                $obj->delete();
            }
        }
    }

    public static function getAll($languageId)
    {
        return AdConfig::where('language_id', $languageId)
            ->orWhere('language_id', null)
            ->orderBy('ord')->get();
    }

    public static function getByKey($key)
    {
        if (is_array($key)) {
            return AdConfig::whereIn('ad_config.key', $key)->get();
        }

        return AdConfig::where('ad_config.key', $key)->first();
    }

    protected static function sanitizeValue($type, $value): ?string
    {
        if ((int) $type !== (int) config('backend.configInput.texteditor')) {
            return $value;
        }

        return HtmlSanitizer::clean($value);
    }
}
