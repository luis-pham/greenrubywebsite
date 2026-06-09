<?php
namespace Modules\BackEnd\Entities;

use Modules\BackEnd\Entities\BaseModelMultiPrimaryKey;

class AdRolePrivilege extends BaseModelMultiPrimaryKey
{
    protected $table = 'ad_role_privilege';
    protected $primaryKey = ['role_id', 'privilege_id'];
    protected $fillable = [
        'role_id',
        'privilege_id'
    ];
    protected $hidden = [];
    public $timestamps = false;
}
