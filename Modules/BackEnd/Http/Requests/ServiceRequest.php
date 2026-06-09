<?php
namespace Modules\BackEnd\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;

class ServiceRequest extends FormRequest
{
    public function rules(Request $request)
    {
        return [
            'name' => 'required|max:100',
            'group_id' => 'required|exists:app_group,id',
            'description' => 'nullable|max:300',
            'image_link' => 'nullable|string',
            'price' => 'required_if:type,1|nullable|numeric|min:0',
            'type' => 'required|in:1,2',
            'status' => 'nullable|in:0,1'
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Vui lòng nhập Tên dịch vụ!',
            'name.max' => 'Tên dịch vụ tối đa 100 ký tự!',
            'group_id.required' => 'Vui lòng chọn Nhóm dịch vụ!',
            'group_id.exists' => 'Nhóm dịch vụ không tồn tại!',
            'description.max' => 'Mô tả tối đa 300 ký tự!',
            'image_link.string' => 'Ảnh đại diện không hợp lệ!',
            'price.numeric' => 'Giá phải là số!',
            'price.min' => 'Giá phải lớn hơn hoặc bằng 0!',
            'price.required_if' => 'Vui lòng nhập Giá!',
            'type.required' => 'Vui lòng chọn Loại dịch vụ!',
            'type.in' => 'Loại dịch vụ không hợp lệ!',
           
        ];
    }
}