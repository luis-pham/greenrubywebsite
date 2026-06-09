<?php
namespace Modules\BackEnd\Entities;

class AdMenu extends BaseModel
{
    protected $table = 'ad_menu';
    protected $fillable = [
        'parent_id',
        'privilege_id',
        'name',
        'url',
        'active_url',
        'is_multi_language',
        'icon',
        'ord'
    ];
    protected $hidden = [];
}
