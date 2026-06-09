<?php
namespace Modules\BackEnd\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;

class PersonalUpdateRequest extends FormRequest
{
    public function rules(Request $request)
    {
        return [
            'fullname' => 'required',
            'email' => 'sometimes|nullable|email',
            'avatar' => 'sometimes|nullable|mimes:' . implode(',', config('backend.fileTypeImage')) . '|max:1024'
        ];
    }

    public function messages()
    {
        return [
            'fullname.required' => 'Bạn phải nhập Họ tên!',
            'email.email' => 'Bạn phải nhập Email đúng định dạng!',
            'avatar.mimes' => 'Ảnh bìa phải có định dạng: ' . implode(', ', config('backend.fileTypeImage')) . '!',
            'avatar.max' => 'Bạn phải chọn Ảnh đại diện nhỏ hơn 1 MB!'
        ];
    }
}
