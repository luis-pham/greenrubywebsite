<?php
namespace Modules\FrontEnd\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\BackEnd\Helpers\Utilities;
use Modules\BackEnd\Services\AppCategoryService;
use Modules\FrontEnd\Constants\PageCodeConsts;
use Modules\FrontEnd\Constants\PageConfigKeyConsts;
use Modules\FrontEnd\Helpers\FeArticleUtils;
use Modules\FrontEnd\Helpers\FeHreflangUtils;
use Modules\FrontEnd\Helpers\FeLanguageUtils;
use Modules\FrontEnd\Helpers\FeUtils;
use Modules\FrontEnd\Services\AppArticleService;
use Modules\FrontEnd\Services\AppCategoryService as FeAppCategoryService;

class ArticleController extends Controller
{
    private $baseView = 'frontend::article.';

    public function index(Request $request)
    {
        $page = $request->route('page') ?? 1;
        $keyword = $request->get('k');

        $language = FeLanguageUtils::getCurrentLanguage();
        $languageCode = $request->route('languageCode');

        if ($page <= 0) {
            $page = 1;
        }

        $lastBreadcrumb = $this->getBreadcrumbByCategory([], $languageCode);
        $listBreadcrumb = FeUtils::bindBreadcrumb($lastBreadcrumb, $languageCode);

        $categoryRoot = AppCategoryService::getBySlug('root', config('backend.categoryType.article'), $language->id);
        $listCategoryChild = $categoryRoot
            ? FeAppCategoryService::getByParentId($categoryRoot->id, config('backend.categoryType.article'), $language->id)
            : [];

        $param = ['keyword' => $keyword];
        $listArticleCount = AppArticleService::getPagingCount($param, $language->id);
        $pageSize = config('frontend.paginationArticleLimit');
        $totalPage = (int)floor(($listArticleCount - 1) / $pageSize + 1);
        if ($listArticleCount > 0 && $page > $totalPage) {
            return abort(404);
        }

        $pageConfig = FeUtils::getPageConfigByCode(PageCodeConsts::ARTICLE, $language->id);

        $listArticleFeatured = AppArticleService::getPaging([
            'is_featured' => true,
            'is_disabled_paginate' => true
        ], $language->id);

        $config = Utilities::getAllConfig($language);
        $seo = FeUtils::resolveHubSeo(
            PageCodeConsts::ARTICLE,
            $language,
            fn () => FeUtils::bindWebsiteTitle('Blog', $config['website-name']),
            $config['website-description']
        );
        if ($page > 1) {
            $seo['title'] .= ' - ' . __('frontend::common.page') . ' ' . $page;
        }

        $param['page'] = $page;
        $param['pageSize'] = $pageSize;
        $listArticle = AppArticleService::getPaging($param, $language->id);

        $menuUrlActive = route(Utilities::getRouteName('frontend.article.index'), ['languageCode' => $languageCode]);

        $url = $page == 1
            ? FeUtils::frontendRoute('frontend.article.index', [], $languageCode)
            : FeUtils::frontendRoute('frontend.article.index.paginate', ['page' => $page], $languageCode);

        FeUtils::applyHubSeoMeta($seo, $url, $config);

        return view($this->baseView . __FUNCTION__, compact('pageConfig', 'menuUrlActive', 'listBreadcrumb', 'listArticleFeatured', 'listCategoryChild', 'listArticle', 'totalPage'));
    }

