<?php
namespace Modules\BackEnd\Entities;

use Modules\BackEnd\Entities\BaseModel;

class AppPage extends BaseModel
{
    protected $table = 'app_page';
    protected $fillable = [
        'language_id',
        'title',
        'slug',
        'description',
        'seo_title',
        'seo_description',
        'created_at',
        'created_by',
        'updated_at',
        'updated_by'
    ];
    protected $hidden = [];
}
