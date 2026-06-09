<?php
namespace Modules\BackEnd\Entities;

use Modules\BackEnd\Entities\BaseModel;

class AppGroup extends BaseModel
{
    protected $table = 'app_group';
    protected $fillable = [
        'language_id',
        'name',
        'slug',
        'description',
        'image_link',
        'seo_title',
        'seo_description',
        'tab',
        'type',
        'created_at',
        'created_by',
        'updated_at',
        'updated_by'
    ];
    protected $hidden = [];

    
}
