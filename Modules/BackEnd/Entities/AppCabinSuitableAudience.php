<?php
namespace Modules\BackEnd\Entities;

use Modules\BackEnd\Entities\BaseModelMultiPrimaryKey;

class AppCabinSuitableAudience extends BaseModelMultiPrimaryKey
{
    protected $table = 'app_cabin_suitable_audience';
    protected $primaryKey = ['cabin_id', 'group_id'];
    protected $fillable = [
        'cabin_id',
        'group_id',
        'ord'
    ];
    protected $hidden = [];
    public $timestamps = false;
}
