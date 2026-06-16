<?php
namespace Modules\FrontEnd\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\BackEnd\Helpers\Utilities;
use Modules\BackEnd\Services\AppGroupService;
use Modules\FrontEnd\Constants\PageCodeConsts;
use Modules\FrontEnd\Constants\PageConfigKeyConsts;
use Modules\FrontEnd\Helpers\FeLanguageUtils;
use Modules\FrontEnd\Helpers\FeUtils;
use Modules\FrontEnd\Services\AppFaqService;
use Modules\FrontEnd\Services\AppGroupService as FeAppGroupService;

class FaqController extends Controller
{
    private $baseView = 'frontend::faq.';

    public function index(Request $request)
    {
        $page = $request->route('page') ?? 1;
        $keyword = $request->get('k');

        $language = FeLanguageUtils::getCurrentLanguage();
        $languageCode = $request->route('languageCode');

        if ($page <= 0) {
            $page = 1;
        }

        $lastBreadcrumb = $this->getBreadcrumbByGroup([], $languageCode);
        $listBreadcrumb = FeUtils::bindBreadcrumb($lastBreadcrumb, $languageCode);

        $listGroup = AppGroupService::getAll(config('backend.groupType.faq'), $language->id);

        $param = ['keyword' => $keyword];
        $listFaqCount = AppFaqService::getPagingCount($param, $language->id);
        $pageSize = config('frontend.paginationLimit');
        $totalPage = (int)floor(($listFaqCount - 1) / $pageSize + 1);
        if ($listFaqCount > 0 && $page > $totalPage) {
            return abort(404);
        }

        $config = Utilities::getAllConfig($language);
        $title = FeUtils::bindWebsiteTitle(__('frontend::faq.page_title'), $config['website-name']);
        if ($page > 1) {
            $title .= ' - ' . __('frontend::common.page') . ' ' . $page;
        }

        $param['page'] = $page;
        $param['pageSize'] = $pageSize;
        $listFaq = AppFaqService::getPaging($param, $language->id);

        $menuUrlActive = route(Utilities::getRouteName('frontend.faq.index'), ['languageCode' => $languageCode]);

        $url = $page == 1
            ? FeUtils::frontendRoute('frontend.faq.index', [], $languageCode)
            : FeUtils::frontendRoute('frontend.faq.index.paginate', ['page' => $page], $languageCode);

        \SEO::setTitle($title);
        \SEO::setCanonical($url);

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

        return view($this->baseView . __FUNCTION__, compact('menuUrlActive', 'listBreadcrumb', 'listGroup', 'listFaq', 'totalPage', 'config'));
    }

    public function category(Request $request)
    {
        $slug = $request->route('slug');
        $page = $request->route('page') ?? 1;
        $keyword = $request->get('k');

        $language = FeLanguageUtils::getCurrentLanguage();
        $languageCode = $request->route('languageCode');

        $group = AppGroupService::getBySlug($slug, config('backend.groupType.faq'), $language->id);
        if (!$group || $group->slug == 'root') {
            return abort(404);
        }

        if ($page <= 0) {
            $page = 1;
        }

        $lastBreadcrumb = $this->getBreadcrumbByGroup($group, $languageCode);
        $listBreadcrumb = FeUtils::bindBreadcrumb($lastBreadcrumb, $languageCode);

        $listGroup = AppGroupService::getAll(config('backend.groupType.faq'), $language->id);

        $param = [
            'group_id' => $group->id,
            'keyword' => $keyword
        ];
        $listFaqCount = AppFaqService::getPagingCount($param, $language->id);
        $pageSize = config('frontend.paginationLimit');
        $totalPage = (int)floor(($listFaqCount - 1) / $pageSize + 1);
        if ($listFaqCount > 0 && $page > $totalPage) {
            return abort(404);
        }

        $config = Utilities::getAllConfig($language);
        $title = $group->name;
        $title = FeUtils::bindWebsiteTitle($title, $config['website-name']);
        if ($page > 1) {
            $title .= ' - ' . __('frontend::common.page') . ' ' . $page;
        }

        $param['page'] = $page;
        $param['pageSize'] = $pageSize;
        $listFaq = AppFaqService::getPaging($param, $language->id);

        $menuUrlActive = route(Utilities::getRouteName('frontend.faq.index'), ['languageCode' => $languageCode]);

        $url = $page == 1
            ? FeUtils::frontendRoute('frontend.faq.category', ['slug' => $group->slug], $languageCode)
            : FeUtils::frontendRoute('frontend.faq.category.paginate', ['slug' => $group->slug, 'page' => $page], $languageCode);

        \SEO::setTitle($title);
        \SEO::setCanonical($url);

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

        return view($this->baseView . 'index', compact('menuUrlActive', 'listBreadcrumb', 'group', 'listGroup', 'listFaq', 'totalPage', 'config'));
    }

    private function getBreadcrumbByGroup($group, $languageCode)
    {
        $list = [[
            'name' => __('frontend::faq.page_title'),
            'url' => route(Utilities::getRouteName('frontend.faq.index'), ['languageCode' => $languageCode])
        ]];

        if ($group) {
            $list[] = [
                'name' => $group->name,
                'url' => route(Utilities::getRouteName('frontend.faq.category'), ['languageCode' => $languageCode, 'slug' => $group->slug])
            ];
        }

        return $list;
    }
}