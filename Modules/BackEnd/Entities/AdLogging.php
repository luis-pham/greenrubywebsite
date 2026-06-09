<?php
namespace Modules\BackEnd\Entities;

use Illuminate\Database\Eloquent\Model;

class AdLogging extends Model
{
    const UPDATED_AT = null;

    public $timestamps = true;

    protected $table = 'ad_logging';
    protected $fillable = [
        'user_id',
        'action',
        'detail',
        'ip',
        'user_agent',
        'type'
    ];
    protected $hidden = [];
}
