<?php

namespace Modules\BackEnd\Http\Requests\Cruise;

use DB;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\BackEnd\Helpers\LanguageUtils;

class CruiseCreateUpdateRequest extends FormRequest
{
    public function rules(): array
    {
        $language = LanguageUtils::getCurrentLanguage();
        $id = $this->route('id');

        $uniqueRule = Rule::unique('app_itinerary')
            ->where(fn($query) => $query
                ->where('language_id', $language->id)
                ->where(DB::raw('BINARY name'), DB::raw('BINARY ' . DB::getPdo()->quote($this->input('name'))))
            )
            ->when($id, fn($rule) => $rule->ignore($id));
        return [
            'name'               => ['required', 'string', 'max:100', 'min:3',$uniqueRule],
            'summary'            => ['required', 'string', 'max:300', 'min:3'],
            'content'            => ['nullable', 'string'], // longtext → không giới hạn max
            'image_link'         => ['required', 'string', 'max:255'], // hoặc 'image' nếu upload file
            'cover_link'         => ['nullable', 'string', 'max:255'],
            'image_gallery'      => ['nullable', 'array'],
            'star_rating'        => ['nullable', 'numeric', 'between:1,5'], // thường 1.0 → 5.0
            'capacity'           => ['required', 'integer', 'min:1', 'max:1000'], // tùy business, có thể điều chỉnh max
            'total_floor'        => ['required', 'integer', 'min:1', 'max:20'],   // số tầng tàu
            'dimension_length'   => ['required', 'numeric', 'min:10', 'max:1000'], // chiều dài tàu (mét)
            'year_built'         => ['required', 'integer', 'min:1900', 'max:' . ((int)date('Y') + 1)],
            'description_design' => ['nullable', 'string'], // longtext
            'listAmenityId'      => ['nullable', 'array'],
            'listServiceId'      => ['nullable', 'array'],
            'green_technology.image_link' => 'nullable|string|max:255',
            'green_technology.name' => 'nullable|string|max:255',
            'green_technology.description' => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'             => 'Tên du thuyền là bắt buộc.',
            'name.max'                  => 'Tên du thuyền không được vượt quá 100 ký tự.',
            'name.min'                  => 'Tên du thuyền phải có ít nhất 3 ký tự.',
            'name.unique'             => 'Tên du thuyền đã tồn tại, vui lòng chọn tên khác.',

            'summary.required'          => 'Mô tả là bắt buộc.',
            'summary.max'               => 'Mô tả không được vượt quá 300 ký tự.',
            'summary.min'               => 'Mô tả phải có ít nhất 3 ký tự.',

            'image_link.required'       => 'Ảnh đại diện là bắt buộc.',
            'image_link.max'            => 'Đường dẫn ảnh đại diện không được vượt quá 255 ký tự.',

            'cover_link.max'          => 'Đường dẫn ảnh bìa không được vượt quá 255 ký tự.',

            'star_rating.between'       => 'Đánh giá sao phải nằm trong khoảng 1 đến 5.',

            'capacity.required'         => 'Sức chứa là bắt buộc.',
            'capacity.min'              => 'Sức chứa phải lớn hơn hoặc bằng 1.',
            'capacity.max'              => 'Sức chứa tối đa hợp lý là 1000 người.',

            'total_floor.required'      => 'Số tầng là bắt buộc.',
            'total_floor.min'           => 'Số tầng phải ít nhất là 1.',
            'total_floor.max'           => 'Số tầng tối đa hợp lý là 20.',

            'dimension_length.required' => 'Chiều dài tàu là bắt buộc.',
            'dimension_length.min'      => 'Chiều dài tàu phải lớn hơn hoặc bằng 10 mét.',
            'dimension_length.max'      => 'Chiều dài tàu tối đa hợp lý là 200 mét.',

            'year_built.required'       => 'Năm đóng tàu là bắt buộc.',
            'year_built.min'            => 'Năm đóng tàu phải từ 1900 trở lên.',
            'year_built.max'            => 'Năm đóng tàu không được lớn hơn năm hiện tại.',

            'description_design.string' => 'Mô tả thiết kế phải là chuỗi',

            'green_technology.image_link.string' => 'Đường dẫn hình ảnh của công nghệ xanh phải là chuỗi ký tự.',
            'green_technology.image_link.max' => 'Đường dẫn hình ảnh của công nghệ xanh không được vượt quá 255 ký tự.',
            'green_technology.name.string' => 'Tên của công nghệ xanh phải là chuỗi ký tự.',
            'green_technology.name.max' => 'Tên của công nghệ xanh không được vượt quá 255 ký tự.',
            'green_technology.description.string' => 'Mô tả của công nghệ xanh phải là chuỗi ký tự.',
            'green_technology.description.max' => 'Mô tả của công nghệ xanh không được vượt quá 255 ký tự.',

            // Thêm các message khác nếu cần
            '*.integer'                 => 'Giá trị phải là số nguyên.',
            '*.numeric'                 => 'Giá trị phải là số.',
        ];
    }

    protected function prepareForValidation() {
        $this->merge([
            'name' => trim($this->name ?? ''),
            'summary' => trim($this->summary ?? ''),
            'content' => trim($this->content ?? ''),
            'image_link' => trim($this->image_link ?? ''),
            'description_design' => trim($this->description_design ?? ''),
            'image_gallery' => json_decode($this->image_gallery ?? '',true),
            'green_technology' => [
                'name'        => trim($this->green_technology['name'] ?? ''),
                'image_link'  => trim($this->green_technology['image_link'] ?? ''),
                'description' => trim($this->green_technology['description'] ?? ''),
            ],
        ]);
    }
}
