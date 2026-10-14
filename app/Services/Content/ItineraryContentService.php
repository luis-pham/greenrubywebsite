<?php

namespace App\Services\Content;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\BackEnd\Entities\AdLanguage;
use Modules\BackEnd\Entities\AppCruise;
use Modules\BackEnd\Entities\AppItinerary;
use Modules\BackEnd\Helpers\Utilities;
use Modules\FrontEnd\Constants\PageCodeConsts;
use Modules\FrontEnd\Helpers\FeUtils;
use Modules\FrontEnd\Services\AppCabinService as FeAppCabinService;
use Modules\FrontEnd\Services\AppCruiseItineraryService;
use Modules\FrontEnd\Services\AppFaqService;

/**
 * Itinerary payloads for the Content API. Listing / canonical-cruise logic is delegated to the Blade
 * frontend's AppCruiseItineraryService so both sites show the same (itinerary, cruise) pairs.
 */
class ItineraryContentService
{
    public const MAX_DEPARTURES = 60;

    public static function index(AdLanguage $language): array
    {
        $config = Utilities::getAllConfig($language);
        $seo = FeUtils::resolveHubSeo(
            PageCodeConsts::ITINERARY,
            $language,
            fn () => FeUtils::bindWebsiteTitle($config['website-name'] ?? '', $config['website-slogan'] ?? ''),
            (string) ($config['website-description'] ?? '')
        );

        return [
            'seo' => [
                'title' => self::clean($seo['title']),
                'description' => self::clean($seo['description']),
            ],
            'items' => self::listing($language),
            'pageConfig' => self::pageConfig($language),
        ];
    }

    /**
     * Same set & order as the Blade /itinerary listing (resolveItinerariesForListing), minus rows that
     * ended up without a cruise (the Blade service's filter() result is discarded, so it never drops them).
     */
    public static function listing(AdLanguage $language, ?int $onlyCruiseId = null): array
    {
        $rows = AppCruiseItineraryService::resolveItinerariesForListing((int) $language->id)
            ->filter(fn ($row) => !empty($row->cruise_id) && !empty($row->cruise_name))
            ->values();
        if ($onlyCruiseId !== null) {
            $rows = $rows->filter(fn ($row) => (int) $row->cruise_id === $onlyCruiseId)->values();
        }
        if ($rows->isEmpty()) {
            return [];
        }

        $itineraries = AppItinerary::whereIn('id', $rows->pluck('id')->all())
            ->where('language_id', $language->id)
            ->get()
            ->keyBy('id');
        $nextDepartures = self::nextDepartures($rows->pluck('id')->all());

        return $rows
            ->map(function ($row) use ($itineraries, $nextDepartures, $language) {
                $itinerary = $itineraries->get($row->id);
                if (!$itinerary) {
                    return null;
                }

                return self::summary(
                    $itinerary,
                    (int) $row->cruise_id,
                    $row->cruise_name,
                    $row->price ?? null,
                    $language,
                    $nextDepartures[$row->cruise_id . ':' . $row->id] ?? null
                );
            })
            ->filter()
            ->values()
            ->all();
    }

    public static function summary(
        AppItinerary $itinerary,
        int $cruiseId,
        ?string $cruiseName,
        $price,
        AdLanguage $language,
        ?string $nextDeparture
    ): array {
        $showPublic = (bool) config('frontend.showPublicPrices', false);

        return [
            'itineraryId' => (int) $itinerary->id,
            'cruiseId' => $cruiseId,
            'slug' => CabinContentService::slug($itinerary->name),
            'name' => $itinerary->name,
            'description' => self::plain($itinerary->description),
            'cruiseName' => $cruiseName,
            'cruiseSlug' => $cruiseName !== null ? CabinContentService::slug($cruiseName) : null,
            'duration' => (int) $itinerary->duration,
            'bay' => HomepageContentService::bayCode($itinerary->bay),
            'image' => CabinContentService::fileLink($itinerary->image_link ?: $itinerary->cover_link),
            'destinations' => self::destinations($itinerary->destination),
            'startTime' => self::time($itinerary->start_time),
            'fromPrice' => $showPublic && is_numeric($price) && (float) $price > 0 ? CabinContentService::number($price) : null,
            'currency' => self::currency($language),
            'nextDeparture' => $nextDeparture,
        ];
    }

