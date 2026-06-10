<?php
namespace Modules\BackEnd\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;

class ExpActivityRequest extends FormRequest
{
    public function rules(Request $request)
    {
        return [
            'name' => 'required|max:100',
            'group_id' => 'required|exists:app_group,id',
            'cruise_id' => 'nullable|integer',
            'summary' => 'required|max:300',
            'content' => 'nullable',
            'image_link' => 'nullable|string',
            'duration' => 'required|numeric|min:0',
            'start_time' => 'required',
            'end_time' => 'required',
            'note' => 'nullable',
            'is_featured' => 'nullable|boolean',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Vui lòng nhập Tên hoạt động!',
            'name.max' => 'Tên hoạt động tối đa 100 ký tự!',
            'group_id.required' => 'Vui lòng chọn Loại hoạt động!',
            'group_id.exists' => 'Loại hoạt động không tồn tại!',
            'summary.max' => 'Mô tả ngắn tối đa 300 ký tự!',
            'summary.required' => 'Vui lòng nhập Mô tả ngắn!',
            'image_link.string' => 'Ảnh hoạt động không hợp lệ!',
            'duration.required' => 'Vui lòng nhập Thời lượng!',
            'duration.numeric' => 'Thời lượng phải là số!',
            'duration.min' => 'Thời lượng phải lớn hơn hoặc bằng 0!',
            'start_time.required' => 'Vui lòng nhập Thời gian bắt đầu!',
            'end_time.required' => 'Vui lòng nhập Thời gian kết thúc!',
        ];
    }
}