<?php
namespace Modules\BackEnd\Entities;

use Illuminate\Database\Eloquent\Model;

class AdPrivilege extends BaseModel
{
    protected $table = 'ad_privilege';
    protected $fillable = [
        'resource_id',
        'name',
        'alias',
        'ord'
    ];
    protected $hidden = [];
}
