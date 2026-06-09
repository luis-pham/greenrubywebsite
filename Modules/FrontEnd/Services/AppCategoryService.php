<?php
namespace Modules\FrontEnd\Services;

use Illuminate\Support\Facades\DB;
use Modules\BackEnd\Entities\AppCategory;
use Modules\BackEnd\Services\AppCategoryService As BeAppCategoryService;

class AppCategoryService
{
    public static function getAll($type, $languageId = null)
    {
        $query = AppCategory::where('slug', '!=', 'root');
        $query = $query->where('type', $type);
        if ($languageId) {
            $query = $query->where('language_id', $languageId);
        }
        $query = $query->orderBy('lft');
        return $query->get();
    }
    
    public static function getByLevel($level, $type, $languageId)
    {
        return AppCategory::where('lvl', $level)
            ->where('type', $type)
            ->where('language_id', $languageId)
            ->orderBy('lft')
            ->get();
    }

    public static function getByParentId($parentId, $type, $languageId)
    {
        return AppCategory::where('parent_id', $parentId)
            ->where('type', $type)
            ->where('language_id', $languageId)
            ->orderBy('lft')
            ->get();
    }

    public static function getParent($id, $type, $languageId)
    {
        DB::beginTransaction();
        try {
            $obj = BeAppCategoryService::find($id, $type, $languageId);
            if ($obj) {
                return AppCategory::where('lvl', '>', 0)
                    ->where('lft', '<=', $obj->lft)
                    ->where('rgt', '>=', $obj->rgt)
                    ->where('type', $type)
                    ->where('language_id', $languageId)
                    ->orderBy('lft')
                    ->get();
            }
            
            DB::commit();

            return null;
        } catch (\Exception $e) {
            DB::rollBack();

            return null;
        }
    }

    public static function getParentByLevel($id, $level, $type, $languageId)
    {
        DB::beginTransaction();
        try {
            $obj = BeAppCategoryService::find($id, $type, $languageId);
            if ($obj) {
                return AppCategory::where('lvl', $level)
                    ->where('lft', '<=', $obj->lft)
                    ->where('rgt', '>=', $obj->rgt)
                    ->where('type', $type)
                    ->where('language_id', $languageId)
                    ->orderBy('lft')
                    ->first();
            }
            
            DB::commit();

            return null;
        } catch (\Exception $e) {
            DB::rollBack();

            return null;
        }
    }
}
