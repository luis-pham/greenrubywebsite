<?php

namespace Modules\FrontEnd\Helpers;

use Illuminate\Support\Str;
use Modules\BackEnd\Constants\PageConfigConsts;
use Modules\BackEnd\Helpers\Utilities;
use Modules\BackEnd\Services\AdConfigService;
use Modules\BackEnd\Services\AppSeoKeywordService;
use Modules\BackEnd\Services\SourceDataService;
use Modules\FrontEnd\Services\AppCruiseService;
use Modules\FrontEnd\Services\AppPageConfigService;

class FeUtils
{
    public static function isHome()
    {
        $currentRouteName = \Request::route()->getName();
        return $currentRouteName == 'frontend.index' ||
            $currentRouteName == Utilities::bindRouteNameMultiLanguage('frontend.index');
    }

    public static function getExcerpt($value, $limit)
    {
        return Str::words($value, $limit, ' ...');
    }

    public static function getConfigValueByKey($key) {
        $data = AdConfigService::getByKey($key);
        if (is_array($key)) {
            return $data->pluck('value', 'key')->toArray();
        } else {
            return $data != null ? $data->value : '';
        }
    }

    public static function getImageLink($url)
    {
        if (!$url) {
            return config('frontend.imageBlank');
        }

        return Utilities::getFileLink($url);
    }

    public static function getThumbnail($params)
    {
        if (!array_key_exists('link', $params) || !$params['link']) {
            $params['link'] = config('frontend.imageBlank');
        } else {
            $params['link'] = Utilities::getFileLink($params['link']);
        }
        if (Str::of($params['link'])->startsWith('http://') || Str::of($params['link'])->startsWith('https://')) {
            return $params['link'];
        }

        if (!array_key_exists('w', $params) && !array_key_exists('h', $params)) {
            return asset($params['link']);
        }

        if (!array_key_exists('cr', $params)) {
            $params['cr'] = true;
        }

        $params['q'] = max(1, min(100, (int) env('IMAGE_PROXY_QUALITY', 100)));

        return route('frontend.image.thumbnail', $params);
    }

    public static function formatDisplayCurrency($value)
    {
        if (is_null($value)) {
            return '';
        }

        $currentLanguage = FeLanguageUtils::getCurrentLanguage();

        try {
            $currencyFormat = json_decode($currentLanguage->currency_format);
            $number = number_format($value, $currencyFormat->decimalPlace, $currencyFormat->decimalSeparator, $currencyFormat->thousandSeparator);
            return sprintf('%s%s%s', $currencyFormat->prefix, $number, $currencyFormat->suffix);
        } catch (\Exception $e) {
            return $value;
        }
    }

    public static function bindBreadcrumb($lastBreadcrumb = [], $languageCode)
    {
        $list = [[
            'name' => __('frontend::common.homepage'),
            'url' => route(Utilities::getRouteName('frontend.index'), ['languageCode' => $languageCode])
        ]];

        return array_merge($list, $lastBreadcrumb);
    }

