<?php
namespace Modules\BackEnd\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;

class FaqRequest extends FormRequest
{
    public function rules(Request $request)
    {
        return [
            'group_id' => 'required',
            'question' => 'required',
            'answer' => 'required'
        ];
    }

    public function messages()
    {
        return [
            'group_id.required' => 'Bạn phải chọn Chuyên mục!',
            'question.required' => 'Bạn phải nhập Câu hỏi!',
            'answer.required' => 'Bạn phải nhập Trả lời!'
        ];
    }
}