    public function category(Request $request)
    {
        $slug = $request->route('slug');
        $page = $request->route('page') ?? 1;
        $keyword = $request->get('k');

        $language = FeLanguageUtils::getCurrentLanguage();
        $languageCode = $request->route('languageCode');

        $articleType = config('backend.categoryType.article');
        $category = AppCategoryService::getBySlug($slug, $articleType, $language->id);

        if (!$category || $category->slug === 'root') {
            $sourceCategory = \Modules\BackEnd\Entities\AppCategory::where('slug', $slug)
                ->where('type', $articleType)
                ->where('slug', '!=', 'root')
                ->first();

            if ($sourceCategory) {
                $localizedCategory = FeHreflangUtils::findArticleCategoryCounterpart(
                    $sourceCategory,
                    $language->id
                );
                if ($localizedCategory && $localizedCategory->slug && $localizedCategory->slug !== 'root') {
                    $redirectRoute = ($page && (int) $page > 1)
                        ? 'frontend.article.category.paginate'
                        : 'frontend.article.category';
                    $redirectParams = ['slug' => $localizedCategory->slug];
                    if ($page && (int) $page > 1) {
                        $redirectParams['page'] = (int) $page;
                    }

                    return redirect(
                        FeUtils::frontendRoute($redirectRoute, $redirectParams, $languageCode),
                        301
                    );
                }
            }

            return abort(404);
        }

        if ($page <= 0) {
            $page = 1;
        }

        $listCategoryParent = FeAppCategoryService::getParent($category->id, config('backend.categoryType.article'), $language->id);
        $lastBreadcrumb = $this->getBreadcrumbByCategory($listCategoryParent, $languageCode);
        $listBreadcrumb = FeUtils::bindBreadcrumb($lastBreadcrumb, $languageCode);

        if ($category->lvl > 1) {
            $categoryRoot = FeAppCategoryService::getParentByLevel($category->id, 1, config('backend.categoryType.article'), $language->id);
        } else {
            $categoryRoot = $category;
        }

        $listCategoryChild = FeAppCategoryService::getByParentId($category->id, config('backend.categoryType.article'), $language->id);
        $categoryParent = count($listCategoryChild) == 0
                            ? AppCategoryService::find($category->parent_id, config('backend.categoryType.article'), $language->id)
                            : $category;

        if (count($listCategoryChild) == 0) {
            $listCategoryChild = FeAppCategoryService::getByParentId($category->parent_id, config('backend.categoryType.article'), $language->id);
        }

        $param = [
            'keyword' => $keyword,
            'category_id' => $category->id,
            'include_child' => true
        ];
        $listArticleCount = AppArticleService::getPagingCount($param, $language->id);
        $pageSize = config('frontend.paginationArticleLimit');
        $totalPage = (int)floor(($listArticleCount - 1) / $pageSize + 1);
        if ($listArticleCount > 0 && $page > $totalPage) {
            return abort(404);
        }

        $pageConfig = FeUtils::getPageConfigByCode(PageCodeConsts::ARTICLE, $language->id);

        $listArticleFeatured = AppArticleService::getPaging([
            'category_id' => $category->id,
            'include_child' => true,
            'is_featured' => true,
            'is_disabled_paginate' => true
        ], $language->id);

        $config = Utilities::getAllConfig($language);
        $title = ($category->seo_title ? $category->seo_title : $category->name);
        $title = FeUtils::bindWebsiteTitle($title, $config['website-name']);
        if ($page > 1) {
            $title .= ' - ' . __('frontend::common.page') . ' ' . $page;
        }

        $param['page'] = $page;
        $param['pageSize'] = $pageSize;
        $listArticle = AppArticleService::getPaging($param, $language->id);

        $menuUrlActive = route(Utilities::getRouteName('frontend.article.index'), ['languageCode' => $languageCode]);

        $url = $page == 1
            ? route(Utilities::getRouteName('frontend.article.category'), ['languageCode' => $languageCode, 'slug' => $category->slug])
            : route(Utilities::getRouteName('frontend.article.category.paginate'), ['languageCode' => $languageCode, 'slug' => $category->slug, 'page' => $page]);

        \SEO::setTitle($title);
        $seoDescription = trim((string) ($category->seo_description ?? ''));
        if ($seoDescription !== '') {
            \SEO::setDescription($seoDescription);
        }
        \SEO::setCanonical($url);

        // Empty hubs stay crawlable for discovery but must not be indexed until they have articles.
        if ($listArticleCount === 0) {
            FeUtils::applyNoIndexFollowMeta();
        }

        \OpenGraph::setSiteName($config['website-name']);
        \OpenGraph::setTitle($title);
        \OpenGraph::setUrl($url);
        \OpenGraph::addImage(\URL::to('/') . config('frontend.organizationLogoSocial.url'), [
            'width' => config('frontend.organizationLogoSocial.width'),
            'height' => config('frontend.organizationLogoSocial.height'),
        ]);
        \TwitterCard::setType('summary');
        \TwitterCard::setTitle($title);
        \TwitterCard::setDescription($config['website-description']);
        \TwitterCard::setUrl($url);
        \TwitterCard::setImage(\URL::to('/') . config('frontend.organizationLogoSocial.url'));

        return view($this->baseView . 'index', compact('pageConfig', 'menuUrlActive', 'listBreadcrumb', 'categoryParent', 'category', 'listArticleFeatured', 'listCategoryChild', 'listArticle', 'totalPage'));
    }

    public function redirectLegacyShow(Request $request, $slug, $id)
    {
        $languageCode = $request->route('languageCode');
        $url = FeArticleUtils::resolveLegacyUrl($slug, $id, $languageCode);

        if (!$url) {
            return abort(404);
        }

        return redirect($url, 301);
    }

