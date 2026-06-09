<?php
namespace Modules\BackEnd\Entities;

use Modules\BackEnd\Entities\BaseModelMultiPrimaryKey;

class AppItineraryService extends BaseModelMultiPrimaryKey
{
    protected $table = 'app_itinerary_service';
    protected $primaryKey = ['itineraries_id', 'service_id'];
    protected $fillable = [
        'itineraries_id',
        'service_id',
        'ord'
    ];
    protected $hidden = [];
    public $timestamps = false;
}
