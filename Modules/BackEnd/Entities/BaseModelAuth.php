<?php
namespace Modules\BackEnd\Entities;

use Illuminate\Foundation\Auth\User;

class BaseModelAuth extends User
{
    public $timestamps = false;
    
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (\Auth::user()) {
                $model->created_at = $model->freshTimestamp();
                $model->created_by = \Auth::user()->id;
            }
        });

        static::updating(function ($model) {
            if (!(self::checkUseSoftDelete($model) && $model->deleted_by) && \Auth::user()) {
                $model->updated_at = $model->freshTimestamp();
                $model->updated_by = \Auth::user()->id;
            }
        });

        static::deleting(function ($model) {
            if (self::checkUseSoftDelete($model) && \Auth::user()) {
                $model->deleted_at = $model->freshTimestamp();
                $model->deleted_by = \Auth::user()->id;
                $model->save();
            }
        });
    }

    protected static function checkUseSoftDelete($model)
    {
        return in_array('Illuminate\Database\Eloquent\SoftDeletes', class_uses($model));
    }
}
