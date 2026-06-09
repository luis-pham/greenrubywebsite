<?php
namespace Modules\FrontEnd\Services;

use Illuminate\Support\Facades\DB;
use Modules\BackEnd\Entities\AppArticle;
use Modules\BackEnd\Entities\AppCategory;
use Modules\BackEnd\Services\AppCategoryService;

class AppArticleService
{
    public static function find($id, $languageId)
    {
        return AppArticle::where('id', $id)
            ->where('is_published', true)
            ->where('language_id', $languageId)
            ->first();
    }

    public static function findJoin($id, $languageId)
    {
        return AppArticle::select('app_article.*', DB::raw('app_category.name AS category_name, app_category.slug AS category_slug'))
            ->leftJoin('app_category', 'app_category.id', '=', 'app_article.category_id')
            ->where('app_article.id', $id)
            ->where('is_published', true)
            ->where('app_article.language_id', $languageId)
            ->first();
    }

    public static function getPaging($param, $languageId = null)
    {
        $query = AppArticle::select('app_article.*', DB::raw('app_category.name AS category_name, app_category.slug AS category_slug'));
        $query = $query->leftJoin('app_category', 'app_category.id', '=', 'app_article.category_id');
        if (array_key_exists('keyword', $param)) {
            $query = $query->where(function ($query) use ($param) {
                $query->where('title', 'like', '%' . $param['keyword'] . '%');
                $query->orWhere('sub_title', 'like', '%' . $param['keyword'] . '%');
                $query->orWhere('lead', 'like', '%' . $param['keyword'] . '%');
                $query->orWhere('content', 'like', '%' . $param['keyword'] . '%');
            });
        }
        if (array_key_exists('is_featured', $param)) {
            $query = $query->where('is_featured', $param['is_featured']);
        }
        if (array_key_exists('exclude_id', $param)) {
            if (is_array($param['exclude_id'])) {
                $query = $query->whereNotIn('app_article.id', $param['exclude_id']);
            } else {
                $query = $query->where('app_article.id', '!=', $param['exclude_id']);
            }
        }
        $query = $query->where('is_published', true);
        if ($languageId) {
            $query = $query->where('app_article.language_id', $languageId);
        }

        $includeChild = array_key_exists('include_child', $param) && $param['include_child'];
        if (!$includeChild) {
            if (array_key_exists('category_id', $param)) {
                if (!is_array($param['category_id'])) {
                    $query = $query->where('category_id', $param['category_id']);
                } else {
                    $query = $query->whereIn('category_id', $param['category_id']);
                }
            }
            $query = $query->orderBy('publish_date', 'desc');
            
            if (!(array_key_exists('is_disabled_paginate', $param) && $param['is_disabled_paginate'])) {
                if (!array_key_exists('page', $param)) {
                    $param['page'] = 1;
                }
                if (!array_key_exists('pageSize', $param)) {
                    $param['pageSize'] = config('frontend.paginationLimit');
                }
                $offset = ($param['page'] - 1) * $param['pageSize'];
                $query = $query->skip($offset)->take($param['pageSize']);
            }
            
            return $query->get();
        }

        $list = [];

        DB::beginTransaction();
        try {
            if (array_key_exists('category_id', $param)) {
                $listCategoryId = [];

                $type = config('backend.categoryType.article');

                $queryCategory = new AppCategory();
                if (!is_array($param['category_id'])) {
                    $queryCategory = $queryCategory->where('id', $param['category_id']);
                } else {
                    $queryCategory = $queryCategory->whereIn('id', $param['category_id']);
                }
                $queryCategory = $queryCategory->where('type', $type);
                $queryCategory = $queryCategory->where('language_id', $languageId);
                $listCategory = $queryCategory->get();
                for ($i = 0; $i < count($listCategory); $i++) {
                    $listCategoryChildId = AppCategoryService::getChildByLftAndRgt($listCategory[$i]->lft, $listCategory[$i]->rgt, $type, $languageId)->pluck('id')->toArray();
                    $listCategoryId[] = $listCategory[$i]->id;
                    $listCategoryId = array_merge($listCategoryId, $listCategoryChildId);
                }

                $listCategoryId = array_unique($listCategoryId);
                if (count($listCategoryId) > 0) {
                    $query = $query->whereIn('category_id', $listCategoryId);
                }
            }
            
            $query = $query->orderBy('publish_date', 'desc');
            
            if (!(array_key_exists('is_disabled_paginate', $param) && $param['is_disabled_paginate'])) {
                if (!array_key_exists('page', $param)) {
                    $param['page'] = 1;
                }
                if (!array_key_exists('pageSize', $param)) {
                    $param['pageSize'] = config('frontend.paginationLimit');
                }
                $offset = ($param['page'] - 1) * $param['pageSize'];
                $query = $query->skip($offset)->take($param['pageSize']);
            }
            
            $list = $query->get();
            
            DB::commit();

            return $list;
        } catch (\Exception $e) {
            DB::rollBack();
        }

        return $list;
    }

