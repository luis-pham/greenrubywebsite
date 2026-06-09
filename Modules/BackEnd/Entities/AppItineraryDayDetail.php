<?php
namespace Modules\BackEnd\Entities;

use Modules\BackEnd\Entities\BaseModel;

class AppItineraryDayDetail extends BaseModel
{
    protected $table = 'app_itinerary_day_detail';
    protected $fillable = [
        'itinerary_day_id',
        'time',
        'title',
        'description',
        'ord'
    ];
    protected $hidden = [];
}
