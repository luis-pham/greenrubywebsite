<?php
namespace Modules\BackEnd\Services;

use Illuminate\Support\Facades\DB;
use Modules\BackEnd\Entities\AppArticle;

class AppArticleService
{
    public static function find($id, $languageId)
    {
        return AppArticle::where('id', $id)
            ->where('language_id', $languageId)
            ->first();
    }

    public static function create($data, $languageId)
    {
        $obj = new AppArticle();
        $obj->language_id = $languageId;
        $obj->category_id = array_key_exists('category_id', $data) ? $data['category_id'] : null;
        $obj->title = array_key_exists('title', $data) ? $data['title'] : null;
        $obj->sub_title = array_key_exists('sub_title', $data) ? $data['sub_title'] : null;
        $obj->lead = array_key_exists('lead', $data) ? \App\Support\HtmlSanitizer::clean($data['lead']) : null;
        $obj->content = array_key_exists('content', $data) ? \App\Support\HtmlSanitizer::clean($data['content']) : null;
        $obj->publish_date = array_key_exists('publish_date', $data) ? $data['publish_date'] : null;
        $obj->image_link = array_key_exists('image_link', $data) ? $data['image_link'] : null;
        $obj->is_featured = array_key_exists('is_featured', $data) ? $data['is_featured'] : null;
        $obj->seo_title = array_key_exists('seo_title', $data) ? $data['seo_title'] : null;
        $obj->seo_description = array_key_exists('seo_description', $data) ? $data['seo_description'] : null;
        $obj->is_published = array_key_exists('is_published', $data) ? $data['is_published'] : 0;
        $obj->save();

        return $obj->id;
    }

    public static function update($data, $languageId)
    {
        $obj = self::find($data['id'], $languageId);
        if ($obj) {
            $obj->category_id = array_key_exists('category_id', $data) ? $data['category_id'] : $obj->category_id;
            $obj->title = array_key_exists('title', $data) ? $data['title'] : $obj->title;
            $obj->sub_title = array_key_exists('sub_title', $data) ? $data['sub_title'] : $obj->sub_title;
            $obj->lead = array_key_exists('lead', $data) ? \App\Support\HtmlSanitizer::clean($data['lead']) : $obj->lead;
            $obj->content = array_key_exists('content', $data) ? \App\Support\HtmlSanitizer::clean($data['content']) : $obj->content;
            $obj->publish_date = array_key_exists('publish_date', $data) ? $data['publish_date'] : $obj->publish_date;
            $obj->image_link = array_key_exists('image_link', $data) ? $data['image_link'] : $obj->image_link;
            $obj->is_featured = array_key_exists('is_featured', $data) ? $data['is_featured'] : $obj->is_featured;
            $obj->seo_title = array_key_exists('seo_title', $data) ? $data['seo_title'] : $obj->seo_title;
            $obj->seo_description = array_key_exists('seo_description', $data) ? $data['seo_description'] : $obj->seo_description;
            $obj->is_published = array_key_exists('is_published', $data) ? $data['is_published'] : $obj->is_published;
            $obj->save();
        }
    }

    public static function delete($id, $languageId)
    {
        if (is_array($id)) {
            AppArticle::whereIn('id', $id)
                ->where('language_id', $languageId)
                ->delete();
        } else {
            $obj = self::find($id, $languageId);
            if ($obj) {
                $obj->delete();
            }
        }
    }

    public static function getAll($languageId)
    {
        return AppArticle::where('language_id', $languageId)->get();
    }

    public static function findJoin($id, $languageId)
    {
        return AppArticle::select('app_article.*', DB::raw('app_category.name AS category_name'))
            ->leftJoin('app_category', function($join) use ($languageId) {
                $join->on('app_category.id', '=', 'app_article.category_id');
                $join->where('app_category.slug', '!=', 'root');
                $join->where('app_category.type', config('backend.categoryType.article'));
                $join->where('app_category.language_id', $languageId);
            })
            ->where('app_article.id', $id)
            ->where('app_article.language_id', $languageId)
            ->first();
    }

    public static function getPaging($param, $languageId)
    {
        $list = new AppArticle();
        $list = $list->select('app_article.*', DB::raw('app_category.name AS category_name, ad_user.fullname AS created_by_fullname'));
        $list = $list->leftJoin('app_category', function($join) use ($languageId) {
            $join->on('app_category.id', '=', 'app_article.category_id');
            $join->where('app_category.slug', '!=', 'root');
            $join->where('app_category.type', config('backend.categoryType.article'));
            $join->where('app_category.language_id', $languageId);
        });
        $list = $list->leftJoin('ad_user', 'ad_user.id', '=', 'app_article.created_by');
        if (array_key_exists('keyword', $param)) {
            $list = $list->where(function ($query) use ($param) {
                $query->where('title', 'like', '%' . $param['keyword'] . '%');
                $query->orWhere('sub_title', 'like', '%' . $param['keyword'] . '%');
                $query->orWhere('lead', 'like', '%' . $param['keyword'] . '%');
                $query->orWhere('content', 'like', '%' . $param['keyword'] . '%');
            });
        }
        if (array_key_exists('category_id', $param)) {
            $list = $list->where('category_id', $param['category_id']);
        }
        if (array_key_exists('is_published', $param)) {
            $list = $list->where('app_article.is_published', $param['is_published']);
        }
        if (array_key_exists('is_featured', $param)) {
            $list = $list->where('app_article.is_featured', $param['is_featured']);
        }
        $list = $list->where('app_article.language_id', $languageId);
        $list = $list->orderBy('publish_date', 'desc');
        return $list->paginate(config('backend.paginationLimit'));
    }
}
