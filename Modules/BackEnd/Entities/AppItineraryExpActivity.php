<?php
namespace Modules\BackEnd\Entities;

use Modules\BackEnd\Entities\BaseModelMultiPrimaryKey;

class AppItineraryExpActivity extends BaseModelMultiPrimaryKey
{
    protected $table = 'app_itinerary_exp_activity';
    protected $primaryKey = ['itineraries_id', 'exp_activity_id'];
    protected $fillable = [
        'itineraries_id',
        'exp_activity_id',
        'ord'
    ];
    protected $hidden = [];
    public $timestamps = false;
}
