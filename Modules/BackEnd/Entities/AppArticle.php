<?php
namespace Modules\BackEnd\Entities;

use Modules\BackEnd\Entities\BaseModel;

class AppArticle extends BaseModel
{
    protected $table = 'app_article';
    protected $fillable = [
        'language_id',
        'category_id',
        'title',
        'sub_title',
        'lead',
        'content',
        'author',
        'publish_date',
        'image_link',
        'point',
        'is_featured',
        'seo_title',
        'seo_description',
        'is_published',
        'created_at',
        'created_by',
        'updated_at',
        'updated_by'
    ];
    protected $hidden = [];
}
