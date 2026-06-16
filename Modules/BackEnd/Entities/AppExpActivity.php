<?php
namespace Modules\BackEnd\Entities;

use Modules\BackEnd\Entities\BaseModel;

class AppExpActivity extends BaseModel
{
    protected $table = 'app_exp_activity';
    protected $fillable = [
        'language_id',
        'group_id',
        'cruise_id',
        'name',
        'summary',
        'seo_title',
        'seo_description',
        'content',
        'image_link',
        'cover_link',
        'duration',
        'start_time',
        'end_time',
        'note',
        'is_featured'
    ];
    protected $hidden = [];

    public function cruise()
    {
        return $this->belongsTo(
            AppCruise::class,
            'cruise_id'
        );
    }

    public function group()
    {
        return $this->belongsTo(AppGroup::class, 'group_id');
    }
}
