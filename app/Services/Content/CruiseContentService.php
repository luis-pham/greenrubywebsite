<?php

namespace App\Services\Content;

use Illuminate\Support\Facades\DB;
use Modules\BackEnd\Entities\AdLanguage;
use Modules\BackEnd\Entities\AppCabin;
use Modules\BackEnd\Entities\AppCruise;
use Modules\BackEnd\Entities\AppExpActivity;
use Modules\BackEnd\Entities\AppItinerary;
use Modules\FrontEnd\Helpers\FeCruiseUtils;
use Modules\FrontEnd\Helpers\FeHreflangUtils;
use Modules\FrontEnd\Helpers\FeUtils;
use Modules\FrontEnd\Services\AppCabinService as FeAppCabinService;
use Modules\FrontEnd\Services\AppCruiseService;
use Modules\FrontEnd\Services\AppItineraryService;

/**
 * Cruise (ship) payloads for the Content API. Mirrors the Blade CruiseController::show data sources.
 */
class CruiseContentService
{
    public static function index(AdLanguage $language): array
    {
        return [
            'items' => AppCruiseService::getAll((int) $language->id)
                ->sortBy('id')
                ->map(fn (AppCruise $cruise) => self::summary($cruise))
                ->values(),
        ];
    }

    public static function summary(AppCruise $cruise): array
    {
        return [
            'id' => (int) $cruise->id,
            'slug' => CabinContentService::slug($cruise->name),
            'name' => $cruise->name,
            'summary' => ItineraryContentService::plain($cruise->summary),
            'image' => CabinContentService::fileLink($cruise->image_link),
            'cover' => CabinContentService::fileLink($cruise->cover_link),
            'bay' => self::bay($cruise),
            'capacity' => CabinContentService::number($cruise->capacity),
            'length' => CabinContentService::number($cruise->dimension_length),
        ];
    }

    public static function detail(int $id, AdLanguage $language): ?array
    {
        $languageId = (int) $language->id;
        $cruise = AppCruiseService::findByIdJoin($id, $languageId);
        if (!$cruise) {
            return null;
        }

        // Same query as CruiseController::show: activities of this cruise first, then global (cruise_id NULL) ones.
        $experiences = AppExpActivity::where('language_id', $cruise->language_id ?? $languageId)
            ->where(fn ($q) => $q->where('cruise_id', $cruise->id)->orWhereNull('cruise_id'))
            ->orderBy('cruise_id', 'desc')
            ->orderBy('id', 'asc')
            ->get();

        $facilities = AppCabin::query()
            ->select('app_cabin.*', 'app_group.name as group_name')
            ->join('app_group', 'app_group.id', '=', 'app_cabin.group_id')
            ->whereNotIn('app_group.slug', config('backend.listAccommodationSlug'))
            ->where('app_cabin.language_id', $languageId)
            ->where('app_cabin.cruise_id', $cruise->id)
            ->orderBy('app_cabin.ord')
            ->orderBy('app_cabin.id')
            ->get()
            ->map(fn ($c) => [
                'id' => (int) $c->id,
                'name' => $c->name,
                'summary' => ItineraryContentService::plain($c->summary),
                'image' => CabinContentService::fileLink($c->image_link),
                'group' => $c->group_name,
            ])->values();

        // Same source as the Blade "itineraries" block: one itinerary per duration on this ship's bay.
        $minPrices = FeAppCabinService::getMinPriceByCruiseId((int) $cruise->id);
        $itineraries = AppItineraryService::getEarliestItinerariesWithMinPriceAndOfBay((int) $cruise->id)
            ->sortKeys()
            ->pluck('id')
            ->all();
        $itineraryModels = AppItinerary::whereIn('id', $itineraries)
            ->where('language_id', $languageId)
            ->get()
            ->keyBy('id');
        $nextDepartures = DB::table('app_cruise_itinerary')
            ->select('itinerary_id', DB::raw('MIN(start_at) AS next_at'))
            ->where('cruise_id', $cruise->id)
            ->whereIn('itinerary_id', $itineraries ?: [0])
            ->where('start_at', '>=', DB::raw('CURRENT_DATE()'))
            ->groupBy('itinerary_id')
            ->pluck('next_at', 'itinerary_id');

        $itinerarySummaries = collect($itineraries)
            ->map(fn ($itineraryId) => $itineraryModels->get($itineraryId))
            ->filter()
            ->map(fn ($it) => ItineraryContentService::summary(
                $it,
                (int) $cruise->id,
                $cruise->name,
                $minPrices->where('duration', $it->duration)->first()?->min_price,
                $language,
                isset($nextDepartures[$it->id]) ? substr((string) $nextDepartures[$it->id], 0, 10) : null
            ))
            ->values();

        $seoTitle = ItineraryContentService::clean($cruise->seo_title ?? null);
        $seoDescription = ItineraryContentService::clean($cruise->seo_description ?? null);

        return [
            'id' => (int) $cruise->id,
            'slug' => CabinContentService::slug($cruise->name),
            'name' => $cruise->name,
            'summary' => ItineraryContentService::plain($cruise->summary),
            'contentHtml' => safe_html($cruise->content) ?? '',
            'image' => CabinContentService::fileLink($cruise->image_link),
            'cover' => CabinContentService::fileLink($cruise->cover_link),
            'bay' => self::bay($cruise),
            'starRating' => CabinContentService::number($cruise->star_rating),
            'capacity' => CabinContentService::number($cruise->capacity),
            'totalFloor' => CabinContentService::number($cruise->total_floor),
            'totalCabin' => CabinContentService::number($cruise->total_cabin),
            'length' => CabinContentService::number($cruise->dimension_length),
            'yearBuilt' => CabinContentService::number($cruise->year_built),
            'descriptionDesignHtml' => safe_html($cruise->description_design) ?? '',
            'greenTechnology' => self::greenTechnology($cruise->green_technology),
            'amenities' => $cruise->cruiseAmenities->map(fn ($a) => [
                'name' => $a->name,
                'icon' => CabinContentService::fileLink($a->icon),
            ])->values(),
            'services' => ItineraryContentService::services($cruise->cruiseServices),
            'cabins' => CabinContentService::listAccommodation($languageId, (int) $cruise->id)
                ->map(fn ($cabin) => CabinContentService::summary($cabin))
                ->values(),
            'facilities' => $facilities,
            'experiences' => ItineraryContentService::activities($experiences),
            'itineraries' => $itinerarySummaries,
            'gallery' => ItineraryContentService::gallery($cruise->galleryImages, $cruise->name),
            // CruiseController: title = seo_title ?: name (Green Ruby name normalised), description = seo_description ?: lead.
            // app_cruise has no seo_* / lead columns today, so this falls back to name / summary.
            'seo' => [
                'title' => FeUtils::formatGreenRubyMenuName($seoTitle ?? $cruise->name),
                'description' => $seoDescription ?? ItineraryContentService::plain($cruise->lead ?? $cruise->summary),
            ],
            'alternate' => self::alternate($cruise, $language),
            'updatedAt' => CabinContentService::isoDate($cruise->getRawOriginal('updated_at') ?: $cruise->getRawOriginal('created_at')),
        ];
    }

