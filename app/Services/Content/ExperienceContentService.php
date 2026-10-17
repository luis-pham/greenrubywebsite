<?php

namespace App\Services\Content;

use Illuminate\Support\Collection;
use Modules\BackEnd\Entities\AdLanguage;
use Modules\BackEnd\Entities\AppCruise;
use Modules\BackEnd\Entities\AppExpActivity;
use Modules\BackEnd\Services\AppExpActivityService;
use Modules\BackEnd\Services\AppGroupService;
use Modules\FrontEnd\Constants\PageCodeConsts;

/**
 * Experience (app_exp_activity) payloads for the Content API. Mirrors the Blade ExperienceController:
 * same lists (getAll / getExpActivityFeatured), same group tabs, same related rule (same group, max 5).
 */
class ExperienceContentService
{
    /** config('backend.groupTabType.expActivity') value => language-neutral tab key. */
    public const TABS = [1 => 'onboard', 2 => 'outdoor'];

    public static function index(AdLanguage $language): array
    {
        $languageId = (int) $language->id;
        $groups = self::groups($language);

        $config = PageConfigContentService::decode(PageCodeConsts::EXPERIENCE, $language);

        return [
            'seo' => PageConfigContentService::seo(PageCodeConsts::EXPERIENCE, $language),
            'page' => (object) $config,
            'tabs' => self::tabs($groups, $language),
            'groups' => $groups->map(fn ($g) => self::groupRef($g))->values(),
            'featured' => self::presentMany(AppExpActivityService::getExpActivityFeatured($languageId), $language, $groups),
            'items' => self::presentMany(AppExpActivityService::getAll($languageId), $language, $groups),
        ];
    }

    public static function detail(int $id, AdLanguage $language): ?array
    {
        $languageId = (int) $language->id;
        $obj = AppExpActivityService::findJoin($id, $languageId);
        if (!$obj) {
            return null;
        }

        $groups = self::groups($language);
        $summary = self::summary($obj, $groups, self::cruises(collect([$obj])));

        $all = AppExpActivityService::getAll($languageId);
        $related = $all->where('id', '!=', $obj->id)->where('group_id', '=', $obj->group_id)->take(5);

        $gallery = collect(AppExpActivityService::getActivityGallery($obj->id))
            ->map(fn ($img) => PageConfigContentService::galleryItem([
                'link' => $img->link,
                'thumbnail' => $img->thumbnail,
                'title' => $img->title,
                'description' => $img->description,
            ]))
            ->filter()
            ->map(fn ($g) => ['src' => $g['src'], 'thumbnail' => $g['thumbnail'], 'alt' => $g['alt'] ?? $obj->name, 'type' => $g['type']])
            ->values();

        $suitableFor = collect(AppExpActivityService::getSuitableAudiences($obj->id, $languageId))
            ->map(fn ($name, $groupId) => ['id' => (int) $groupId, 'name' => $name])
            ->values();

        return array_merge($summary, [
            'contentHtml' => PageConfigContentService::html($obj->content),
            'seo' => [
                'title' => ItineraryContentService::clean($obj->seo_title) ?? ItineraryContentService::clean($obj->name),
                'description' => ItineraryContentService::clean($obj->seo_description) ?? $summary['summary'],
            ],
            'gallery' => $gallery,
            'suitableFor' => $suitableFor,
            'related' => self::presentMany($related, $language, $groups),
            'canonicalSlug' => $summary['slug'],
            'alternate' => self::alternate($obj, $language),
            'updatedAt' => CabinContentService::isoDate($obj->getRawOriginal('updated_at') ?: $obj->getRawOriginal('created_at')),
        ]);
    }

    /** @param iterable<AppExpActivity> $rows */
    public static function presentMany($rows, AdLanguage $language, ?Collection $groups = null): array
    {
        $rows = collect($rows);
        $groups ??= self::groups($language);
        $cruises = self::cruises($rows);

        return $rows->map(fn ($e) => self::summary($e, $groups, $cruises))->values()->all();
    }

