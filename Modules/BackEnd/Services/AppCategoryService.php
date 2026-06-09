<?php
namespace Modules\BackEnd\Services;

use Illuminate\Support\Facades\DB;
use Modules\BackEnd\Entities\AppArticle;
use Modules\BackEnd\Entities\AppCategory;

class AppCategoryService
{
    public static function find($id, $type, $languageId)
    {
        return AppCategory::where('id', $id)
            ->where('type', $type)
            ->where('language_id', $languageId)
            ->first();
    }

    public static function create($data, $type, $languageId)
    {
        DB::beginTransaction();
        try {
            $obj = new AppCategory();
            if (array_key_exists('parent_id', $data) && $data['parent_id']) {
                $obj->parent_id = $data['parent_id'];
            } else {
                $objParent = self::getBySlug('root', $type, $languageId);
                if (!$objParent) {
                    $objParent = new AppCategory();
                    $objParent->language_id = $languageId;
                    $objParent->name = 'Root';
                    $objParent->slug = 'root';
                    $objParent->lvl = 0;
                    $objParent->lft = 0;
                    $objParent->rgt = 1;
                    $objParent->type = $type;
                    $objParent->save();
                }
                $obj->parent_id = $objParent->id;
            }

            $obj->type = $type;
            $obj->language_id = $languageId;

            $objParent = self::find($obj->parent_id, $obj->type, $obj->language_id);

            AppCategory::where('lft', '>', $objParent->rgt)
                ->where('type', $obj->type)
                ->where('language_id', $obj->language_id)
                ->increment('lft', 2);

            AppCategory::where('rgt', '>=', $objParent->rgt)
                ->where('type', $obj->type)
                ->where('language_id', $obj->language_id)
                ->increment('rgt', 2);

            $obj->name = array_key_exists('name', $data) ? $data['name'] : null;
            $obj->slug = array_key_exists('slug', $data) ? $data['slug'] : null;
            $obj->icon = array_key_exists('icon', $data) ? $data['icon'] : null;
            $obj->description = array_key_exists('description', $data) ? $data['description'] : null;
            $obj->seo_title = array_key_exists('seo_title', $data) ? $data['seo_title'] : null;
            $obj->seo_description = array_key_exists('seo_description', $data) ? $data['seo_description'] : null;
            $obj->lft = $objParent->rgt;
            $obj->rgt = $objParent->rgt + 1;
            $obj->lvl = $objParent->lvl + 1;
            $obj->save();
            
            DB::commit();

            return $obj->id;
        } catch (\Exception $e) {
            DB::rollBack();

            throw $e;
        }
    }