    public static function bindArticleContent($content, $languageId)
    {
        try {
            $content = mb_convert_encoding($content, 'html-entities', 'utf-8');
            $dom = new \DOMDocument();
            @$dom->loadHTML($content, LIBXML_HTML_NODEFDTD);
            $listElement = $dom->getElementsByTagName('a');
            for ($i = 0; $i < count($listElement); $i++) {
                $href = $listElement[$i]->getAttribute('href');
                if (\Str::of($href)->startsWith('http://') || \Str::of($href)->startsWith('https://')) {
                    $parseUrl = parse_url($href);
                    if (request()->getHost() != $parseUrl['host']) {
                        $listElement[$i]->setAttribute('rel', 'nofollow');
                    }
                }
            }

            $anchorImageFancyBox = $dom->createElement('a');
            $anchorImageFancyBox->setAttribute('data-fancybox', 'article');
            
            $listElement = $dom->getElementsByTagName('img');
            foreach ($listElement as $element) {
                $src = $element->getAttribute('src');
                if (\Str::of($src)->startsWith('/')) {
                    $src = url($src);
                    $element->setAttribute('src', $src);
                }
                $anchorImageFancyBoxClone = $anchorImageFancyBox->cloneNode();
                $anchorImageFancyBoxClone->setAttribute('href', $src);
                $anchorImageFancyBoxClone->setAttribute('data-caption', $element->getAttribute('alt'));
                $element->parentNode->replaceChild($anchorImageFancyBoxClone, $element);
                $anchorImageFancyBoxClone->appendChild($element);
            }

            $listSeoKeyword = AppSeoKeywordService::getAll($languageId)->pluck('link', 'keyword')->toArray();
            if (count($listSeoKeyword) > 0) {
                foreach ($listSeoKeyword as $key => $value) {
                    if (!$value) {
                        continue;
                    }
                    
                    $url = \Str::of($value)->startsWith('/') ? url($value) : $value;
                    $xpath = new \DOMXPath($dom);
                    $isAppendLink = false;
                    $listElement = $xpath->query('//text()[not(ancestor::a)][(ancestor::p|ancestor::blockquote)]');
                    foreach ($listElement as $node) {
                        $parent = $node->parentNode;
                        $parts = preg_split('/\b(' . preg_quote($key, '/') . ')\b/msi', $node->textContent, 0, PREG_SPLIT_DELIM_CAPTURE);
                        foreach ($parts as $index => $part) {
                            if (empty($part)) {
                                continue;
                            }

                            if ($index % 2 != 0 && !$isAppendLink) {
                                $element = $dom->createElement('a', htmlentities($part));
                                $element->setAttribute('href', $url);
                                $isAppendLink = true;
                            } else {
                                $element = $dom->createTextNode($part);
                            }
                            $parent->insertBefore($element, $node);
                        }
                        $parent->removeChild($node);
                    }
                }
            }

            $trim_off_front = strpos($dom->saveHTML(),'<body>') + 6;
            $trim_off_end = (strrpos($dom->saveHTML(),'</body>')) - strlen($dom->saveHTML());

            return substr($dom->saveHTML(), $trim_off_front, $trim_off_end);
        } catch (\Exception $e) {
            return $content;
        }
    }

    public static function getAbsoluteUrl($url)
    {
        try {
            return parse_url($url)['path'];
        } catch (\Exception $e) {
            return null;
        }
    }

    public static function normalizeMenuPath($path)
    {
        if ($path === null || $path === '') {
            return '';
        }

        $path = '/' . trim($path, '/');
        if ($path === '//') {
            return '/';
        }

        static $aliases = [
            '/service' => '/services',
            '/experience' => '/experiences',
        ];

        return $aliases[$path] ?? $path;
    }

    public static function getLocalizedFrontendPathSegmentMap(): array
    {
        return [
            'cruise' => 'du-thuyen',
            'itinerary' => 'hanh-trinh',
            'experiences' => 'hoat-dong-trai-nghiem',
            'experience' => 'hoat-dong-trai-nghiem',
            'services' => 'dich-vu',
            'service' => 'dich-vu',
            'gallery' => 'thu-vien',
            'contact' => 'lien-he',
            'about-us' => 'gioi-thieu',
            'faq' => 'cau-hoi-thuong-gap',
            'legal' => 'phap-ly',
            'safety-policies' => 'chinh-sach-an-toan',
            'terms-and-conditions' => 'dieu-khoan-dieu-kien',
            'privacy-policy' => 'chinh-sach-bao-mat',
            'payment-methods' => 'phuong-thuc-thanh-toan',
        ];
    }

