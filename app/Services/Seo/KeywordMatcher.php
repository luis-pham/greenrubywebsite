<?php

namespace App\Services\Seo;

use Illuminate\Support\Str;

/**
 * Keyword and text comparison used by the SEO guard. Pure functions, no database.
 *
 * Keywords are compared on normalised token sets (Vietnamese diacritics folded, "Halong" = "Ha Long",
 * "2D1N" = "2 days 1 night" = "2 ngày 1 đêm", stop words removed), not on raw strings, so "Ha Long Bay
 * cruise 2 days 1 night" and "halong bay 2d1n cruise" are the same keyword.
 */
class KeywordMatcher
{
    /** Search intents, as stored in app_article_plan.intent and app_seo_owner.intent. */
    public const INTENTS = ['I' => 'informational', 'C' => 'commercial', 'T' => 'transactional', 'N' => 'navigational'];

    private const STOP = [
        // English
        'a', 'an', 'the', 'in', 'on', 'at', 'to', 'of', 'for', 'and', 'or', 'with', 'from', 'by', 'is', 'are', 'what',
        'how', 'your', 'you', 'do', 'does', 'can', 'it', 'its', 'my', 'guide', 'complete',
        // Vietnamese (diacritics already folded)
        'o', 'cho', 'va', 'cua', 'la', 'nhung', 'cac', 'mot', 'nao', 'gi', 'khi', 'tai', 'voi', 'den', 'tu', 've',
        'co', 'khong', 'nen', 'hay', 'nhat', 'can', 'biet', 'di',
    ];

    /** Lower-case ASCII with Vietnamese folded, "&" and punctuation removed, common spellings unified. */
    public static function normalize(?string $text): string
    {
        $t = Str::lower(Str::ascii((string) $text));
        $t = str_replace(['&', '/', '-', '_'], ' ', $t);
        $t = preg_replace('/[^a-z0-9 ]+/', ' ', $t);
        $t = ' ' . preg_replace('/\s+/', ' ', $t) . ' ';
        $patterns = [
            '/ halong /' => ' ha long ',
            '/ halongbay /' => ' ha long bay ',
            '/ lanha /' => ' lan ha ',
            '/ (\d) ?d ?(\d) ?n /' => ' $1d$2n ',
            '/ (\d) days? (\d) nights? /' => ' $1d$2n ',
            '/ (\d) ngay (\d) dem /' => ' $1d$2n ',
            '/ (\d) nights? /' => ' $1 night ',
            // A comparison is its own topic ("ha long bay vs lan ha bay", "ha long hay lan ha").
            '/ (vs|versus|hay|or) /' => ' compare ',
            '/ cruises /' => ' cruise ',
            '/ caves /' => ' cave ',
            '/ islands /' => ' island ',
            '/ beaches /' => ' beach ',
            '/ villages /' => ' village ',
            '/ du thuyen /' => ' cruise ',
            '/ vinh /' => ' bay ',
            '/ hang /' => ' cave ',
            '/ dao /' => ' island ',
        ];
        // Apply twice so overlapping matches (" 2 days 1 night 3 days 2 nights ") are all caught.
        for ($i = 0; $i < 2; $i++) {
            $t = preg_replace(array_keys($patterns), array_values($patterns), $t);
        }

        return trim(preg_replace('/\s+/', ' ', $t));
    }

    /** @return string[] distinct meaningful tokens */
    public static function tokens(?string $text): array
    {
        $words = array_filter(explode(' ', self::normalize($text)), fn ($w) => $w !== '' && !in_array($w, self::STOP, true));

        return array_values(array_unique($words));
    }

    /** Words that change what the reader wants; one side having it and the other not means a different intent. */
    private const SHAPERS = ['compare', 'price', 'worth'];

    /** Place words: context, not topic ("ha long bay kayaking" and "ha long bay typhoon" share no topic). */
    private const PLACE = ['ha', 'long', 'bay', 'lan', 'cat', 'ba', 'vietnam', 'viet', 'nam', 'quang', 'ninh', 'hai', 'phong'];