    public static function update($data, $type, $languageId)
    {
        DB::beginTransaction();
        try {
            $obj = self::find($data['id'], $type, $languageId);
            if ($obj) {
                $obj->name = array_key_exists('name', $data) ? $data['name'] : $obj->name;
                $obj->slug = array_key_exists('slug', $data) ? $data['slug'] : $obj->slug;
                $obj->icon = array_key_exists('icon', $data) ? $data['icon'] : $obj->icon;
                $obj->description = array_key_exists('description', $data) ? $data['description'] : $obj->description;
                $obj->seo_title = array_key_exists('seo_title', $data) ? $data['seo_title'] : $obj->seo_title;
                $obj->seo_description = array_key_exists('seo_description', $data) ? $data['seo_description'] : $obj->seo_description;
                $obj->save();

                if (array_key_exists('parent_id', $data) && $data['parent_id']) {
                    $parentId = $data['parent_id'];
                } else {
                    $objParent = self::getBySlug('root', $type, $languageId);
                    $parentId = $objParent->id;
                }
                if ($parentId != $obj->parent_id) {
                    $widthNode = $obj->rgt - $obj->lft + 1;

                    AppCategory::whereBetween('lft', [$obj->lft, $obj->rgt])
                        ->where('type', $obj->type)
                        ->where('language_id', $obj->language_id)
                        ->update([
                            'rgt' => DB::raw('rgt - ' . $obj->rgt),
                            'lft' => DB::raw('lft - ' . $obj->lft)
                        ]);
                    
                    AppCategory::where('rgt', '>', $obj->rgt)
                        ->where('type', $obj->type)
                        ->where('language_id', $obj->language_id)
                        ->decrement('rgt', $widthNode);
                    
                    AppCategory::where('lft', '>', $obj->rgt)
                        ->where('type', $obj->type)
                        ->where('language_id', $obj->language_id)
                        ->decrement('lft', $widthNode);
                    
                    $objParent = self::find($parentId, $type, $languageId);
                    
                    AppCategory::where(function ($query) use ($objParent) {
                            $query->where('lft', '>=', $objParent->rgt);
                            $query->where('rgt', '>', 0);
                        })
                        ->where('type', $obj->type)
                        ->where('language_id', $obj->language_id)
                        ->increment('lft', $widthNode);
                    
                    AppCategory::where('rgt', '>=', $objParent->rgt)
                        ->where('type', $obj->type)
                        ->where('language_id', $obj->language_id)
                        ->increment('rgt', $widthNode);
                    
                    $newLvl = $objParent->lvl + 1;
                    
                    AppCategory::where('rgt', '<=', 0)
                        ->where('type', $obj->type)
                        ->where('language_id', $obj->language_id)
                        ->decrement('lvl', $obj->lvl - $newLvl);
                    
                    $newParentId = $parentId;
                    $newLft = $objParent->rgt;
                    $newRgt = $objParent->rgt + $widthNode - 1;
                                    
                    $obj->parent_id = $newParentId;
                    $obj->lft = $newLft;
                    $obj->rgt = $newRgt;
                    $obj->save();
                    
                    AppCategory::where('rgt', '<', 0)
                        ->where('type', $obj->type)
                        ->where('language_id', $obj->language_id)
                        ->update([
                            'lft' => DB::raw('lft + ' . $newLft),
                            'rgt' => DB::raw('rgt + ' . $newRgt)
                        ]);
                }
            }
            
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            throw $e;
        }
    }

    public static function delete($id, $type, $languageId)
    {
        DB::beginTransaction();
        try {
            $listId = [];
            if (is_array($id)) {
                $list = AppCategory::select('id')
                    ->whereIn('id', $id)
                    ->where('type', $type)
                    ->where('language_id', $languageId)
                    ->get();
                for ($i = 0; $i < count($list); $i++) {
                    $obj = self::find($list[$i]->id, $type, $languageId);
                    if ($obj) {
                        $listId = array_merge($listId, self::deleteNested($obj));
                    }
                }
            } else {
                $obj = self::find($id, $type, $languageId);
                if ($obj) {
                    $listId = array_merge($listId, self::deleteNested($obj));
                }
            }

            switch ($type) {
                case config('backend.categoryType.article'):
                    AppArticle::whereIn('category_id', $listId)->where('language_id', $languageId)->update(['category_id' => null]);
                    break;
                default:
                    break;
            }
            
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            throw $e;
        }
    }

    public static function getAll($type, $languageId)
    {
        return AppCategory::where('slug', '!=', 'root')
            ->where('type', $type)
            ->where('language_id', $languageId)
            ->orderBy('lft')
            ->get();
    }

    public static function getPaging($param, $type, $languageId)
    {
        $list = new AppCategory();
        if (array_key_exists('keyword', $param)) {
            $list = $list->where('name', 'like', '%' . $param['keyword'] . '%');
            $list = $list->orWhere('description', 'like', '%' . $param['keyword'] . '%');
        }
        if (array_key_exists('parent_id', $param)) {
            $objParent = AppCategory::where('id', $param['parent_id'])
                ->where('type', $type)
                ->where('language_id', $languageId)
                ->first();
            if ($objParent) {
                $list = $list->where(function ($query) use ($objParent) {
                    $query->where('lft', '>', $objParent->lft);
                    $query->Where('rgt', '<', $objParent->rgt);
                });
            }
        }
        $list = $list->where('slug', '!=', 'root');
        $list = $list->where('type', $type);
        $list = $list->where('language_id', $languageId);
        $list = $list->orderBy('lft');
        if (!(array_key_exists('is_disabled_paginate', $param) && $param['is_disabled_paginate'])) {
            if (!array_key_exists('pageSize', $param)) {
                $param['pageSize'] = config('backend.paginationLimit');
            }
            return $list->paginate($param['pageSize']);
        }

        return $list->get();
    }