    public static function localizeMenuUrl(?string $url, $language): string
    {
        if ($url === null || $url === '') {
            return '';
        }

        if (Str::startsWith($url, ['javascript:', '#'])) {
            return $url;
        }

        $resolvedCruiseUrl = self::resolveCruiseMenuUrl($url, $language);
        if ($resolvedCruiseUrl !== null) {
            return $resolvedCruiseUrl;
        }

        $defaultLanguage = \Modules\BackEnd\Services\AdLanguageService::getDefaultLanguage();
        $isDefault = $language && $defaultLanguage && (int) $language->id === (int) $defaultLanguage->id;

        $parsed = parse_url($url);
        $path = $parsed['path'] ?? '';
        if ($path === '') {
            return $url;
        }

        $query = '';
        if (!empty($parsed['query'])) {
            parse_str($parsed['query'], $queryParams);
            unset($queryParams['languageCode']);
            if (count($queryParams) > 0) {
                $query = '?' . http_build_query($queryParams);
            }
        }
        $fragment = isset($parsed['fragment']) ? '#' . $parsed['fragment'] : '';
        $host = $parsed['host'] ?? null;

        if ($host && $host !== request()->getHost()) {
            return $url;
        }

        $path = self::normalizeMenuPath($path);
        $segments = array_values(array_filter(explode('/', trim($path, '/'))));

        if (!empty($segments) && preg_match('/^[a-z]{2}$/', $segments[0])) {
            array_shift($segments);
        }

        if (!$isDefault && $language && !empty($language->code)) {
            $map = self::getLocalizedFrontendPathSegmentMap();

            if (!empty($segments) && isset($map[$segments[0]])) {
                $segments[0] = $map[$segments[0]];
            }

            if (($segments[0] ?? '') === 'gallery' && isset($segments[1]) && str_starts_with($segments[1], 'page-')) {
                $segments[0] = 'thu-vien';
                $segments[1] = 'trang-' . substr($segments[1], 5);
            }

            array_unshift($segments, $language->code);
        }

        $newPath = empty($segments) ? '/' : '/' . implode('/', $segments);

        if ($host) {
            $scheme = $parsed['scheme'] ?? 'https';

            return $scheme . '://' . $host . $newPath . $query . $fragment;
        }

        if ($isDefault) {
            return $newPath . $query . $fragment;
        }

        return asset($newPath . $query . $fragment);
    }

    private static function resolveCruiseMenuUrl(string $url, $language): ?string
    {
        $path = self::getAbsoluteUrl($url);
        if (!$path) {
            return null;
        }

        $segments = array_values(array_filter(explode('/', trim(self::normalizeMenuPath($path), '/'))));
        if (!empty($segments[0]) && preg_match('/^[a-z]{2}$/', $segments[0])) {
            array_shift($segments);
        }

        $segment = $segments[0] ?? '';
        if (!in_array($segment, ['cruise', 'du-thuyen'], true)) {
            return null;
        }

        $filename = $segments[1] ?? '';
        if (!preg_match('/-(\d+)\.html$/', $filename, $matches)) {
            return null;
        }

        $id = (int) $matches[1];
        if ($id <= 0) {
            return null;
        }

        $cruise = AppCruiseService::findByIdJoin($id, $language->id);
        if (!$cruise) {
            return null;
        }

        $routeName = ($language && empty($language->is_default))
            ? Utilities::bindRouteNameMultiLanguage('frontend.cruise.show')
            : 'frontend.cruise.show';

        $params = [
            'slug' => Utilities::convertToAlias($cruise->name),
            'id' => $cruise->id,
        ];

        if ($language && empty($language->is_default)) {
            $params['languageCode'] = $language->code;
        }

        return route($routeName, $params);
    }

    public static function isMenuItemActive($menuUrlActive, $itemUrl)
    {
        if (!isset($menuUrlActive) || !$menuUrlActive || !$itemUrl) {
            return false;
        }

        $activePath = self::normalizeMenuPath(self::getAbsoluteUrl($menuUrlActive) ?? '');
        $itemPath = self::normalizeMenuPath(self::getAbsoluteUrl($itemUrl) ?? '');

        if ($activePath === $itemPath) {
            return true;
        }

        if ($itemPath !== '/' && str_starts_with($activePath, $itemPath . '/')) {
            return true;
        }

        return false;
    }

