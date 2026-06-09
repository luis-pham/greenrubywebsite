<?php
namespace Modules\BackEnd\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Modules\BackEnd\Helpers\LanguageUtils;
use Modules\BackEnd\Services\AppCategoryService;

class CategoryRequest extends FormRequest
{
    public function rules(Request $request)
    {
        $typeName = Request::route('typeName');
        $language = LanguageUtils::getCurrentLanguage();
        $type = config('backend.categoryType.' . $typeName);
        
        return [
            'name' => 'required',
            'slug' => [
                'required',
                function ($attribute, $value, $fail) use ($request, $type, $language) {
                    $obj = AppCategoryService::getBySlug($value, $type, $language->id);
                    if ($obj && $obj->id != $request->id) {
                        return $fail('Slug đã được sử dụng!');
                    }
                }
            ],
            'parent_id' => [
                function ($attribute, $value, $fail) use ($request, $type, $language) {
                    if ($request->id && $request->parent_id) {
                        if ($request->id == $request->parent_id) {
                            return $fail('Chuyên mục cha không hợp lệ!');
                        }

                        $list = AppCategoryService::getChildById($request->id, $type, $language->id);
                        for ($i = 0; $i < count($list); $i++) {
                            if ($request->parent_id == $list[$i]->id) {
                                return $fail('Chuyên mục cha không hợp lệ!');
                            }
                        }
                    }
                }
            ],
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
