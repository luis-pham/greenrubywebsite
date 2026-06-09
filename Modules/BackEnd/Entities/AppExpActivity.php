<?php
namespace Modules\BackEnd\Entities;

use Modules\BackEnd\Entities\BaseModel;

class AppExpActivity extends BaseModel
{
    protected $table = 'app_exp_activity';
    protected $fillable = [
        'language_id',
        'group_id',
        'name',
        'summary',
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
    
}