    public static function getPrimaryMenuNavKey(?string $url): string
    {
        $path = self::normalizeMenuPath(self::getAbsoluteUrl($url) ?? '');

        if (preg_match('#^/[a-z]{2}(/.*)?$#', $path, $matches)) {
            $path = isset($matches[1]) && $matches[1] !== '' ? $matches[1] : '/';
        }

        if ($path === '/' || $path === '') {
            return 'home';
        }

        if (str_starts_with($path, '/cruise') || str_starts_with($path, '/du-thuyen')) {
            return 'cruise';
        }

        if (str_starts_with($path, '/itinerary') || str_starts_with($path, '/hanh-trinh')) {
            return 'itinerary';
        }

        if (str_starts_with($path, '/services') || str_starts_with($path, '/dich-vu')) {
            return 'services';
        }

        if (str_starts_with($path, '/experiences') || str_starts_with($path, '/hoat-dong-trai-nghiem')) {
            return 'experiences';
        }

        return 'other';
    }

    public static function resolvePrimaryMenuNavKey($menuItem): string
    {
        $navKey = self::getPrimaryMenuNavKey($menuItem->url ?? null);

        if ($navKey !== 'other') {
            return $navKey;
        }

        $children = $menuItem->child ?? [];
        if (count($children) > 0) {
            $childKey = self::getPrimaryMenuNavKey($children[0]->url ?? null);
            if ($childKey !== 'other') {
                return $childKey;
            }
        }

        return 'other';
    }

    public static function bindWebsiteTitle($title, $description)
    {
        if ($description) {
            $title .= ' | ' . $description;
        }
        return $title;
    }

    public static function getPageConfigByCode($code, $languageId)
    {
        $list = [];
        $data = AppPageConfigService::getByPageCode($code, $languageId);
        for ($i = 0; $i < count($data); $i++) {
            switch ($data[$i]->type) {
                case config('backend.configInput.textbox'):
                    $list[$data[$i]->key] = $data[$i]->value;
                    break;
                case config('backend.configInput.textarea'):
                    $list[$data[$i]->key] = $data[$i]->value;
                    break;
                case config('backend.configInput.texteditor'):
                    $list[$data[$i]->key] = $data[$i]->value;
                    break;
                case config('backend.configInput.selectbox'):
                    $list[$data[$i]->key] = $data[$i]->value;
                    break;
                case config('backend.configInput.image'):
                    $list[$data[$i]->key] = $data[$i]->value;
                    break;
                case config('backend.configInput.gallery'):
                    $list[$data[$i]->key] = json_decode($data[$i]->value);
                    break;
                case config('backend.configInput.sourceData'):
                    $id = json_decode($data[$i]->value);
                    $list[$data[$i]->key] = self::getPageConfigSourceDataValue($id, $data[$i]->list_value, $languageId);
                    break;
                default:
                    $list[$data[$i]->key] = $data[$i]->value;
                    break;
            }
        }
        return $list;
    }

    public static function getPageConfigSourceDataValue($id, $sourceDataType, $languageId)
    {
        $list = [];
        $data = null;
        switch ($sourceDataType) {
            case PageConfigConsts::SOURCE_DATA_TYPE_ARTICLE:
                $data = SourceDataService::getArticleById($id, $languageId);
                break;
            case PageConfigConsts::SOURCE_DATA_TYPE_FAQ:
                $data = SourceDataService::getFaqById($id, $languageId);
                break;
            case PageConfigConsts::SOURCE_DATA_TYPE_CRUISE_ITINERARY:
                $data = SourceDataService::getCruiseItineraryById($id, $languageId);
                break;
            case PageConfigConsts::SOURCE_DATA_TYPE_CRUISE:
                $data = SourceDataService::getCruiseById($id, $languageId);
                break;
            case PageConfigConsts::SOURCE_DATA_TYPE_CABIN:
                $data = SourceDataService::getCabinById($id, $languageId);
                break;
            case PageConfigConsts::SOURCE_DATA_TYPE_SERVICE:
                $data = SourceDataService::getServiceById($id, $languageId);
                break;
            case PageConfigConsts::SOURCE_DATA_TYPE_AMENITY:
                $data = SourceDataService::getAmenityById($id, $languageId);
                break;
            case PageConfigConsts::SOURCE_DATA_TYPE_EXP_ACTIVITY:
                $data = SourceDataService::getExpActivityById($id, $languageId);
                break;
            default:
                break;
        }

        if ($data && count($data) > 0) {
            $data = $sourceDataType != PageConfigConsts::SOURCE_DATA_TYPE_CRUISE_ITINERARY
                ? $data->keyBy('id')
                : $data->mapWithKeys(function ($item) {
                      return [$item->id . '-' . $item->cruise_id => $item];
                  });

            for ($i = 0; $i < count($id); $i++) {
                if ($data->has($id[$i])) {
                    $list[] = $data[$id[$i]];
                }
            }
        }

        return $list;
    }

