<?php
namespace Modules\FrontEnd\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\BackEnd\Helpers\Utilities;
use Modules\BackEnd\Services\AdLanguageService;
use Modules\BackEnd\Services\AppExpActivityService;
use Modules\BackEnd\Services\AppServiceService;
use Modules\BackEnd\Services\AppItineraryService;
use Modules\BackEnd\Services\AppGroupService;
use Modules\FrontEnd\Helpers\FeArticleUtils;
use Modules\FrontEnd\Helpers\FeUtils;
use Modules\FrontEnd\Services\AppArticleService;
use Modules\FrontEnd\Services\AppCategoryService;
use Modules\FrontEnd\Services\AppFaqService;
use Modules\FrontEnd\Services\AppCruiseService;
use Modules\FrontEnd\Services\AppCruiseItineraryService;
use Modules\FrontEnd\Services\GalleryService;
use Modules\FrontEnd\Services\PageService;
use Modules\BackEnd\Entities\AppCruise;

class SitemapController extends Controller
{
    private $baseView = 'frontend::sitemap.';

    public function index()
    {
        $article = AppArticleService::getLatestUpdate([]);
        $listCategoryUpdatedAt = [];
        if ($article) {
            $listCategoryUpdatedAt[] = $article->updated_at ?: $article->created_at;
        }

        $experience = AppExpActivityService::getLatestUpdate([]);
        $service = AppServiceService::getLatestUpdate([]);
        $itinerary = AppItineraryService::getLatestUpdate([]);
        $cruise = AppCruiseService::getLatestUpdate([]);
        $gallery = GalleryService::getGalleryUpdatedAt([]);
        $faq = AppFaqService::getLatestUpdate([]);

        

        $categoryUpdatedAt = null;
        if (count($listCategoryUpdatedAt) > 0) {
            $categoryUpdatedAt = $listCategoryUpdatedAt[0];
            if (count($listCategoryUpdatedAt) > 1) {
                for ($i = 1; $i < count($listCategoryUpdatedAt); $i++) {
			        $date1 = Carbon::createFromFormat('Y-m-d H:i:s', $categoryUpdatedAt);
			        $date2 = Carbon::createFromFormat('Y-m-d H:i:s', $listCategoryUpdatedAt[$i]);
                    if ($date1->lte($date2)) {
                        $categoryUpdatedAt = $listCategoryUpdatedAt[$i];
                    }
                }
            }
        }

        $listPageUpdatedAt = [];
        foreach ([$article, $experience, $service, $itinerary, $cruise] as $item) {
            if (!$item) {
                continue;
            }
            $listPageUpdatedAt[] = $item->updated_at ?: $item->created_at;
        }
        $pageUpdatedAt = null;
        if (count($listPageUpdatedAt) > 0) {
            $pageUpdatedAt = $listPageUpdatedAt[0];
            if (count($listPageUpdatedAt) > 1) {
                for ($i = 1; $i < count($listPageUpdatedAt); $i++) {
                    $date1 = Carbon::createFromFormat('Y-m-d H:i:s', $pageUpdatedAt);
                    $date2 = Carbon::createFromFormat('Y-m-d H:i:s', $listPageUpdatedAt[$i]);
                    if ($date1->lte($date2)) {
                        $pageUpdatedAt = $listPageUpdatedAt[$i];
                    }
                }
            }
        }

        return response()->view($this->baseView . __FUNCTION__, [
            'article' => $article,
            'experience' => $experience,
            'service' => $service,
            'itinerary' => $itinerary,
            'cruise' => $cruise,
            'gallery' => $gallery,
            'faq' => $faq,
            'categoryUpdatedAt' => $categoryUpdatedAt,
            'pageUpdatedAt' => $pageUpdatedAt,
        ])->header('Content-Type', 'text/xml');
    }

