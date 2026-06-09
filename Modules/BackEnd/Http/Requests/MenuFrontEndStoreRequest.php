<?php
namespace Modules\BackEnd\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Modules\BackEnd\Helpers\LanguageUtils;
use Modules\BackEnd\Services\AppMenuFrontEndService;

class MenuFrontEndStoreRequest extends FormRequest
{
    public function rules(Request $request)
    {
        $language = LanguageUtils::getCurrentLanguage();

        return [
            'code' => [
                'required',
                function ($attribute, $value, $fail) use ($language) {
                    $obj = AppMenuFrontEndService::getByCode($value, $language->id);
                    if ($obj) {
                        return $fail('Mã đã được sử dụng!');
                    }
                }
            ],
            'name' => 'required',
            'menu' => 'required'
        ];
    }

    public function messages()
    {
        return [
            'code.required' => 'Bạn phải nhập Mã!',
            'name.required' => 'Bạn phải nhập Tên!',
            'menu.required' => 'Bạn phải nhập danh sách Menu!'
        ];
    }
}