    /** Null when the itinerary does not exist in this language (or no cruise exists to host it). */
    public static function detail(int $itineraryId, AdLanguage $language): ?array
    {
        $languageId = (int) $language->id;

        $canonical = AppCruiseItineraryService::resolveCanonicalShowParams($itineraryId, $languageId);
        if (!$canonical) {
            return null;
        }
        $cruiseId = (int) $canonical['cruise_id'];

        // Same loader as the Blade show page: cruise (+cabins), itinerary (+services/days/gallery), future departures.
        $records = AppCruiseItineraryService::findByIdsJoin($cruiseId, $itineraryId, $languageId);
        $first = $records->first();
        if (!$first || !$first->cruise || !$first->itinerary) {
            return null;
        }
        /** @var AppCruise $cruise */
        $cruise = $first->cruise;
        /** @var AppItinerary $itinerary */
        $itinerary = $first->itinerary;
        $itinerary->loadMissing('itineraryActivities');

        $departures = $records
            ->pluck('start_at')
            ->filter()
            ->map(fn ($d) => Carbon::parse($d)->format('Y-m-d'))
            ->unique()
            ->sort()
            ->take(self::MAX_DEPARTURES)
            ->values();

        $price = FeAppCabinService::getMinPriceByCruiseId($cruiseId)
            ->where('duration', $itinerary->duration)
            ->first()?->min_price;

        $summary = self::summary($itinerary, $cruiseId, $cruise->name, $price, $language, $departures->first());

        $seoTitle = self::clean($itinerary->seo_title);
        $seoDescription = self::clean($itinerary->seo_description);

        return array_merge($summary, [
            'canonical' => [
                'cruiseId' => $cruiseId,
                'itineraryId' => (int) $canonical['itinerary_id'],
                'slug' => $canonical['slug'],
            ],
            'cover' => CabinContentService::fileLink($itinerary->cover_link ?: $itinerary->image_link),
            'descriptionHtml' => safe_html($itinerary->description) ?? '',
            'seo' => [
                'title' => $seoTitle ?? self::clean($itinerary->name),
                'description' => $seoDescription ?? $summary['description'],
            ],
            'cruise' => self::cruiseRef($cruise),
            'days' => $itinerary->itineraryDays
                ->sortBy([['day', 'asc'], ['id', 'asc']])
                ->map(fn ($day) => [
                    'day' => (int) $day->day,
                    'title' => self::clean($day->title ?? null),
                    'details' => $day->itineraryDayDetails
                        ->sortBy([['ord', 'asc'], ['id', 'asc']])
                        ->map(fn ($d) => [
                            'time' => self::time($d->time),
                            'title' => self::clean($d->title),
                            'descriptionHtml' => safe_html(self::clean($d->description)),
                        ])->values(),
                ])->values(),
            'importantNotes' => self::importantNotes($itinerary->important_note),
            'services' => self::services($itinerary->itineraryServices),
            'activities' => self::activities($itinerary->itineraryActivities),
            'gallery' => self::gallery($itinerary->galleryImages, $itinerary->name),
            'departures' => $departures,
            'cabins' => CabinContentService::listAccommodation($languageId, $cruiseId)
                ->map(fn ($cabin) => CabinContentService::summary($cabin))
                ->values(),
            // Only an "itinerary" page-config FAQ list (none exists today); the hub's generic FAQ fallback is not reused here.
            'faqs' => self::pageConfig($language, false)['faqs'],
            'alternate' => self::alternate($itinerary, $language),
            'updatedAt' => CabinContentService::isoDate($itinerary->getRawOriginal('updated_at') ?: $itinerary->getRawOriginal('created_at')),
        ]);
    }

    /**
     * Counterpart = the only itinerary in the other language with the same duration + bay (the pairing
     * FeHreflangUtils uses), and only when the source pair is itself unique; then its canonical cruise.
     */
    public static function alternate(AppItinerary $itinerary, AdLanguage $language): ?array
    {
        $target = self::otherLanguage($language);
        if (!$target) {
            return null;
        }

        $sameInSource = AppItinerary::where('language_id', $language->id)
            ->where('duration', $itinerary->duration)
            ->where('bay', $itinerary->bay)
            ->count();
        if ($sameInSource !== 1) {
            return null;
        }

        $matches = AppItinerary::where('language_id', $target->id)
            ->where('duration', $itinerary->duration)
            ->where('bay', $itinerary->bay)
            ->limit(2)
            ->get();
        if ($matches->count() !== 1) {
            return null;
        }

        $canonical = AppCruiseItineraryService::resolveCanonicalShowParams((int) $matches->first()->id, (int) $target->id);
        if (!$canonical) {
            return null;
        }

        return [
            'lang' => $target->code,
            'cruiseId' => (int) $canonical['cruise_id'],
            'itineraryId' => (int) $canonical['itinerary_id'],
            'slug' => $canonical['slug'],
        ];
    }

    /**
     * There is no app_page_config for page code "itinerary" today; any keys added later are passed
     * through camelCased. FAQs fall back to what the Blade /itinerary page renders (AppFaqService::getPaging).
     */
    public static function pageConfig(AdLanguage $language, bool $faqFallback = true): array
    {
        $config = FeUtils::getPageConfigByCode(PageCodeConsts::ITINERARY, (int) $language->id);

        $out = [];
        $faqSource = null;
        foreach ($config as $key => $value) {
            if (str_contains($key, 'faq')) {
                $faqSource = $value;
                continue;
            }
            $name = Str::camel(preg_replace('/^itinerary-/', '', $key));
            $out[$name] = is_string($value) ? self::clean($value) : $value;
        }

        if ($faqSource === null && $faqFallback) {
            $faqSource = AppFaqService::getPaging([], (int) $language->id);
        }
        $out['faqs'] = collect(is_iterable($faqSource) ? $faqSource : [])
            ->map(fn ($f) => [
                'question' => self::plain(self::field($f, 'question')),
                'answerHtml' => safe_html((string) self::field($f, 'answer')),
            ])
            ->filter(fn ($f) => $f['question'])
            ->values()
            ->all();

        return $out;
    }

