<?php
namespace Modules\BackEnd\Helpers;

use Modules\BackEnd\Entities\AppGroup;

class FacilityProfileUtils
{
    public const PROFILE_CABIN = 'cabin';
    public const PROFILE_ONBOARD = 'onboard';
    public const PROFILE_EVENT = 'event';

    public static function normalizeSlug($slug)
    {
        return strtolower(str_replace([' ', '_'], '-', trim((string) $slug)));
    }

    public static function getProfileBySlug($slug)
    {
        $slug = self::normalizeSlug($slug);
        $map = config('backend.facilityProfileSlugs', []);

        return $map[$slug] ?? self::PROFILE_ONBOARD;
    }

    public static function getProfileByGroupId($groupId)
    {
        if (!$groupId) {
            return null;
        }

        $group = AppGroup::find($groupId);
        if (!$group) {
            return null;
        }

        return self::getProfileBySlug($group->slug ?? '');
    }

    public static function requiresView($profile)
    {
        return $profile === self::PROFILE_CABIN;
    }

    public static function requiresPrice($profile)
    {
        return $profile === self::PROFILE_CABIN;
    }

    public static function requiresCapacity($profile)
    {
        return in_array($profile, [self::PROFILE_CABIN, self::PROFILE_EVENT], true);
    }

    public static function requiresArea($profile)
    {
        return in_array($profile, [self::PROFILE_CABIN, self::PROFILE_EVENT], true);
    }

    public static function getVisibleSections($profile)
    {
        $sections = config('backend.facilityProfileSections.' . $profile, []);

        return is_array($sections) ? $sections : [];
    }

    public static function getLabelKeys($profile)
    {
        $key = 'profile_' . $profile;

        return [
            'label_name' => __('backend::cabin.' . $key . '_label_name'),
            'placeholder_name' => __('backend::cabin.' . $key . '_placeholder_name'),
            'label_summary' => __('backend::cabin.' . $key . '_label_summary'),
            'placeholder_summary' => __('backend::cabin.' . $key . '_placeholder_summary'),
            'label_content' => __('backend::cabin.' . $key . '_label_content'),
            'placeholder_content' => __('backend::cabin.' . $key . '_placeholder_content'),
            'label_view' => __('backend::cabin.' . $key . '_label_view'),
            'placeholder_view' => __('backend::cabin.' . $key . '_placeholder_view'),
            'label_capacity_max' => __('backend::cabin.' . $key . '_label_capacity_max'),
            'label_area_m2' => __('backend::cabin.' . $key . '_label_area_m2'),
            'section_gallery' => __('backend::cabin.' . $key . '_section_gallery'),
            'section_operations' => __('backend::cabin.' . $key . '_section_operations'),
        ];
    }

    public static function getJsConfig()
    {
        $profiles = [
            self::PROFILE_CABIN,
            self::PROFILE_ONBOARD,
            self::PROFILE_EVENT,
        ];

        $labels = [];
        foreach ($profiles as $profile) {
            $labels[$profile] = self::getLabelKeys($profile);
        }

        $sections = [];
        foreach ($profiles as $profile) {
            $sections[$profile] = self::getVisibleSections($profile);
        }

        return [
            'slugToProfile' => config('backend.facilityProfileSlugs', []),
            'profileSections' => $sections,
            'profileLabels' => $labels,
            'defaultProfile' => self::PROFILE_ONBOARD,
        ];
    }

    public static function applyDefaultValues(array $data, $profile)
    {
        if ($profile === self::PROFILE_CABIN) {
            return $data;
        }

        if (empty($data['view'])) {
            $data['view'] = '—';
        }

        $data['cabin_class'] = null;
        $data['discount_percent'] = array_key_exists('discount_percent', $data) && $data['discount_percent'] !== null && $data['discount_percent'] !== ''
            ? $data['discount_percent']
            : 0;
        $data['over_capacity_adult'] = $data['over_capacity_adult'] ?? 0;
        $data['over_capacity_child_6_12'] = $data['over_capacity_child_6_12'] ?? 0;
        $data['over_capacity_child_2_5'] = $data['over_capacity_child_2_5'] ?? 0;
        $data['over_capacity_infant'] = $data['over_capacity_infant'] ?? 0;

        if ($profile === self::PROFILE_ONBOARD) {
            if (empty($data['capacity'])) {
                $data['capacity'] = 1;
            }
            if (empty($data['area'])) {
                $data['area'] = 1;
            }
        }

        return $data;
    }
}
