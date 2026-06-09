<?php
namespace Modules\BackEnd\Entities;

use Illuminate\Support\Str;

class AdUser extends BaseModelAuth
{
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
        'provider'
    ];
    protected $hidden = ['password'];

    public static $listAbility = [];

    public function setAttribute($key, $value)
    {
        if ($key != $this->getRememberTokenName()) {
            parent::setAttribute($key, $value);
        }
    }

    public function hasAccess($resourceAlias, $privilegAlias)
    {
        $userId = $this->getAuthIdentifier();
        if ($userId == config('backend.adUserAdmin')) {
            return true;
        }

        return self::getAbilityByUserId($userId, $resourceAlias, $privilegAlias);
    }

    public function ad_user_role()
    {
        return $this->hasMany(AdUserRole::class, 'user_id', 'id');
    }

    protected static function boot()
    {
        parent::boot();
        
        static::deleted(function ($model) {
            $model->ad_user_role()->delete();
        });
    }

    public static function getAbilityByUserId($userId, $resourceAlias, $privilegAlias)
    {
        $key = $userId . '-' . $resourceAlias . '-' . $privilegAlias;
        if (array_key_exists($key, self::$listAbility)) {
            return self::$listAbility[$key];
        }

        $count = self::join('ad_user_role', 'ad_user_role.user_id', '=', 'ad_user.id')
                    ->join('ad_role', 'ad_role.id', '=', 'ad_user_role.role_id')
                    ->join('ad_role_privilege', 'ad_role_privilege.role_id', '=', 'ad_role.id')
                    ->join('ad_privilege', 'ad_privilege.id', '=', 'ad_role_privilege.privilege_id')
                    ->join('ad_resource', 'ad_resource.id', '=', 'ad_privilege.resource_id')
                    ->where('ad_user.id', $userId)
                    ->where('ad_resource.alias', $resourceAlias)
                    ->where('ad_privilege.alias', $privilegAlias)
                    ->count();

        self::$listAbility[$key] = $count > 0;

        return self::$listAbility[$key];
    }

    public static function deleteAbilityByUserId($userId)
    {
        foreach (self::$listAbility as $key => $value) {
            if (Str::startsWith($key, $userId . '-')) {
                unset(self::$listAbility[$key]);
            }
        }
    }
}
