<?php
namespace Modules\BackEnd\Services;

use Illuminate\Support\Facades\DB;
use Modules\BackEnd\Entities\AppService;
use Modules\BackEnd\Entities\AppTestimonial;



class AppTestimonialService
{
    public static function find($id, $languageId = null)
    {
        $query = AppTestimonial::where('id', $id);
        if ($languageId !== null) {
            $query->where('language_id', $languageId);
        }
        return $query->first();
    }

    public static function create($data, $languageId)
    {
        DB::beginTransaction();
        try {
            $obj = new AppTestimonial();
            $obj->language_id = $languageId;
            $obj->fullname = array_key_exists('fullname', $data) ? $data['fullname'] : null;
            $obj->position = array_key_exists('position', $data) ? $data['position'] : null;
            $obj->avatar = array_key_exists('avatar', $data) ? $data['avatar'] : null;
            $obj->content = array_key_exists('content', $data) ? $data['content'] : null;
            $obj->ord = array_key_exists('ord', $data) && $data['ord'] 
            ? $data['ord'] 
            : self::getNextOrder($languageId);
            $obj->save();
            DB::commit();

            return $obj->id;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public static function update($data, $languageId)
    {
        DB::beginTransaction();
        try {
            $obj = self::find($data['id'], $languageId);
            if ($obj) {
                $obj->fullname = array_key_exists('fullname', $data) ? $data['fullname'] : $obj->fullname;
                $obj->position = array_key_exists('position', $data) ? $data['position'] : $obj->position;
                $obj->avatar = array_key_exists('avatar', $data) ? $data['avatar'] : $obj->avatar;
                $obj->content = array_key_exists('content', $data) ? $data['content'] : $obj->content;
                $obj->ord = array_key_exists('ord', $data) ? $data['ord'] : $obj->ord;
                $obj->save();
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public static function delete($id, $languageId)
    {
        if (is_array($id)) {
            AppTestimonial::whereIn('id', $id)
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
        return AppTestimonial::where('language_id', $languageId)
            ->orderBy('ord')
            ->get();
    }



    public static function getPaging($param, $languageId)
    {
        $list = new AppTestimonial();
        $list = $list->select('app_testimonial.*');
       
        
        if (array_key_exists('keyword', $param)) {
            $list = $list->where(function($query) use ($param) {
                $query->where('app_testimonial.fullname', 'like'    , '%' . $param['keyword'] . '%')
                      ->orWhere('app_testimonial.content', 'like', '%' . $param['keyword'] . '%');
            });
        }
             
        $list = $list->where('app_testimonial.language_id', $languageId);
        $list = $list->orderBy('ord');        
        if (!(array_key_exists('is_disabled_paginate', $param) && $param['is_disabled_paginate'])) {
            if (!array_key_exists('pageSize', $param)) {
                $param['pageSize'] = config('backend.paginationLimit');
            }
            return $list->paginate($param['pageSize']);
        }

        return $list->get();

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
    public static function getNextOrder($languageId)
    {
        $ord = AppTestimonial::where('language_id', $languageId)->max('ord');
        return $ord ? $ord + 1 : 1;
    }

    public static function getById($id, $languageId)
    {
        if (is_array($id)) {
            return AppTestimonial::whereIn('id', $id)
                ->where('language_id', $languageId)
                ->get();
        } else {
            return self::find($id, $languageId);
        }
    }

 
}