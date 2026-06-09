<?php
namespace Modules\BackEnd\Entities;

use Modules\BackEnd\Entities\BaseModelMultiPrimaryKey;

class AppCabinAmenity extends BaseModelMultiPrimaryKey
{
    protected $table = 'app_cabin_amenity';
    protected $primaryKey = ['cabin_id', 'amenity_id'];
    protected $fillable = [
        'cabin_id',
        'amenity_id',
        'ord'
    ];
    protected $hidden = [];
    public $timestamps = false;
}
