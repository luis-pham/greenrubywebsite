<?php

namespace App\Services\Seo;

/**
 * Decides whether a candidate (a plan item or an article about to be written or published) competes with
 * something that already exists. Pure: the caller passes the inventory (SeoInventory::targets()).
 *
 * Candidate: ['language_id', 'keyword', 'secondary' => string[], 'intent' => I|C|T|N|null, 'title', 'content',
 *             'self' => ['article' => id|null, 'plan' => id|null, 'url' => string|null]]
 * Target:    ['type' => owner|article|destination|cabin|plan, 'id', 'language_id', 'keywords' => string[] (first = primary),
 *             'intent', 'url', 'status' => live|scheduled|draft|hidden|planned|gap, 'role' => hub|pillar|detail|null,
 *             'label', 'content' (optional), 'entity' => bool (a named place: the page owns that entity)]
 *
 * A target is a CONFLICT when the candidate would answer the same query for the same reader:
 *   - the keywords match (similarity >= 0.85) and the intents are the same, adjacent or unknown, or
 *   - the candidate is about a named place with its own page (destination) and is informational, or about a
 *     cabin category that has its own page, or
 *   - the texts largely overlap (>= 40% of the candidate's 6-word shingles) and the keywords are related.
 * A registry row with status "gap" (an intent with no page yet) is reported as GAP: the candidate can fill it.
 * It is a HEALTHY OVERLAP when the topics are related but the reader's need differs: different intent,
 * or a narrower page under a hub/pillar (e.g. one itinerary under the itinerary hub), or a looser keyword match.
 */
class OverlapDetector
{
    public const MATCH = 0.85;
    public const RELATED = 0.5;
    public const CONTENT = 0.4;

    /** @return array{verdict: string, recommendation: string, target: ?array, matches: array[]} */
    public static function analyse(array $candidate, array $targets): array
    {
        $matches = [];
        foreach ($targets as $t) {
            if ((int) ($t['language_id'] ?? 0) !== (int) $candidate['language_id'] || self::isSelf($candidate, $t)) {
                continue;
            }
            $m = self::compare($candidate, $t);
            if ($m) {
                $matches[] = $m;
            }
        }
        usort($matches, fn ($a, $b) => [$b['class'] === 'conflict', $b['score']] <=> [$a['class'] === 'conflict', $a['score']]);

        $conflicts = array_values(array_filter($matches, fn ($m) => $m['class'] === 'conflict'));
        if (!$conflicts) {
            return ['verdict' => $matches ? 'healthy_overlap' : 'clear', 'recommendation' => 'create_new', 'target' => null, 'matches' => $matches];
        }
        // An existing page (live, scheduled or written but hidden) wins over drafts and plans: update it.
        $existing = array_values(array_filter($conflicts, fn ($m) => in_array($m['status'], ['live', 'scheduled', 'hidden'], true) || in_array($m['type'], ['destination', 'cabin'], true)));
        $target = $existing[0] ?? $conflicts[0];

        return [
            'verdict' => 'conflict',
            'recommendation' => $existing ? 'update_existing' : 'merge',
            'target' => $target,
            'matches' => $matches,
        ];
    }

    private static function isSelf(array $c, array $t): bool
    {
        $self = $c['self'] ?? [];
        if (($t['type'] === 'article' && !empty($self['article']) && (int) $t['id'] === (int) $self['article'])
            || ($t['type'] === 'plan' && !empty($self['plan']) && (int) $t['id'] === (int) $self['plan'])) {
            return true;
        }
        // The registry entry that names this very URL (or this article) as the owner.
        return $t['type'] === 'owner' && (
            (!empty($self['url']) && rtrim((string) $t['url'], '/') === rtrim($self['url'], '/'))
            || (!empty($self['article']) && ($t['owner_type'] ?? null) === 'article' && (int) ($t['owner_ref'] ?? 0) === (int) $self['article'])
        );
    }

