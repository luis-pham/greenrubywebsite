<?php

namespace Modules\BackEnd\Helpers;

/** Admin edit pages per content type, in a given content language (phase 1E-b: matrix "open editor"). */
final class AdminLinks
{
    private const EDIT = [
        'cruise' => ['backend.cruise.edit', []],
        'cabin' => ['backend.cabin.edit', []],
        'itinerary' => ['backend.itinerary.edit', []],
        'experience' => ['backend.exp-activity.edit', []],
        'article' => ['backend.article.edit', []],
        'article_category' => ['backend.category.edit', ['typeName' => 'article']],
        'faq_group' => ['backend.group.edit', ['typeName' => 'faq']],
        'faq' => ['backend.faq.edit', []],
        'destination' => ['backend.destination.edit', []],
        'service' => ['backend.service.edit', []],
        'amenity' => ['backend.amenity.edit', []],
    ];

    public static function edit(string $type, int $id, $language): ?string
    {
        if (!isset(self::EDIT[$type])) {
            return null;
        }
        [$name, $params] = self::EDIT[$type];
        $params['id'] = $id;
        if ($language && !$language->is_default) {
            $name = Utilities::bindRouteNameMultiLanguage($name);
            $params['languageCode'] = $language->code;
        }
        try {
            return route($name, $params);
        } catch (\Throwable $e) {
            return null;
        }
    }
}
