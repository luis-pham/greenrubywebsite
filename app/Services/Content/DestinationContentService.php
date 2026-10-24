<?php

namespace App\Services\Content;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Modules\BackEnd\Entities\AdLanguage;
use Modules\BackEnd\Entities\AppDestination;

/**
 * Destination (app_destination) payloads for the Content API. Only published rows are exposed;
 * editor_notes and the editorial status never leave the CMS. Coordinates are sent only when an editor
 * marked them as verified.
 */
class DestinationContentService
{
    public const PAGE_CODE = 'destinations';

    public static function index(AdLanguage $language): array
    {
        $rows = self::published($language)->get();
        $stops = self::stops($rows->pluck('id')->all(), $language);

        return [
            'seo' => PageConfigContentService::seo(self::PAGE_CODE, $language),
            // Hub switched off in the admin: the frontend 404s the hub; items are empty, so details 404 too.
            'visible' => PageConfigContentService::visible(self::PAGE_CODE, $language),
            'items' => $rows->map(fn ($d) => self::summary($d, $stops))->values()->all(),
        ];
    }

    public static function detail(string $slug, AdLanguage $language): ?array
    {
        $d = self::published($language)->where('slug', $slug)->first();
        if (!$d) {
            return null;
        }

        $all = self::published($language)->get();
        $stops = self::stops($all->pluck('id')->all(), $language);
        $summary = self::summary($d, $stops);

        // Same bay first, then the same kind of place elsewhere: what a visitor would compare it with.
        $related = $all->where('id', '!=', $d->id)
            ->sortBy(fn ($x) => [$x->bay === $d->bay ? 0 : 1, $x->category === $d->category ? 0 : 1, $x->ord])
            ->take(3)
            ->map(fn ($x) => self::summary($x, $stops))
            ->values()
            ->all();

        return array_merge($summary, [
            'heading' => ItineraryContentService::clean($d->heading) ?? $d->name,
            'contentHtml' => PageConfigContentService::html($d->content),
            'facts' => array_values($d->facts ?? []),
            'faq' => array_values(array_map(fn ($f) => ['question' => $f['q'], 'answer' => $f['a']], $d->faq ?? [])),
            'sources' => array_values($d->sources ?? []),
            'itineraries' => $stops->get($d->id, []),
            'related' => $related,
            'seo' => [
                'title' => ItineraryContentService::clean($d->seo_title) ?? $d->name,
                'description' => ItineraryContentService::clean($d->seo_description) ?? $summary['summary'],
            ],
            'alternate' => self::alternate($d, $language),
            'factCheckedAt' => $d->fact_checked_at ? substr((string) $d->fact_checked_at, 0, 10) : null,
            'updatedAt' => CabinContentService::isoDate($d->getRawOriginal('updated_at') ?: $d->getRawOriginal('created_at')),
        ]);
    }

    public static function summary(AppDestination $d, Collection $stops): array
    {
        $verified = $d->coords_verified && $d->latitude !== null && $d->longitude !== null;

        return [
            'id' => (int) $d->id,
            'slug' => $d->slug,
            'name' => $d->name,
            'bay' => $d->bay,
            'category' => $d->category,
            'summary' => ItineraryContentService::plain($d->summary),
            'image' => $d->image_link
                ? ['src' => CabinContentService::fileLink($d->image_link), 'alt' => $d->image_alt ?: $d->name, 'credit' => ItineraryContentService::clean($d->image_credit)]
                : null,
            'coordinates' => $verified ? ['lat' => (float) $d->latitude, 'lng' => (float) $d->longitude] : null,
            'isGreenRubyStop' => !empty($stops->get($d->id)),
            'itineraryIds' => array_column($stops->get($d->id, []), 'itineraryId'),
        ];
    }

    /** Published destinations of the language, in admin order; none while the hub is switched off. */
    public static function published(AdLanguage $language)
    {
        $query = AppDestination::where('language_id', $language->id)->where('status', 'published')->orderBy('ord')->orderBy('id');

        return PageConfigContentService::visible(self::PAGE_CODE, $language) ? $query : $query->whereRaw('1 = 0');
    }

    /** Explicit translation link only (destinations have no heuristic counterpart). */
    public static function alternate(AppDestination $d, AdLanguage $language): ?array
    {
        $target = ItineraryContentService::otherLanguage($language);
        if (!$target || !PageConfigContentService::visible(self::PAGE_CODE, $target)) {
            return null;
        }
        $id = ContentTranslations::linkedId('destination', (int) $d->id, (int) $target->id);
        $match = $id ? AppDestination::where('id', $id)->where('status', 'published')->first() : null;

        return $match ? ['lang' => $target->code, 'slug' => $match->slug] : null;
    }

    /**
     * destination id => itinerary summaries (as on /itineraries) of the itineraries that stop there.
     * Itineraries that are not listed on the site (no cruise) are left out.
     */
    protected static function stops(array $destinationIds, AdLanguage $language): Collection
    {
        if ($destinationIds === []) {
            return collect();
        }
        $links = DB::table('app_destination_itinerary')->whereIn('destination_id', $destinationIds)->get();
        if ($links->isEmpty()) {
            return collect();
        }
        $listing = collect(ItineraryContentService::listing($language))->keyBy('itineraryId');

        return $links->groupBy('destination_id')->map(
            fn ($rows) => $rows->map(fn ($row) => $listing->get((int) $row->itinerary_id))->filter()->map(fn ($it) => [
                'cruiseId' => $it['cruiseId'],
                'itineraryId' => $it['itineraryId'],
                'slug' => $it['slug'],
                'name' => $it['name'],
                'cruiseName' => $it['cruiseName'],
                'duration' => $it['duration'],
                'bay' => $it['bay'],
            ])->sortBy('duration')->values()->all()
        )->filter();
    }
}
