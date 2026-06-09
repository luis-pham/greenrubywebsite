<?php
namespace Modules\BackEnd\Entities;

use Modules\BackEnd\Entities\BaseModel;

class AppAmenity extends BaseModel
{
    protected $table = 'app_amenity';
    protected $fillable = [
        'language_id',
        'icon',
        'name',
        'description',
        'ord',
        'created_at',
        'created_by',
        'updated_at',
        'updated_by'
    ];
    protected $hidden = [];
}

