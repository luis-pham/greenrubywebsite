<?php
namespace Modules\BackEnd\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Modules\BackEnd\Services\AdUserService;

class UserStoreRequest extends FormRequest
{
    public function rules(Request $request)
    {
        return [
            'username' => [
                'required',
                function ($attribute, $value, $fail) use ($request) {
                    $obj = AdUserService::getByUsername($value);
                    if ($obj && $obj->id != $request->id) {
                        return $fail('Tên đăng nhập đã được sử dụng!');
                    }
                }
            ],
            'password' => 'required|min:6|confirmed',
            'fullname' => 'required',
            'email' => [
                'email',
                function ($attribute, $value, $fail) use ($request) {
                    $obj = AdUserService::getByEmail($value);
                    if ($obj && $obj->id != $request->id) {
                        return $fail('Email đã được sử dụng!');
                    }
                }
            ]
        ];
    }

    public function messages()
    {
        return [
            'username.required' => 'Bạn phải nhập Tên đăng nhập!',
            'password.required' => 'Bạn phải nhập Mật khẩu!',
            'password.min' => 'Mật khẩu phải có ít nhất 6 ký tự',
            'password.confirmed' => 'Xác nhận mật khẩu không chính xác!',
            'fullname.required' => 'Bạn phải nhập Họ tên!',
            'email.email' => 'Bạn phải nhập Email đúng định dạng!'
        ];
    }
}