    public function page()
    {
        $article = AppArticleService::getLatestUpdate([]);
        $experience = AppExpActivityService::getLatestUpdate([]);
        $service = AppServiceService::getLatestUpdate([]);
        $itinerary = AppItineraryService::getLatestUpdate([]);
        $cruise = AppCruiseService::getLatestUpdate([]);
        $gallery = GalleryService::getGalleryUpdatedAt([]);
        $faq = AppFaqService::getLatestUpdate([]);
        $legal = PageService::getLegalUpdatedAt([]);
        $about = PageService::getAboutUpdatedAt([]);
        $contact = PageService::getContactUpdatedAt([]);
        $pages = [];

        $homepageLastmod = $cruise
            ? Carbon::parse($cruise->updated_at ?: $cruise->created_at)->format('Y-m-d\TH:i:sP')
            : Carbon::now()->format('Y-m-d\TH:i:sP');

        foreach ($this->localizedHubUrls('frontend.index') as $entry) {
            $pages[] = [
                'loc' => $entry['loc'],
                'lastmod' => $homepageLastmod,
            ];
        }

        if ($article) {
            $lastmod = Carbon::parse($article->updated_at ?: $article->created_at)->format('Y-m-d\TH:i:sP');
            foreach ($this->localizedHubUrls('frontend.article.index') as $entry) {
                $pages[] = ['loc' => $entry['loc'], 'lastmod' => $lastmod];
            }
        }
        if ($experience) {
            $lastmod = Carbon::parse($experience->updated_at ?: $experience->created_at)->format('Y-m-d\TH:i:sP');
            foreach ($this->localizedHubUrls('frontend.experience.index') as $entry) {
                $pages[] = ['loc' => $entry['loc'], 'lastmod' => $lastmod];
            }
        }
        if ($service) {
            $lastmod = Carbon::parse($service->updated_at ?: $service->created_at)->format('Y-m-d\TH:i:sP');
            foreach ($this->localizedHubUrls('frontend.service.index') as $entry) {
                $pages[] = ['loc' => $entry['loc'], 'lastmod' => $lastmod];
            }
        }
        if ($itinerary) {
            $lastmod = Carbon::parse($itinerary->updated_at ?: $itinerary->created_at)->format('Y-m-d\TH:i:sP');
            foreach ($this->localizedHubUrls('frontend.itinerary.index') as $entry) {
                $pages[] = ['loc' => $entry['loc'], 'lastmod' => $lastmod];
            }
        }
        if ($gallery) {
            $lastmod = Carbon::parse($gallery)->format('Y-m-d\TH:i:sP');
            foreach ($this->localizedHubUrls('frontend.gallery.index') as $entry) {
                $pages[] = ['loc' => $entry['loc'], 'lastmod' => $lastmod];
            }
        }
        if ($faq) {
            $lastmod = Carbon::parse($faq->updated_at ?: $faq->created_at)->format('Y-m-d\TH:i:sP');
            foreach ($this->localizedHubUrls('frontend.faq.index') as $entry) {
                $pages[] = ['loc' => $entry['loc'], 'lastmod' => $lastmod];
            }
        }
        if ($legal) {
            $lastmod = Carbon::parse($legal)->format('Y-m-d\TH:i:sP');
            foreach ($this->localizedHubUrls('frontend.page.legal') as $entry) {
                $pages[] = ['loc' => $entry['loc'], 'lastmod' => $lastmod];
            }
            foreach ([
                'frontend.page.safety-policies',
                'frontend.page.terms-and-conditions',
                'frontend.page.privacy-policy',
                'frontend.page.payment-methods',
            ] as $routeName) {
                foreach ($this->localizedHubUrls($routeName) as $entry) {
                    $pages[] = ['loc' => $entry['loc'], 'lastmod' => $lastmod];
                }
            }
        }
        if ($about) {
            $lastmod = Carbon::parse($about)->format('Y-m-d\TH:i:sP');
            foreach ($this->localizedHubUrls('frontend.about.index') as $entry) {
                $pages[] = ['loc' => $entry['loc'], 'lastmod' => $lastmod];
            }
        }
        if ($contact) {
            $lastmod = Carbon::parse($contact)->format('Y-m-d\TH:i:sP');
            foreach ($this->localizedHubUrls('frontend.contact.index') as $entry) {
                $pages[] = ['loc' => $entry['loc'], 'lastmod' => $lastmod];
            }
        }

        foreach ($this->localizedHubUrls('frontend.booking') as $entry) {
            $pages[] = [
                'loc' => $entry['loc'],
                'lastmod' => $homepageLastmod,
            ];
        }

        return response()->view($this->baseView . __FUNCTION__, [
            'pages' => $pages,
        ])->header('Content-Type', 'text/xml');
    }
    
