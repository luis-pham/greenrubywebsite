<?php

namespace Modules\BackEnd\Services;

use Illuminate\Support\Facades\DB;
use Modules\BackEnd\Entities\AppArticle;
use Modules\BackEnd\Entities\AppAmenity;
use Modules\BackEnd\Entities\AppCabin;
use Modules\BackEnd\Entities\AppCabinPrice;
use Modules\BackEnd\Entities\AppCruise;
use Modules\BackEnd\Entities\AppExpActivity;
use Modules\BackEnd\Entities\AppFaq;
use Modules\BackEnd\Entities\AppItinerary;
use Modules\BackEnd\Entities\AppService;
use Modules\BackEnd\Services\AppArticleService;
use Modules\BackEnd\Services\AppAmenityService;
use Modules\BackEnd\Services\AppCabinService;
use Modules\BackEnd\Services\AppCruiseService;
use Modules\BackEnd\Services\AppExpActivityService;
use Modules\BackEnd\Services\AppFaqService;
use Modules\BackEnd\Services\AppServiceService;

class SourceDataService
{
    public static function getArticlePaging($param, $languageId)
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
        if (array_key_exists('exclude_id', $param)) {
            if (is_array($param['exclude_id'])) {
                $list = $list->whereNotIn('app_article.id', $param['exclude_id']);
            } else {
                $list = $list->where('app_article.id', '!=', $param['exclude_id']);
            }
        }
        $list = $list->where('app_article.is_published', true);
        $list = $list->where('app_article.language_id', $languageId);
        $list = $list->orderBy('publish_date', 'desc');
        return $list->paginate(config('backend.paginationLimit'));
    }

    public static function getArticleById($id, $languageId)
    {
        $list = new AppArticle();
        $list = $list->select('app_article.*', DB::raw('app_category.name AS category_name, app_category.slug AS category_slug'));
        $list = $list->leftJoin('app_category', function($join) use ($languageId) {
            $join->on('app_category.id', '=', 'app_article.category_id');
            $join->where('app_category.slug', '!=', 'root');
            $join->where('app_category.type', config('backend.categoryType.article'));
            $join->where('app_category.language_id', $languageId);
        });
        $list = $list->where('app_article.language_id', $languageId);
        $list = $list->where('app_article.is_published', true);

        if (is_array($id)) {
            return $list->whereIn('app_article.id', $id)->get();
        } else {
            return $list->where('app_article.id', $id)->first();
        }
    }

    public static function getFaqPaging($param, $languageId)
    {
        $list = new AppFaq();
        $list = $list->select('app_faq.*', DB::raw('app_group.id AS group_id, app_group.name AS group_name'));
        $list = $list->leftJoin('app_group', function($join) use ($languageId) {
            $join->on('app_group.id', '=', 'app_faq.group_id');
            $join->where('app_group.type', config('backend.groupType.faq'));
            $join->where('app_group.language_id', $languageId);
        });
        if (array_key_exists('keyword', $param)) {
            $list = $list->where('question', 'like', '%' . $param['keyword'] . '%');
            $list = $list->orWhere('answer', 'like', '%' . $param['keyword'] . '%');
        }
        if (array_key_exists('group_id', $param)) {
            $list = $list->where('group_id', $param['group_id']);
        }
        if (array_key_exists('exclude_id', $param)) {
            if (is_array($param['exclude_id'])) {
                $list = $list->whereNotIn('app_faq.id', $param['exclude_id']);
            } else {
                $list = $list->where('app_faq.id', '!=', $param['exclude_id']);
            }
        }
        $list = $list->where('app_faq.language_id', $languageId);
        $list = $list->orderBy('ord');
        return $list->paginate(config('backend.paginationLimit'));
    }

    public static function getFaqById($id, $languageId)
    {
        if (is_array($id)) {
            $list = AppFaq::select('app_faq.*', DB::raw('app_group.name AS group_name'));
            $list = $list->leftJoin('app_group', function($join) use ($languageId) {
                $join->on('app_group.id', '=', 'app_faq.group_id');
                $join->where('app_group.type', config('backend.groupType.faq'));
                $join->where('app_group.language_id', $languageId);
            });
            $list = $list->whereIn('app_faq.id', $id);
            $list = $list->where('app_faq.language_id', $languageId);
            $list = $list->orderBy('ord');
            return $list->get();
        } else {
            return AppFaqService::findJoin($id, $languageId);
        }
    }
    
    public static function getCruiseItineraryPaging($param, $languageId)
    {
        $list = new AppItinerary();
        $list = $list->select('app_itinerary.*', 'app_cruise_itinerary.cruise_id', 'app_cruise_itinerary.itinerary_id', DB::raw('app_cruise.name AS cruise_name'))->distinct();
        $list = $list->join('app_cruise_itinerary', 'app_cruise_itinerary.itinerary_id', '=', 'app_itinerary.id');
        $list = $list->join('app_cruise', 'app_cruise.id', '=', 'app_cruise_itinerary.cruise_id');
        if (array_key_exists('keyword', $param)) {
            $list = $list->where('app_itinerary.name', 'like', '%' . $param['keyword'] . '%');
            $list = $list->orWhere('app_itinerary.description', 'like', '%' . $param['keyword'] . '%');
        }
        if (array_key_exists('duration', $param)) {
            $list = $list->where('duration', $param['duration']);
        }
        if (array_key_exists('exclude_id', $param)) {
            if (is_array($param['exclude_id'])) {
                $list = $list->whereNotIn(DB::raw('CONCAT(app_itinerary.id, \'-\', app_cruise.id)'), $param['exclude_id']);
            } else {
                $list = $list->where(DB::raw('CONCAT(app_itinerary.id, \'-\', app_cruise.id)'), '!=', $param['exclude_id']);
            }
        }
        $list = $list->where('app_itinerary.language_id', $languageId);
        $list = $list->orderBy('app_itinerary.id', 'desc');
        return $list->paginate(config('backend.paginationLimit'));
    }
    
    public static function getCruiseItineraryById($id, $languageId)
    {
        $list = new AppItinerary();
        $list = $list->select('app_itinerary.*', DB::raw('app_cruise.id AS cruise_id, app_cruise.name AS cruise_name'));
        $list = $list->join('app_cruise_itinerary', 'app_cruise_itinerary.itinerary_id', '=', 'app_itinerary.id');
        $list = $list->join('app_cruise', 'app_cruise.id', '=', 'app_cruise_itinerary.cruise_id');
        $list = $list->where('app_itinerary.language_id', $languageId);
        if (is_array($id)) {
            return $list->whereIn(DB::raw('CONCAT(app_itinerary.id, \'-\', app_cruise.id)'), $id)->get();
        } else {
            return $list->where(DB::raw('CONCAT(app_itinerary.id, \'-\', app_cruise.id)'), $id)->first();
        }
    }

    public static function getCruisePaging($param, $languageId)
    {
        $list = new AppCruise();
        if (array_key_exists('keyword', $param)) {
            $list = $list->where(function ($query) use ($param) {
                $query->where('name', 'like', '%' . $param['keyword'] . '%');
                $query->orWhere('summary', 'like', '%' . $param['keyword'] . '%');
                $query->orWhere('content', 'like', '%' . $param['keyword'] . '%');
            });
        }
        if (array_key_exists('exclude_id', $param)) {
            if (is_array($param['exclude_id'])) {
                $list = $list->whereNotIn('id', $param['exclude_id']);
            } else {
                $list = $list->where('id', '!=', $param['exclude_id']);
            }
        }
        $list = $list->where('language_id', $languageId);
        $list = $list->orderBy('id', 'desc');
        return $list->paginate(config('backend.paginationLimit'));
    }

    public static function getCruiseById($id, $languageId)
    {
        if (is_array($id)) {
            return AppCruise::whereIn('id', $id)
                ->where('language_id', $languageId)
                ->get();
        } else {
            return AppCruiseService::find($id, $languageId);
        }
    }

    public static function getCabinPaging($param, $languageId)
    {
        $list = new AppCabin();
        $list = $list->select('app_cabin.*', DB::raw('app_group.id AS group_id, app_group.name AS group_name, app_cruise.id AS cruise_id, app_cruise.name AS cruise_name'));
        $list = $list->join('app_cruise', 'app_cruise.id', '=', 'app_cabin.cruise_id');
        $list = $list->leftJoin('app_group', function($join) use ($languageId) {
            $join->on('app_group.id', '=', 'app_cabin.group_id');
            $join->where('app_group.type', config('backend.groupType.cabin'));
            $join->where('app_group.language_id', $languageId);
        });
        if (array_key_exists('keyword', $param)) {
            $list = $list->where(function ($query) use ($param) {
                $query->where('app_cabin.name', 'like', '%' . $param['keyword'] . '%');
                $query->orWhere('app_cabin.summary', 'like', '%' . $param['keyword'] . '%');
                $query->orWhere('app_cabin.content', 'like', '%' . $param['keyword'] . '%');
            });
        }
        if (array_key_exists('cruise_id', $param)) {
            $list = $list->where('cruise_id', $param['cruise_id']);
        }
        if (array_key_exists('exclude_id', $param)) {
            if (is_array($param['exclude_id'])) {
                $list = $list->whereNotIn('app_cabin.id', $param['exclude_id']);
            } else {
                $list = $list->where('app_cabin.id', '!=', $param['exclude_id']);
            }
        }
        $list = $list->where('app_cabin.language_id', $languageId);
        $list = $list->orderBy('app_cabin.id', 'desc');
        return $list->paginate(config('backend.paginationLimit'));
    }

    public static function getCabinById($id, $languageId)
    {
        $list = new AppCabin();
        $list = $list->select('app_cabin.*', DB::raw('app_cruise.name AS cruise_name'));
        $list = $list->join('app_cruise', 'app_cruise.id', '=', 'app_cabin.cruise_id');
        $list = $list->where('app_cabin.language_id', $languageId);
        if (is_array($id)) {
            return $list->whereIn('app_cabin.id', $id)->get();
        } else {
            return $list->where('app_cabin.id', $id)->first();
        }
    }

    public static function getCabinMinPriceById($id)
    {
        $list = new AppCabinPrice();
        $list = $list->select('cabin_id', DB::raw('MIN(price) AS min_price'));
        if (is_array($id)) {
            $list = $list->whereIn('cabin_id', $id);
        } else {
            $list = $list->where('cabin_id', $id);
        }
        $list = $list->groupBy('cabin_id');
        return $list->get();
    }

    public static function getServicePaging($param, $languageId)
    {
        $list = new AppService();
        $list = $list->select('app_service.*', DB::raw('app_group.id AS group_id, app_group.name AS group_name'));
        $list = $list->leftJoin('app_group', function($join) use ($languageId) {
            $join->on('app_group.id', '=', 'app_service.group_id');
            $join->where('app_group.type', config('backend.groupType.service'));
            $join->where('app_group.language_id', $languageId);
        });
        if (array_key_exists('keyword', $param)) {
            $list = $list->where('app_group.name', 'like', '%' . $param['keyword'] . '%');
            $list = $list->orWhere('app_group.description', 'like', '%' . $param['keyword'] . '%');
        }
        if (array_key_exists('group_id', $param)) {
            $list = $list->where('group_id', $param['group_id']);
        }
        if (array_key_exists('exclude_id', $param)) {
            if (is_array($param['exclude_id'])) {
                $list = $list->whereNotIn('app_service.id', $param['exclude_id']);
            } else {
                $list = $list->where('app_service.id', '!=', $param['exclude_id']);
            }
        }
        $list = $list->where('app_service.language_id', $languageId);
        $list = $list->orderBy('app_service.id', 'desc');
        return $list->paginate(config('backend.paginationLimit'));
    }

    public static function getServiceById($id, $languageId)
    {
        if (is_array($id)) {
            return AppService::whereIn('id', $id)
                ->where('language_id', $languageId)
                ->get();
        } else {
            return AppServiceService::find($id, $languageId);
        }
    }

    public static function getAmenityPaging($param, $languageId)
    {
        $list = new AppAmenity();
        if (array_key_exists('keyword', $param)) {
            $list = $list->where('name', 'like', '%' . $param['keyword'] . '%');
            $list = $list->orWhere('description', 'like', '%' . $param['keyword'] . '%');
        }
        if (array_key_exists('exclude_id', $param)) {
            if (is_array($param['exclude_id'])) {
                $list = $list->whereNotIn('id', $param['exclude_id']);
            } else {
                $list = $list->where('id', '!=', $param['exclude_id']);
            }
        }
        $list = $list->where('language_id', $languageId);
        $list = $list->orderBy('ord');
        return $list->paginate(config('backend.paginationLimit'));
    }

    public static function getAmenityById($id, $languageId)
    {
        if (is_array($id)) {
            return AppAmenity::whereIn('id', $id)
                ->where('language_id', $languageId)
                ->get();
        } else {
            return AppAmenityService::find($id, $languageId);
        }
    }

    public static function getExpActivityPaging($param, $languageId)
    {
        $list = new AppExpActivity();
        $list = $list->select('app_exp_activity.*', DB::raw('app_group.id AS group_id, app_group.name AS group_name'));
        $list = $list->leftJoin('app_group', function($join) use ($languageId) {
            $join->on('app_group.id', '=', 'app_exp_activity.group_id');
            $join->where('app_group.type', config('backend.groupType.expActivity'));
            $join->where('app_group.language_id', $languageId);
        });
        if (array_key_exists('keyword', $param)) {
            $list = $list->where('app_exp_activity.name', 'like', '%' . $param['keyword'] . '%');
            $list = $list->orWhere('summary', 'like', '%' . $param['keyword'] . '%');
            $list = $list->orWhere('content', 'like', '%' . $param['keyword'] . '%');
        }
        if (array_key_exists('group_id', $param)) {
            $list = $list->where('group_id', $param['group_id']);
        }
        if (array_key_exists('exclude_id', $param)) {
            if (is_array($param['exclude_id'])) {
                $list = $list->whereNotIn('app_exp_activity.id', $param['exclude_id']);
            } else {
                $list = $list->where('app_exp_activity.id', '!=', $param['exclude_id']);
            }
        }
        $list = $list->where('app_exp_activity.language_id', $languageId);
        $list = $list->orderBy('app_exp_activity.id', 'desc');
        return $list->paginate(config('backend.paginationLimit'));
    }

    public static function getExpActivityById($id, $languageId)
    {
        $query = new AppExpActivity();
        $query = $query->select('app_exp_activity.*', DB::raw('app_group.name AS group_name'));
        $query = $query->leftJoin('app_group', function($join) use ($languageId) {
            $join->on('app_group.id', '=', 'app_exp_activity.group_id');
            $join->where('app_group.type', config('backend.groupType.expActivity'));
            $join->where('app_group.language_id', $languageId);
        });
        $query = $query->where('app_exp_activity.language_id', $languageId);
        if (is_array($id)) {
            return $query->whereIn('app_exp_activity.id', $id)->get();
        } else {
            return $query->whereIn('app_exp_activity.id', $id)->first();
        }
    }
}