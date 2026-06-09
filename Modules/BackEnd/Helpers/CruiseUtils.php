<?php

namespace Modules\BackEnd\Helpers;

class CruiseUtils
{
    public static function formatDisplayDurationName($duration)
    {
        $value = '';
        if ($duration <= 0) {
            return $value;
        }
        
        $value = $duration . ' Ngày';
        if ($duration > 1) {
            $value .= ' ' . ($duration - 1) . ' Đêm';
        }
        return $value;
    }

    public static function getListDuration()
    {
        return [
            2 => self::formatDisplayDurationName(2),
            3 => self::formatDisplayDurationName(3)
        ];
    }

    public static function formatDisplayItineraryDestination($destination)
    {
        try {
            $list = json_decode($destination, true);
            return implode(", ", $list);
        } catch (\Exception $e) {
            return '';
        }
    }

    public static function formatDisplayServiceType($type)
    {
        switch ($type) {
            case 0:
                return '<span class="badge badge-warning">Không bao gồm</span>';
            case 1:
                return '<span class="badge badge-info">Bao gồm</span>';
            default:
                return '';
        }
    }
}