    /** Explicit ContentTranslations link, else FeHreflangUtils::findCruiseCounterpart when the ship's bay is unique in both languages. */
    public static function alternate(AppCruise $cruise, AdLanguage $language): ?array
    {
        $target = ItineraryContentService::otherLanguage($language);
        if (!$target) {
            return null;
        }

        [$linked, $linkedId] = ContentTranslations::lookup('cruise', (int) $cruise->id, (int) $target->id);
        if ($linked) {
            $match = $linkedId ? AppCruise::where('id', $linkedId)->first() : null;

            return $match ? ['lang' => $target->code, 'id' => (int) $match->id, 'slug' => CabinContentService::slug($match->name)] : null;
        }

        $bay = FeCruiseUtils::getBayForCruise($cruise->name);
        $countBay = fn (int $languageId) => AppCruise::where('language_id', $languageId)->get(['id', 'name'])
            ->filter(fn ($c) => FeCruiseUtils::getBayForCruise($c->name) === $bay)
            ->count();
        if ($countBay((int) $language->id) !== 1 || $countBay((int) $target->id) !== 1) {
            return null;
        }

        try {
            $counterpart = FeHreflangUtils::findCruiseCounterpart((int) $cruise->id, (int) $language->id, $target);
        } catch (\Throwable) {
            return null;
        }
        if (!$counterpart instanceof AppCruise || FeCruiseUtils::getBayForCruise($counterpart->name) !== $bay) {
            return null;
        }

        return [
            'lang' => $target->code,
            'id' => (int) $counterpart->id,
            'slug' => CabinContentService::slug($counterpart->name),
        ];
    }

    /** Ship bay from its name (FeCruiseUtils: "2"/"II" => Lan Ha), as a language-neutral code. */
    public static function bay(AppCruise $cruise): ?string
    {
        return HomepageContentService::bayCode(FeCruiseUtils::getBayForCruise($cruise->name));
    }

    /**
     * green_technology is stored as a single {name, description, image_link} object today; a list of such
     * objects (or title/icon aliases) is accepted too. Entries with neither title nor description are dropped.
     */
    protected static function greenTechnology($value): array
    {
        if (is_string($value)) {
            $value = json_decode($value);
        }
        if ($value === null) {
            return [];
        }
        $value = json_decode(json_encode($value), true);
        if (!is_array($value)) {
            return [];
        }
        $items = array_is_list($value) ? $value : [$value];

        return collect($items)
            ->filter(fn ($i) => is_array($i))
            ->map(fn ($i) => [
                'title' => ItineraryContentService::clean($i['title'] ?? $i['name'] ?? null),
                'description' => ItineraryContentService::plain($i['description'] ?? null),
                'icon' => CabinContentService::fileLink($i['icon'] ?? $i['image_link'] ?? null),
            ])
            ->filter(fn ($i) => $i['title'] !== null || $i['description'] !== null)
            ->values()
            ->all();
    }
}