    // ---- shared presenters (also used by CruiseContentService) ----

    public static function cruiseRef(AppCruise $cruise): array
    {
        return [
            'id' => (int) $cruise->id,
            'name' => $cruise->name,
            'slug' => CabinContentService::slug($cruise->name),
            'image' => CabinContentService::fileLink($cruise->image_link),
            'capacity' => CabinContentService::number($cruise->capacity),
            'length' => CabinContentService::number($cruise->dimension_length),
        ];
    }

    /** app_service rows: type 1 = included, type 2 = optional (exclusive). Admin order preserved. */
    public static function services(Collection $services): array
    {
        $showPublic = (bool) config('frontend.showPublicPrices', false);
        $base = fn ($s) => [
            'id' => (int) $s->id,
            'name' => $s->name,
            'description' => self::plain($s->description),
            'image' => CabinContentService::fileLink($s->image_link),
        ];

        return [
            'included' => $services
                ->filter(fn ($s) => (int) $s->type === (int) config('backend.appServiceType.inclusive'))
                ->map($base)->values(),
            'optional' => $services
                ->filter(fn ($s) => (int) $s->type === (int) config('backend.appServiceType.exclusive'))
                ->map(fn ($s) => $base($s) + [
                    'price' => $showPublic && (float) $s->price > 0 ? CabinContentService::number($s->price) : null,
                ])->values(),
        ];
    }

    public static function activities(Collection $activities): array
    {
        return $activities->map(fn ($e) => [
            'id' => (int) $e->id,
            'slug' => CabinContentService::slug($e->name),
            'name' => $e->name,
            'summary' => self::plain($e->summary),
            'image' => CabinContentService::fileLink($e->image_link ?: $e->cover_link),
            'duration' => self::clean($e->duration),
        ])->values()->all();
    }

    public static function gallery(Collection $files, ?string $fallbackAlt): array
    {
        return $files->map(fn ($file) => [
            'src' => CabinContentService::fileLink($file->link),
            'alt' => self::clean($file->description) ?? self::clean($fallbackAlt),
        ])->filter(fn ($g) => $g['src'] !== null)->values()->all();
    }

    public static function currency(AdLanguage $language): string
    {
        return strtolower((string) $language->code) === 'vi' ? 'VND' : 'USD';
    }

    public static function otherLanguage(AdLanguage $language): ?AdLanguage
    {
        return AdLanguage::where('id', '!=', $language->id)
            ->whereIn('code', ['en', 'vi'])
            ->orderBy('ord')
            ->first();
    }

    public static function plain($html): ?string
    {
        $text = html_entity_decode(strip_tags((string) $html), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return self::clean(preg_replace('/\s+/u', ' ', $text));
    }

    public static function clean($value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    /** Earliest future departure per "cruiseId:itineraryId". */
    protected static function nextDepartures(array $itineraryIds): array
    {
        return DB::table('app_cruise_itinerary')
            ->select('cruise_id', 'itinerary_id', DB::raw('MIN(start_at) AS next_at'))
            ->whereIn('itinerary_id', $itineraryIds)
            ->where('start_at', '>=', DB::raw('CURRENT_DATE()'))
            ->groupBy('cruise_id', 'itinerary_id')
            ->get()
            ->mapWithKeys(fn ($r) => [$r->cruise_id . ':' . $r->itinerary_id => Carbon::parse($r->next_at)->format('Y-m-d')])
            ->all();
    }

    protected static function destinations($json): array
    {
        $list = json_decode((string) $json, true);

        return is_array($list)
            ? array_values(array_filter(array_map(fn ($d) => is_string($d) ? trim($d) : null, $list)))
            : [];
    }

    /** important_note is a collection of {content (html), image_link}; exposed as plain strings. */
    protected static function importantNotes($notes): array
    {
        return collect($notes ?? [])
            ->map(fn ($n) => self::plain(is_array($n) || is_object($n) ? self::field($n, 'content') : $n))
            ->filter()
            ->values()
            ->all();
    }

    protected static function time($value): ?string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }
        try {
            return Carbon::parse($value)->format('H:i');
        } catch (\Throwable) {
            return null;
        }
    }

    protected static function field($item, string $key)
    {
        if (is_array($item)) {
            return $item[$key] ?? null;
        }

        return is_object($item) ? ($item->{$key} ?? null) : null;
    }
}
