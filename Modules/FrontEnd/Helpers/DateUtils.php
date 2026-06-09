<?php

namespace Modules\FrontEnd\Helpers;

use Carbon\Carbon;

class DateUtils
{
    public static function formatDisplayHHMMA($date) : string{
        if(!$date) return "";
        if(is_string($date)) $date = Carbon::parse($date);
        return $date->format('h:i A');
    }
}
