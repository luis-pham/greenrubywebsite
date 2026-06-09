<?php
namespace Modules\BackEnd\Entities;

class AdUserRole extends BaseModelMultiPrimaryKey
{
    protected $table = 'ad_user_role';
    protected $primaryKey = ['user_id', 'role_id'];
    protected $fillable = [
        'user_id',
        'role_id'
    ];
    protected $hidden = [];
}