    public static function getBySlug($slug, $type, $languageId)
    {
        return AppCategory::where('slug', $slug)
            ->where('type', $type)
            ->where('language_id', $languageId)
            ->first();
    }

    public static function findJoin($id, $type, $languageId)
    {
        return AppCategory::select('app_category.*', DB::raw('app_parent.name AS parent_name'))
            ->leftJoin('app_category AS app_parent', function($join) use($type, $languageId) {
                $join->on('app_parent.id', '=', 'app_category.parent_id');
                $join->where('app_parent.slug', '!=', 'root');
                $join->where('app_parent.type', $type);
                $join->where('app_parent.language_id', $languageId);
            })
            ->where('app_category.type', $type)
            ->where('app_category.language_id', $languageId)
            ->where('app_category.id', $id)
            ->first();
    }

    public static function getChildById($id, $type, $languageId)
    {
        DB::beginTransaction();
        try {
            $list = [];
            $obj = self::find($id, $type, $languageId);
            if ($obj) {
                $list = self::getChildByLftAndRgt($obj->lft, $obj->rgt, $type, $languageId);
            }

            DB::commit();

            return $list;
        } catch (\Exception $e) {
            DB::rollBack();

            throw $e;
        }
    }

    public static function getChildByLftAndRgt($lft, $rgt, $type, $languageId)
    {
        return AppCategory::where('lft', '>', $lft)
            ->where('rgt', '<', $rgt)
            ->where('type', $type)
            ->where('language_id', $languageId)
            ->get();
    }