    public static function summary(AppExpActivity $e, Collection $groups, Collection $cruises): array
    {
        $group = $groups->get($e->group_id);
        $cruise = $cruises->get($e->cruise_id);

        return [
            'id' => (int) $e->id,
            'slug' => CabinContentService::slug($e->name),
            'name' => $e->name,
            'summary' => ItineraryContentService::plain($e->summary),
            'image' => CabinContentService::fileLink($e->image_link),
            'cover' => CabinContentService::fileLink($e->cover_link),
            'duration' => ItineraryContentService::clean($e->duration),
            'startTime' => self::time($e->start_time),
            'endTime' => self::time($e->end_time),
            'notes' => self::notes($e->note),
            'isFeatured' => (bool) $e->is_featured,
            'group' => $group ? self::groupRef($group) : null,
            'cruise' => $cruise
                ? ['id' => (int) $cruise->id, 'slug' => CabinContentService::slug($cruise->name), 'name' => $cruise->name]
                : null,
        ];
    }

    /**
     * Explicit ContentTranslations link first; otherwise the counterpart rule of
     * FeHreflangUtils::findExperienceCounterpart (ids are per-language rows, so it is
     * "same duration + same non-placeholder image_link"), kept only when exactly one unlinked row matches.
     */
    public static function alternate(AppExpActivity $e, AdLanguage $language): ?array
    {
        $target = ItineraryContentService::otherLanguage($language);
        if ($target) {
            [$linked, $linkedId] = ContentTranslations::lookup('experience', (int) $e->id, (int) $target->id);
            if ($linked) {
                $match = $linkedId ? AppExpActivity::where('id', $linkedId)->first() : null;

                return $match ? ['lang' => $target->code, 'id' => (int) $match->id, 'slug' => CabinContentService::slug($match->name)] : null;
            }
        }

        $image = self::normalizeAsset($e->image_link);
        if (!$target || $image === '' || str_ends_with($image, 'blank.gif')) {
            return null;
        }

        $matches = AppExpActivity::where('language_id', $target->id)
            ->where('duration', $e->duration)
            ->get()
            ->filter(fn ($row) => self::normalizeAsset($row->image_link) === $image)
            ->values();
        if ($matches->count() !== 1 || !ContentTranslations::unclaimed('experience', $matches->first())) {
            return null;
        }
        $match = $matches->first();

        return ['lang' => $target->code, 'id' => (int) $match->id, 'slug' => CabinContentService::slug($match->name)];
    }

    /** Experience groups (app_group type expActivity) keyed by id, admin order. */
    public static function groups(AdLanguage $language): Collection
    {
        return AppGroupService::getAll(config('backend.groupType.expActivity'), (int) $language->id)
            ->filter(fn ($g) => $g->slug !== 'root')
            ->keyBy('id');
    }

    public static function groupRef($group): array
    {
        return [
            'id' => (int) $group->id,
            'slug' => $group->slug,
            'name' => $group->name,
            'tab' => self::TABS[(int) $group->tab] ?? null,
        ];
    }

    /** Blade "tabButtons": one tab per groupTabType that has at least one group. */
    protected static function tabs(Collection $groups, AdLanguage $language): array
    {
        $tabs = [];
        foreach (config('backend.groupTabType.expActivity', []) as $langKey => $tabValue) {
            $ids = $groups->where('tab', $tabValue)->pluck('id')->map(fn ($id) => (int) $id)->values()->all();
            if ($ids !== []) {
                $tabs[] = [
                    'key' => self::TABS[(int) $tabValue] ?? (string) $langKey,
                    'name' => __('backend::group.' . $langKey, [], $language->code),
                    'groupIds' => $ids,
                ];
            }
        }

        return $tabs;
    }

    protected static function cruises(Collection $rows): Collection
    {
        $ids = $rows->pluck('cruise_id')->filter()->unique()->values()->all();

        return $ids === [] ? collect() : AppCruise::whereIn('id', $ids)->get(['id', 'name'])->keyBy('id');
    }

    /** "note" is a JSON array of short strings ("Small group, Morning, relaxing"). */
    protected static function notes($json): array
    {
        $list = json_decode((string) $json, true);
        if (!is_array($list)) {
            $single = ItineraryContentService::clean(is_string($json) ? $json : null);

            return $single !== null && $json !== '[]' ? [$single] : [];
        }

        return array_values(array_filter(array_map(fn ($n) => is_string($n) ? ItineraryContentService::clean($n) : null, $list)));
    }

    protected static function time($value): ?string
    {
        $value = trim((string) $value);

        return preg_match('/^(\d{1,2}):(\d{2})/', $value, $m) ? sprintf('%02d:%s', $m[1], $m[2]) : null;
    }

    protected static function normalizeAsset(?string $path): string
    {
        $path = trim((string) $path);
        $path = preg_replace('#^https?://[^/]+#i', '', $path) ?? $path;

        return ltrim($path, '/');
    }
}
