<?php

namespace Modules\BackEnd\Http\Requests\Itinerary;

use DB;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\BackEnd\Helpers\CruiseUtils;
use Modules\BackEnd\Helpers\LanguageUtils;

class ItineraryCreateUpdateRequest extends FormRequest
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
            'name'            => ['required', 'string', 'max:99', 'min:3',$uniqueRule],
            'description'     => ['required', 'string', 'max:300', 'min:3'],
            'duration'        => ['required', 'integer', 'min:1', 'max:'.array_key_last(CruiseUtils::getListDuration())],
            'image_link'      => ['nullable', 'string', 'max:255'],
            'cover_link'      => ['nullable', 'string', 'max:255'],
            'image_gallery'   => ['nullable','array'],
            'important_note'  => ['nullable', 'array'], // longtext → không giới hạn max
            'important_note.*.content'       => ['required', 'string', 'max:255'],
            'important_note.*.image_link' => ['nullable', 'string', 'max:255'],

            'destination'     => ['required', 'string'], // longtext, nhưng thường validate như string
            'start_time'      => ['nullable', 'date_format:H:i'], // chỉ giờ:phút (24h format),
            'listServiceId'   => ['nullable', 'array'],
            'listActivityId'  => ['nullable', 'array'],
            'bay'             => ['required','max:256'],
            'itinerary_days'    => ['required', 'array','size:'.$this->input('duration')],
            'itinerary_days.*.itinerary_day_details'       => ['required', 'array', 'min:1'],
            'itinerary_days.*.itinerary_day_details.*.time' => [
                'required',
                'date_format:H:i',
            ],

            // Title field
            'itinerary_days.*.itinerary_day_details.*.title' => [
                'required',
                'string',
                'max:255',
            ],

            // Description field
            'itinerary_days.*.itinerary_day_details.*.description' => [
                'required',
                'string',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'           => 'Tên hành trình là bắt buộc.',
            'name.max'                => 'Tên hành trình không được vượt quá 100 ký tự.',
            'name.min'                => 'Tên hành trình phải có ít nhất 3 ký tự.',
            'name.unique'             => 'Tên hành trình đã tồn tại, vui lòng chọn tên khác.',

            'description.required'    => 'Mô tả ngắn là bắt buộc.',
            'description.max'         => 'Mô tả ngắn không được vượt quá 300 ký tự.',
            'description.min'         => 'Mô tả ngắn phải có ít nhất 3 ký tự.',

            'duration.required'       => 'Thời lượng là bắt buộc.',
            'duration.integer'        => 'Thời lượng phải là số nguyên.',
            'duration.min'            => 'Thời lượng phải lớn hơn hoặc bằng 1.',
            'duration.max'            => 'Thời lượng tối đa hợp lý là 365 ngày.',

            'image_link.max'          => 'Đường dẫn ảnh đại diện không được vượt quá 255 ký tự.',
            'cover_link.max'          => 'Đường dẫn ảnh bìa không được vượt quá 255 ký tự.',

            'important_note.array' => 'Ghi chú quan trọng phải là một danh sách.',
            // important_note.*.name
            'important_note.*.content.required' => 'Nội dung ghi chú quan trọng không được để trống.',
            'important_note.*.content.string' => 'Nội dung ghi chú quan trọng phải là chuỗi ký tự.',
            'important_note.*.content.max' => 'Nội dung ghi chú quan trọng không được vượt quá 255 ký tự.',

            'important_note.*.image_link.string' => 'Ảnh ghi chú quan trọng phải là chuỗi ký tự.',
            'important_note.*.image_link.max' => 'Ảnh ghi chú quan trọng không được vượt quá 255 ký tự.',

            'destination.required'    => 'Điểm đến là bắt buộc.',

            'start_time.date_format'  => 'Giờ bắt đầu phải có định dạng HH:MM (ví dụ: 08:30).',

            'itinerary_days.required' =>
                'Danh sách hành trình theo ngày là bắt buộc.',

            'itinerary_days.size' =>
                'Số ngày trong hành trình phải bằng thời lượng đã chọn.',

            'itinerary_days.*.itinerary_day_details.required' =>
                'Mỗi ngày phải có ít nhất một hoạt động.',

            'itinerary_days.*.itinerary_day_details.min' =>
                'Mỗi ngày phải có ít nhất một hoạt động.',

            'itinerary_days.*.itinerary_day_details.*.time.required' =>
                'Thời gian hoạt động là bắt buộc.',

            'itinerary_days.*.itinerary_day_details.*.time.date_format' =>
                'Thời gian phải có định dạng HH:MM (ví dụ: 08:30).',

            'itinerary_days.*.itinerary_day_details.*.title.required' =>
                'Tiêu đề hoạt động là bắt buộc.',

            'itinerary_days.*.itinerary_day_details.*.description.required' =>
                'Mô tả ngắn hoạt động là bắt buộc.',
            // Các rule chung
            '*.required'              => 'Trường này là bắt buộc.',
            '*.string'                => 'Giá trị phải là chuỗi ký tự.',
            '*.integer'               => 'Giá trị phải là số nguyên.',
            '*.numeric'               => 'Giá trị phải là số.',

        ];
    }

    /**
     * Prepare the data for validation (optional - nếu cần xử lý trước)
     */
    protected function prepareForValidation()
    {
        // Ví dụ: trim các trường string
        $this->merge([
            'name'        => trim($this->name ?? ''),
            'description' => trim($this->description ?? ''),
            'destination' => trim($this->destination ?? ''),
            'important_note' => collect($this->important_note)
                                ->filter(fn ($item) => is_array($item))
                                ->map(function ($item) {
                                    return [
                                        'content'       => trim($item['content'] ?? ''),
                                        'image_link' => trim($item['image_link'] ?? ''),
                                    ];
                                })
                                ->filter(function ($item) {
                                    return $item['content'] !== '';
                                })
                                ->values()
                                ->toArray(),
            'image_gallery' => json_decode($this->image_gallery ?? '',true),
        ]);
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated();

        if(isset($data['destination'])){
            $data['destination'] = json_encode(preg_split('/\s*,\s*/', $data['destination']));
        }

        return data_get($data, $key, $default);
    }
}
