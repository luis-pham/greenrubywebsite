<?php
namespace Modules\BackEnd\Entities;

use Modules\BackEnd\Entities\BaseModel;

class AdRole extends BaseModel
{
    protected $table = 'ad_role';
    protected $fillable = [
        'name'
    ];
    protected $hidden = [];
}
