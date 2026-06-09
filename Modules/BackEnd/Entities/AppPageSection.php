<?php
namespace Modules\BackEnd\Entities;

use Modules\BackEnd\Entities\BaseModel;

class AppPageSection extends BaseModel
{
    protected $table = 'app_page_section';
    protected $fillable = [
        'page_id',
        'name',
        'ord',
        'created_at',
        'created_by',
        'updated_at',
        'updated_by'
    ];
    protected $hidden = [];
}
