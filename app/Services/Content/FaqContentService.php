<?php

namespace App\Services\Content;

use Illuminate\Support\Collection;
use Modules\BackEnd\Entities\AdLanguage;
use Modules\BackEnd\Entities\AppGroup;
use Modules\BackEnd\Services\AppGroupService;
use Modules\BackEnd\Services\AppPageService;
use Modules\FrontEnd\Helpers\FeHreflangUtils;
use Modules\FrontEnd\Services\AppFaqService;

/**
 * GET /faqs. Mirrors FaqController@index / @category: groups = app_group type faq (admin order),
 * FAQs ordered by app_faq.ord, pages of config('frontend.paginationLimit'), optional keyword "k".
 */
class FaqContentService
{
    /**
     * Returns null for a Blade 404 (unknown group, page past the end). For a group slug that belongs to the
     * other language, returns only {redirect:{group,page}} — the Blade site 301s to that localized slug.
     */
    public static function index(AdLanguage $language, ?string $groupSlug, int $page, ?string $keyword): ?array
    {
        $languageId = (int) $language->id;
        $faqType = config('backend.groupType.faq');
        $groups = self::groups($language);

        $group = null;
        if ($groupSlug !== null && $groupSlug !== '') {
            $group = AppGroupService::getBySlug($groupSlug, $faqType, $languageId);
            if (!$group || $group->slug === 'root') {
                $source = AppGroup::where('slug', $groupSlug)->where('type', $faqType)->where('slug', '!=', 'root')->first();
                $localized = $source ? FeHreflangUtils::findFaqGroupCounterpart($source, $languageId) : null;
                if ($localized && $localized->slug && $localized->slug !== 'root') {
                    return ['redirect' => ['group' => $localized->slug, 'page' => $page > 1 ? $page : null]];
                }

                return null;
            }
        }

        $param = [];
        if ($group) {
            $param['group_id'] = $group->id;
        }
        if ($keyword !== null && $keyword !== '') {
            $param['keyword'] = $keyword;
        }

        $total = AppFaqService::getPagingCount($param, $languageId);
        $perPage = (int) config('frontend.paginationLimit', 20);
        $pagination = PageConfigContentService::paginate($page, $perPage, $total);
        if ($total > 0 && $page > $pagination['lastPage']) {
            return null;
        }

        $rows = AppFaqService::getPaging($param + ['page' => $page, 'pageSize' => $perPage], $languageId);

        // Group: seo_title ?: name. Hub: app_page "faq" SEO (admin page config), else the translated page title.
        $hubSeo = $group ? null : PageConfigContentService::seo('faq', $language);
        $title = $group
            ? (ItineraryContentService::clean($group->seo_title) ?? $group->name)
            : ($hubSeo['title'] ?? __('frontend::faq.page_title', [], $language->code));
        // Only what the editor set (group / app_page "faq" seo_description): null lets the frontend use its own default.
        $hubPage = $group ? null : AppPageService::getByCode('faq', $language->id);
        $description = ItineraryContentService::clean($group ? $group->seo_description : ($hubPage->seo_description ?? null));

        return [
            'seo' => [
                'title' => PageConfigContentService::pageSuffix(ItineraryContentService::clean($title), $page, $language),
                'description' => $description,
            ],
            'group' => $group ? self::groupRef($group) : null,
            'groups' => $groups->map(fn ($g) => self::groupRef($g) + [
                'count' => AppFaqService::getPagingCount(['group_id' => $g->id], $languageId),
            ])->values(),
            'items' => self::presentMany($rows, $language, $groups),
            'pagination' => $pagination,
            'keyword' => $keyword !== '' ? $keyword : null,
        ];
    }

    /** @param iterable $rows app_faq rows */
    public static function presentMany($rows, AdLanguage $language, ?Collection $groups = null): array
    {
        $groups ??= self::groups($language);

        return collect($rows)
            ->map(fn ($f) => self::present($f, $groups))
            ->filter(fn ($f) => $f['question'] !== null)
            ->values()
            ->all();
    }

    /** question is stored as HTML in the CMS; exposed both as plain text and sanitized HTML. */
    public static function present($faq, Collection $groups): array
    {
        $group = $groups->get($faq->group_id);

        return [
            'id' => (int) $faq->id,
            'question' => ItineraryContentService::plain($faq->question),
            'questionHtml' => PageConfigContentService::html($faq->question),
            'answerHtml' => PageConfigContentService::html($faq->answer),
            'group' => $group ? self::groupRef($group) : null,
        ];
    }

    public static function groups(AdLanguage $language): Collection
    {
        return AppGroupService::getAll(config('backend.groupType.faq'), (int) $language->id)
            ->filter(fn ($g) => $g->slug !== 'root')
            ->keyBy('id');
    }

    protected static function groupRef($group): array
    {
        return ['id' => (int) $group->id, 'slug' => $group->slug, 'name' => $group->name];
    }
}
