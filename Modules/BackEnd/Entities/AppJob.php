<?php
namespace Modules\BackEnd\Entities;

use Modules\BackEnd\Entities\BaseModel;

/** Job posting (recruitment, Vietnamese only): see database/migrations/2026_11_17_000001_create_app_job_table.php. */
class AppJob extends BaseModel
{
    protected $table = 'app_job';
    protected $fillable = [
        'slug',
        'title',
        'department',
        'employment_type',
        'location',
        'address_locality',
        'address_region',
        'summary',
        'intro',
        'description',
        'requirements',
        'benefits',
        'apply_email',
        'status',
        'posted_at',
        'deadline',
        'seo_title',
        'seo_description',
        'ord',
        'created_at',
        'created_by',
        'updated_at',
        'updated_by'
    ];
    protected $hidden = [];
}
