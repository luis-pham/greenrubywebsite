<?php
namespace Modules\BackEnd\Services;

use Modules\BackEnd\Entities\AdMenu;

class AdMenuService
{
    public static function getMenuByUserId($userId)
    {
        if ($userId == config('backend.adUserAdmin')) {
            return AdMenu::where('status', 1)->orderBy('ord')->get();
        }

        return AdMenu::where(function ($query) use ($userId) {
            $query->whereNull('privilege_id');
            $query->orWhereIn('privilege_id', function ($query) use ($userId) {
                $query->from('ad_user_role')
                        ->join('ad_role_privilege', 'ad_user_role.role_id', '=', 'ad_role_privilege.role_id')
                        ->where('user_id', $userId)
                        ->select(['privilege_id'])
                        ->distinct();
            });
        })
        ->where('status', 1)
        ->orderBy('ord')
        ->get();
    }
}
