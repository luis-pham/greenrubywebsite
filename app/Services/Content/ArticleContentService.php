<?php

namespace App\Services\Content;

use Modules\BackEnd\Entities\AdLanguage;
use Modules\BackEnd\Entities\AppCategory;
use Modules\BackEnd\Services\AppCategoryService;
use Modules\FrontEnd\Constants\PageCodeConsts;
use Modules\FrontEnd\Constants\PageConfigKeyConsts;
use Modules\FrontEnd\Helpers\FeArticleUtils;
use Modules\FrontEnd\Helpers\FeHreflangUtils;
use Modules\FrontEnd\Helpers\FeUtils;
use Modules\BackEnd\Helpers\Utilities;
use Modules\FrontEnd\Services\AppArticleService;
use Modules\FrontEnd\Services\AppCategoryService as FeAppCategoryService;

/**
 * Blog payloads for the Content API. Mirrors ArticleController (index / category / show / legacy redirect):
 * only published articles, newest publish_date first, category listings include sub-categories.
 */
class ArticleContentService
{
    /**
     * GET /articles. Null => Blade 404 (unknown category, page past the end). For a category slug of the
     * other language returns only {redirect:{category,page}} (the Blade site 301s to the localized slug).
     */
    public static function index(AdLanguage $language, ?string $categorySlug, int $page, ?string $keyword): ?array
    {
        $languageId = (int) $language->id;
        $type = config('backend.categoryType.article');

        $category = null;
        if ($categorySlug !== null && $categorySlug !== '') {
            $category = AppCategoryService::getBySlug($categorySlug, $type, $languageId);
            if (!$category || $category->slug === 'root') {
                $source = AppCategory::where('slug', $categorySlug)->where('type', $type)->where('slug', '!=', 'root')->first();
                $localized = $source ? FeHreflangUtils::findArticleCategoryCounterpart($source, $languageId) : null;
                if ($localized && $localized->slug && $localized->slug !== 'root') {
                    return ['redirect' => ['category' => $localized->slug, 'page' => $page > 1 ? $page : null]];
                }

                return null;
            }
        }

        $param = [];
        if ($keyword !== null && $keyword !== '') {
            $param['keyword'] = $keyword;
        }
        if ($category) {
            $param['category_id'] = $category->id;
            $param['include_child'] = true;
        }

        $total = AppArticleService::getPagingCount($param, $languageId);
        $perPage = (int) config('frontend.paginationArticleLimit', 10);
        $pagination = PageConfigContentService::paginate($page, $perPage, $total);
        if ($total > 0 && $page > $pagination['lastPage']) {
            return null;
        }

        $featuredParam = ['is_featured' => true, 'is_disabled_paginate' => true];
        if ($category) {
            $featuredParam += ['category_id' => $category->id, 'include_child' => true];
        }

        return [
            'seo' => self::listingSeo($language, $category, $page),
            'noIndex' => $category !== null && $total === 0,
            'category' => $category ? self::categoryDetail($category, $language) : null,
            'categories' => self::categoryTree($language),
            'featured' => self::summaries(AppArticleService::getPaging($featuredParam, $languageId)),
            'items' => self::summaries(AppArticleService::getPaging($param + ['page' => $page, 'pageSize' => $perPage], $languageId)),
            'popular' => self::popular($language),
            'pagination' => $pagination,
            'keyword' => $keyword !== '' ? $keyword : null,
        ];
    }

    /** GET /articles/{categorySlug}/{slug}. Null when missing/unpublished (findByCategoryAndSlug). */
    public static function detail(AdLanguage $language, string $categorySlug, string $slug): ?array
    {
        $languageId = (int) $language->id;
        $obj = AppArticleService::findByCategoryAndSlug($categorySlug, $slug, $languageId);
        if (!$obj) {
            return null;
        }

        $type = config('backend.categoryType.article');
        $summary = self::summary($obj);

        $related = [];
        $category = AppCategoryService::find($obj->category_id, $type, $languageId);
        if ($category) {
            $related = self::summaries(AppArticleService::getPaging([
                'page' => 1,
                'pageSize' => 5,
                'category_id' => $obj->category_id,
                'exclude_id' => [$obj->id],
            ], $languageId));
        }

        return array_merge($summary, [
            'leadHtml' => PageConfigContentService::html($obj->lead),
            'contentHtml' => self::contentHtml($obj->content, $languageId),
            'seo' => [
                'title' => ItineraryContentService::clean($obj->seo_title) ?? ItineraryContentService::clean($obj->title),
                'description' => ItineraryContentService::clean($obj->seo_description) ?? $summary['lead'],
            ],
            // superset of the summary's {id,slug,name}: + description, parent, breadcrumb
            'category' => $category ? self::categoryDetail($category, $language) : $summary['category'],
            'related' => $related,
            'popular' => self::popular($language),
            'canonical' => [
                'categorySlug' => $obj->category_slug,
                'slug' => FeArticleUtils::getArticleSlug($obj),
            ],
            'alternate' => self::alternate($obj, $language),
            'updatedAt' => CabinContentService::isoDate($obj->getRawOriginal('updated_at') ?: $obj->getRawOriginal('created_at')),
        ]);
    }

    /** GET /articles/legacy/{id}: target of the old /blog/{slug}-{id}.html URLs (FeArticleUtils::resolveLegacyUrl). */
    public static function legacy(AdLanguage $language, int $id): ?array
    {
        $article = AppArticleService::findJoin($id, (int) $language->id);
        if (!$article) {
            return null;
        }

        return [
            'id' => (int) $article->id,
            // null => the Blade site redirects to the blog index (article without category)
            'categorySlug' => $article->category_slug ?: null,
            'slug' => $article->category_slug ? FeArticleUtils::getArticleSlug($article) : null,
        ];
    }