    public static function getPagingCount($param, $languageId = null)
    {
        $query = new AppArticle();
        if (array_key_exists('keyword', $param)) {
            $query = $query->where(function ($query) use ($param) {
                $query->where('title', 'like', '%' . $param['keyword'] . '%');
                $query->orWhere('lead', 'like', '%' . $param['keyword'] . '%');
                $query->orWhere('content', 'like', '%' . $param['keyword'] . '%');
            });
        }
        if (array_key_exists('is_featured', $param)) {
            $query = $query->where('is_featured', $param['is_featured']);
        }
        if (array_key_exists('exclude_id', $param)) {
            if (is_array($param['exclude_id'])) {
                $query = $query->whereNotIn('app_article.id', $param['exclude_id']);
            } else {
                $query = $query->where('app_article.id', '!=', $param['exclude_id']);
            }
        }
        $query = $query->where('is_published', true);
        if ($languageId) {
            $query = $query->where('app_article.language_id', $languageId);
        }

        $includeChild = array_key_exists('include_child', $param) && $param['include_child'];
        if (!$includeChild) {
            if (array_key_exists('category_id', $param)) {
                if (!is_array($param['category_id'])) {
                    $query = $query->where('category_id', $param['category_id']);
                } else {
                    $query = $query->whereIn('category_id', $param['category_id']);
                }
            }
        } else {
            if (array_key_exists('category_id', $param)) {
                $listCategoryId = [];

                $type = config('backend.categoryType.article');

                $queryCategory = new AppCategory();
                if (!is_array($param['category_id'])) {
                    $queryCategory = $queryCategory->where('id', $param['category_id']);
                } else {
                    $queryCategory = $queryCategory->whereIn('id', $param['category_id']);
                }
                $queryCategory = $queryCategory->where('type', $type);
                $queryCategory = $queryCategory->where('language_id', $languageId);
                $listCategory = $queryCategory->get();
                for ($i = 0; $i < count($listCategory); $i++) {
                    $listCategoryChildId = AppCategoryService::getChildByLftAndRgt($listCategory[$i]->lft, $listCategory[$i]->rgt, $type, $languageId)->pluck('id')->toArray();
                    $listCategoryId[] = $listCategory[$i]->id;
                    $listCategoryId = array_merge($listCategoryId, $listCategoryChildId);
                }

                $listCategoryId = array_unique($listCategoryId);
                if (count($listCategoryId) > 0) {
                    $query = $query->whereIn('category_id', $listCategoryId);
                }
            }
        }

        return $query->count();
    }

    public static function getLatestUpdate($param, $languageId = null)
    {
        $query = AppArticle::select('app_article.*', DB::raw('IFNULL(app_article.updated_at, app_article.created_at) AS `lastmod`'));
        $query = $query->where('is_published', true);
        if (array_key_exists('category_id', $param)) {
            $query = $query->where('category_id', $param['category_id']);
        }
        if ($languageId) {
            $query = $query->where('app_article.language_id', $languageId);
        }
        $query = $query->orderBy('updated_at', 'desc');
        return $query->first();
    }
}
