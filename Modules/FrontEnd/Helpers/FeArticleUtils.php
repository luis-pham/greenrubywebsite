<?php

namespace Modules\FrontEnd\Helpers;

use Modules\BackEnd\Helpers\Utilities;
use Modules\FrontEnd\Services\AppArticleService;

class FeArticleUtils
{
    public static function getArticleSlug($article): string
    {
        if (!empty($article->slug)) {
            return $article->slug;
        }

        return Utilities::convertToAlias($article->title ?? '');
    }

    public static function getShowUrl($article, ?string $languageCode = null): string
    {
        $categorySlug = $article->category_slug ?? null;
        if (!$categorySlug) {
            $joined = AppArticleService::findJoin($article->id, $article->language_id);
            $categorySlug = $joined->category_slug ?? null;
        }

        if (!$categorySlug) {
            return route(Utilities::getRouteName('frontend.article.index'), array_filter([
                'languageCode' => $languageCode,
            ]));
        }

        $params = [
            'categorySlug' => $categorySlug,
            'articleSlug' => self::getArticleSlug($article),
        ];

        if ($languageCode) {
            $params['languageCode'] = $languageCode;
        }

        return route(Utilities::getRouteName('frontend.article.show'), $params);
    }

    public static function resolveLegacyUrl(string $legacySlug, $id, ?string $languageCode = null): ?string
    {
        $language = FeLanguageUtils::getCurrentLanguage();
        $article = AppArticleService::findJoin($id, $language->id);

        if (!$article) {
            return null;
        }

        $articleSlug = self::getArticleSlug($article);
        if ($legacySlug !== $articleSlug && $legacySlug !== Utilities::convertToAlias($article->title)) {
            // Still redirect to canonical URL even if legacy slug differs.
        }

        return self::getShowUrl($article, $languageCode);
    }
}
