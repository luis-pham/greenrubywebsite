<?php
namespace Modules\BackEnd\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Modules\BackEnd\Helpers\Utilities;

class FileRequest extends FormRequest
{
    public function rules(Request $request)
    {
        return [
            'file' => 'required',
            'file.*' => 'max:' . Utilities::getUploadMaxFileSize('KB') .'|mimes:' . implode(',', Utilities::getFileTypeAllowUpload())
        ];
    }

    public function messages()
    {
        return [
            'file.required' => 'Bạn phải chọn File!',
            'file.*.max' => 'Bạn phải chọn File dung lượng nhỏ hơn ' . Utilities::getUploadMaxFileSize('MB', true) . '!',
            'file.*.mimes' => 'File upload phải có định dạng: ' . implode(', ', Utilities::getFileTypeAllowUpload()) . '!'
        ];
    }
}
