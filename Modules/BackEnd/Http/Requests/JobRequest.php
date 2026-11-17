<?php
namespace Modules\BackEnd\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\BackEnd\Services\AppJobService;

class JobRequest extends FormRequest
{
    public function rules()
    {
        return [
            'title' => 'required|max:150',
            'slug' => 'nullable|max:120',
            'department' => 'required|max:100',
            'employment_type' => ['nullable', Rule::in(array_keys(config('backend.jobEmploymentType')))],
            'location' => 'nullable|max:150',
            'address_locality' => 'nullable|max:100',
            'address_region' => 'nullable|max:100',
            'summary' => 'nullable|max:600',
            'intro' => 'nullable',
            'description' => 'nullable',
            'requirements' => 'nullable',
            'benefits' => 'nullable',
            'apply_email' => 'nullable|email|max:150',
            'status' => ['required', Rule::in(array_keys(config('backend.jobStatus')))],
            'posted_at' => 'nullable|date',
            'deadline' => 'nullable|date',
            'seo_title' => 'nullable|max:65',
            'seo_description' => 'nullable|max:255',
            'ord' => 'nullable|integer|min:0',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $slug = AppJobService::slugify($this->input('slug') ?: $this->input('title'));
            if ($slug === '') {
                $validator->errors()->add('slug', 'Đường dẫn (slug) không hợp lệ!');
            } elseif (AppJobService::slugTaken($slug, $this->route('id'))) {
                $validator->errors()->add('slug', "Đường dẫn \"$slug\" đã được dùng cho vị trí khác!");
            }

            if ($this->input('status') !== 'published') {
                return;
            }
            // A published vacancy must say what the job is: the page and Google's JobPosting need a description.
            $hasText = fn ($field) => trim(html_entity_decode(strip_tags((string) $this->input($field)), ENT_QUOTES | ENT_HTML5, 'UTF-8'), " \t\n\r\0\x0B\u{00A0}") !== '';
            if (!$hasText('description') && !$hasText('intro')) {
                $validator->errors()->add('description', 'Vị trí đang tuyển cần có "Giới thiệu công việc" hoặc "Mô tả công việc"!');
            }
            $deadline = $this->input('deadline');
            if ($deadline && strtotime($deadline) !== false && date('Y-m-d', strtotime($deadline)) < now()->toDateString()) {
                $validator->errors()->add('deadline', 'Hạn nộp hồ sơ đã qua: đổi hạn nộp hoặc chọn trạng thái "Đã đóng".');
            }
        });
    }

    public function messages()
    {
        return [
            'title.required' => 'Vui lòng nhập Tên vị trí!',
            'department.required' => 'Vui lòng nhập Bộ phận!',
            'apply_email.email' => 'Email nhận hồ sơ không hợp lệ!',
            'seo_title.max' => 'Tiêu đề SEO tối đa 65 ký tự!',
            'deadline.date' => 'Hạn nộp hồ sơ không hợp lệ!',
        ];
    }
}
