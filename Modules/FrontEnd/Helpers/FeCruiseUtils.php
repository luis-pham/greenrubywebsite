<?php

namespace Modules\FrontEnd\Helpers;

class FeCruiseUtils
{
    public static function formatDisplayDurationName($duration, $displayShortName = false)
    {
        $value = '';
        if ($duration <= 0) {
            return $value;
        }

        $dayUnit = $displayShortName ? __('frontend::common.day_short') : ' ' . __('frontend::common.day');
        $nightUnit = $displayShortName ? __('frontend::common.night_short') : ' ' . __('frontend::common.night');

        $value = $duration . $dayUnit;
        if ($duration > 1) {
            if (!$displayShortName) {
                $value .= ' ';
            }
            $value .= ($duration - 1) . $nightUnit;
        }
        return $value;
    }

    public static function getListDuration()
    {
        return [
            2 => self::formatDisplayDurationName(2,true),
            3 => self::formatDisplayDurationName(3,true)
        ];
    }

    public static function getListBay()
    {
        return [
            1 => __('frontend::common.ha_long'),
            2 => __('frontend::common.lan_ha'),
        ];
    }

    public static function formatDisplayItineraryBay($bay){
        return match ($bay) {
            1 => __('frontend::common.ha_long_bay'),
            2 => __('frontend::common.lan_ha_bay'),
            default => "",
        };
    }

    public static function getItineraryDeparture($destination){
        try{
            $list = json_decode($destination, true);
            return count($list) > 0 ? $list[0] : '';
        }catch (\Exception $e){
            return '';
        }
    }
}
