<?php

namespace App\Support;

use Modules\BackEnd\Entities\AppCategory;

/**
 * Company news (trade shows, events, partnerships) is written as ordinary articles in the article
 * category "news" (EN) / "tin-tuc" (VI). The frontend serves it at /news and /vi/tin-tuc instead of
 * under /blog, so blog listings, the blog category tree and blog search leave it out.
 */
class NewsCategory
{
    public const SLUGS = ['news', 'tin-tuc'];

    private static array $ids = [];

    public static function isNews(?string $slug): bool
    {
        return in_array($slug, self::SLUGS, true);
    }

    /** Ids of the news categories (article type), for one language or all. */
    public static function ids(?int $languageId = null): array
    {
        $key = $languageId ?? 0;

        return self::$ids[$key] ??= AppCategory::where('type', config('backend.categoryType.article'))
            ->whereIn('slug', self::SLUGS)
            ->when($languageId, fn ($q) => $q->where('language_id', $languageId))
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    /** "/blog/news/x.html" -> "/news/x.html", "/vi/blog/tin-tuc" -> "/vi/tin-tuc" (absolute URLs too). */
    public static function publicUrl(string $url): string
    {
        return preg_replace('#/blog/(' . implode('|', self::SLUGS) . ')(?=[/?\#]|$)#', '/$1', $url);
    }
}
