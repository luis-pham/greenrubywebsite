<?php
namespace Modules\BackEnd\Entities;

use Modules\BackEnd\Entities\BaseModelMultiPrimaryKey;

class AppCabinPrice extends BaseModelMultiPrimaryKey
{
    protected $table = 'app_cabin_price';
    protected $primaryKey = ['cabin_id', 'duration', 'guest'];
    protected $fillable = [
        'cabin_id',
        'duration',
        'guest',
        'price'
    ];
    protected $hidden = [];
    public $timestamps = false;
}
