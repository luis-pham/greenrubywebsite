<?php

namespace Modules\BackEnd\Helpers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Modules\BackEnd\Services\AdConfigService;
use Modules\BackEnd\Services\AdUserService;

class Utilities
{
    public static function getUrlWithGoBack($targetUrl, $currentUrl = '')
    {
        if (!$currentUrl) {
            $currentUrl = Request::getRequestUri();
        }
        return self::setQueryStringToUrl($targetUrl, ['lastUrl' => $currentUrl]);
    }

    public static function getGoBackUrl($defaultUrl)
    {
        $goBackUrl = Request::get('lastUrl');
        return !empty(trim($goBackUrl)) ? $goBackUrl : $defaultUrl;
    }

    public static function setQueryStringToUrl($url, array $queryString = [])
    {
        if (count($queryString) == 0) {
            return $url;
        }

        $currentQuery = [];

        $parseUrl = parse_url($url);

        $url = $parseUrl['path'];
        if (isset($parseUrl['query'])) {
            $query = $parseUrl['query'];
            $query = explode('&', $query);

            for ($i = 0; $i < count($query); $i++) {
                $obj = explode('=', $query[$i]);
                if (isset($obj[0]) && $obj[0]) {
                    $currentQuery[$obj[0]] = isset($obj[1]) ? $obj[1] : '';
                }
            }

            $queryString = array_merge(
                $currentQuery,
                $queryString
            );
        }

        return $url . '/?' . http_build_query($queryString);
    }

    public static function stripVietnameseAccents($value)
    {
        $value = preg_replace("/(à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ)/", 'a', $value);
        $value = preg_replace("/(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ)/", 'e', $value);
        $value = preg_replace("/(ì|í|ị|ỉ|ĩ)/", 'i', $value);
        $value = preg_replace("/(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ)/", 'o', $value);
        $value = preg_replace("/(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ)/", 'u', $value);
        $value = preg_replace("/(ỳ|ý|ỵ|ỷ|ỹ)/", 'y', $value);
        $value = preg_replace("/(đ)/", 'd', $value);

        $value = preg_replace("/(À|Á|Ạ|Ả|Ã|Â|Ầ|Ấ|Ậ|Ẩ|Ẫ|Ă|Ằ|Ắ|Ặ|Ẳ|Ẵ)/", 'A', $value);
        $value = preg_replace("/(È|É|Ẹ|Ẻ|Ẽ|Ê|Ề|Ế|Ệ|Ể|Ễ)/", 'E', $value);
        $value = preg_replace("/(Ì|Í|Ị|Ỉ|Ĩ)/", 'I', $value);
        $value = preg_replace("/(Ò|Ó|Ọ|Ỏ|Õ|Ô|Ồ|Ố|Ộ|Ổ|Ỗ|Ơ|Ờ|Ớ|Ợ|Ở|Ỡ)/", 'O', $value);
        $value = preg_replace("/(Ù|Ú|Ụ|Ủ|Ũ|Ư|Ừ|Ứ|Ự|Ử|Ữ)/", 'U', $value);
        $value = preg_replace("/(Ỳ|Ý|Ỵ|Ỷ|Ỹ)/", 'Y', $value);
        $value = preg_replace("/(Đ)/", 'D', $value);

        return preg_replace('/[^A-Za-z0-9\-]/', '-', $value);
    }

    public static function convertToAlias($value)
    {
        $value = self::stripVietnameseAccents($value);
        $value = preg_replace("/!|@|%|\^|\*|\(|\)|\+|\=|\<|\>|\?|\/|,|\.|\:|\;|\'| |\"|\&|\#|\[|\]|~|$|_/", '-', $value);
        $value = preg_replace("/-+-/", '-', $value);
        $value = preg_replace("/^\-+|\-+$/", '', $value);
        $value = strtolower($value);
        return $value;
    }

