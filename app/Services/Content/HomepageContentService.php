<?php

namespace App\Services\Content;

use Modules\BackEnd\Entities\AdLanguage;
use Modules\BackEnd\Helpers\Utilities;
use Modules\FrontEnd\Helpers\FeUtils;
use Modules\BackEnd\Services\AppPageService;

/**
 * Homepage payload for the Content API: the "homepage" page config (with its sourceData pickers
 * already hydrated by FeUtils::getPageConfigByCode) reshaped into plain, camelCase JSON.
 * The admin's ordering of picked items is preserved.
 */
class HomepageContentService
{
    public static function build(AdLanguage $language): array
    {
        $config = FeUtils::getPageConfigByCode('homepage', (int) $language->id);
        $get = fn (string $key) => $config[$key] ?? null;
        $list = fn (string $key) => collect(is_iterable($get($key)) ? $get($key) : []);
        $text = fn (string $key) => ($v = trim((string) $get($key))) !== '' ? $v : null;
        $page = AppPageService::getByCode('homepage', $language->id);

        return [
            'seo' => [
                'title' => self::clean($page->seo_title ?? null),
                'description' => self::clean($page->seo_description ?? null),
            ],
            'banner' => $list('homepage-banner')->map(fn ($s) => [
                'title' => self::clean(self::field($s, 'title')),
                'description' => self::clean(self::field($s, 'description')),
                'image' => CabinContentService::fileLink(self::field($s, 'link')),
            ])->filter(fn ($s) => $s['image'])->values(),
            'slogan' => $text('homepage-slogan'),
            'cruises' => $list('homepage-cruise')->map(fn ($c) => [
                'id' => (int) self::field($c, 'id'),
                'slug' => Utilities::convertToAlias((string) self::field($c, 'name')),
                'name' => self::field($c, 'name'),
                'summary' => self::clean(self::field($c, 'summary')),
                'image' => CabinContentService::fileLink(self::field($c, 'image_link')),
                'cover' => CabinContentService::fileLink(self::field($c, 'cover_link')),
                'capacity' => self::number(self::field($c, 'capacity')),
                'length' => self::number(self::field($c, 'dimension_length')),
                'starRating' => self::number(self::field($c, 'star_rating')),
            ])->values(),
            'itineraries' => $list('homepage-cruise-itinerary')->map(fn ($i) => [
                'itineraryId' => (int) self::field($i, 'id'),
                'cruiseId' => (int) self::field($i, 'cruise_id'),
                'slug' => Utilities::convertToAlias((string) self::field($i, 'name')),
                'name' => self::field($i, 'name'),
                'description' => self::clean(strip_tags((string) self::field($i, 'description'))),
                'cruiseName' => self::field($i, 'cruise_name'),
                'duration' => (int) self::field($i, 'duration'),
                'bay' => self::bayCode(self::field($i, 'bay')),
                'image' => CabinContentService::fileLink(self::field($i, 'image_link') ?: self::field($i, 'cover_link')),
                'destinations' => array_values(array_filter((array) json_decode((string) self::field($i, 'destination'), true), 'is_string')),
            ])->values(),
            'cabins' => self::cabins($list('homepage-cabin')->map(fn ($c) => (int) self::field($c, 'id'))->all(), $language),
            'services' => $list('homepage-service')->map(fn ($s) => [
                'id' => (int) self::field($s, 'id'),
                'name' => self::field($s, 'name'),
                'description' => self::clean(strip_tags((string) self::field($s, 'description'))),
                'image' => CabinContentService::fileLink(self::field($s, 'image_link')),
            ])->values(),
            'experiences' => $list('homepage-exp-activity')->map(fn ($e) => [
                'id' => (int) self::field($e, 'id'),
                'slug' => Utilities::convertToAlias((string) self::field($e, 'name')),
                'name' => self::field($e, 'name'),
                'summary' => self::clean(strip_tags((string) self::field($e, 'summary'))),
                'image' => CabinContentService::fileLink(self::field($e, 'image_link') ?: self::field($e, 'cover_link')),
                'group' => self::field($e, 'group_name'),
                'duration' => self::clean(self::field($e, 'duration')),
            ])->values(),
            'sustainability' => $list('homepage-sustainability')->map(fn ($s) => [
                'title' => self::clean(self::field($s, 'title')),
                'description' => self::clean(self::field($s, 'description')),
                'icon' => CabinContentService::fileLink(self::field($s, 'link')),
            ])->values(),
            'awards' => $list('homepage-award')->map(fn ($a) => [
                'title' => self::clean(self::field($a, 'title')),
                'description' => self::clean(self::field($a, 'description')),
                'icon' => CabinContentService::fileLink(self::field($a, 'link')),
            ])->values(),
            'faqs' => $list('homepage-faq')->map(fn ($f) => [
                'question' => self::clean(html_entity_decode(strip_tags((string) self::field($f, 'question')), ENT_QUOTES | ENT_HTML5)),
                'answerHtml' => safe_html((string) self::field($f, 'answer')),
            ])->filter(fn ($f) => $f['question'])->values(),
            'support' => $text('homepage-support'),
            'readyToSail' => $text('homepage-ready-to-sail-description'),
        ];
    }

    /** Accommodation cabins only (the picker also allows facilities), in the admin's order. */
    protected static function cabins(array $ids, AdLanguage $language): array
    {
        if ($ids === []) {
            return [];
        }
        $found = CabinContentService::accommodationQuery((int) $language->id)
            ->whereIn('app_cabin.id', $ids)
            ->get()
            ->keyBy('id');

        return collect($ids)
            ->map(fn ($id) => $found->get($id))
            ->filter()
            ->map(fn ($cabin) => CabinContentService::summary($cabin))
            ->values()
            ->all();
    }

    /** Language-neutral bay code (config('backend.itineraryBay') only holds Vietnamese labels). */
    protected static function bayCode($bay): ?string
    {
        return [1 => 'ha-long', 2 => 'lan-ha'][(int) $bay] ?? null;
    }

    /** Page-config items are arrays (galleries) or Eloquent rows/stdClass (sourceData). */
    protected static function field($item, string $key)
    {
        if (is_array($item)) {
            return $item[$key] ?? null;
        }

        return is_object($item) ? ($item->{$key} ?? null) : null;
    }

    protected static function clean($value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    protected static function number($value)
    {
        return is_numeric($value) ? $value + 0 : null;
    }
}
