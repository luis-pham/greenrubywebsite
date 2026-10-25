<?php

namespace App\Services\Seo;

use Illuminate\Support\Facades\DB;

/**
 * Pre-writing and publishing gate (G3). Before a plan item is queued for AI drafting or started, and before an
 * article goes live, its keyword is checked against the inventory. A conflict blocks the action unless a user
 * whose role is in config('seo.override_roles') overrides it with a written reason. Every check is logged in
 * app_seo_guard_log (outcome allowed / blocked / overridden), so the decision trail survives.
 */
class SeoGuard
{
    /** Report for an arbitrary candidate (the writing app's "check" panel). */
    public static function check(array $candidate): array
    {
        $report = OverlapDetector::analyse($candidate, SeoInventory::targets((int) $candidate['language_id']));
        // Content is only needed for the comparison; keep reports and logs small.
        $report['matches'] = array_slice($report['matches'], 0, 8);

        return $report + ['keyword' => $candidate['keyword']];
    }

    public static function candidateFromArticle(object $a): array
    {
        $plan = DB::table('app_article_plan')->where('article_id', $a->id)->first();

        return [
            'language_id' => (int) $a->language_id,
            'keyword' => trim((string) ($a->focus_keyword ?: $a->title)),
            'secondary' => $plan ? SeoInventory::list($plan->secondary_kw) : [],
            'intent' => $plan->intent ?? null,
            'title' => (string) $a->title,
            'content' => (string) $a->content,
            'self' => ['article' => (int) $a->id, 'plan' => $plan ? (int) $plan->id : null,
                'url' => SeoInventory::articleUrl((int) $a->language_id, DB::table('app_category')->where('id', $a->category_id)->value('slug'), $a->slug)],
        ];
    }

    public static function candidateFromPlan(object $p): array
    {
        $languageId = (int) (DB::table('ad_language')->where('code', $p->lang)->value('id') ?? 2);

        return [
            'language_id' => $languageId,
            'keyword' => trim((string) ($p->primary_kw ?: $p->title)),
            'secondary' => SeoInventory::list($p->secondary_kw),
            'intent' => $p->intent,
            'title' => (string) $p->title,
            'content' => '',
            'self' => ['article' => $p->article_id ? (int) $p->article_id : null, 'plan' => (int) $p->id, 'url' => null],
        ];
    }

    public static function canOverride(?int $userId): bool
    {
        if (!$userId) {
            return false;
        }

        return DB::table('ad_user_role')->where('user_id', $userId)->whereIn('role_id', config('seo.override_roles', [1]))->exists();
    }

    /**
     * Runs the gate. Returns ['allowed' => bool, 'outcome' => allowed|blocked|overridden, 'report' => array, 'logId' => int].
     * $reason overrides a conflict only for a user allowed to override; a previous override of the same entity and
     * keyword still counts (overriding at plan stage also lets the AI queue through).
     */
    public static function gate(string $action, string $entityType, int $entityId, array $candidate, ?int $userId, ?string $reason = null): array
    {
        $report = self::check($candidate);
        $reason = trim((string) $reason);
        $outcome = 'allowed';
        if ($report['verdict'] === 'conflict' && config('seo.guard', true)) {
            if ($reason !== '' && self::canOverride($userId)) {
                $outcome = 'overridden';
            } elseif (self::hasOverride($entityType, $entityId, $candidate['keyword'])) {
                $outcome = 'overridden';
                $reason = 'Đã được override trước đó (xem nhật ký).';
            } else {
                $outcome = 'blocked';
            }
        }

        $logId = DB::table('app_seo_guard_log')->insertGetId([
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'language_id' => $candidate['language_id'],
            'keyword' => mb_substr($candidate['keyword'], 0, 255),
            'verdict' => $report['verdict'],
            'recommendation' => $report['recommendation'],
            'outcome' => $outcome,
            'override_reason' => $outcome === 'overridden' ? $reason : null,
            'report' => json_encode($report, JSON_UNESCAPED_UNICODE),
            'user_id' => $userId,
            'created_at' => now(),
        ]);

        return ['allowed' => $outcome !== 'blocked', 'outcome' => $outcome, 'report' => $report, 'logId' => $logId,
            'canOverride' => self::canOverride($userId)];
    }

    public static function hasOverride(string $entityType, int $entityId, string $keyword): bool
    {
        $key = KeywordMatcher::normalize($keyword);

        return DB::table('app_seo_guard_log')->where('entity_type', $entityType)->where('entity_id', $entityId)
            ->where('outcome', 'overridden')->whereNotNull('override_reason')->pluck('keyword')
            ->contains(fn ($k) => KeywordMatcher::normalize($k) === $key);
    }
}