    public static function getUploadFileInfo($fileName)
    {
        $fileNameWithoutExt = pathinfo($fileName, PATHINFO_FILENAME);
        $fileNameExt = pathinfo($fileName, PATHINFO_EXTENSION);
        $fileName = $fileNameExt
                        ? sprintf('%s.%s', self::convertToAlias($fileNameWithoutExt), $fileNameExt)
                        : self::convertToAlias($fileNameWithoutExt);

        $baseUploadPath = sprintf('%s%s', public_path(), config('backend.uploadPath'));

        $now = new \DateTime();
        $surfixUploadPath = sprintf('%s/%s/%s', $now->format('Y'), $now->format('m'), $now->format('d'));

        $uploadPath = sprintf('%s/%s', $baseUploadPath, $surfixUploadPath);
        if (!file_exists($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        $fileNameTmp = '';
        $fileNumber = 0;
        while (file_exists(sprintf('%s/%s', $uploadPath, $fileNumber == 0 ? $fileName : $fileNameTmp))) {
            $fileNameTmp = $fileNameExt
                            ? sprintf('%s-%d.%s', $fileNameWithoutExt, ++$fileNumber, $fileNameExt)
                            : sprintf('%s-%d', $fileNameWithoutExt, ++$fileNumber);
            $fileNameTmpWithoutExt = pathinfo($fileNameTmp, PATHINFO_FILENAME);
            $fileNameTmp = $fileNameTmpWithoutExt
                            ? sprintf('%s.%s', self::convertToAlias($fileNameTmpWithoutExt), $fileNameExt)
                            : self::convertToAlias($fileNameTmpWithoutExt);
        }
        if ($fileNameTmp) {
            $fileName = $fileNameTmp;
        }

        $filePath = sprintf('/%s/%s', $surfixUploadPath, $fileName);

        return [
            'uploadPath' => $uploadPath,
            'filePath' => $filePath,
            'fileName' => $fileName,
        ];
    }

    public static function getFileTypeAllowUpload()
    {
        $fileTypeAllow = [];
        $fileTypeAllow = array_merge($fileTypeAllow, config('backend.fileTypeImage'));
        $fileTypeAllow = array_merge($fileTypeAllow, config('backend.fileTypeAudio'));
        $fileTypeAllow = array_merge($fileTypeAllow, config('backend.fileTypeVideo'));
        $fileTypeAllow = array_merge($fileTypeAllow, config('backend.fileTypeOther'));
        return $fileTypeAllow;
    }

    public static function getFileTypeByExtension($extension)
    {
        $fileType = config('backend.fileType');

        $fileTypeAllow = config('backend.fileTypeImage');
        for ($i = 0; $i < count($fileTypeAllow); $i++) {
            if ($extension == $fileTypeAllow[$i]) {
                return $fileType['image'];
            }
        }

        $fileTypeAllow = config('backend.fileTypeAudio');
        for ($i = 0; $i < count($fileTypeAllow); $i++) {
            if ($extension == $fileTypeAllow[$i]) {
                return $fileType['audio'];
            }
        }

        $fileTypeAllow = config('backend.fileTypeVideo');
        for ($i = 0; $i < count($fileTypeAllow); $i++) {
            if ($extension == $fileTypeAllow[$i]) {
                return $fileType['video'];
            }
        }

        return $fileType['other'];
    }

    public static function getFileTypeNameByExtension($extension)
    {
        $fileType = config('backend.fileType');
        $fileTypeName = self::getFileTypeName();

        $fileTypeAllow = config('backend.fileTypeImage');
        for ($i = 0; $i < count($fileTypeAllow); $i++) {
            if ($extension == $fileTypeAllow[$i]) {
                return $fileTypeName[$fileType['image']];
            }
        }

        $fileTypeAllow = config('backend.fileTypeAudio');
        for ($i = 0; $i < count($fileTypeAllow); $i++) {
            if ($extension == $fileTypeAllow[$i]) {
                return $fileTypeName[$fileType['audio']];
            }
        }

        $fileTypeAllow = config('backend.fileTypeVideo');
        for ($i = 0; $i < count($fileTypeAllow); $i++) {
            if ($extension == $fileTypeAllow[$i]) {
                return $fileTypeName[$fileType['video']];
            }
        }

        return $fileTypeName[$fileType['other']];
    }

    public static function getFileExtensionByType($type)
    {
        switch ($type) {
            case config('backend.fileType.image'):
                return config('backend.fileTypeImage');
            case config('backend.fileType.audio'):
                return config('backend.fileTypeAudio');
            case config('backend.fileType.video'):
                return config('backend.fileTypeVideo');
            case config('backend.fileType.other'):
                return config('backend.fileTypeOther');
            default:
                return [];
        }
    }

    public static function getFileTypeName()
    {
        $fileType = config('backend.fileType');
        return [
            $fileType['image'] => 'Hình ảnh',
            $fileType['audio'] => 'Âm thanh',
            $fileType['video'] => 'Video',
            $fileType['other'] => 'Khác'
        ];
    }

    public static function getUploadMaxFileSize($unit = '', $showUnit = false)
    {
        $fileUploadMaxSize = config('backend.fileUploadMaxSize') * 1024;

        $postMaxSize = self::parseSize(ini_get('post_max_size'));
        if ($postMaxSize > 0 && $fileUploadMaxSize > $postMaxSize) {
            $fileUploadMaxSize = $postMaxSize;
        }

        $uploadMaxSize = self::parseSize(ini_get('upload_max_filesize'));
        if ($uploadMaxSize > 0 && $fileUploadMaxSize > $uploadMaxSize) {
            $fileUploadMaxSize = $uploadMaxSize;
        }

        return self::formatDisplayFileSize($fileUploadMaxSize, $unit, $showUnit);
    }

    public static function parseSize($size)
    {
        $unit = preg_replace('/[^bkmgtpezy]/i', '', $size);
        $size = preg_replace('/[^0-9\.]/', '', $size);
        if ($unit) {
            return round($size * pow(1024, stripos('bkmgtpezy', $unit[0])));
        } else {
            return round($size);
        }
    }

    public static function getFileLink($url)
    {
        if (!$url) {
            return '';
        }

        if (Str::of($url)->startsWith('http://') || Str::of($url)->startsWith('https://')) {
            return $url;
        }

        $uploadPath = config('backend.uploadPath');

        if (Str::of($url)->startsWith($uploadPath)) {
            return $url;
        }

        if (!Str::of($url)->startsWith('/')) {
            $url = '/' . $url;
        }

        return sprintf('%s%s', $uploadPath, $url);
    }

    public static function getAvatar($url)
    {
        if (!$url) {
            return '/assets/backend/images/no-avatar.jpg';
        }

        return self::getFileLink($url);
    }

    public static function getStorageUploadFileInfo($fileName, $baseUploadPath = '')
    {
        $fileNameWithoutExt = pathinfo($fileName, PATHINFO_FILENAME);
        $fileNameExt = pathinfo($fileName, PATHINFO_EXTENSION);
        $fileName = $fileNameExt
                        ? sprintf('%s.%s', self::convertToAlias($fileNameWithoutExt), $fileNameExt)
                        : self::convertToAlias($fileNameWithoutExt);

        if (!$baseUploadPath) {
            $baseUploadPath = 'public';
        }

        $now = new \DateTime();
        $surfixUploadPath = sprintf('%s/%s/%s', $now->format('Y'), $now->format('m'), $now->format('d'));

        $uploadPath = sprintf('%s/%s', $baseUploadPath, $surfixUploadPath);

        $fileNameTmp = '';
        $fileNumber = 0;
        while (Storage::exists(sprintf('%s/%s', $uploadPath, $fileNumber == 0 ? $fileName : $fileNameTmp))) {
            $fileNameTmp = $fileNameExt
                            ? sprintf('%s-%d.%s', $fileNameWithoutExt, ++$fileNumber, $fileNameExt)
                            : sprintf('%s-%d', $fileNameWithoutExt, ++$fileNumber);
            $fileNameTmpWithoutExt = pathinfo($fileNameTmp, PATHINFO_FILENAME);
            $fileNameTmp = $fileNameTmpWithoutExt
                            ? sprintf('%s.%s', self::convertToAlias($fileNameTmpWithoutExt), $fileNameExt)
                            : self::convertToAlias($fileNameTmpWithoutExt);
        }
        if ($fileNameTmp) {
            $fileName = $fileNameTmp;
        }

        $filePath = sprintf('app/%s/%s', $uploadPath, $fileName);

        return [
            'uploadPath' => $uploadPath,
            'filePath' => $filePath,
            'fileName' => $fileName,
        ];
    }

    public static function getFileContentMimeType($fileContent)
    {
        $fileInfo = new \finfo(FILEINFO_MIME_TYPE);
        return $fileInfo->buffer($fileContent);
    }

    public static function getInputFileAccept($list)
    {
        if (!$list || count($list) == 0) {
            return null;
        }

        return '.' . implode(',.', $list);
    }

    public static function parseDateOnly($value)
    {
        try {
            return \Carbon\Carbon::createFromFormat(config('backend.displayDateFormat'), $value)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

    public static function parseDateTime($value)
    {
        try {
            return \Carbon\Carbon::createFromFormat(config('backend.displayDateTimeFormat'), $value)->format('Y-m-d H:i:s');
        } catch (\Exception $e) {
            return null;
        }
    }

    public static function formatDisplayDateTime($value, $format = '')
    {
        if (!$value) {
            return '';
        }

        if (!$format) {
            $format = config('backend.displayDateTimeFormat');
        }

        return date($format, strtotime($value));
    }

    public static function formatDisplayDateOnly($value)
    {
        if (!$value) {
            return '';
        }

        return date(config('backend.displayDateFormat'), strtotime($value));
    }

    public static function formatDisplayTime($value)
    {
        if (!$value) {
            return '';
        }

        return date(config('backend.displayTimeFormat'), strtotime($value));
    }

    public static function formatDisplayNiceNumber($value)
    {
        if ($value > 1000 && $value < 999999) {
            $value = $value / 1000;
            $value = floor($value * 10) / 10;
            return $value . 'K';
        } else if ($value > 1000000) {
            $value = $value / 1000000;
            $value = floor($value * 10) / 10;
            return $value . 'M';
        } else
            return $value;
    }

    public static function formatDisplayFileSize($size, $unit = '', $showUnit = false)
    {
        switch ($unit) {
            case 'KB':
               $size = round($size / 1024, 2);
               if ($showUnit) {
                   $size .= ' ' . $unit;
               }
                break;
            case 'MB':
               $size = round($size / (1024 * 1024), 2);
               if ($showUnit) {
                   $size .= ' ' . $unit;
               }
                break;
            case 'GB':
               $size = round($size / (1024 * 1024 * 1024), 2);
               if ($showUnit) {
                   $size .= ' ' . $unit;
               }
                break;
            default:
               if ($showUnit) {
                   $size .= ' B';
               }
                break;
       }

        return $size;
    }

    public static function getFirstDayOfMonth($value)
    {
        return Carbon::createFromFormat('Y-m-d', $value)
                    ->firstOfMonth()
                    ->format('Y-m-d');
    }

    public static function getErrorMessageByStatusCode($statusCode)
    {
        switch ($statusCode) {
            case 403:
                return 'Không có quyền sử dụng chức năng này';
            case 404:
                return 'Không tìm thấy đường dẫn này';
            default:
                return 'Hệ thống đang bận bảo trì hoặc nâng cấp';
        }
    }

    public static function getUserById($id)
    {
        return AdUserService::find($id);
    }

    public static function getUserTheme()
    {
        $userTheme = config('backend.userTheme');
        return [
            $userTheme['default'] => 'Mặc định',
            $userTheme['dark'] => 'Tối'
        ];
    }

    public static function formatDisplayLogType($type)
    {
        switch ($type) {
            case config('backend.logType.info'):
                return '<span class="badge badge-success">Thông tin</span>';
            case config('backend.logType.error'):
                return '<span class="badge badge-danger">Lỗi</span>';
            case config('backend.logType.system'):
                return '<span class="badge badge-warning">Hệ thống</span>';
            default:
                return '';
        }
    }

    public static function formatDisplayUserStatus($status)
    {
        switch ($status) {
            case config('backend.userStatus.unactive'):
                return '<span class="badge badge-secondary">Chưa kích hoạt</span>';
            case config('backend.userStatus.actived'):
                return '<span class="badge badge-success">Hoạt động</span>';
            case config('backend.userStatus.locked'):
                return '<span class="badge badge-danger">Đã khóa</span>';
            default:
                return '';
        }
    }

    public static function formatDisplayUserTheme($theme)
    {
        switch ($theme) {
            case config('backend.userTheme.default'):
                return 'Mặc định';
            case config('backend.userTheme.dark'):
                return 'Tối';
            default:
                return '';
        }
    }

    public static function formatDisplayArticleStatus($isPublished) {
        return $isPublished
                    ? '<span class="badge badge-success">Đã xuất bản</span>'
                    : '<span class="badge badge-secondary">Chưa xuất bản</span>';
    }

    public static function formatDisplayTourStatus($isPublished) {
        return $isPublished
                    ? '<span class="badge badge-success">Đã xuất bản</span>'
                    : '<span class="badge badge-secondary">Chưa xuất bản</span>';
    }

    public static function formatDisplayAdvStatus($isPublished) {
        return $isPublished
                    ? '<span class="badge badge-success">Đã xuất bản</span>'
                    : '<span class="badge badge-secondary">Chưa xuất bản</span>';
    }
    
    public static function formatDisplayGroupTab($type, $tab, $languageCode)
    {
        switch ($type) {
            case config('backend.groupType.expActivity'):
                $listTab = self::getListTabByType($type, $languageCode);
                return array_key_exists($tab, $listTab) ? $listTab[$tab] : '';
            default:
                return '';
        }
    }

    public static function getListTabByType($type, $languageCode)
    {
        switch ($type) {
            case config('backend.groupType.expActivity'):
                return [
                    config('backend.groupTabType.expActivity.onboard_activities') => Lang::get('backend::group.onboard_activities', [], $languageCode),
                    config('backend.groupTabType.expActivity.outdoor_activities') => Lang::get('backend::group.outdoor_activities', [], $languageCode),
                ];
            default:
                return [];
        }
    }

    public static function getCategoryNameByLevel($name, $level, $numOfDash = 4)
    {
        $dash = '';
        if ($numOfDash > 0) {
            for ($i = 0; $i < $numOfDash; $i++) {
                $dash .= "-";
            }
        }

        if ($level > 1) {
            $prefix = '';
            for ($i = 0; $i < $level - 1; $i++) {
                $prefix .= $dash;
            }
            $name = $prefix . ' ' . $name;
        }

        return $name;
    }

    public static function formatDisplayCurrency($value)
    {
        if (is_null($value)) {
            return '';
        }

        $currentLanguage = LanguageUtils::getCurrentLanguage();

        try {
            $currencyFormat = json_decode($currentLanguage->currency_format);
            $number = number_format($value, $currencyFormat->decimalPlace, $currencyFormat->decimalSeparator, $currencyFormat->thousandSeparator);
            return sprintf('%s%s%s', $currencyFormat->prefix, $number, $currencyFormat->suffix);
        } catch (\Exception $e) {
            return $value;
        }
    }

    public static function formatStoredAmount($rawAmount, ?string $currency = 'usd', bool $withSymbol = true): string
    {
        if ($rawAmount === null) {
            return '';
        }

        $currency = strtolower($currency ?? 'usd');

        $display = $currency === 'usd'
            ? $rawAmount / 100
            : $rawAmount;

        $decimals = $currency === 'usd' ? 2 : 0;
        $number = number_format($display, $decimals, '.', ',');

        if (! $withSymbol) {
            return $number;
        }

        return $currency === 'usd'
            ? $number . ' $'
            : $number . ' ₫';
    }

    public static function getYoutubeEmbedCode($link)
	{
		$embed = str_replace('m.youtube.com', 'youtube.com', $link);
		$embed = str_replace('watch?v=', 'embed/', $embed);
		return $embed;
	}

    public static $routeNameMultiLangaugeSurfix = 'multi-language';

    public static function bindRouteNameMultiLanguage($routeName)
    {
        return $routeName . '|' . self::$routeNameMultiLangaugeSurfix;
    }

    public static function getRouteName($routeName)
    {
        $languageCode = Route::current()->parameter('languageCode');
        return $languageCode ? $routeName . '|' . self::$routeNameMultiLangaugeSurfix : $routeName;
    }

    public static function getAllConfig($language)
    {
        $config = [];
        $list = AdConfigService::getAll($language->id);
        for ($i = 0; $i < count($list); $i++) {
            $key = $list[$i]->key;
            if ($list[$i]->language_id) {
                $key = preg_replace('/-' . $language->code . '$/', '', $key);
            }
            $config[$key] = $list[$i]->value;
        }
        return $config;
    }
}
