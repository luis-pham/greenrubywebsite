<?php

namespace App\Support;

use HTMLPurifier;
use HTMLPurifier_Config;

/**
 * Allow-list sanitizer for editor (TinyMCE) HTML that is rendered on the public site.
 *
 * Keeps what the editor produces: headings, lists, quotes, tables (with merged cells), images, links,
 * HTML5 figure/video/audio, YouTube/Vimeo embeds, the editor templates' classes and a small set of
 * presentational inline styles (alignment, colours, image sizing). Scripts, forms, event handlers,
 * arbitrary iframes and unknown classes are removed.
 */
class HtmlSanitizer
{
    /** Bump when the definition below changes, so HTMLPurifier rebuilds its serialized cache. */
    private const DEFINITION_REV = 2;

    /** Classes used by the TinyMCE templates and embed plugins (public/assets/backend/plugins/tinymce). */
    private const CLASSES = [
        'box-media', 'box-media-default', 'box-media-left', 'box-media-right', 'content', 'caption',
        'box-quote', 'embed-iframe', 'iframe-16-9', 'iframe-4-3', 'iframe-3-2', 'iframe-1-1',
    ];

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
        $config->set('HTML.DefinitionID', 'greenruby-content');
        $config->set('HTML.DefinitionRev', self::DEFINITION_REV);
        $config->set('HTML.Allowed', implode(',', [
            'p[style|class]', 'br', 'strong', 'b', 'em', 'i', 'u', 's', 'sub', 'sup',
            'h1[style]', 'h2[style]', 'h3[style]', 'h4[style]', 'h5[style]', 'h6[style]',
            'ul[style]', 'ol[start|style]', 'li[style]', 'blockquote[class|style]', 'pre', 'code',
            'a[href|title|target|rel|class]',
            'img[src|alt|title|width|height|style|class]',
            'table[style|class]', 'caption', 'colgroup', 'col[span|width]',
            'thead', 'tbody', 'tfoot', 'tr', 'th[colspan|rowspan|scope|style]', 'td[colspan|rowspan|style]',
            'div[class|style]', 'span[class|style]', 'hr',
            'figure[class|style]', 'figcaption',
            'iframe[src|width|height|title|frameborder|allow|allowfullscreen]',
            'video[src|controls|poster|preload|width|height|playsinline|muted|loop]',
            'audio[src|controls|preload]', 'source[src|type]',
        ]));
        $config->set('Attr.AllowedClasses', self::CLASSES);
        $config->set('CSS.AllowedProperties', [
            'text-align', 'color', 'background-color', 'font-weight', 'font-style', 'text-decoration',
            'width', 'height', 'max-width', 'float', 'vertical-align', 'margin-left', 'margin-right',
        ]);
        // Only video players may be embedded.
        $config->set('HTML.SafeIframe', true);
        $config->set('URI.SafeIframeRegexp', '%^https://(www\.youtube(-nocookie)?\.com/embed/|player\.vimeo\.com/video/)%');
        $config->set('URI.AllowedSchemes', ['http' => true, 'https' => true, 'mailto' => true, 'tel' => true]);
        $config->set('Attr.AllowedFrameTargets', ['_blank', '_self']);
        $config->set('HTML.TargetBlank', true);
        $config->set('AutoFormat.RemoveEmpty', true);
        // Media elements are legitimately empty (their content is the src); keep them.
        $config->set('AutoFormat.RemoveEmpty.Predicate', [
            'colgroup' => [], 'th' => [], 'td' => [], 'iframe' => ['src'],
            'video' => [], 'audio' => [], 'source' => ['src'], 'img' => ['src'],
        ]);

        if ($def = $config->maybeGetRawHTMLDefinition()) {
            // HTML5 elements HTMLPurifier 4.x does not define.
            $def->addElement('figure', 'Block', 'Optional: (figcaption, Flow) | (Flow, figcaption) | Flow', 'Common');
            $def->addElement('figcaption', 'Inline', 'Flow', 'Common');
            $media = [
                'src' => 'URI',
                'controls' => 'Bool',
                'preload' => 'Enum#auto,metadata,none',
            ];
            $def->addElement('video', 'Block', 'Optional: (source, Flow) | (Flow, source) | Flow', 'Common', $media + [
                'poster' => 'URI',
                'width' => 'Length',
                'height' => 'Length',
                'playsinline' => 'Bool',
                'muted' => 'Bool',
                'loop' => 'Bool',
            ]);
            $def->addElement('audio', 'Block', 'Optional: (source, Flow) | (Flow, source) | Flow', 'Common', $media);
            $def->addElement('source', 'Block', 'Empty', 'Common', ['src' => 'URI', 'type' => 'Text']);
            $def->addAttribute('iframe', 'allow', 'Text');
            $def->addAttribute('iframe', 'allowfullscreen', 'Bool');
        }

        if (!is_dir(storage_path('app/htmlpurifier'))) {
            mkdir(storage_path('app/htmlpurifier'), 0755, true);
        }

        self::$purifier = new HTMLPurifier($config);

        return self::$purifier;
    }
}