    public function article()
    {
        $defaultLanguage = AdLanguageService::getDefaultLanguage();
        $listLanguage = $this->getAllLanguage();
        
        $list = AppArticleService::getPaging(['is_disabled_paginate' => true]);
        $published = [];
        for ($i = 0; $i < count($list); $i++) {
            $languageCode = $defaultLanguage->id == $list[$i]->language_id
                ? null
                : ($listLanguage[$list[$i]->language_id] ?? null);
            try {
                $list[$i]->url = FeArticleUtils::getShowUrl($list[$i], $languageCode);
                $published[] = $list[$i];
            } catch (\Throwable) {
                continue;
            }
        }

        return response()->view($this->baseView . __FUNCTION__, [
            'list' => $published,
        ])->header('Content-Type', 'text/xml');
    }

    public function category(Request $request)
    {
        $defaultLanguage = AdLanguageService::getDefaultLanguage();
        $listLanguage = $this->getAllLanguage();

        $type = $request->route('type');
        
        $listCategory = [];
        
        if ($type == 'article') {
            $list = AppCategoryService::getAll(config('backend.categoryType.article'));
            for ($i = 0; $i < count($list); $i++) {
                $article = AppArticleService::getLatestUpdate([
                    'category_id' => $list[$i]->id
                ]);
                if ($article) {
                    $list[$i]->url = $defaultLanguage->id == $list[$i]->language_id
                        ? route('frontend.article.category', ['slug' => Utilities::convertToAlias($list[$i]->name)])
                        : route(Utilities::bindRouteNameMultiLanguage('frontend.article.category'), ['languageCode' => $listLanguage[$list[$i]->language_id], 'slug' => Utilities::convertToAlias($list[$i]->name)]);
                    $list[$i]->created_at = $article->created_at;
                    $list[$i]->updated_at = $article->updated_at;
                    $listCategory[] = $list[$i];
                }
            }
        }

        return response()->view($this->baseView . __FUNCTION__, [
            'listCategory' => $listCategory
        ])->header('Content-Type', 'text/xml');
    }

    public function experience(){
        $defaultLanguage = AdLanguageService::getDefaultLanguage();
        $listLanguage = $this->getAllLanguage();

        $list = AppExpActivityService::getPaging(['is_disabled_paginate' => true]);
        for ($i = 0; $i < count($list); $i++) {
            $languageId = $list[$i]->language_id ?? $defaultLanguage->id;
            $list[$i]->url = $defaultLanguage->id == $languageId
                ? route('frontend.experience.show', ['slug' => Utilities::convertToAlias($list[$i]->name), 'id' => $list[$i]->id])
                : route(Utilities::bindRouteNameMultiLanguage('frontend.experience.show'), ['languageCode' => $listLanguage[$languageId], 'slug' => Utilities::convertToAlias($list[$i]->name), 'id' => $list[$i]->id]);
        }

        return response()->view($this->baseView . __FUNCTION__, [
            'list' => $list,
        ])->header('Content-Type', 'text/xml');
    }

    public function service()
    {
        $defaultLanguage = AdLanguageService::getDefaultLanguage();
        $service = AppServiceService::getLatestUpdate([], $defaultLanguage->id);
        $list = [];

        if (!$service) {
            $service = (object) [
                'updated_at' => Carbon::now(),
                'created_at' => Carbon::now(),
            ];
        }

        $service->url = route('frontend.service.index');
        $list[] = $service;

        return response()->view($this->baseView . __FUNCTION__, [
            'list' => $list,
        ])->header('Content-Type', 'text/xml');
    }

