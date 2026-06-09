<?php
namespace Modules\FrontEnd\Services;

use Illuminate\Support\Facades\DB;
use Modules\BackEnd\Entities\AppFaq;

class AppFaqService
{
    public static function getPaging($param, $languageId)
    {
        $query = new AppFaq();
        $query = $query->select('app_faq.*', DB::raw('app_group.name AS group_name'));
        $query = $query->leftJoin('app_group', function($join) use ($languageId) {
            $join->on('app_group.id', '=', 'app_faq.group_id');
            $join->where('app_group.type', config('backend.groupType.faq'));
            $join->where('app_group.language_id', $languageId);
        });
        if (array_key_exists('keyword', $param)) {
            $query = $query->where(function ($query) use ($param) {
                $query->where('question', 'like', '%' . $param['keyword'] . '%');
                $query->orWhere('answer', 'like', '%' . $param['keyword'] . '%');
            });
        }
        if (array_key_exists('exclude_id', $param)) {
            if (is_array($param['exclude_id'])) {
                $query = $query->whereNotIn('app_faq.id', $param['exclude_id']);
            } else {
                $query = $query->where('app_faq.id', '!=', $param['exclude_id']);
            }
        }
        if (array_key_exists('group_id', $param)) {
            if (!is_array($param['group_id'])) {
                $query = $query->where('group_id', $param['group_id']);
            } else {
                $query = $query->whereIn('group_id', $param['group_id']);
            }
        }
        $query = $query->where('app_faq.language_id', $languageId);
        $query = $query->orderBy('app_faq.ord');
        
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

    public static function getPagingCount($param, $languageId)
    {
        $query = new AppFaq();
        if (array_key_exists('keyword', $param)) {
            $query = $query->where(function ($query) use ($param) {
                $query->where('question', 'like', '%' . $param['keyword'] . '%');
                $query->orWhere('answer', 'like', '%' . $param['keyword'] . '%');
            });
        }
        if (array_key_exists('exclude_id', $param)) {
            if (is_array($param['exclude_id'])) {
                $query = $query->whereNotIn('id', $param['exclude_id']);
            } else {
                $query = $query->where('id', '!=', $param['exclude_id']);
            }
        }
        if (array_key_exists('group_id', $param)) {
            if (!is_array($param['group_id'])) {
                $query = $query->where('group_id', $param['group_id']);
            } else {
                $query = $query->whereIn('group_id', $param['group_id']);
            }
        }
        $query = $query->where('language_id', $languageId);
        
        return $query->count();
    }

    public static function getLatestUpdate($languageId = null)
    {
        $query = AppFaq::select('app_faq.*', DB::raw('IFNULL(app_faq.updated_at, app_faq.created_at) AS `lastmod`'));

        if ($languageId) {
            $query = $query->where('app_faq.language_id', $languageId);
        }
        $query = $query->orderBy('updated_at', 'desc');
        return $query->first();
    }
}