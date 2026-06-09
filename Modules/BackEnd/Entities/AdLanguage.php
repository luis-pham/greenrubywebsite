<?php
namespace Modules\BackEnd\Entities;

use Illuminate\Database\Eloquent\Model;

class AdLanguage extends Model
{
    const CREATED_AT = null;
    const UPDATED_AT = null;

    public $timestamps = true;

    protected $table = 'ad_language';
    protected $fillable = [
        'code',
        'name',
        'short_name',
        'image_link',
        'currency_format',
        'is_default',
        'ord'
    ];
    protected $hidden = [];
}
