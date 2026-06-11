<?php

namespace App\Support;

use HTMLPurifier;
use HTMLPurifier_Config;

class HtmlSanitizer
{
    protected static ?HTMLPurifier $purifier = null;

    public static function clean(?string $html): ?string
    {
        if ($html === null || $html === '') {
            return $html;
        }

        return self::purifier()->purify($html);
    }

    protected static function purifier(): HTMLPurifier
    {
        if (self::$purifier !== null) {
            return self::$purifier;
        }

        $config = HTMLPurifier_Config::createDefault();
        $config->set('Cache.SerializerPath', storage_path('app/htmlpurifier'));
        $config->set('HTML.Allowed', implode(',', [
            'p', 'br', 'strong', 'b', 'em', 'i', 'u', 's', 'sub', 'sup',
            'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
            'ul', 'ol', 'li', 'blockquote', 'pre', 'code',
            'a[href|title|target|rel]',
            'img[src|alt|title|width|height]',
            'table', 'thead', 'tbody', 'tr', 'th', 'td',
            'div', 'span', 'hr',
        ]));
        $config->set('URI.AllowedSchemes', ['http' => true, 'https' => true, 'mailto' => true, 'tel' => true]);
        $config->set('Attr.AllowedFrameTargets', ['_blank', '_self']);
        $config->set('HTML.TargetBlank', true);
        $config->set('AutoFormat.RemoveEmpty', true);

        if (!is_dir(storage_path('app/htmlpurifier'))) {
            mkdir(storage_path('app/htmlpurifier'), 0755, true);
        }

        self::$purifier = new HTMLPurifier($config);

        return self::$purifier;
    }
}
