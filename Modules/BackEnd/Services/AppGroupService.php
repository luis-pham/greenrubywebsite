<?php
namespace Modules\BackEnd\Services;

use Illuminate\Support\Facades\DB;
use Modules\BackEnd\Entities\AppCabin;
use Modules\BackEnd\Entities\AppExpActivity;
use Modules\BackEnd\Entities\AppFaq;
use Modules\BackEnd\Entities\AppGroup;
use Modules\BackEnd\Entities\AppService;
use Modules\BackEnd\Entities\AppCabinSuitableAudience;
use Modules\BackEnd\Entities\AppExpActivitySuitableAudience;

class AppGroupService
{
    public static function find($id, $type, $languageId)
    {
        return AppGroup::where('id', $id)
            ->where('type', $type)
            ->where('language_id', $languageId)
            ->first();
    }

    public static function create($data, $type, $languageId)
    {
        $obj = new AppGroup();
        $obj->type = $type;
        $obj->language_id = $languageId;
        $obj->name = array_key_exists('name', $data) ? $data['name'] : null;
        $obj->slug = array_key_exists('slug', $data) ? $data['slug'] : null;
        $obj->description = array_key_exists('description', $data) ? $data['description'] : null;
        $obj->tab = array_key_exists('tab', $data) ? $data['tab'] : null;
        $obj->image_link = array_key_exists('image_link', $data) ? $data['image_link'] : null;
        $obj->seo_title = array_key_exists('seo_title', $data) ? $data['seo_title'] : null;
        $obj->seo_description = array_key_exists('seo_description', $data) ? $data['seo_description'] : null;
        $obj->ord = array_key_exists('ord', $data) ? $data['ord'] : null;
        $obj->save();

        return $obj->id;
    }

    public static function update($data, $type, $languageId)
    {
        $obj = self::find($data['id'], $type, $languageId);
        if ($obj) {
            $obj->name = array_key_exists('name', $data) ? $data['name'] : $obj->name;
            $obj->slug = array_key_exists('slug', $data) ? $data['slug'] : $obj->slug;
            $obj->description = array_key_exists('description', $data) ? $data['description'] : $obj->description;
            $obj->tab = array_key_exists('tab', $data) ? $data['tab'] : $obj->tab;
            $obj->image_link = array_key_exists('image_link', $data) ? $data['image_link'] : $obj->image_link;
            $obj->seo_title = array_key_exists('seo_title', $data) ? $data['seo_title'] : $obj->seo_title;
            $obj->seo_description = array_key_exists('seo_description', $data) ? $data['seo_description'] : $obj->seo_description;
            $obj->save();
        }
    }

    public static function delete($id, $type, $languageId)
    {
        DB::beginTransaction();
        try {
            $listId = [];
            if (is_array($id)) {
                AppGroup::whereIn('id', $id)
                    ->where('type', $type)
                    ->where('language_id', $languageId)
                    ->delete();
                $listId = $id;
            } else {
                $obj = self::find($id, $type, $languageId);
                if ($obj) {
                    $obj->delete();
                }
                $listId[] = $id;
            }

            switch ($type) {
                case config('backend.groupType.faq'):
                    AppFaq::whereIn('group_id', $listId)->where('language_id', $languageId)->update(['group_id' => null]);
                    break;
                case config('backend.groupType.cabin'):
                    AppCabin::whereIn('group_id', $listId)->where('language_id', $languageId)->update(['group_id' => null]);
                    break;
                case config('backend.groupType.expActivity'):
                    AppExpActivity::whereIn('group_id', $listId)->where('language_id', $languageId)->update(['group_id' => null]);
                    break;
                case config('backend.groupType.service'):
                    AppService::whereIn('group_id', $listId)->where('language_id', $languageId)->update(['group_id' => null]);
                    break;
                case config('backend.groupType.suitableAudience'):
                    AppCabinSuitableAudience::whereIn('group_id', $listId)->where('language_id', $languageId)->delete();
                    AppExpActivitySuitableAudience::whereIn('group_id', $listId)->where('language_id', $languageId)->delete();
                    break;
                default:
                    break;
            }
            
            DB::commit();

            return $obj->id;
        } catch (\Exception $e) {
            DB::rollBack();

            throw $e;
        }
    }

    public static function getAll($type, $languageId)
    {
        return AppGroup::where('type', $type)
            ->where('language_id', $languageId)
            ->orderBy('ord')
            ->get();
    }

    public static function getPaging($param, $type, $languageId)
    {
        $list = new AppGroup();
        if (array_key_exists('keyword', $param)) {
            $list = $list->where('name', 'like', '%' . $param['keyword'] . '%');
            $list = $list->orWhere('description', 'like', '%' . $param['keyword'] . '%');
        }
        $list = $list->where('type', $type);
        $list = $list->where('language_id', $languageId);
        $list = $list->orderBy('ord');
        if (!(array_key_exists('is_disabled_paginate', $param) && $param['is_disabled_paginate'])) {
            if (!array_key_exists('pageSize', $param)) {
                $param['pageSize'] = config('backend.paginationLimit');
            }
            return $list->paginate($param['pageSize']);
        }

        return $list->get();
    }

    public static function findOrCreate($name, $type, $languageId)
    {
        $group = AppGroup::where('name', $name)
            ->where('type', $type)
            ->where('language_id', $languageId)
            ->first();
        
        if ($group) {
            return $group->id;
        }
        
        // Tạo mới
        $newGroup = new AppGroup();
        $newGroup->language_id = $languageId;
        $newGroup->type = $type;
        $newGroup->name = $name;
        $newGroup->slug = \Str::slug($name);
        $newGroup->ord = 999;
        $newGroup->save();
        
        return $newGroup->id;
    }
    public static function getNextOrder($type, $languageId)
    {
        $ord = AppGroup::where('type', $type)
            ->where('language_id', $languageId)
            ->max('ord');
        return $ord ? $ord + 1 : 1;
    }

    public static function saveOrder($list, $type, $languageId)
    {
        DB::beginTransaction();
        try {
            foreach ($list as $key => $value) {
                $obj = self::find($key, $type, $languageId);
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

    public static function getBySlug($slug, $type, $languageId)
    {
        return AppGroup::where('slug', $slug)
            ->where('type', $type)
            ->where('language_id', $languageId)
            ->first();
    }
}
