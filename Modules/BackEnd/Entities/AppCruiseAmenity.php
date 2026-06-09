<?php
namespace Modules\BackEnd\Entities;

use Modules\BackEnd\Entities\BaseModelMultiPrimaryKey;

class AppCruiseAmenity extends BaseModelMultiPrimaryKey
{
    protected $table = 'app_cruise_amenity';
    protected $primaryKey = ['cruise_id', 'amenity_id'];
    protected $fillable = [
        'cruise_id',
        'amenity_id',
        'ord'
    ];
    protected $hidden = [];
    public $timestamps = false;
}
