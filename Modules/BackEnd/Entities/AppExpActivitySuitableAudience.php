<?php
namespace Modules\BackEnd\Entities;

use Modules\BackEnd\Entities\BaseModelMultiPrimaryKey;

class AppExpActivitySuitableAudience extends BaseModelMultiPrimaryKey
{
    protected $table = 'app_exp_activity_suitable_audience';
    protected $primaryKey = ['exp_activity_id', 'group_id'];
    protected $fillable = [
        'exp_activity_id',
        'group_id',
        'ord'
    ];
    protected $hidden = [];
    public $timestamps = false;
}
