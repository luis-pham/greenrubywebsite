<?php

namespace Modules\BackEnd\Helpers;

use Modules\BackEnd\Services\AdLoggingService;

class Logging
{
    public static function log($action, $detail, $type)
    {
        $logLevel = config('backend.logLevel');
        if (!in_array($type, $logLevel)) {
            return;
        }

        $data = [
            'action' => $action,
            'detail' => $detail,
            'ip' => \Request::ip(),
            'user_agent' => \Request::server('HTTP_USER_AGENT'),
            'type' => $type
        ];
        if (\Auth::user()) {
            $data['user_id'] = \Auth::user()->id;
        }

        AdLoggingService::create($data);
    }
    
    public static function logInfo($action, $detail = null)
    {
        self::log($action, $detail, config('backend.logType.info'));
    }
    
    public static function logError($action, $detail = null)
    {
        self::log($action, $detail, config('backend.logType.error'));
    }
    
    public static function logSystem($action, $detail = null)
    {
        self::log($action, $detail, config('backend.logType.system'));
    }
}