    /** Which bay a keyword is about (null when none or both), to tell "lan ha bay cruise" from "ha long bay cruise". */
    public static function bay(?string $text): ?string
    {
        $n = ' ' . self::normalize($text) . ' ';
        $bays = array_keys(array_filter(['ha-long' => str_contains($n, ' ha long '), 'lan-ha' => str_contains($n, ' lan ha ') || str_contains($n, ' cat ba ')]));

        return count($bays) === 1 ? $bays[0] : null;
    }

    /**
     * 0..1. Exact match after normalisation is 1. Otherwise the topic words (place words removed) are compared:
     * the larger of Jaccard and a discounted containment, so "sung sot cave visitor guide" still matches
     * "sung sot cave". Keywords about two different bays are halved.
     */
    public static function similarity(?string $a, ?string $b): float
    {
        $na = self::normalize($a);
        $nb = self::normalize($b);
        if ($na === '' || $nb === '') {
            return 0.0;
        }
        if ($na === $nb) {
            return 1.0;
        }
        $ta = self::tokens($a);
        $tb = self::tokens($b);
        $ca = array_values(array_diff($ta, self::PLACE));
        $cb = array_values(array_diff($tb, self::PLACE));
        // A keyword that is only a place ("lan ha bay") is compared on its place words.
        [$ta, $tb] = ($ca && $cb) ? [$ca, $cb] : [$ta, $tb];
        if (!$ta || !$tb) {
            return 0.0;
        }
        $shared = count(array_intersect($ta, $tb));
        $jaccard = $shared / count(array_unique(array_merge($ta, $tb)));
        $containment = $shared / min(count($ta), count($tb));
        $score = max($jaccard, 0.9 * $containment * min(1, min(count($ta), count($tb)) / 2));
        // Only one side names a topic ("lan ha bay" vs "ha long bay vs lan ha bay"), or only one side compares,
        // prices or weighs up: related, never the same query.
        if ((bool) $ca !== (bool) $cb || array_intersect($ta, self::SHAPERS) !== array_intersect($tb, self::SHAPERS)) {
            $score = min($score, 0.6);
        }
        $bayA = self::bay($a);
        $bayB = self::bay($b);
        if ($bayA && $bayB && $bayA !== $bayB) {
            $score /= 2;
        }

        return round($score, 3);
    }

    /** True when every token of $narrow's broader counterpart $broad is in $narrow and $narrow adds more. */
    public static function isNarrowerThan(?string $narrow, ?string $broad): bool
    {
        $tn = self::tokens($narrow);
        $tb = self::tokens($broad);

        return $tb && count($tn) > count($tb) && !array_diff($tb, $tn);
    }

    /** "same", "adjacent" (C~I, C~T), "different" or "unknown". */
    public static function intentRelation(?string $a, ?string $b): string
    {
        $a = $a ? strtoupper($a) : null;
        $b = $b ? strtoupper($b) : null;
        if (!$a || !$b || !isset(self::INTENTS[$a], self::INTENTS[$b])) {
            return 'unknown';
        }
        if ($a === $b) {
            return 'same';
        }
        $pair = [$a, $b];
        sort($pair);

        return in_array(implode('', $pair), ['CI', 'CT'], true) ? 'adjacent' : 'different';
    }

    /**
     * Share of the candidate's 6-word shingles that also occur in the other text (0..1). A supporting signal
     * only: it catches drafts that restate an existing page under a different keyword; it never decides alone.
     */
    public static function contentOverlap(?string $candidate, ?string $other): float
    {
        $c = self::shingles($candidate);
        $o = self::shingles($other);
        if (count($c) < 20 || !$o) {
            return 0.0;
        }

        return round(count(array_intersect_key($c, $o)) / count($c), 3);
    }

    private static function shingles(?string $text): array
    {
        $words = array_values(array_filter(explode(' ', self::normalize(strip_tags((string) $text)))));
        $out = [];
        for ($i = 0; $i + 6 <= count($words); $i++) {
            $out[implode(' ', array_slice($words, $i, 6))] = true;
        }

        return $out;
    }
}
