<?php
namespace Modules\FrontEnd\Entities;

use Illuminate\Foundation\Auth\User;
use Modules\BackEnd\Entities\AdUserRole;

class AppUser extends User
{
    public $timestamps = false;

    protected $table = 'ad_user';
    protected $fillable = [
        'username',
        'password',
        'fullname',
        'email',
        'avatar',
        'cover',
        'theme',
        'status',
        'remember_token'
    ];
    protected $hidden = ['password', 'remember_token'];

    public function ad_user_role()
    {
        return $this->hasMany(AdUserRole::class, 'user_id', 'id');
    }
}
