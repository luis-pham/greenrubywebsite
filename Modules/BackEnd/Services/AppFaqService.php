<?php
namespace Modules\BackEnd\Services;

use Illuminate\Support\Facades\DB;
use Modules\BackEnd\Entities\AppFaq;

class AppFaqService
{
    public static function find($id, $languageId)
    {
        return AppFaq::where('id', $id)
            ->where('language_id', $languageId)
            ->first();
    }

    public static function create($data, $languageId)
    {
        $obj = new AppFaq();
        $obj->language_id = $languageId;
        $obj->group_id = array_key_exists('group_id', $data) ? $data['group_id'] : null;
        $obj->question = array_key_exists('question', $data) ? \App\Support\HtmlSanitizer::clean($data['question']) : null;
        $obj->answer = array_key_exists('answer', $data) ? \App\Support\HtmlSanitizer::clean($data['answer']) : null;
        $obj->ord = array_key_exists('ord', $data) ? $data['ord'] : null;
        $obj->save();

        return $obj->id;
    }

    public static function update($data, $languageId)
    {
        $obj = self::find($data['id'], $languageId);
        if ($obj) {
            $obj->group_id = array_key_exists('group_id', $data) ? $data['group_id'] : $obj->group_id;
            $obj->question = array_key_exists('question', $data) ? \App\Support\HtmlSanitizer::clean($data['question']) : $obj->question;
            $obj->answer = array_key_exists('answer', $data) ? \App\Support\HtmlSanitizer::clean($data['answer']) : $obj->answer;
            $obj->save();
        }
    }

    public static function delete($id, $languageId)
    {
        if (is_array($id)) {
            AppFaq::whereIn('id', $id)
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
        return AppFaq::where('language_id', $languageId)
            ->orderBy('ord')
            ->get();
    }

    public static function findJoin($id, $languageId)
    {
        return AppFaq::select('app_faq.*', DB::raw('app_group.name AS group_name'))
            ->leftJoin('app_group', function($join) use ($languageId) {
                $join->on('app_group.id', '=', 'app_faq.group_id');
                $join->where('app_group.type', config('backend.groupType.faq'));
                $join->where('app_group.language_id', $languageId);
            })
            ->where('app_faq.id', $id)
            ->where('app_faq.language_id', $languageId)
            ->first();
    }

    public static function getPaging($param, $languageId)
    {
        $list = new AppFaq();
        $list = $list->select('app_faq.*', DB::raw('app_group.name AS group_name'));
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
        $list = $list->where('app_faq.language_id', $languageId);
        $list = $list->orderBy('ord');
        if (!(array_key_exists('is_disabled_paginate', $param) && $param['is_disabled_paginate'])) {
            if (!array_key_exists('pageSize', $param)) {
                $param['pageSize'] = config('backend.paginationLimit');
            }
            return $list->paginate($param['pageSize']);
        }

        return $list->get();
    }

    public static function getNextOrder($languageId)
    {
        $ord = AppFaq::where('language_id', $languageId)->max('ord');
        return $ord ? $ord + 1 : 1;
    }

    public static function saveOrder($list, $languageId)
    {
        DB::beginTransaction();
        try {
            foreach ($list as $key => $value) {
                $obj = self::find($key, $languageId);
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
}
