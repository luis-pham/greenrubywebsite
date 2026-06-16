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
use Modules\FrontEnd\Helpers\FeHreflangUtils;
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
        $about = PageService::getAboutUpdatedAt([]);
        $contact = PageService::getContactUpdatedAt([]);
        $entries = [];

        $homepageLastmod = $cruise
            ? Carbon::parse($cruise->updated_at ?: $cruise->created_at)->format('Y-m-d\TH:i:sP')
            : Carbon::now()->format('Y-m-d\TH:i:sP');

        $entries = array_merge($entries, $this->buildHubEntries('frontend.index', $homepageLastmod, [], '1.0', 'weekly'));

        if ($article) {
            $lastmod = Carbon::parse($article->updated_at ?: $article->created_at)->format('Y-m-d\TH:i:sP');
            $entries = array_merge($entries, $this->buildHubEntries('frontend.article.index', $lastmod));
        }
        if ($experience) {
            $lastmod = Carbon::parse($experience->updated_at ?: $experience->created_at)->format('Y-m-d\TH:i:sP');
            $entries = array_merge($entries, $this->buildHubEntries('frontend.experience.index', $lastmod));
        }
        if ($service) {
            $lastmod = Carbon::parse($service->updated_at ?: $service->created_at)->format('Y-m-d\TH:i:sP');
            $entries = array_merge($entries, $this->buildHubEntries('frontend.service.index', $lastmod));
        }
        if ($itinerary) {
            $lastmod = Carbon::parse($itinerary->updated_at ?: $itinerary->created_at)->format('Y-m-d\TH:i:sP');
            $entries = array_merge($entries, $this->buildHubEntries('frontend.itinerary.index', $lastmod));
        }
        if ($gallery) {
            $lastmod = Carbon::parse($gallery)->format('Y-m-d\TH:i:sP');
            $entries = array_merge($entries, $this->buildHubEntries('frontend.gallery.index', $lastmod));
        }
        if ($faq) {
            $lastmod = Carbon::parse($faq->updated_at ?: $faq->created_at)->format('Y-m-d\TH:i:sP');
            $entries = array_merge($entries, $this->buildHubEntries('frontend.faq.index', $lastmod));
        }
        if ($about) {
            $lastmod = Carbon::parse($about)->format('Y-m-d\TH:i:sP');
            $entries = array_merge($entries, $this->buildHubEntries('frontend.about.index', $lastmod));
        }
        if ($contact) {
            $lastmod = Carbon::parse($contact)->format('Y-m-d\TH:i:sP');
            $entries = array_merge($entries, $this->buildHubEntries('frontend.contact.index', $lastmod));
        }

        return response()->view($this->baseView . __FUNCTION__, [
            'entries' => $entries,
        ])->header('Content-Type', 'text/xml');
    }
    
    public function article()
    {
        $defaultLanguage = AdLanguageService::getDefaultLanguage();
        $listLanguage = $this->getAllLanguage();
        $entries = [];

        $list = AppArticleService::getPaging(['is_disabled_paginate' => true]);
        for ($i = 0; $i < count($list); $i++) {
            $languageCode = $defaultLanguage->id == $list[$i]->language_id
                ? null
                : ($listLanguage[$list[$i]->language_id] ?? null);
            try {
                $url = FeArticleUtils::getShowUrl($list[$i], $languageCode);
                $alternates = FeHreflangUtils::getAlternateLinksForRoute('frontend.article.show', [
                    'categorySlug' => $list[$i]->category_slug,
                    'articleSlug' => FeArticleUtils::getArticleSlug($list[$i]),
                ], $list[$i]->language_id);

                $entries[] = $this->makeUrlEntry(
                    $url,
                    Carbon::parse($list[$i]->updated_at ?: $list[$i]->created_at)->format('Y-m-d\TH:i:sP'),
                    'monthly',
                    '0.5',
                    $alternates
                );
            } catch (\Throwable) {
                continue;
            }
        }

        return response()->view($this->baseView . __FUNCTION__, [
            'entries' => $entries,
        ])->header('Content-Type', 'text/xml');
    }

    public function category(Request $request)
    {
        $defaultLanguage = AdLanguageService::getDefaultLanguage();
        $listLanguage = $this->getAllLanguage();
        $type = $request->route('type');
        $entries = [];

        if ($type == 'article') {
            $list = AppCategoryService::getAll(config('backend.categoryType.article'));
            for ($i = 0; $i < count($list); $i++) {
                $article = AppArticleService::getLatestUpdate([
                    'category_id' => $list[$i]->id
                ]);
                if (!$article) {
                    continue;
                }

                $slug = Utilities::convertToAlias($list[$i]->name);
                $languageId = $list[$i]->language_id;
                $url = $defaultLanguage->id == $languageId
                    ? route('frontend.article.category', ['slug' => $slug])
                    : route(Utilities::bindRouteNameMultiLanguage('frontend.article.category'), ['languageCode' => $listLanguage[$languageId], 'slug' => $slug]);

                $alternates = FeHreflangUtils::getAlternateLinksForRoute('frontend.article.category', [
                    'slug' => $slug,
                ], $languageId);

                $entries[] = $this->makeUrlEntry(
                    $url,
                    Carbon::parse($article->updated_at ?: $article->created_at)->format('Y-m-d\TH:i:sP'),
                    'monthly',
                    '0.6',
                    $alternates
                );
            }
        }

        return response()->view($this->baseView . __FUNCTION__, [
            'entries' => $entries,
        ])->header('Content-Type', 'text/xml');
    }

    public function experience()
    {
        $defaultLanguage = AdLanguageService::getDefaultLanguage();
        $listLanguage = $this->getAllLanguage();
        $entries = [];

        $list = AppExpActivityService::getPaging(['is_disabled_paginate' => true]);
        for ($i = 0; $i < count($list); $i++) {
            $languageId = $list[$i]->language_id ?? $defaultLanguage->id;
            $slug = Utilities::convertToAlias($list[$i]->name);
            try {
                $url = $defaultLanguage->id == $languageId
                    ? route('frontend.experience.show', ['slug' => $slug, 'id' => $list[$i]->id])
                    : route(Utilities::bindRouteNameMultiLanguage('frontend.experience.show'), ['languageCode' => $listLanguage[$languageId], 'slug' => $slug, 'id' => $list[$i]->id]);
            } catch (\Throwable) {
                continue;
            }

            $alternates = FeHreflangUtils::getAlternateLinksForRoute('frontend.experience.show', [
                'slug' => $slug,
                'id' => $list[$i]->id,
            ], $languageId);

            $entries[] = $this->makeUrlEntry(
                $url,
                Carbon::parse($list[$i]->updated_at ?: $list[$i]->created_at)->format('Y-m-d\TH:i:sP'),
                'monthly',
                '0.6',
                $alternates
            );
        }

        return response()->view($this->baseView . __FUNCTION__, [
            'entries' => $entries,
        ])->header('Content-Type', 'text/xml');
    }

    public function service()
    {
        $defaultLanguage = AdLanguageService::getDefaultLanguage();
        $service = AppServiceService::getLatestUpdate([], $defaultLanguage->id);

        if (!$service) {
            $service = (object) [
                'updated_at' => Carbon::now(),
                'created_at' => Carbon::now(),
            ];
        }

        $lastmod = Carbon::parse($service->updated_at ?: $service->created_at)->format('Y-m-d\TH:i:sP');
        $entries = $this->buildHubEntries('frontend.service.index', $lastmod);

        return response()->view($this->baseView . __FUNCTION__, [
            'entries' => $entries,
        ])->header('Content-Type', 'text/xml');
    }

    public function itinerary()
    {
        $entries = [];

        foreach (AdLanguageService::getAll() as $language) {
            $sourceList = AppCruiseItineraryService::resolveItinerariesForListing($language->id);

            foreach ($sourceList as $item) {
                if (empty($item->cruise_id) || empty($item->id)) {
                    continue;
                }

                $slug = Utilities::convertToAlias($item->name);
                $params = [
                    'slug' => $slug,
                    'cruise_id' => $item->cruise_id,
                    'itinerary_id' => $item->id,
                ];

                try {
                    $url = FeUtils::frontendRoute('frontend.itinerary.show', $params, $language->code);
                } catch (\Throwable) {
                    continue;
                }

                $alternates = FeHreflangUtils::getAlternateLinksForRoute(
                    'frontend.itinerary.show',
                    $params,
                    $language->id
                );

                $entries[] = $this->makeUrlEntry(
                    $url,
                    Carbon::parse($item->updated_at ?: $item->created_at)->format('Y-m-d\TH:i:sP'),
                    'monthly',
                    '0.7',
                    $alternates
                );
            }
        }

        return response()->view($this->baseView . __FUNCTION__, [
            'entries' => $entries,
        ])->header('Content-Type', 'text/xml');
    }


    public function cruise()
    {
        $defaultLanguage = AdLanguageService::getDefaultLanguage();
        $listLanguage = $this->getAllLanguage();
        $entries = [];

        $list = AppCruise::orderBy('id', 'asc')->get();
        for ($i = 0; $i < count($list); $i++) {
            $languageId = $list[$i]->language_id ?? $defaultLanguage->id;
            $slug = Utilities::convertToAlias($list[$i]->name);
            try {
                $url = $defaultLanguage->id == $languageId
                    ? route('frontend.cruise.show', ['slug' => $slug, 'id' => $list[$i]->id])
                    : route(Utilities::bindRouteNameMultiLanguage('frontend.cruise.show'), ['languageCode' => $listLanguage[$languageId], 'slug' => $slug, 'id' => $list[$i]->id]);
            } catch (\Throwable) {
                continue;
            }

            $alternates = FeHreflangUtils::getAlternateLinksForRoute('frontend.cruise.show', [
                'slug' => $slug,
                'id' => $list[$i]->id,
            ], $languageId);

            $entries[] = $this->makeUrlEntry(
                $url,
                Carbon::parse($list[$i]->updated_at ?: $list[$i]->created_at)->format('Y-m-d\TH:i:sP'),
                'monthly',
                '0.7',
                $alternates
            );
        }

        return response()->view($this->baseView . __FUNCTION__, [
            'entries' => $entries,
        ])->header('Content-Type', 'text/xml');
    }

    public function gallery()
    {
        $entries = [];
        $defaultLanguage = AdLanguageService::getDefaultLanguage();
        $galleryLastmod = GalleryService::getGalleryUpdatedAt($defaultLanguage->id);

        if ($galleryLastmod) {
            $lastmod = Carbon::parse($galleryLastmod)->format('Y-m-d\TH:i:sP');
            $entries = array_merge($entries, $this->buildHubEntries('frontend.gallery.index', $lastmod));

            foreach (AdLanguageService::getAll() as $language) {
                $languageLastmod = GalleryService::getGalleryUpdatedAt($language->id);
                if (!$languageLastmod) {
                    continue;
                }

                $entryLastmod = Carbon::parse($languageLastmod)->format('Y-m-d\TH:i:sP');
                $filters = GalleryService::getGalleryFilter($language->id);
                foreach (array_keys($filters) as $slug) {
                    $params = ['slug' => $slug];
                    $alternates = FeHreflangUtils::getAlternateLinksForRoute('frontend.gallery.category', $params, $language->id);

                    try {
                        $url = FeUtils::frontendRoute('frontend.gallery.category', $params, $language->code);
                    } catch (\Throwable) {
                        continue;
                    }

                    $entries[] = $this->makeUrlEntry($url, $entryLastmod, 'monthly', '0.6', $alternates);
                }
            }
        }

        return response()->view($this->baseView . __FUNCTION__, [
            'entries' => $entries,
        ])->header('Content-Type', 'text/xml');
    }

    public function faq()
    {
        $entries = [];
        $lastmod = Carbon::now()->format('Y-m-d\TH:i:sP');

        $entries = array_merge($entries, $this->buildHubEntries('frontend.faq.index', $lastmod));

        foreach (AdLanguageService::getAll() as $language) {
            $listGroup = AppGroupService::getAll(config('backend.groupType.faq'), $language->id);
            foreach ($listGroup as $group) {
                if (!$group || !$group->slug || $group->slug === 'root') {
                    continue;
                }

                $params = ['slug' => $group->slug];
                $alternates = FeHreflangUtils::getAlternateLinksForRoute('frontend.faq.category', $params, $language->id);

                try {
                    $url = FeUtils::frontendRoute('frontend.faq.category', $params, $language->code);
                } catch (\Throwable) {
                    continue;
                }

                $entries[] = $this->makeUrlEntry($url, $lastmod, 'monthly', '0.6', $alternates);
            }
        }

        return response()->view($this->baseView . __FUNCTION__, [
            'entries' => $entries,
        ])->header('Content-Type', 'text/xml');
    }

    
    private function makeUrlEntry(
        string $loc,
        string $lastmod,
        string $changefreq,
        string $priority,
        array $alternates = []
    ): array {
        return [
            'loc' => $loc,
            'lastmod' => $lastmod,
            'changefreq' => $changefreq,
            'priority' => $priority,
            'alternates' => $alternates,
        ];
    }

    private function buildHubEntries(
        string $routeName,
        string $lastmod,
        array $params = [],
        string $priority = '0.8',
        string $changefreq = 'weekly'
    ): array {
        $alternates = FeHreflangUtils::getAlternateLinksForRoute($routeName, $params);
        $entries = [];

        foreach ($this->localizedHubUrls($routeName, $params) as $hub) {
            $entries[] = $this->makeUrlEntry($hub['loc'], $lastmod, $changefreq, $priority, $alternates);
        }

        return $entries;
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
