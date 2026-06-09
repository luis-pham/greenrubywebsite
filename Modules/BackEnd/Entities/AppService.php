<?php
namespace Modules\BackEnd\Entities;

use Modules\BackEnd\Entities\BaseModel;

class AppService extends BaseModel
{
    protected $table = 'app_service';
    protected $fillable = [
        'language_id',
        'group_id',
        'name',
        'description',
        'image_link',
        'price',
        'type',
        'status'
    ];
    protected $hidden = [];
}