    public function show(Request $request)
    {
        $language = FeLanguageUtils::getCurrentLanguage();
        $languageCode = $request->route('languageCode');

        $categorySlug = $request->route('categorySlug');
        $articleSlug = $request->route('articleSlug');

        $obj = AppArticleService::findByCategoryAndSlug($categorySlug, $articleSlug, $language->id);
        if (!$obj) {
            return abort(404);
        }

        $canonicalCategorySlug = $obj->category_slug;
        $canonicalArticleSlug = FeArticleUtils::getArticleSlug($obj);
        if ($canonicalCategorySlug !== $categorySlug || $canonicalArticleSlug !== $articleSlug) {
            return redirect(FeArticleUtils::getShowUrl($obj, $languageCode), 301);
        }

        $pageConfig = FeUtils::getPageConfigByCode(PageCodeConsts::ARTICLE, $language->id);

        $obj->content = FeUtils::bindArticleContent($obj->content, $language->id);

        $listArticleRelated = [];
        $menuUrlActive = '';
        $lastBreadcrumb = [];
        $listCategoryChild = [];
        $categoryParent = null;
        $category = AppCategoryService::find($obj->category_id, config('backend.categoryType.article'), $language->id);
        if ($category) {
            $listArticleRelated = AppArticleService::getPaging([
                'page' => 1,
                'pageSize' => 5,
                'category_id' => $obj->category_id,
                'exclude_id' => [$obj->id]
            ], $language->id);

            $categoryRoot = $category->lvl > 1 ? FeAppCategoryService::getParentByLevel($category->id, 1, config('backend.categoryType.article'), $language->id) : $category;
            $menuUrlActive = route(Utilities::getRouteName('frontend.article.category'), ['languageCode' => $languageCode, 'slug' => $categoryRoot->slug]);

            $listCategory = FeAppCategoryService::getParent($obj->category_id, config('backend.categoryType.article'), $language->id);
            $lastBreadcrumb = $this->getBreadcrumbByCategory($listCategory, $languageCode);

            $listCategoryChild = FeAppCategoryService::getByParentId($category->id, config('backend.categoryType.article'), $language->id);
            $categoryParent = count($listCategoryChild) == 0
                ? AppCategoryService::find($category->parent_id, config('backend.categoryType.article'), $language->id)
                : $category;

            if (count($listCategoryChild) == 0) {
                $listCategoryChild = FeAppCategoryService::getByParentId($category->parent_id, config('backend.categoryType.article'), $language->id);
            }
        }
        $lastBreadcrumb[] = [
            'name' => $obj->title,
            'url' => FeArticleUtils::getShowUrl($obj, $languageCode)
        ];
        $listBreadcrumb = FeUtils::bindBreadcrumb($lastBreadcrumb, $languageCode);

        $menuUrlActive = route(Utilities::getRouteName('frontend.article.index'), ['languageCode' => $languageCode]);

        $config = Utilities::getAllConfig($language);
        $title = $obj->seo_title ? $obj->seo_title : $obj->title;
        $title = FeUtils::bindWebsiteTitle($title, $config['website-name']);
        $url = FeArticleUtils::getShowUrl($obj, $languageCode);

        \SEO::setTitle($title);
        \SEO::setDescription($obj->seo_description ? $obj->seo_description : strip_tags($obj->lead));
        \SEO::setCanonical($url);

        \OpenGraph::setSiteName($config['website-name']);
        \OpenGraph::setTitle($title);
        \OpenGraph::setUrl($url);
        if ($obj->image_link) {
            $image = FeUtils::getThumbnail(['link' => $obj->image_link, 'w' => 1200, 'h' => 630]);
            \OpenGraph::addImage($image);
        }

        \TwitterCard::setType('summary');
        \TwitterCard::setTitle($title);
        \TwitterCard::setDescription($obj->seo_description ? $obj->seo_description : strip_tags($obj->lead));
        \TwitterCard::setUrl($url);
        \TwitterCard::setImage($obj->image_link ? \URL::to('/') . $obj->image_link : \URL::to('/') . config('frontend.organizationLogoSocial.url'));

        return view($this->baseView . __FUNCTION__, compact('pageConfig', 'title', 'config', 'menuUrlActive', 'listBreadcrumb', 'obj', 'listArticleRelated', 'category', 'categoryParent', 'listCategoryChild', 'url'));
    }

    private function getBreadcrumbByCategory($listCategory, $languageCode)
    {
        $list = [[
            'name' => 'Blog',
            'url' => route(Utilities::getRouteName('frontend.article.index'), ['languageCode' => $languageCode])
        ]];

        for ($i = 0; $i < count($listCategory); $i++) {
            $list[] = [
                'name' => $listCategory[$i]->name,
                'url' => route(Utilities::getRouteName('frontend.article.category'), ['languageCode' => $languageCode, 'slug' => $listCategory[$i]->slug])
            ];
        }

        return $list;
    }
}
