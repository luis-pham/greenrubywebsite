<?php

namespace Modules\BackEnd\Http\Requests\Itinerary;

use Illuminate\Foundation\Http\FormRequest;

class ItineraryIndexPagingRequest extends FormRequest
{

    public function rules(): array {
        return [
            'name' => 'nullable|string|max:255',
            'duration' => 'nullable|integer|min:2|max:365'
        ];
    }

    public function messages(): array {
        return [
            'name.string' => 'Tên du thuyền phải là chuỗi ',
            'name.max' => 'Tên du thuyền phải nhỏ hơn hoặc bằng :max kí tự',
            'duration.integer' => 'Thời lượng phải là số nguyên',
            'duration.min' => 'Thời lượng phải lớn hơn :min.',
            'duration.max' => 'Thời lượng phải nhỏ hơn :max.',
        ];
    }

    public function prepareForValidation(){
        $this->merge([
            'name' => trim($this->name ?? '')
        ]);
    }
}
