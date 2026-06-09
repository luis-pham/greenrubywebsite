<?php
namespace Modules\BackEnd\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;

class TestimonialRequest extends FormRequest
{
    public function rules(Request $request)
    {
        return [
            'fullname' => 'required|max:100',
            'position' => 'required|max:100',
            'avatar' => 'required|string',
            'content' => 'required|max:300',
        ];
    }

    public function messages()
    {
        return [
            'fullname.required' => 'Vui lòng nhập Họ và tên!',
            'fullname.max' => 'Họ và tên tối đa 100 ký tự!',
            'position.required' => 'Vui lòng nhập Vị trí/Chức vụ!',
            'position.max' => 'Vị trí/Chức vụ tối đa 100 ký tự!',
            'avatar.required' => 'Vui lòng chọn Ảnh đại diện!',
            'content.required' => 'Vui lòng nhập Nội dung đánh giá!',
            'content.max' => 'Nội dung đánh giá tối đa 300 ký tự!',
        ];
    }
}