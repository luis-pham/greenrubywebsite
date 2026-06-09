<?php
namespace Modules\BackEnd\Entities;

use Modules\BackEnd\Entities\BaseModel;

class AppFaq extends BaseModel
{
    protected $table = 'app_faq';
    protected $fillable = [
        'language_id',
        'group_id',
        'question',
        'answer',
        'ord',
        'created_at',
        'created_by',
        'updated_at',
        'updated_by'
    ];
    protected $hidden = [];
}