    public static function moveUp($id, $type, $languageId)
    {
        DB::beginTransaction();
        try {
            // SELECT @PId = [PId], @Lvl = [Lvl], @Lft = [Lft], @Rgt = [Rgt]
            // FROM [Category] WHERE [Id] = @Id
            $obj = self::find($id, $type, $languageId);
            if ($obj) {
                // SELECT TOP(1) @BroId = [Id] FROM [Category]
                // WHERE [Lft] < @Lft AND [PId] = @PId
                // ORDER BY [Lft] DESC
                $objBrother = AppCategory::where('lft', '<', $obj->lft)
                    ->where('parent_id', $obj->parent_id)
                    ->where('type', $obj->type)
                    ->where('language_id', $obj->language_id)
                    ->orderBy('lft', 'DESC')
                    ->first();
                if ($objBrother) {
                    // SET @WidthNode = @Rgt - @Lft + 1
                    $widthNode = $obj->rgt - $obj->lft + 1;
                    
                    // UPDATE [Category] SET [Rgt] = [Rgt] -  @Rgt, [Lft] = [Lft] - @Lft
                    // WHERE [Lft] BETWEEN @Lft AND @Rgt
                    AppCategory::whereBetween('lft', [$obj->lft, $obj->rgt])
                        ->where('type', $obj->type)
                        ->where('language_id', $obj->language_id)
                        ->update([
                            'rgt' => DB::raw('rgt - ' . $obj->rgt),
                            'lft' => DB::raw('lft - ' . $obj->lft)
                        ]);
                    
                    // UPDATE [Category]
                    // SET [Rgt] = [Rgt] - @WidthNode
                    // WHERE [Rgt] > @Rgt
                    AppCategory::where('rgt', '>', $obj->rgt)
                        ->where('type', $obj->type)
                        ->where('language_id', $obj->language_id)
                        ->decrement('rgt', $widthNode);
                    
                    // UPDATE [Category]
                    // SET [Lft] = [Lft] - @WidthNode
                    // WHERE [Lft] > @Rgt
                    AppCategory::where('lft', '>', $obj->rgt)
                        ->where('type', $obj->type)
                        ->where('language_id', $obj->language_id)
                        ->decrement('lft', $widthNode);
                    
                    // SELECT @BroLft = [Lft]
                    // FROM [Category] WHERE [Id] = @BroId
                    $objBrotherLft = AppCategory::select('lft')
                        ->where('id', $objBrother->id)
                        ->where('type', $obj->type)
                        ->where('language_id', $obj->language_id)
                        ->first()['lft'];
                    
                    // UPDATE [Category] SET [Lft] = [Lft] + @WidthNode
                    // WHERE [Lft] >= @BroLft AND [Rgt] > 0
                    AppCategory::where('lft', '>=', $objBrotherLft)
                        ->where('rgt', '>', 0)
                        ->where('type', $obj->type)
                        ->where('language_id', $obj->language_id)
                        ->increment('lft', $widthNode);
                    
                    // UPDATE [Category] SET [Rgt] = [Rgt] + @WidthNode
                    // WHERE [Rgt] >= @BroLft
                    AppCategory::where('rgt', '>=', $objBrotherLft)
                        ->where('type', $obj->type)
                        ->where('language_id', $obj->language_id)
                        ->increment('rgt', $widthNode);
                    
                    // SELECT @PLvl = [Lvl]
                    // FROM [Category] WHERE [Id] = @PId
                    $objParentLvl = AppCategory::select('lvl')
                        ->where('id', $obj->parent_id)
                        ->where('type', $obj->type)
                        ->where('language_id', $obj->language_id)
                        ->first()['lvl'];
                    
                    // SET @NewLvl = @PLvl + 1
                    $newLvl = $objParentLvl + 1;
                    
                    // UPDATE [Category] SET [Lvl] = [Lvl] - @Lvl + @NewLvl
                    // WHERE [Rgt] <= 0
                    AppCategory::where('rgt', '<=', 0)
                        ->where('type', $obj->type)
                        ->where('language_id', $obj->language_id)
                        ->decrement('lvl', $obj->lvl - $newLvl);
                    
                    // SET @NewRgt = @BroLft + @WidthNode - 1;
                    $newRgt = $objBrotherLft + $widthNode - 1;
                    
                    // UPDATE [Category] SET [PId] = @PId, [Lft] = @BroLft, [Rgt] = @NewRgt
                    // WHERE [Id] = @Id
                    $obj->lft = $objBrotherLft;
                    $obj->rgt = $newRgt;
                    $obj->save();
                    
                    // UPDATE [Category] SET [Rgt] = [Rgt] + @NewRgt, [Lft] = [Lft] + @BroLft
                    // WHERE [Rgt] < 0
                    AppCategory::where('rgt', '<', 0)
                        ->where('type', $obj->type)
                        ->where('language_id', $obj->language_id)
                        ->update([
                            'rgt' => DB::raw('rgt + ' . $newRgt),
                            'lft' => DB::raw('lft + ' . $objBrotherLft)
                        ]);
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            throw $e;
        }
    }

    public static function moveDown($id, $type, $languageId)
    {
        DB::beginTransaction();
        try {
            // SELECT @PId = [PId], @Lvl = [Lvl], @Lft = [Lft], @Rgt = [Rgt]
            // FROM [Menu] WHERE [Id] = @Id
            $obj = self::find($id, $type, $languageId);
            if ($obj) {
                // SELECT TOP(1) @BroId = [Id] FROM [Menu]
                // WHERE [Lft] > @Lft AND [PId] = @PId
                // ORDER BY [Lft]
                $objBrother = AppCategory::where('lft', '>', $obj->lft)
                    ->where('parent_id', $obj->parent_id)
                    ->where('type', $obj->type)
                    ->where('language_id', $obj->language_id)
                    ->orderBy('lft')
                    ->first();
                if ($objBrother) {
                    // SET @WidthNode = @Rgt - @Lft + 1
                    $widthNode = $obj->rgt - $obj->lft + 1;
            
                    // UPDATE [Menu] SET [Rgt] = [Rgt] - @Rgt, [Lft] = [Lft] - @Lft
                    // WHERE [Lft] BETWEEN @Lft AND @Rgt
                    AppCategory::whereBetween('lft', [$obj->lft, $obj->rgt])
                        ->where('type', $obj->type)
                        ->where('language_id', $obj->language_id)
                        ->update([
                            'rgt' => DB::raw('rgt - ' . $obj->rgt),
                            'lft' => DB::raw('lft - ' . $obj->lft)
                        ]);
                    
                    // UPDATE [Menu] SET [Rgt] = [Rgt] - @WidthNode
                    // WHERE [Rgt] > @Rgt
                    AppCategory::where('rgt', '>', $obj->rgt)
                        ->where('type', $obj->type)
                        ->where('language_id', $obj->language_id)
                        ->decrement('rgt', $widthNode);
                    
                    // UPDATE [Menu] SET [Lft] = [Lft] - @WidthNode
                    // WHERE [Lft] > @Rgt
                    AppCategory::where('lft', '>', $obj->rgt)
                        ->where('type', $obj->type)
                        ->where('language_id', $obj->language_id)
                        ->decrement('lft', $widthNode);
                    
                    // SELECT @BroRgt = [Rgt]
                    // FROM [Menu] WHERE [Id] = @BroId
                    $objBrotherRgt = AppCategory::select('rgt')
                        ->where('id', $objBrother->id)
                        ->where('type', $obj->type)
                        ->where('language_id', $obj->language_id)
                        ->first()['rgt'];
                    
                    // UPDATE [Menu] SET [Lft] = [Lft] + @WidthNode
                    // WHERE [Lft] > @BroRgt AND [Rgt] > 0
                    AppCategory::where('lft', '>', $objBrotherRgt)
                        ->where('rgt', '>', 0)
                        ->where('type', $obj->type)
                        ->where('language_id', $obj->language_id)
                        ->increment('lft', $widthNode);
                    
                    // UPDATE [Menu] SET [Rgt] = [Rgt] + @WidthNode 
                    // WHERE [Rgt] > @BroRgt
                    AppCategory::where('rgt', '>', $objBrotherRgt)
                        ->where('type', $obj->type)
                        ->where('language_id', $obj->language_id)
                        ->increment('rgt', $widthNode);
                    
                    // SELECT @PLvl = [Lvl]
                    // FROM [Menu] WHERE [Id] = @PId
                    $objParentLvl = AppCategory::select('lvl')
                        ->where('id', $obj->parent_id)
                        ->where('type', $obj->type)
                        ->where('language_id', $obj->language_id)
                        ->first()['lvl'];
                    
                    // SET @NewLvl = @PLvl + 1
                    $newLvl = $objParentLvl + 1;
                    
                    // UPDATE [Menu] SET [Lvl] = [Lvl] - @Lvl + @NewLvl
                    // WHERE [Rgt] <= 0
                    AppCategory::where('rgt', '<=', 0)
                        ->where('type', $obj->type)
                        ->where('language_id', $obj->language_id)
                        ->decrement('lvl', $obj->lvl - $newLvl);
                    
                    // SET @NewLft = @BroRgt + 1
                    // SET @NewRgt = @BroRgt + @WidthNode;
                    $newLft = $objBrotherRgt + 1;
                    $newRgt = $objBrotherRgt + $widthNode;
                            
                    // UPDATE [Menu] SET [PId] = @PId, [Lft] = @NewLft, [Rgt] = @NewRgt 
                    // WHERE [Id] = @Id
                    $obj->lft = $newLft;
                    $obj->rgt = $newRgt;
                    $obj->save();
                    
                    // UPDATE [Menu] SET [Rgt] = [Rgt] + @NewRgt, [Lft] = [Lft] + @NewLft 
                    // WHERE [Rgt] < 0
                    AppCategory::where('rgt', '<', 0)
                        ->where('type', $obj->type)
                        ->where('language_id', $obj->language_id)
                        ->update([
                            'rgt' => DB::raw('rgt + ' . $newRgt),
                            'lft' => DB::raw('lft + ' . $newLft)
                        ]);
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            throw $e;
        }
    }

    private static function deleteNested($obj)
    {
        if (!$obj || $obj->slug == 'root') {
            return;
        }

        $widthNode = $obj->rgt - $obj->lft + 1;

        $listId = AppCategory::select('id')
            ->where('lft', '>=', $obj->lft)
            ->where('rgt', '<=', $obj->rgt)
            ->where('type', $obj->type)
            ->where('language_id', $obj->language_id)
            ->pluck('id')
            ->toArray();

        AppCategory::whereIn('id', $listId)->delete();

        AppCategory::where('rgt', '>', $obj->rgt)
            ->where('type', $obj->type)
            ->where('language_id', $obj->language_id)
            ->decrement('rgt', $widthNode);

        AppCategory::where('lft', '>', $obj->rgt)
            ->where('type', $obj->type)
            ->where('language_id', $obj->language_id)
            ->decrement('lft', $widthNode);

        return $listId;
    }
}
