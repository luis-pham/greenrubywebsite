<?php
namespace Modules\BackEnd\Http\Requests\Cruise;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;

class CruiseItineraryCreateUpdateRequest extends FormRequest
{
    public function rules(): array
    {
        $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH');
        $cruiseId = $this->route('id');

        return [
            'itinerary_id' => 'required|integer|exists:app_itinerary,id',
            'start_at' => [
                'required',
                'date',
                'date_format:Y-m-d',
                'after_or_equal:today',
                function ($attribute, $value, $fail) use ($cruiseId, $isUpdate) {
                    // Get language_id of the current cruise
                    $currentCruise = DB::table('app_cruise')
                        ->where('id', $cruiseId)
                        ->select('language_id')
                        ->first();

                    if (!$currentCruise) {
                        $fail('Du thuyền không tồn tại.');
                        return;
                    }

                    $query = DB::table('app_cruise_itinerary')
                        ->where('cruise_id', $cruiseId)
                        ->where('start_at', $value);

                    if ($query->exists()) {
                        $fail("Ngày khởi hành {$value} đã tồn tại cho hành trình này. Vui lòng chọn ngày khác.");
                    }
                },
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'itinerary_id.required' => 'Vui lòng chọn hành trình.',
            'itinerary_id.integer'  => 'Mã hành trình phải là số nguyên hợp lệ.',
            'itinerary_id.exists'   => 'Hành trình được chọn không tồn tại hoặc không hợp lệ.',
            'start_at.required'     => 'Ngày khởi hành là bắt buộc.',
            'start_at.date'         => 'Ngày khởi hành phải là ngày hợp lệ.',
            'start_at.date_format'  => 'Ngày khởi hành phải có dạng YYYY-mm-dd.',
            'start_at.after_or_equal' => 'Ngày khởi hành phải lớn hơn hoặc bằng ngày hiện tại'
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('start_at')) {
            try {
                $this->merge([
                    'start_at' => Carbon::createFromFormat('d/m/Y', $this->start_at)
                        ->format('Y-m-d'),
                ]);
            } catch (\Exception $e) {
                // Invalid → validation will fail on date_format rule
            }
        }
    }
}
