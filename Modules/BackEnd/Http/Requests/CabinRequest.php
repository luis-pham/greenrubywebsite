<?php
namespace Modules\BackEnd\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Modules\BackEnd\Helpers\CruiseUtils;
use Modules\BackEnd\Helpers\FacilityProfileUtils;

class CabinRequest extends FormRequest
{
    protected static $guestLabelKeys = [
        1 => 'guest_single',
        2 => 'guest_double',
        3 => 'guest_triple',
        4 => 'guest_quad',
    ];

    protected static function getGuestLabel($guest)
    {
        if (isset(self::$guestLabelKeys[$guest])) {
            return __('backend::cabin.' . self::$guestLabelKeys[$guest]);
        }
        return __('backend::cabin.guest_count', ['n' => $guest]);
    }

    protected function getFacilityProfile()
    {
        return FacilityProfileUtils::getProfileByGroupId($this->input('group_id'));
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'name' => $this->normalizeString($this->input('name')),
            'summary' => $this->normalizeString($this->input('summary')),
            'content' => $this->normalizeString($this->input('content')),
            'image_link' => $this->normalizeString($this->input('image_link')),
            'view' => $this->normalizeString($this->input('view')),
            'room_title' => $this->normalizeStringArray($this->input('room_title', [])),
            'room_description' => $this->normalizeStringArray($this->input('room_description', [])),
            'amenity_name' => $this->normalizeStringArray($this->input('amenity_name', [])),
            'amenity_description' => $this->normalizeStringArray($this->input('amenity_description', [])),
            'amenity_icon' => $this->normalizeStringArray($this->input('amenity_icon', [])),
            'audience_name' => $this->normalizeStringArray($this->input('audience_name', [])),
            'audience_icon' => $this->normalizeStringArray($this->input('audience_icon', [])),
        ]);

        $profile = $this->getFacilityProfile();
        if ($profile && $profile !== FacilityProfileUtils::PROFILE_CABIN) {
            $defaults = FacilityProfileUtils::applyDefaultValues($this->all(), $profile);
            $this->merge($defaults);
        }
    }

    protected function normalizeString($value)
    {
        if (is_string($value)) {
            $value = trim($value);

            return $value === '' ? null : $value;
        }

        return $value;
    }

    protected function normalizeStringArray($values)
    {
        if (!is_array($values)) {
            return [];
        }

        $result = [];

        foreach ($values as $key => $value) {
            if (!is_string($value)) {
                continue;
            }

            $trimmed = trim($value);

            if ($trimmed === '') {
                continue;
            }

            $result[$key] = $trimmed;
        }

        return $result;
    }

    public function rules(Request $request)
    {
        $profile = $this->getFacilityProfile() ?: FacilityProfileUtils::PROFILE_CABIN;

        $rules = [
            'name' => 'required|max:100',
            'group_id' => 'required|integer|exists:app_group,id',
            'cruise_id' => 'required|exists:app_cruise,id',
            'summary' => 'required|max:200',
            'content' => 'nullable',
            'discount_percent' => 'nullable|numeric|min:0|max:100',
            'ord' => 'nullable|integer',
        ];

        if (FacilityProfileUtils::requiresView($profile)) {
            $rules['view'] = 'required|max:50';
        } else {
            $rules['view'] = 'nullable|max:50';
        }

        if (FacilityProfileUtils::requiresCapacity($profile)) {
            if ($profile === FacilityProfileUtils::PROFILE_EVENT) {
                $rules['capacity'] = 'required|numeric|min:1|max:500';
            } else {
                $rules['capacity'] = 'required|numeric|min:1|max:10';
            }
        } else {
            $rules['capacity'] = 'nullable|numeric|min:1|max:500';
        }

        if (FacilityProfileUtils::requiresArea($profile)) {
            $rules['area'] = 'required|numeric|min:1|max:10000';
        } else {
            $rules['area'] = 'nullable|numeric|min:1|max:10000';
        }

        if ($profile === FacilityProfileUtils::PROFILE_CABIN) {
            $rules['over_capacity_adult'] = 'nullable|integer|min:0|max:50|lte:capacity';
            $rules['over_capacity_child_6_12'] = 'nullable|integer|min:0|max:50|lte:capacity';
            $rules['over_capacity_child_2_5'] = 'nullable|integer|min:0|max:50|lte:capacity';
            $rules['over_capacity_infant'] = 'nullable|integer|min:0|max:50|lte:capacity';
        } else {
            $rules['over_capacity_adult'] = 'nullable|integer|min:0|max:50';
            $rules['over_capacity_child_6_12'] = 'nullable|integer|min:0|max:50';
            $rules['over_capacity_child_2_5'] = 'nullable|integer|min:0|max:50';
            $rules['over_capacity_infant'] = 'nullable|integer|min:0|max:50';
        }

        if (FacilityProfileUtils::requiresPrice($profile)) {
            $capacity = (int) $this->input('capacity', 0);
            if ($capacity > 0) {
                foreach (array_keys(CruiseUtils::getListDuration()) as $duration) {
                    for ($guest = 1; $guest <= $capacity; $guest++) {
                        $rules["price.{$duration}.{$guest}"] = 'required|numeric|min:0.01|max:10000000000';
                    }
                }
            }
        }

        return $rules;
    }

    public function messages()
    {
        $messages = [
            'name.required' => __('backend::cabin.validation_name_required'),
            'name.max' => __('backend::cabin.validation_name_max'),
            'group_id.required' => __('backend::cabin.validation_group_id_required'),
            'group_id.integer' => __('backend::cabin.validation_group_id_integer'),
            'group_id.exists' => __('backend::cabin.validation_group_id_exists'),
            'cruise_id.required' => __('backend::cabin.validation_cruise_id_required'),
            'cruise_id.exists' => __('backend::cabin.validation_cruise_id_exists'),
            'summary.required' => __('backend::cabin.validation_summary_required'),
            'summary.max' => __('backend::cabin.validation_summary_max'),
            'view.required' => __('backend::cabin.validation_view_required'),
            'view.max' => __('backend::cabin.validation_view_max'),
            'capacity.required' => __('backend::cabin.validation_capacity_required'),
            'capacity.numeric' => __('backend::cabin.validation_capacity_numeric'),
            'capacity.min' => __('backend::cabin.validation_capacity_min'),
            'capacity.max' => __('backend::cabin.validation_capacity_max'),
            'over_capacity_adult.integer' => __('backend::cabin.validation_over_capacity_integer'),
            'over_capacity_adult.min' => __('backend::cabin.validation_over_capacity_min'),
            'over_capacity_adult.max' => __('backend::cabin.validation_over_capacity_max'),
            'over_capacity_child_6_12.integer' => __('backend::cabin.validation_over_capacity_integer'),
            'over_capacity_child_6_12.min' => __('backend::cabin.validation_over_capacity_min'),
            'over_capacity_child_6_12.max' => __('backend::cabin.validation_over_capacity_max'),
            'over_capacity_child_2_5.integer' => __('backend::cabin.validation_over_capacity_integer'),
            'over_capacity_child_2_5.min' => __('backend::cabin.validation_over_capacity_min'),
            'over_capacity_child_2_5.max' => __('backend::cabin.validation_over_capacity_max'),
            'over_capacity_infant.integer' => __('backend::cabin.validation_over_capacity_integer'),
            'over_capacity_infant.min' => __('backend::cabin.validation_over_capacity_min'),
            'over_capacity_infant.max' => __('backend::cabin.validation_over_capacity_max'),
            'area.required' => __('backend::cabin.validation_area_required'),
            'area.numeric' => __('backend::cabin.validation_area_numeric'),
            'area.min' => __('backend::cabin.validation_area_min'),
            'area.max' => __('backend::cabin.validation_area_max'),
            'discount_percent.numeric' => __('backend::cabin.validation_discount_numeric'),
            'discount_percent.min' => __('backend::cabin.validation_discount_min'),
            'discount_percent.max' => __('backend::cabin.validation_discount_max'),
            'ord.integer' => __('backend::cabin.validation_ord_integer'),
        ];

        $profile = $this->getFacilityProfile() ?: FacilityProfileUtils::PROFILE_CABIN;
        if (FacilityProfileUtils::requiresPrice($profile)) {
            $capacity = (int) $this->input('capacity', 0);
            if ($capacity > 0) {
                $durationLabels = CruiseUtils::getListDuration();
                foreach (array_keys($durationLabels) as $duration) {
                    $durationLabel = $durationLabels[$duration];
                    for ($guest = 1; $guest <= $capacity; $guest++) {
                        $guestLabel = self::getGuestLabel($guest);
                        $key = "price.{$duration}.{$guest}";
                        $messages["{$key}.required"] = __('backend::cabin.validation_price_required', [
                            'duration' => $durationLabel,
                            'guest' => $guestLabel,
                        ]);
                        $messages["{$key}.numeric"] = __('backend::cabin.validation_price_numeric', [
                            'duration' => $durationLabel,
                            'guest' => $guestLabel,
                        ]);
                        $messages["{$key}.min"] = __('backend::cabin.validation_price_min', [
                            'duration' => $durationLabel,
                            'guest' => $guestLabel,
                        ]);
                        $messages["{$key}.max"] = __('backend::cabin.validation_price_max', [
                            'duration' => $durationLabel,
                            'guest' => $guestLabel,
                        ]);
                    }
                }
            }
        }

        return $messages;
    }
}