    public static function summaries($rows): array
    {
        return collect($rows)->map(fn ($a) => self::summary($a))->values()->all();
    }

    /** Rows must carry category_slug / category_name (AppArticleService / SourceDataService joins). */
    public static function summary($a): array
    {
        return [
            'id' => (int) $a->id,
            'slug' => FeArticleUtils::getArticleSlug($a),
            'title' => $a->title,
            'subTitle' => ItineraryContentService::clean($a->sub_title),
            'lead' => ItineraryContentService::plain($a->lead),
            'image' => CabinContentService::fileLink($a->image_link),
            'publishDate' => CabinContentService::isoDate($a->getRawOriginal('publish_date')),
            'isFeatured' => (bool) $a->is_featured,
            'category' => !empty($a->category_slug)
                ? ['id' => (int) $a->category_id, 'slug' => $a->category_slug, 'name' => $a->category_name]
                : null,
        ];
    }

    /** "article-popular" page-config picks (sidebar on blog, article and legal pages). */
    public static function popular(AdLanguage $language): array
    {
        $config = FeUtils::getPageConfigByCode(PageCodeConsts::ARTICLE, (int) $language->id);

        return self::summaries($config[PageConfigKeyConsts::ARTICLE_POPULAR] ?? []);
    }

    /** Non-root article categories as a tree (nested-set order), with published-article counts incl. children. */
    public static function categoryTree(AdLanguage $language): array
    {
        $languageId = (int) $language->id;
        $type = config('backend.categoryType.article');
        $all = FeAppCategoryService::getAll($type, $languageId);
        $rootIds = AppCategory::where('type', $type)->where('language_id', $languageId)->where('slug', 'root')->pluck('id')->all();

        $build = function ($parentIds) use (&$build, $all, $languageId) {
            return $all->filter(fn ($c) => in_array($c->parent_id, $parentIds, false))
                ->map(fn ($c) => [
                    'id' => (int) $c->id,
                    'slug' => $c->slug,
                    'name' => $c->name,
                    'count' => AppArticleService::getPagingCount(['category_id' => $c->id, 'include_child' => true], $languageId),
                    'children' => $build([$c->id]),
                ])
                ->values()
                ->all();
        };

        return $build(array_merge($rootIds, [null]));
    }

    protected static function categoryDetail($category, AdLanguage $language): array
    {
        $type = config('backend.categoryType.article');
        $ancestors = AppCategory::where('type', $type)
            ->where('language_id', $language->id)
            ->where('lvl', '>', 0)
            ->where('lft', '<=', $category->lft)
            ->where('rgt', '>=', $category->rgt)
            ->orderBy('lft')
            ->get(['id', 'slug', 'name', 'parent_id']);
        $parent = $ancestors->firstWhere('id', $category->parent_id);

        return [
            'id' => (int) $category->id,
            'slug' => $category->slug,
            'name' => $category->name,
            'description' => ItineraryContentService::plain($category->description),
            'parent' => $parent ? ['id' => (int) $parent->id, 'slug' => $parent->slug, 'name' => $parent->name] : null,
            'breadcrumb' => $ancestors->map(fn ($c) => ['slug' => $c->slug, 'name' => $c->name])->values()->all(),
        ];
    }

    /**
     * Index: app_page "article" SEO, else "Blog". Category: seo_title ?: name, description seo_description
     * ?: website-description. " - Page N" suffix for page > 1 (as on the Blade site).
     */
    protected static function listingSeo(AdLanguage $language, $category, int $page): array
    {
        if (!$category) {
            $seo = PageConfigContentService::seo(PageCodeConsts::ARTICLE, $language);
        } else {
            $config = Utilities::getAllConfig($language);
            $seo = [
                'title' => ItineraryContentService::clean($category->seo_title) ?? ItineraryContentService::clean($category->name),
                'description' => ItineraryContentService::clean($category->seo_description)
                    ?? ItineraryContentService::clean($config['website-description'] ?? null),
            ];
        }
        $seo['title'] = PageConfigContentService::pageSuffix($seo['title'], $page, $language);

        return $seo;
    }

    /**
     * Blade pipeline (FeUtils::bindArticleContent then safe_html): SEO-keyword links, rel=nofollow on external
     * links, images wrapped in a link to themselves. bindArticleContent makes "/..." URLs absolute against the
     * CMS host; they are turned back into root-relative paths here.
     */
    protected static function contentHtml(?string $content, int $languageId): ?string
    {
        if (ItineraryContentService::clean($content) === null) {
            return null;
        }
        $html = FeUtils::bindArticleContent($content, $languageId);
        $root = rtrim(url('/'), '/');
        $html = preg_replace('#(\s(?:href|src)=["\'])' . preg_quote($root, '#') . '(/[^"\']*)#i', '$1$2', (string) $html);

        return PageConfigContentService::html($html);
    }

    /**
     * Blade hreflang (FeHreflangUtils::resolveArticleShow) only pairs the same article id in the other
     * language; ids are unique across languages, so this is null unless that ever changes.
     */
    protected static function alternate($obj, AdLanguage $language): ?array
    {
        $target = ItineraryContentService::otherLanguage($language);
        $other = $target ? AppArticleService::findJoin($obj->id, (int) $target->id) : null;
        if (!$other || !$other->category_slug) {
            return null;
        }

        return [
            'lang' => $target->code,
            'id' => (int) $other->id,
            'categorySlug' => $other->category_slug,
            'slug' => FeArticleUtils::getArticleSlug($other),
        ];
    }
}
