<?php
namespace Modules\BackEnd\Entities;

use Modules\BackEnd\Entities\BaseModel;

class AppTestimonial extends BaseModel
{
    protected $table = 'app_testimonial';
    protected $fillable = [
        'language_id',
        'fullname',
        'position',
        'avatar',
        'content',
        'ord',
        'created_at',
        'created_by',
        'updated_at',
        'updated_by'
    ];
    protected $hidden = [];
}