    public function itinerary(){
        $defaultLanguage = AdLanguageService::getDefaultLanguage();
        $listLanguage = $this->getAllLanguage();

        $sourceList = AppCruiseItineraryService::getScheduledItineraries($defaultLanguage->id);
        $list = [];
        for ($i = 0; $i < count($sourceList); $i++) {
            if (empty($sourceList[$i]->cruise_id) || empty($sourceList[$i]->id)) {
                continue;
            }
            $languageId = $sourceList[$i]->language_id ?? $defaultLanguage->id;
            $sourceList[$i]->url = $defaultLanguage->id == $languageId
                ? route('frontend.itinerary.show', ['slug' => Utilities::convertToAlias($sourceList[$i]->name), 'cruise_id' => $sourceList[$i]->cruise_id, 'itinerary_id' => $sourceList[$i]->id])
                : route(Utilities::bindRouteNameMultiLanguage('frontend.itinerary.show'), ['languageCode' => $listLanguage[$languageId], 'slug' => Utilities::convertToAlias($sourceList[$i]->name), 'cruise_id' => $sourceList[$i]->cruise_id, 'itinerary_id' => $sourceList[$i]->id]);
            $list[] = $sourceList[$i];
        }

        return response()->view($this->baseView . __FUNCTION__, [
            'list' => $list,
        ])->header('Content-Type', 'text/xml');
    }


    public function cruise()
    {
        $defaultLanguage = AdLanguageService::getDefaultLanguage();
        $listLanguage = $this->getAllLanguage();

        $list = AppCruise::orderBy('id', 'asc')->get();
        for ($i = 0; $i < count($list); $i++) {
            $languageId = $list[$i]->language_id ?? $defaultLanguage->id;
            $list[$i]->url = $defaultLanguage->id == $languageId
                ? route('frontend.cruise.show', ['slug' => Utilities::convertToAlias($list[$i]->name), 'id' => $list[$i]->id])
                : route(Utilities::bindRouteNameMultiLanguage('frontend.cruise.show'), ['languageCode' => $listLanguage[$languageId], 'slug' => Utilities::convertToAlias($list[$i]->name), 'id' => $list[$i]->id]);
        }

        return response()->view($this->baseView . __FUNCTION__, [
            'list' => $list,
        ])->header('Content-Type', 'text/xml');
    }

    public function gallery(){
        $defaultLanguage = AdLanguageService::getDefaultLanguage();
        $galleryLastmod = GalleryService::getGalleryUpdatedAt($defaultLanguage->id);
        $lastmod = Carbon::parse($galleryLastmod)->format('Y-m-d\TH:i:sP');
        $galleryUrls = [];

        $galleryUrls[] = [
            'loc' => route('frontend.gallery.index'),
            'lastmod' => $lastmod,
        ];

        $filters = GalleryService::getGalleryFilter($defaultLanguage->id);
        foreach (array_keys($filters) as $slug) {
            $galleryUrls[] = [
                'loc' => route('frontend.gallery.category', ['slug' => $slug]),
                'lastmod' => $lastmod,
            ];
        }

        return response()->view($this->baseView . __FUNCTION__, [
            'galleryUrls' => $galleryUrls,
        ])->header('Content-Type', 'text/xml');
    }

    public function faq()
    {
        $defaultLanguage = AdLanguageService::getDefaultLanguage();
        $lastmod = Carbon::now()->format('Y-m-d\TH:i:sP');
        $faqUrls = [];
        
        $faqUrls[] = [
            'loc' => route('frontend.faq.index'),
            'lastmod' => $lastmod,
        ];

        $listGroup = AppGroupService::getAll(config('backend.groupType.faq'), $defaultLanguage->id);
        foreach ($listGroup as $group) {
            if (!$group || !$group->slug || $group->slug === 'root') {
                continue;
            }
            $faqUrls[] = [
                'loc' => route('frontend.faq.category', ['slug' => $group->slug]),
                'lastmod' => $lastmod,
            ];
        }

        return response()->view($this->baseView . __FUNCTION__, [
            'faqUrls' => $faqUrls,
        ])->header('Content-Type', 'text/xml');
    }

    
    private function localizedHubUrls(string $baseRouteName, array $params = []): array
    {
        $entries = [];

        foreach (AdLanguageService::getAll() as $language) {
            try {
                $entries[] = [
                    'loc' => FeUtils::frontendRoute($baseRouteName, $params, $language->code),
                    'lang' => $language->code,
                ];
            } catch (\Throwable) {
                continue;
            }
        }

        return $entries;
    }

    private function getAllLanguage()
    {
        return AdLanguageService::getAll()->pluck('code', 'id');
    }
}
