<?php
namespace Modules\BackEnd\Entities;

use Illuminate\Database\Eloquent\Model;

class AppPageConfig extends Model
{
    public $timestamps = false;

    protected $table = 'app_page_config';
    protected $fillable = [
        'page_id',
        'section_id',
        'label',
        'key',
        'value',
        'list_value',
        'type',
        'ord'
    ];
    protected $hidden = [];

    protected static function boot()
    {
        parent::boot();

        static::updating(function ($model) {
            if (\Auth::user()) {
                $model->updated_at = $model->freshTimestamp();
                $model->updated_by = \Auth::user()->id;
            }
        });
    }
}
