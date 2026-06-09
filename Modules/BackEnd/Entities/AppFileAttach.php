<?php
namespace Modules\BackEnd\Entities;

use Modules\BackEnd\Entities\BaseModelMultiPrimaryKey;

class AppFileAttach extends BaseModelMultiPrimaryKey
{
    protected $table = 'app_file_attach';
    protected $primaryKey = ['file_id', 'object_id', 'object_type'];
    protected $fillable = [
        'file_id',
        'object_id',
        'object_type'
    ];
    protected $hidden = [];
    public $timestamps = false;
}