    public static function getErrorMessageByStatusCode($statusCode)
    {
        switch ($statusCode) {
            case 403:
                return __('frontend::error.error_403');
            case 404:
                return __('frontend::error.error_404');
            default:
                return __('frontend::error.error_500');
        }
    }

    public static function getUrlMultiLanguage($language)
    {
        $routeName = \Request::route()->getName();
        $routeName = preg_replace('/\|' . Utilities::$routeNameMultiLangaugeSurfix . '$/', '', $routeName);

        $listRouteName = [
            'frontend.index',
            'frontend.booking',
            'frontend.itinerary.index',
            'frontend.gallery.index',
            'frontend.contact.index',
            'frontend.service.index',
            'frontend.experience.index',
            'frontend.faq.index',
            'frontend.article.index',
            'frontend.page.legal',
            'frontend.page.safety-policies',
            'frontend.page.terms-and-conditions',
            'frontend.page.privacy-policy',
            'frontend.page.payment-methods'
        ];

        if (!in_array($routeName, $listRouteName)) {
            $routeName = 'frontend.index';
        }

        $languageCode = $language->is_default ? null : $language->code;
        if ($languageCode) {
            return route(Utilities::bindRouteNameMultiLanguage($routeName), ['languageCode' => $languageCode]);
        } else {
            return route($routeName);
        }
    }


    public static function extractGreenRubyPart($text)
    {
        $text = trim((string) $text);
        $part = preg_replace('/^.*?\bgreen\s*ruby\b\s*/i', '', $text);
        $part = trim((string) ($part ?? ''));

        if ($part === '') {
            $tokens = preg_split('/\s+/', $text);
            $part = strtoupper((string) (end($tokens) ?: ''));
        } else {
            if (preg_match('/^[IVXLCDM]+$/i', $part)) {
                $romanMap = [
                    'I' => '1',
                    'II' => '2',
                    'III' => '3',
                    'IV' => '4',
                ];
                $upper = strtoupper($part);
                $part = $romanMap[$upper] ?? $upper;
            }
        }

        return $part;
    }

    public static function formatGreenRubyMenuName($name)
    {
        $name = trim((string) $name);
        if ($name === '' || !preg_match('/\bgreen\s*ruby\b/i', $name)) {
            return $name;
        }

        $part = self::extractGreenRubyPart($name);
        if ($part === '') {
            return $name;
        }

        return 'Green Ruby ' . $part;
    }

    public static function formatGreenRubyCruiseNames($listCruiseName)
    {
        if (!is_array($listCruiseName) || empty($listCruiseName)) {
            return '';
        }

        $count = count($listCruiseName);
        if ($count <= 1) {
            $singlePart = self::extractGreenRubyPart($listCruiseName[0] ?? '');
            return $singlePart !== '' ? ('Green Ruby ' . $singlePart) : '';
        }

        $firstPart = self::extractGreenRubyPart($listCruiseName[0] ?? '');
        $parts = [];
        for ($i = 1; $i < $count; $i++) {
            $p = self::extractGreenRubyPart($listCruiseName[$i] ?? '');
            if ($p !== '') {
                $parts[] = $p;
            }
        }

        $result = 'Green Ruby ' . $firstPart;
        if (!empty($parts)) {
            $result .= ' & ' . implode(' & ', $parts);
        }

        return $result;
    }
}