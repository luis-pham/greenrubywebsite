<?php
namespace Modules\BackEnd\Entities;

use Modules\BackEnd\Entities\BaseModel;

class AppDestination extends BaseModel
{
    protected $table = 'app_destination';
    protected $fillable = [
        'language_id',
        'slug',
        'name',
        'heading',
        'bay',
        'category',
        'summary',
        'content',
        'facts',
        'faq',
        'sources',
        'latitude',
        'longitude',
        'coords_verified',
        'coords_source',
        'image_link',
        'image_alt',
        'image_credit',
        'status',
        'editor_notes',
        'fact_checked_at',
        'seo_title',
        'seo_description',
        'ord',
        'created_at',
        'created_by',
        'updated_at',
        'updated_by'
    ];
    protected $casts = [
        'facts' => 'array',
        'faq' => 'array',
        'sources' => 'array',
        'coords_verified' => 'boolean',
    ];
    protected $hidden = [];
}
