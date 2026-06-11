<?php
namespace Modules\BackEnd\Services;

use Illuminate\Support\Facades\DB;
use Modules\BackEnd\Entities\AdUser;
use Modules\BackEnd\Entities\AdUserRole;

class AdUserService
{
    public static function find($id)
    {
        return AdUser::find($id);
    }

    public static function create($data, $dataDetail = [])
    {
        DB::beginTransaction();
        try {
            $obj = new AdUser();
            $obj->username = array_key_exists('username', $data) ? $data['username'] : null;
            $obj->password = array_key_exists('password', $data) ? $data['password'] : null;
            $obj->fullname = array_key_exists('fullname', $data) ? $data['fullname'] : null;
            $obj->email = array_key_exists('email', $data) ? $data['email'] : null;
            $obj->avatar = array_key_exists('avatar', $data) ? $data['avatar'] : null;
            $obj->cover = array_key_exists('cover', $data) ? $data['cover'] : null;
            $obj->theme = array_key_exists('theme', $data) ? $data['theme'] : null;
            $obj->status = config('backend.userStatus.unactive');
            $obj->save();

            for ($i = 0; $i < count($dataDetail); $i++) {
                $dataDetail[$i]['user_id'] = $obj->id;
                $objDetail = new AdUserRole($dataDetail[$i]);
                $objDetail->save();
            }
            
            DB::commit();

            return $obj->id;
        } catch (\Exception $e) {
            DB::rollBack();

            throw $e;
        }
    }

    public static function update($data, $dataDetail = [])
    {
        DB::beginTransaction();
        try {
            $obj = self::find($data['id']);
            if ($obj) {
                $obj->username = array_key_exists('username', $data) ? $data['username'] : $obj->username;
                $obj->password = array_key_exists('password', $data) ? $data['password'] : $obj->password;
                $obj->fullname = array_key_exists('fullname', $data) ? $data['fullname'] : $obj->fullname;
                $obj->email = array_key_exists('email', $data) ? $data['email'] : $obj->email;
                $obj->avatar = array_key_exists('avatar', $data) ? $data['avatar'] : $obj->avatar;
                $obj->cover = array_key_exists('cover', $data) ? $data['cover'] : $obj->cover;
                $obj->theme = array_key_exists('theme', $data) ? $data['theme'] : $obj->theme;
                $obj->status = array_key_exists('status', $data) ? $data['status'] : $obj->status;
                $obj->save();

                AdUserRole::where('user_id', $obj->id)->delete();
    
                if (!empty($dataDetail) > 0) {
                    for ($i = 0; $i < count($dataDetail); $i++) {
                        $dataDetail[$i]['user_id'] = $obj->id;
                        $objDetail = new AdUserRole($dataDetail[$i]);
                        $objDetail->save();
                    }
                }

                AdUser::deleteAbilityByUserId($obj->id);
            }
            
            DB::commit();

            return $obj->id;
        } catch (\Exception $e) {
            DB::rollBack();

            throw $e;
        }
    }

    public static function delete($id)
    {
        if (is_array($id)) {
            AdUser::destroy($id);
            for ($i = 0; $i < count($id); $i++) {
                AdUser::deleteAbilityByUserId($id[$i]);
            }
        } else {
            $obj = self::find($id);
            if ($obj) {
                $obj->delete();
                AdUser::deleteAbilityByUserId($obj->id);
            }
        }
    }

    public static function getAll()
    {
        return AdUser::all();
    }

    public static function updatePersonal($data)
    {
        $obj = self::find($data['id']);
        if ($obj) {
            $obj->fullname = array_key_exists('fullname', $data) ? $data['fullname'] : $obj->fullname;
            $obj->avatar = array_key_exists('avatar', $data) ? $data['avatar'] : $obj->avatar;
            $obj->cover = array_key_exists('cover', $data) ? $data['cover'] : $obj->cover;
            $obj->theme = array_key_exists('theme', $data) ? $data['theme'] : $obj->theme;
            $obj->save();
        }
    }

    public static function updatePassword($data)
    {
        $obj = self::find($data['id']);
        if ($obj) {
            $obj->password = array_key_exists('password', $data) ? $data['password'] : $obj->password;
            $obj->save();
        }
    }

    public static function getPaging($param)
    {
        $list = new AdUser();
        if (array_key_exists('keyword', $param)) {
            $list = $list->where(function ($query) use ($param) {
                $query->where('username', 'like', '%' . $param['keyword'] . '%');
                $query->orWhere('fullname', 'like', '%' . $param['keyword'] . '%');
            });
        }
        if (array_key_exists('role_id', $param)) {
            $list = $list->whereIn('id', function ($query) use ($param) {
                $query->select(['user_id'])->distinct()->from('ad_user_role')->where('role_id', $param['role_id'])->get();
            });
        }
        if (array_key_exists('status', $param)) {
            $list = $list->where('status', $param['status']);
        }
        $list = $list->orderBy('id', 'desc');
        return $list->paginate(config('backend.paginationLimit'));
    }

    public static function getByEmail($email)
    {
        return AdUser::where('email', $email)->first();
    }

    public static function getByUsername($username)
    {
        return AdUser::where('username', $username)->first();
    }
}
