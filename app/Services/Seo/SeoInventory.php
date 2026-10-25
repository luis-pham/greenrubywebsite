<?php

namespace App\Services\Seo;

use App\Services\Content\CabinContentService;
use Illuminate\Support\Facades\DB;

/**
 * Everything a new piece of content could compete with, as OverlapDetector targets: the keyword-ownership
 * registry, every article (published, scheduled and draft), every destination (shown or hidden) and the plan
 * items that are still to be written. Plan items marked "skip" (merge / do not publish) are left out.
 */
class SeoInventory
{
    private const DESTINATION_SEGMENT = ['en' => '/destinations/', 'vi' => '/vi/dia-diem-tham-quan/'];

    public static function targets(int $languageId): array
    {
        return array_merge(self::owners($languageId), self::articles($languageId), self::destinations($languageId),
            self::cabins($languageId), self::plans($languageId));
    }

    public static function owners(int $languageId): array
    {
        return DB::table('app_seo_owner')->where('language_id', $languageId)->whereIn('status', ['active', 'gap'])->get()
            ->map(fn ($o) => [
                'type' => 'owner',
                'id' => (int) $o->id,
                'language_id' => (int) $o->language_id,
                'keywords' => array_merge([$o->keyword], self::list($o->supporting_keywords)),
                'intent' => $o->intent,
                'url' => $o->owner_url,
                'owner_type' => $o->owner_type,
                'owner_ref' => $o->owner_ref,
                'status' => $o->status === 'gap' ? 'gap' : 'live',
                'role' => $o->page_role,
                'label' => $o->owner_url ?: $o->keyword,
            ])->all();
    }

    public static function articles(int $languageId): array
    {
        $intents = DB::table('app_article_plan')->whereNotNull('article_id')->pluck('intent', 'article_id');

        return DB::table('app_article')->where('app_article.language_id', $languageId)
            ->leftJoin('app_category', 'app_category.id', '=', 'app_article.category_id')
            ->get(['app_article.id', 'app_article.language_id', 'app_article.title', 'app_article.seo_title', 'app_article.focus_keyword',
                'app_article.slug', 'app_category.slug as category_slug', 'app_article.is_published', 'app_article.publish_date', 'app_article.content'])
            ->map(function ($a) use ($intents) {
                $status = self::articleStatus($a->is_published, $a->publish_date);

                return [
                    'type' => 'article',
                    'id' => (int) $a->id,
                    'language_id' => (int) $a->language_id,
                    'keywords' => array_values(array_filter([$a->focus_keyword, self::stripBrand($a->seo_title), $a->title])),
                    'intent' => $intents[$a->id] ?? null,
                    'url' => $status === 'draft' ? null : self::articleUrl((int) $a->language_id, $a->category_slug, $a->slug),
                    'status' => $status,
                    'label' => $a->title,
                    'content' => (string) $a->content,
                ];
            })->all();
    }

    public static function destinations(int $languageId): array
    {
        $code = DB::table('ad_language')->where('id', $languageId)->value('code');
        $hubVisible = (bool) (DB::table('app_page')->where('code', 'destinations')->where('language_id', $languageId)->value('is_visible') ?? true);

        return DB::table('app_destination')->where('language_id', $languageId)
            ->get(['id', 'language_id', 'slug', 'name', 'heading', 'status', 'content'])
            ->map(fn ($d) => [
                'type' => 'destination',
                'id' => (int) $d->id,
                'language_id' => (int) $d->language_id,
                'keywords' => array_values(array_filter([$d->name, $d->heading, str_replace('-', ' ', (string) $d->slug)])),
                'intent' => 'I',
                'url' => (self::DESTINATION_SEGMENT[$code] ?? '/destinations/') . $d->slug,
                'status' => $d->status === 'published' && $hubVisible ? 'live' : 'hidden',
                'label' => $d->name,
                'content' => (string) $d->content,
                'entity' => true,
            ])->all();
    }

    /** Cabin categories (accommodation only, not facilities) as named entities; both ships share the names. */
    public static function cabins(int $languageId): array
    {
        $vi = DB::table('ad_language')->where('id', $languageId)->value('code') === 'vi';

        return CabinContentService::listAccommodation($languageId)->sortBy('id')->unique('name')
            ->map(fn ($c) => [
                'type' => 'cabin',
                'id' => (int) $c->id,
                'language_id' => (int) $c->language_id,
                'keywords' => [$c->name],
                'intent' => 'C',
                'url' => ($vi ? '/vi/phong-nghi/' : '/cabin/') . CabinContentService::slug($c->name) . '-' . $c->id . '.html',
                'status' => 'live',
                'label' => $c->name,
                'entity' => true,
            ])->values()->all();
    }

    public static function plans(int $languageId): array
    {
        $code = DB::table('ad_language')->where('id', $languageId)->value('code');

        return DB::table('app_article_plan')->where('lang', $code)->whereNull('article_id')->whereIn('status', ['idea', 'wait'])
            ->get(['id', 'code', 'title', 'primary_kw', 'secondary_kw', 'intent'])
            ->map(fn ($p) => [
                'type' => 'plan',
                'id' => (int) $p->id,
                'language_id' => $languageId,
                'keywords' => array_values(array_filter([$p->primary_kw])),
                'intent' => $p->intent,
                'url' => null,
                'status' => 'planned',
                'label' => "{$p->code} {$p->title}",
            ])->all();
    }

    /** live, scheduled or draft (same rule as the writing app: published with a future date is scheduled). */
    public static function articleStatus($isPublished, $publishDate): string
    {
        if ((int) $isPublished !== 1) {
            return 'draft';
        }

        return $publishDate && \Carbon\Carbon::parse($publishDate)->isFuture() ? 'scheduled' : 'live';
    }

    /** Public URL of an article (same shape as ArticleController::url). */
    public static function articleUrl(int $languageId, ?string $categorySlug, ?string $slug): ?string
    {
        return $categorySlug && $slug ? ($languageId === 1 ? '/vi' : '') . '/blog/' . $categorySlug . '/' . $slug . '.html' : null;
    }

    /** JSON array, or a "a; b, c" string, as a clean list. */
    public static function list($value): array
    {
        $items = is_array($value) ? $value : (json_decode((string) $value, true) ?? preg_split('/[;,\n]+/', (string) $value));

        return array_values(array_filter(array_map(fn ($s) => trim((string) $s), (array) $items)));
    }

    private static function stripBrand(?string $title): ?string
    {
        return $title ? trim(preg_replace('/\s*[|–—-]\s*Green Ruby.*$/i', '', $title)) : null;
    }
}
