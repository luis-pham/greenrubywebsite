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
use Modules\BackEnd\Entities\AppExpActivity;
use Modules\BackEnd\Entities\AppFaq;
use App\Services\Content\CabinContentService;
use App\Services\Content\ContentTranslations;
use Modules\BackEnd\Entities\AppDestination;

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

        $cabinUpdatedAt = \Modules\BackEnd\Entities\AppCabin::query()->max(\DB::raw('COALESCE(updated_at, created_at)'));
        $destinationUpdatedAt = AppDestination::where('status', 'published')->max(\DB::raw('COALESCE(updated_at, created_at)'));

        return response()->view($this->baseView . __FUNCTION__, [
            'cabinUpdatedAt' => $cabinUpdatedAt,
            'destinationUpdatedAt' => $destinationUpdatedAt,
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
        $itinerary = AppItineraryService::getLatestUpdate([]);
        $cruise = AppCruiseService::getLatestUpdate([]);
        $about = PageService::getAboutUpdatedAt([]);
        $contact = PageService::getContactUpdatedAt([]);
        $entries = [];

        $homepageLastmod = $cruise
            ? Carbon::parse($cruise->updated_at ?: $cruise->created_at)->format('Y-m-d\TH:i:sP')
            : '';

        $entries = array_merge($entries, $this->buildHubEntries('frontend.index', $homepageLastmod, [], '1.0', 'weekly'));

        if ($article) {
            $lastmod = Carbon::parse($article->updated_at ?: $article->created_at)->format('Y-m-d\TH:i:sP');
            $entries = array_merge($entries, $this->buildHubEntries('frontend.article.index', $lastmod));
        }
        if ($experience) {
            $lastmod = Carbon::parse($experience->updated_at ?: $experience->created_at)->format('Y-m-d\TH:i:sP');
            $entries = array_merge($entries, $this->buildHubEntries('frontend.experience.index', $lastmod));
        }
        if ($itinerary) {
            $lastmod = Carbon::parse($itinerary->updated_at ?: $itinerary->created_at)->format('Y-m-d\TH:i:sP');
            $entries = array_merge($entries, $this->buildHubEntries('frontend.itinerary.index', $lastmod));
        }
        if ($about) {
            $lastmod = Carbon::parse($about)->format('Y-m-d\TH:i:sP');
            $entries = array_merge($entries, $this->buildHubEntries('frontend.about.index', $lastmod));
        }
        if ($contact) {
            $lastmod = Carbon::parse($contact)->format('Y-m-d\TH:i:sP');
            $entries = array_merge($entries, $this->buildHubEntries('frontend.contact.index', $lastmod));
        }

        // Services, gallery and FAQ hubs live in their own sitemaps (sitemap-service/-category-gallery/-category-faq).
        $entries = array_merge($entries, $this->nextOnlyEntries(self::ECO_DASHBOARD_PATH, '', 'monthly', '0.6'));
        $entries = array_merge($entries, $this->nextOnlyEntries(self::BRAND_FACTS_PATH, '', 'monthly', '0.6'));
        foreach (self::GUIDE_PATHS as $paths) {
            $entries = array_merge($entries, $this->nextOnlyEntries($paths, '', 'monthly', '0.8'));
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

                $slug = $list[$i]->slug ?: Utilities::convertToAlias($list[$i]->name);
                if (!$slug || $slug === 'root') {
                    continue;
                }

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
        $entries = [];

        foreach (AdLanguageService::getAll() as $language) {
            foreach (AppExpActivity::where('language_id', $language->id)->orderBy('id')->get() as $item) {
                $params = ['slug' => Utilities::convertToAlias($item->name), 'id' => $item->id];
                try {
                    $url = FeUtils::frontendRoute('frontend.experience.show', $params, $language->code);
                } catch (\Throwable) {
                    continue;
                }

                $entries[] = $this->makeUrlEntry(
                    $url,
                    Carbon::parse($item->updated_at ?: $item->created_at)->format('Y-m-d\TH:i:sP'),
                    'monthly',
                    '0.6',
                    FeHreflangUtils::getAlternateLinksForRoute('frontend.experience.show', $params, $language->id)
                );
            }
        }

        return response()->view($this->baseView . __FUNCTION__, [
            'entries' => $entries,
        ])->header('Content-Type', 'text/xml');
    }

    public function service()
    {
        $defaultLanguage = AdLanguageService::getDefaultLanguage();
        $service = AppServiceService::getLatestUpdate([], $defaultLanguage->id);
        $lastmod = $service ? Carbon::parse($service->updated_at ?: $service->created_at)->format('Y-m-d\TH:i:sP') : '';
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
                foreach (array_keys(GalleryService::getGalleryFilter($language->id)) as $slug) {
                    $params = ['slug' => $slug];
                    try {
                        $url = FeUtils::frontendRoute('frontend.gallery.category', $params, $language->code);
                    } catch (\Throwable) {
                        continue;
                    }

                    $alternates = FeHreflangUtils::getAlternateLinksForRoute('frontend.gallery.category', $params, $language->id);
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
        // FAQ rows are rarely edited in place (updated_at is often NULL), so fall back to created_at.
        $latest = AppFaq::query()->max(\DB::raw('COALESCE(updated_at, created_at)'));
        if (!$latest) {
            return response()->view($this->baseView . __FUNCTION__, ['entries' => $entries])->header('Content-Type', 'text/xml');
        }
        $lastmod = Carbon::parse($latest)->format('Y-m-d\TH:i:sP');

        $entries = array_merge($entries, $this->buildHubEntries('frontend.faq.index', $lastmod));

        foreach (AdLanguageService::getAll() as $language) {
            $listGroup = AppGroupService::getAll(config('backend.groupType.faq'), $language->id);
            foreach ($listGroup as $group) {
                if (!$group || !$group->slug || $group->slug === 'root') {
                    continue;
                }

                $params = ['slug' => $group->slug];
                try {
                    $url = FeUtils::frontendRoute('frontend.faq.category', $params, $language->code);
                } catch (\Throwable) {
                    continue;
                }

                $alternates = FeHreflangUtils::getAlternateLinksForRoute('frontend.faq.category', $params, $language->id);
                $entries[] = $this->makeUrlEntry($url, $lastmod, 'monthly', '0.6', $alternates);
            }
        }

        return response()->view($this->baseView . __FUNCTION__, [
            'entries' => $entries,
        ])->header('Content-Type', 'text/xml');
    }

    
    /**
     * Cabin detail pages. These exist only on the Next.js frontend (greenruby-web), so URLs are
     * built from its route convention rather than from Laravel route names.
     */
    public function cabin()
    {
        $defaultLanguage = AdLanguageService::getDefaultLanguage();
        $languages = AdLanguageService::getAll()->keyBy('code');
        $cabinUrl = function (string $languageCode, $cabin) use ($defaultLanguage) {
            $path = $languageCode === $defaultLanguage->code
                ? '/cabin/'
                : '/' . $languageCode . '/' . (self::CABIN_SEGMENT[$languageCode] ?? 'cabin') . '/';

            return url($path . CabinContentService::slug($cabin->name) . '-' . $cabin->id . '.html');
        };

        $entries = [];
        foreach ($languages as $language) {
            foreach (CabinContentService::listAccommodation((int) $language->id) as $cabin) {
                $url = $cabinUrl($language->code, $cabin);
                $alternates = [];
                $counterpart = CabinContentService::alternate($cabin, $language);
                $other = $counterpart && isset($languages[$counterpart['lang']])
                    ? CabinContentService::findAccommodation($counterpart['id'], (int) $languages[$counterpart['lang']]->id)
                    : null;
                if ($other) {
                    $links = [$language->code => $url, $counterpart['lang'] => $cabinUrl($counterpart['lang'], $other)];
                    foreach ($links as $code => $href) {
                        $alternates[] = ['hreflang' => $code, 'url' => $href];
                    }
                    $alternates[] = ['hreflang' => 'x-default', 'url' => $links[$defaultLanguage->code] ?? $url];
                }

                $entries[] = $this->makeUrlEntry(
                    $url,
                    Carbon::parse($cabin->updated_at ?: $cabin->created_at)->format('Y-m-d\TH:i:sP'),
                    'monthly',
                    '0.7',
                    $alternates
                );
            }
        }

        return response()->view($this->baseView . 'entries', ['entries' => $entries])->header('Content-Type', 'text/xml');
    }

    /**
     * Destinations hub and details (Next.js only). Details are listed per language; hreflang only for
     * explicitly linked, published pairs (ContentTranslations type "destination").
     */
    public function destination()
    {
        $defaultLanguage = AdLanguageService::getDefaultLanguage();
        $languages = AdLanguageService::getAll()->keyBy('id');
        $prefix = fn (string $code) => $code === $defaultLanguage->code ? '/' : '/' . $code . '/';
        $hubUrl = fn (string $code) => url($prefix($code) . (self::DESTINATIONS_SEGMENT[$code] ?? 'destinations'));
        $detailUrl = fn (string $code, string $slug) => $hubUrl($code) . '/' . $slug;

        $rows = AppDestination::where('status', 'published')->orderBy('ord')->orderBy('id')->get();
        $latest = $rows->max(fn ($d) => $d->getRawOriginal('updated_at') ?: $d->getRawOriginal('created_at'));
        $entries = $latest
            ? $this->nextOnlyEntries(self::DESTINATIONS_SEGMENT, Carbon::parse($latest)->format('Y-m-d\TH:i:sP'), 'weekly', '0.8')
            : [];

        $byId = $rows->keyBy('id');
        foreach ($rows as $d) {
            $language = $languages->get($d->language_id);
            if (!$language) {
                continue;
            }
            $url = $detailUrl($language->code, $d->slug);
            $alternates = [];
            foreach ($languages as $other) {
                if ($other->id === $language->id) {
                    continue;
                }
                $counterpart = $byId->get(ContentTranslations::linkedId('destination', (int) $d->id, (int) $other->id));
                if ($counterpart) {
                    $links = [$language->code => $url, $other->code => $detailUrl($other->code, $counterpart->slug)];
                    foreach ($links as $code => $href) {
                        $alternates[] = ['hreflang' => $code, 'url' => $href];
                    }
                    $alternates[] = ['hreflang' => 'x-default', 'url' => $links[$defaultLanguage->code] ?? $url];
                }
            }
            $entries[] = $this->makeUrlEntry(
                $url,
                Carbon::parse($d->getRawOriginal('updated_at') ?: $d->getRawOriginal('created_at'))->format('Y-m-d\TH:i:sP'),
                'monthly',
                '0.7',
                $alternates
            );
        }

        return response()->view($this->baseView . 'entries', ['entries' => $entries])->header('Content-Type', 'text/xml');
    }

    /** Must match `segments.destinations` in greenruby-web/src/lib/i18n.ts. */
    private const DESTINATIONS_SEGMENT = ['en' => 'destinations', 'vi' => 'diem-den'];

    /** Must match `segments.guideFirstTime` / `segments.guideTransfer` in greenruby-web/src/lib/i18n.ts. */
    private const GUIDE_PATHS = [
        ['en' => 'guides/first-time-halong-bay-cruise', 'vi' => 'cam-nang/lan-dau-di-du-thuyen-ha-long'],
        ['en' => 'guides/hanoi-to-halong-bay', 'vi' => 'cam-nang/ha-noi-di-ha-long'],
    ];

    /** Must match `segments.cabin` in greenruby-web/src/lib/i18n.ts. */
    private const CABIN_SEGMENT = ['vi' => 'phong-nghi'];

    /** Must match `segments.ecoDashboard` in greenruby-web/src/lib/i18n.ts. */
    private const ECO_DASHBOARD_PATH = ['en' => 'eco-dashboard', 'vi' => 'bao-cao-sinh-thai'];

    /** Must match `segments.brandFacts` in greenruby-web/src/lib/i18n.ts. */
    private const BRAND_FACTS_PATH = ['en' => 'about-us/brand-facts', 'vi' => 'gioi-thieu/thong-tin-thuong-hieu'];

    /**
     * One entry per language for a page that exists only on the Next.js frontend, keyed by language code
     * => path segment (default language unprefixed, others under /{code}/), all sharing one hreflang set.
     */
    private function nextOnlyEntries(array $pathByLanguage, string $lastmod, string $changefreq, string $priority): array
    {
        $defaultLanguage = AdLanguageService::getDefaultLanguage();
        $urls = [];
        foreach (AdLanguageService::getAll() as $language) {
            if (isset($pathByLanguage[$language->code])) {
                $prefix = $language->code === $defaultLanguage->code ? '/' : '/' . $language->code . '/';
                $urls[$language->code] = url($prefix . $pathByLanguage[$language->code]);
            }
        }

        $alternates = [];
        if (count($urls) > 1) {
            foreach ($urls as $code => $href) {
                $alternates[] = ['hreflang' => $code, 'url' => $href];
            }
            $alternates[] = ['hreflang' => 'x-default', 'url' => $urls[$defaultLanguage->code] ?? reset($urls)];
        }

        return array_map(fn ($href) => $this->makeUrlEntry($href, $lastmod, $changefreq, $priority, $alternates), array_values($urls));
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
