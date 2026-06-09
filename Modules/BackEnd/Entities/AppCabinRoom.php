<?php
namespace Modules\BackEnd\Entities;

use Modules\BackEnd\Entities\BaseModel;

class AppCabinRoom extends BaseModel
{
    protected $table = 'app_cabin_room';
    protected $fillable = [
        'cabin_id',
        'title',
        'description',
        'ord'
    ];
    protected $hidden = [];
}
