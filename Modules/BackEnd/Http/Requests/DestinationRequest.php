<?php
namespace Modules\BackEnd\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\BackEnd\Helpers\LanguageUtils;
use Modules\BackEnd\Services\AppDestinationService;

class DestinationRequest extends FormRequest
{
    public function rules()
    {
        return [
            'name' => 'required|max:150',
            'heading' => 'nullable|max:160',
            'slug' => 'nullable|max:120',
            'bay' => ['required', Rule::in(array_keys(config('backend.destinationBay')))],
            'category' => ['required', Rule::in(array_keys(config('backend.destinationCategory')))],
            'status' => ['required', Rule::in(array_keys(config('backend.destinationStatus')))],
            'summary' => 'required_if:status,published|nullable|max:600',
            'content' => 'required_if:status,published|nullable',
            'latitude' => 'nullable|numeric|between:-90,90|required_with:longitude',
            'longitude' => 'nullable|numeric|between:-180,180|required_with:latitude',
            'coords_source' => 'nullable|max:255',
            'image_link' => 'nullable|max:255',
            'image_alt' => 'required_with:image_link|nullable|max:255',
            'image_credit' => 'nullable|max:255',
            'fact_checked_at' => 'nullable|date',
            'seo_title' => 'nullable|max:65',
            'seo_description' => 'nullable|max:255',
            'sources.*.url' => 'nullable|url|max:500',
            'itinerary_ids' => 'nullable|array',
            'experience_ids' => 'nullable|array',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $slug = AppDestinationService::slugify($this->input('slug') ?: $this->input('name'));
            if ($slug === '') {
                $validator->errors()->add('slug', 'Đường dẫn (slug) không hợp lệ!');
            } elseif (AppDestinationService::slugTaken($slug, LanguageUtils::getCurrentLanguage()->id, $this->route('id'))) {
                $validator->errors()->add('slug', "Đường dẫn \"$slug\" đã được dùng cho điểm đến khác!");
            }
        });
    }

    public function messages()
    {
        return [
            'name.required' => 'Vui lòng nhập Tên điểm đến!',
            'summary.required_if' => 'Điểm đến đã xuất bản cần có Mô tả ngắn!',
            'content.required_if' => 'Điểm đến đã xuất bản cần có Nội dung!',
            'image_alt.required_with' => 'Vui lòng nhập mô tả ảnh (alt) cho ảnh đại diện!',
            'latitude.required_with' => 'Cần nhập đủ cả vĩ độ và kinh độ!',
            'longitude.required_with' => 'Cần nhập đủ cả vĩ độ và kinh độ!',
            'sources.*.url.url' => 'Đường dẫn nguồn tham khảo không hợp lệ!',
            'seo_title.max' => 'Tiêu đề SEO tối đa 65 ký tự!',
        ];
    }
}
