<?php
namespace Modules\BackEnd\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Modules\BackEnd\Services\AdUserService;

class PersonalChangePasswordUpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules(Request $request)
    {
        return [
            'password' => [
                'required',
                function ($attribute, $value, $fail) use ($request) {
                    $obj = AdUserService::find(\Auth::user()->id);
                    if (!\Hash::check($value, $obj->password)) {
                        return $fail('Mật khẩu hiện tại không chính xác!');
                    }
                }
            ],
            'newPassword' => 'required|min:6|different:password|confirmed'
        ];
    }

    public function messages()
    {
        return [
            'password.required' => 'Bạn phải nhập Mật khẩu hiện tại',
            'newPassword.required' => 'Bạn phải nhập Mật khẩu mới!',
            'newPassword.min' => 'Mật khẩu mới phải có ít nhất 6 ký tự',
            'newPassword.different' => 'Bạn phải nhập Mật khẩu mới khác Mật khẩu hiện tại!',
            'newPassword.confirmed' => 'Xác nhận mật khẩu mới không chính xác!'
        ];
    }
}
