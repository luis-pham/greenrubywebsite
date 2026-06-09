<?php
namespace Modules\BackEnd\Entities;

use Modules\BackEnd\Entities\BaseModel;

class AppFile extends BaseModel
{
    protected $table = 'app_file';
    protected $fillable = [
        'name',
        'link',
        'size',
        'description',
        'created_at',
        'created_by',
        'updated_at',
        'updated_by'
    ];
    protected $hidden = [];
}
