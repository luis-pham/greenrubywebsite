<?php
namespace Modules\BackEnd\Entities;

use Modules\BackEnd\Entities\BaseModel;

class AppMenuFrontEnd extends BaseModel
{
    protected $table = 'app_menu_front_end';
    protected $fillable = [
        'language_id',
        'code',
        'name',
        'description',
        'menu'
    ];
    protected $hidden = [];
}
