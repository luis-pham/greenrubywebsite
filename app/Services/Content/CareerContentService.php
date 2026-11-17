<?php

namespace App\Services\Content;

use Illuminate\Support\Facades\DB;
use Modules\BackEnd\Entities\AppJob;

/**
 * Job postings (app_job) for the Content API: GET /careers and GET /careers/{slug}, read by the website's
 * /tuyen-dung pages (Vietnamese only, so no ?lang).
 *
 * - open   = status "published" and the deadline (inclusive, Vietnam time) not passed: listed, in the sitemap,
 *            apply button and JobPosting schema on its page;
 * - closed = status "closed", or "published" past its deadline: not listed, not in the sitemap; its page stays
 *            (noindex) with a closed notice and no apply button;
 * - drafts, and closed postings that were never published, are never served (404).
 */
class CareerContentService
{
    public const DEFAULT_EMAIL = 'hrm@greenrubycruises.com';

    /** Stored employment_type => schema.org JobPosting employmentType. */
    public const SCHEMA_TYPES = [
        'full-time' => 'FULL_TIME',
        'part-time' => 'PART_TIME',
        'contract' => 'CONTRACTOR',
        'temporary' => 'TEMPORARY',
        'intern' => 'INTERN',
    ];

    /** Application address when a posting has none of its own: system config "recruitment-email". */
    public static function defaultEmail(): string
    {
        $value = trim((string) DB::table('ad_config')->where('key', 'recruitment-email')->value('value'));

        return filter_var($value, FILTER_VALIDATE_EMAIL) ? $value : self::DEFAULT_EMAIL;
    }

    public static function index(): array
    {
        $email = self::defaultEmail();
        $open = self::openQuery()->get();

        return [
            'email' => $email,
            'items' => $open->map(fn (AppJob $job) => self::summary($job))->values()->all(),
        ];
    }

    public static function detail(string $slug): ?array
    {
        // A closed posting keeps its page only if it was ever published (posted_at is set on first publish):
        // a draft switched straight to "closed" stays private.
        $job = AppJob::where('slug', $slug)
            ->where(fn ($q) => $q->where('status', 'published')->orWhere(fn ($q) => $q->where('status', 'closed')->whereNotNull('posted_at')))
            ->first();
        if (!$job) {
            return null;
        }
        $html = fn (?string $value) => self::meaningful(PageConfigContentService::html($value));
        $summary = ItineraryContentService::plain($job->summary);

        return array_merge(self::summary($job), [
            'state' => self::isOpen($job) ? 'open' : 'closed',
            'summary' => $summary,
            'introHtml' => $html($job->intro),
            'descriptionHtml' => $html($job->description),
            'requirementsHtml' => $html($job->requirements),
            'benefitsHtml' => $html($job->benefits),
            'applyEmail' => self::validEmail($job->apply_email) ?? self::defaultEmail(),
            'address' => [
                'locality' => ItineraryContentService::clean($job->address_locality),
                'region' => ItineraryContentService::clean($job->address_region),
            ],
            'seo' => [
                'title' => ItineraryContentService::clean($job->seo_title),
                'description' => ItineraryContentService::clean($job->seo_description) ?? $summary,
            ],
            'updatedAt' => CabinContentService::isoDate($job->getRawOriginal('updated_at') ?: $job->getRawOriginal('created_at')),
        ]);
    }

    public static function summary(AppJob $job): array
    {
        return [
            'id' => (int) $job->id,
            'slug' => $job->slug,
            'title' => $job->title,
            'department' => $job->department,
            'employmentType' => isset(self::SCHEMA_TYPES[$job->employment_type]) ? $job->employment_type : null,
            'location' => ItineraryContentService::clean($job->location),
            'postedAt' => self::date($job->posted_at),
            'deadline' => self::date($job->deadline),
        ];
    }

    /** Open postings in admin order (ord, then newest first). */
    public static function openQuery()
    {
        return AppJob::where('status', 'published')
            ->where(fn ($q) => $q->whereNull('deadline')->orWhere('deadline', '>=', now()->toDateString()))
            ->orderBy('ord')
            ->orderByDesc('id');
    }

    public static function isOpen(AppJob $job): bool
    {
        return $job->status === 'published' && (!$job->deadline || self::date($job->deadline) >= now()->toDateString());
    }

    /** Null for HTML without visible text (an editor's empty "<p>&nbsp;</p>"), so the website hides the section. */
    public static function meaningful(?string $html): ?string
    {
        if ($html === null) {
            return null;
        }
        $text = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $hasMedia = preg_match('/<(img|iframe|video|table)\b/i', $html) === 1;

        return trim($text, " \t\n\r\0\x0B\u{00A0}") === '' && !$hasMedia ? null : $html;
    }

    private static function validEmail(?string $value): ?string
    {
        $value = trim((string) $value);

        return filter_var($value, FILTER_VALIDATE_EMAIL) ? $value : null;
    }

    private static function date($value): ?string
    {
        return $value ? substr((string) $value, 0, 10) : null;
    }
}
