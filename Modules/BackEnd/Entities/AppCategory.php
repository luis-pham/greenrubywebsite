<?php
namespace Modules\BackEnd\Entities;

use Modules\BackEnd\Entities\BaseModel;

class AppCategory extends BaseModel
{
    protected $table = 'app_category';
    protected $fillable = [
        'language_id',
        'parent_id',
        'name',
        'slug',
        'icon',
        'description',
        'seo_title',
        'seo_description',
        'lvl',
        'lft',
        'rgt',
        'type',
        'created_at',
        'created_by',
        'updated_at',
        'updated_by'
    ];
    protected $hidden = [];
}
