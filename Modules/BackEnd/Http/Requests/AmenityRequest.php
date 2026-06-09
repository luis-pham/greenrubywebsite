<?php
namespace Modules\BackEnd\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;

class AmenityRequest extends FormRequest
{
    public function rules(Request $request)
    {
        return [
            'name' => 'required|max:100',
            'description' => 'nullable|max:300',
            'icon' => 'required|string',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Vui lòng nhập Tên tiện ích!',
            'name.max' => 'Tên tiện ích tối đa 100 ký tự!',
            'description.max' => 'Mô tả tối đa 300 ký tự!',
            'icon.required' => 'Vui lòng chọn Biểu tượng!',
  
        ];
    }
}