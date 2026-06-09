<?php
namespace Modules\BackEnd\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;

class ArticleRequest extends FormRequest
{
    public function rules(Request $request)
    {
        $validator['category_id'] = 'required';
        $validator['title'] = 'required';
        $validator['lead'] = 'required';
        $validator['content'] = 'required';
        $validator['publish_date'] = 'required|date_format:"' . config('backend.displayDateTimeFormat') . '"';

        return $validator;
    }

    public function messages()
    {
        $validator['category_id.required'] = 'Bạn phải chọn Chuyên mục!';
        $validator['title.required'] = 'Bạn phải nhập Tiêu đề!';
        $validator['lead.required'] = 'Bạn phải nhập Trích dẫn!';
        $validator['content.required'] = 'Bạn phải nhập Nội dung!';
        $validator['publish_date.required'] = 'Bạn phải nhập Thời gian xuất bản!';
        $validator['publish_date.date_format'] = 'Bạn phải nhập Thời gian xuất bản đúng định dạng Ngày/Tháng/Năm Giờ:Phút. VD: 31/01/2022 08:00!';

        return $validator;
    }
}
