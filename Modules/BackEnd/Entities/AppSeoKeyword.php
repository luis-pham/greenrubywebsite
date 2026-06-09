<?php
namespace Modules\BackEnd\Entities;

use Modules\BackEnd\Entities\BaseModel;

class AppSeoKeyword extends BaseModel
{
    protected $table = 'app_seo_keyword';
    protected $fillable = [
        'language_id',
        'keyword',
        'link',
        'description',
        'created_at',
        'created_by',
        'updated_at',
        'updated_by'
    ];
    protected $hidden = [];
}
