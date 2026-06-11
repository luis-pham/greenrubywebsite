<?php
namespace Modules\BackEnd\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Modules\BackEnd\Helpers\LanguageUtils;
use Modules\BackEnd\Services\AppGroupService;

class GroupRequest extends FormRequest
{
    public function rules(Request $request)
    {
        $typeName = Request::route('typeName');
        $language = LanguageUtils::getCurrentLanguage();
        $type = config('backend.groupType.' . $typeName);
        
        return [
            'name' => 'required',
            'slug' => [
                'required',
                function ($attribute, $value, $fail) use ($request, $type, $language) {
                    $obj = AppGroupService::getBySlug($value, $type, $language->id);
                    if ($obj && $obj->id != $request->id) {
                        return $fail('Slug đã được sử dụng!');
                    }
                }
            ],
            'category_key' => 'nullable|string|max:50',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Bạn phải nhập Tên!',
            'slug.required' => 'Bạn phải nhập Slug!'
        ];
    }
}
