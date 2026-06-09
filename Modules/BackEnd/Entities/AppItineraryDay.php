<?php
namespace Modules\BackEnd\Entities;

use Modules\BackEnd\Entities\BaseModel;

class AppItineraryDay extends BaseModel
{
    protected $table = 'app_itinerary_day';
    protected $fillable = [
        'itinerary_id',
        'day'
    ];
    protected $hidden = [];

    public function itineraryDayDetails(){
        return $this->hasMany(AppItineraryDayDetail::class,'itinerary_day_id','id');
    }

    public static function boot() {
        parent::boot();
        static::deleted(function($model) {
            $model->itineraryDayDetails()->delete();
        });
    }
}
