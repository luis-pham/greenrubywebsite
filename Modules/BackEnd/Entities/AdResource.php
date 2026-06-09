<?php
namespace Modules\BackEnd\Entities;

use Modules\BackEnd\Entities\BaseModel;

class AdResource extends BaseModel
{
    protected $table = 'ad_resource';
    protected $fillable = [
        'name',
        'alias',
        'ord'
    ];
    protected $hidden = [];
}