    private static function compare(array $c, array $t): ?array
    {
        $keywords = array_values(array_filter($t['keywords'] ?? []));
        $score = 0.0;
        $matched = null;
        foreach ($keywords as $k) {
            $s = KeywordMatcher::similarity($c['keyword'], $k);
            if ($s > $score) {
                [$score, $matched] = [$s, $k];
            }
        }
        // Secondary keywords of the candidate count, discounted: they signal topic, not the primary target.
        foreach ($c['secondary'] ?? [] as $sk) {
            foreach ($keywords as $k) {
                $s = 0.8 * KeywordMatcher::similarity($sk, $k);
                if ($s > $score) {
                    [$score, $matched] = [round($s, 3), $k];
                }
            }
        }

        $entity = false;
        if (!empty($t['entity'])) {
            $haystack = ' ' . KeywordMatcher::normalize(($c['keyword'] ?? '') . ' ' . ($c['title'] ?? '')) . ' ';
            foreach ($keywords as $name) {
                $n = KeywordMatcher::normalize($name);
                if ($n !== '' && count(KeywordMatcher::tokens($name)) >= 2 && str_contains($haystack, ' ' . $n . ' ')) {
                    [$entity, $matched] = [true, $name];
                    break;
                }
            }
        }

        $content = (!empty($c['content']) && !empty($t['content'])) ? KeywordMatcher::contentOverlap($c['content'], $t['content']) : 0.0;
        if ($score < self::RELATED && !$entity && $content < self::CONTENT) {
            return null;
        }

        $intent = KeywordMatcher::intentRelation($c['intent'] ?? null, $t['intent'] ?? null);
        if (($t['status'] ?? null) === 'gap' && $score >= self::MATCH) {
            // A recorded intent with no page yet: this content can become its owner.
            return ['class' => 'gap', 'type' => $t['type'], 'id' => $t['id'], 'url' => null, 'label' => $t['label'] ?? $matched,
                'status' => 'gap', 'keyword' => $matched, 'score' => $score, 'intent' => $intent, 'entity' => false, 'content' => 0.0,
                'reasons' => ["Khoảng trống đã ghi nhận cho \"{$matched}\": nội dung này có thể là URL sở hữu (cập nhật bảng sở hữu khi đăng)."]];
        }
        $narrower = in_array($t['role'] ?? null, ['hub', 'pillar'], true) && KeywordMatcher::isNarrowerThan($c['keyword'], $matched);
        $reasons = [];

        if ($entity && (($t['type'] === 'destination' && in_array($c['intent'] ?? null, ['I', null], true)) || $t['type'] === 'cabin')) {
            $class = 'conflict';
            $reasons[] = "Bài nói về \"{$matched}\", " . ($t['type'] === 'cabin' ? 'hạng phòng' : 'địa điểm') . ' đã có trang riêng: bổ sung vào trang đó thay vì viết bài mới.';
        } elseif ($score >= self::MATCH && in_array($intent, ['same', 'adjacent', 'unknown'], true) && !$narrower) {
            $class = 'conflict';
            $reasons[] = "Từ khóa trùng \"{$matched}\" (độ khớp {$score}), cùng nhu cầu tìm kiếm" . ($intent === 'unknown' ? ' (chưa rõ intent)' : '') . '.';
        } elseif ($content >= self::CONTENT && $score >= self::RELATED) {
            $class = 'conflict';
            $reasons[] = 'Nội dung lặp lại phần lớn trang đã có (' . round($content * 100) . '% đoạn văn trùng) dù từ khóa khác.';
        } else {
            $class = 'healthy_overlap';
            if ($narrower) {
                $reasons[] = "Chủ đề hẹp hơn nằm dưới trang tổng \"{$matched}\": link về trang đó.";
            } elseif ($intent === 'different') {
                $reasons[] = 'Liên quan nhưng khác nhu cầu tìm kiếm (' . KeywordMatcher::INTENTS[strtoupper($c['intent'])] . ' / ' . KeywordMatcher::INTENTS[strtoupper($t['intent'])] . '): nên link qua lại.';
            } else {
                $reasons[] = "Chủ đề gần \"{$matched}\" (độ khớp {$score}): nên link qua lại, giữ góc khác.";
            }
        }

        return [
            'class' => $class,
            'type' => $t['type'],
            'id' => $t['id'],
            'url' => $t['url'] ?? null,
            'label' => $t['label'] ?? $matched,
            'status' => $t['status'] ?? null,
            'keyword' => $matched,
            'score' => $score,
            'intent' => $intent,
            'entity' => $entity,
            'content' => $content,
            'reasons' => $reasons,
        ];
    }
}
