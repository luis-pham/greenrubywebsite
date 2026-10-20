<?php

namespace App\Services\Ai;

use Illuminate\Support\Facades\DB;

/**
 * Native Vietnamese editorial review for AI drafts (and for existing Vietnamese articles).
 *
 * A separate pass after the fact/SEO review: Claude reads the whole article as a Vietnamese travel editor
 * and returns surgical edits (original → revision) with a category, a reason and whether the meaning
 * changes. Code applies only edits that keep the meaning, match the text exactly once and keep the same
 * numbers and links; the rest are listed for the human editor. Rules: resources/ai/style-guide-vi.md and
 * resources/ai/vi-terminology.md.
 */
class VietnameseEditor
{
    public const FIELDS = ['title', 'lead', 'seo_title', 'seo_description', 'html'];
    public const CATEGORIES = ['grammar', 'fluency', 'collocation', 'rhythm', 'translationese', 'ai_pattern', 'tone', 'terminology', 'seo', 'fact'];
    public const ACCEPTANCE = ['read_aloud_smoothly', 'native_expressions', 'no_literal_translation', 'no_ai_transitions', 'terminology_consistent', 'facts_preserved', 'tone_matches', 'seo_natural'];
    public const DIMENSIONS = ['grammar', 'fluency', 'collocation', 'rhythm', 'translationese', 'ai_patterns', 'tone', 'terminology', 'seo_naturalness', 'fact_preservation'];

    /** Known translationese and formula patterns (deterministic lint; the model review is the real check). */
    public const PATTERNS = [
        'tàu chị em' => '/(*UCP)tàu chị em/iu',
        'tàu ngày' => '/(*UCP)\btàu ngày\b(?! càng)/iu',
        '"mạnh ở/về" (dịch "strong at")' => '/(*UCP)\bmạnh (ở|về)\b/iu',
        '"thắng về" (so sánh thắng thua)' => '/(*UCP)\bthắng về\b/iu',
        '"Nói gọn:"' => '/(*UCP)\bnói gọn\s*:/iu',
        '"câu trả lời thật là"' => '/(*UCP)câu trả lời (thật|thực) là/iu',
        '"Hãy coi đây là"' => '/(*UCP)hãy coi đây là/iu',
        '"mới là thứ" / "là thứ làm"' => '/(*UCP)(mới là thứ|là thứ làm)/iu',
        '"đáng hơn" rút gọn' => '/(*UCP)\b(đêm|vịnh|tuyến) đáng hơn\b/iu',
        '"Thời gian trong ngày" (tiêu đề)' => '/(*UCP)<th[^>]*>\s*thời gian trong ngày\s*</iu',
        '"đi chậm"' => '/(*UCP)\bđi chậm\b/iu',
        'dấu gạch dài (—)' => '/(*UCP)—/u',
    ];

    /** Marketing words to use sparingly (style-guide-vi §4). */
    public const OVERUSED = ['mang đến', 'mang lại', 'trải nghiệm tuyệt vời', 'đẳng cấp', 'độc đáo', 'tuyệt hảo', 'lý tưởng', 'hành trình đáng nhớ', 'bức tranh thiên nhiên', 'viên ngọc', 'nép mình', 'kỳ vĩ', 'thơ mộng', 'hoà mình', 'hòa mình', 'đắm chìm', 'khó quên', 'trọn vẹn'];

    public function __construct(private ArticleWriter $writer)
    {
    }

    /**
     * @param array{title: string, lead: string, seo_title: string, seo_description: string, html: string} $doc
     * @return array{doc: array, report: array}
     */
    public function review(array $doc, string $context, int $maxRounds = 2): array
    {
        $rounds = [];
        $applied = $flagged = $unapplied = $kept = [];
        $last = null;
        for ($round = 1; $round <= $maxRounds; $round++) {
            $prompt = $this->prompt($doc, $context, $round, $last);
            $res = $this->writer->structuredCall($prompt, 'high', self::schema(), 'vi');
            [$doc, $a, $f, $u] = self::applyEdits($doc, $res['issues'] ?? []);
            $applied = array_merge($applied, $a);
            $flagged = array_merge($flagged, $f);
            $unapplied = array_merge($unapplied, $u);
            $kept = array_merge($kept, $res['kept'] ?? []);
            $rounds[] = ['round' => $round, 'verdict' => $res['verdict'] ?? null, 'scores' => $res['scores'] ?? [], 'acceptance' => $res['acceptance'] ?? [], 'summary' => $res['summary'] ?? '', 'issues' => count($res['issues'] ?? []), 'applied' => count($a)];
            $last = $res;
            $passed = ($res['verdict'] ?? '') === 'pass' && !in_array(false, $res['acceptance'] ?? [], true);
            if ($passed || count($a) === 0) {
                break;
            }
        }

        return ['doc' => $doc, 'report' => [
            'rounds' => $rounds,
            'verdict' => $last['verdict'] ?? null,
            'scores' => $last['scores'] ?? [],
            'acceptance' => $last['acceptance'] ?? [],
            'applied' => $applied,
            'flagged' => $flagged,
            'unapplied' => $unapplied,
            'kept' => array_values(array_unique($kept)),
            'lint' => self::lint(self::plain($doc)),
        ]];
    }

    private function prompt(array $doc, string $context, int $round, ?array $previous): string
    {
        $json = json_encode(array_intersect_key($doc, array_flip(self::FIELDS)), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $again = $previous
            ? "\nĐây là lượt biên tập thứ {$round}. Lượt trước kết luận: " . ($previous['summary'] ?? '') . "\nChỉ sửa những chỗ còn chưa tự nhiên; không sửa lại những chỗ đã ổn.\n"
            : '';

        return <<<TXT
Bạn là biên tập viên du lịch người Việt, biên tập lần cuối một bài tiếng Việt trước khi giao cho tổng biên tập. Áp dụng hướng dẫn biên tập tiếng Việt và bảng thuật ngữ trong system prompt.

<loai_bai>{$context}</loai_bai>
<bai_viet>
{$json}
</bai_viet>
{$again}
Việc cần làm:
1. Đọc toàn bài như đọc thành tiếng. Tìm mọi câu, cụm từ, tiêu đề mục, tiêu đề cột bảng, sapo, SEO title, SEO description nghe không giống người Việt viết: văn dịch, kết hợp từ lạ, cấu trúc câu tiếng Anh, khuôn văn AI lặp lại, giọng lệch loại bài, thuật ngữ sai hoặc không nhất quán, từ khoá chèn gượng, câu đúng ngữ pháp nhưng hiếm người Việt dùng.
2. Với mỗi lỗi, trả về một sửa đổi nhỏ, đúng chỗ:
   - field: trường chứa câu (title, lead, seo_title, seo_description hoặc html).
   - original: chép NGUYÊN VĂN, đúng từng ký tự, một đoạn ngắn nhất đủ để thay (thường một câu hoặc một cụm) từ trường đó. Với html, chép đúng mã nguồn kể cả thẻ nằm trong đoạn đó.
   - revision: đoạn thay thế. Giữ nguyên mọi thẻ HTML, link (href và anchor có thể viết lại cho tự nhiên nhưng phải giữ thẻ <a> và href), con số, ngày giờ, tên riêng, placeholder [IMAGE…], [VIDEO…], [CẦN XÁC NHẬN…].
   - category, why (giải thích ngắn bằng tiếng Việt vì sao nghe không tự nhiên), meaning_changed (true nếu sửa làm đổi dữ kiện, mức độ chắc chắn, phạm vi, số liệu, trạng thái dịch vụ; khi đó vẫn ghi đề xuất nhưng nó sẽ không được tự động áp dụng).
3. Sửa tối thiểu. Không viết lại cả đoạn khi chỉ cần sửa một cụm. Không làm câu dài hơn, trang trọng hơn hay quảng cáo hơn. Không thay câu lủng củng bằng câu hoa mỹ. Không thêm dữ kiện.
4. kept: liệt kê 3 đến 5 câu trong bài đã tự nhiên, cố ý giữ nguyên (chép nguyên văn, bỏ thẻ HTML).
5. acceptance: đánh giá trung thực bài SAU KHI áp các sửa đổi của bạn theo từng tiêu chí. scores: chấm 1–5 cho bài sau sửa. verdict: pass chỉ khi mọi tiêu chí acceptance đều đạt.
6. summary: 2–3 câu tiếng Việt về chất lượng tiếng Việt của bài và những gì còn cần người biên tập xem.
Nếu không chắc về dữ kiện hoặc tên chính thức, đánh dấu meaning_changed = true thay vì đoán.
TXT;
    }

    public static function schema(): array
    {
        $bools = array_fill_keys(self::ACCEPTANCE, ['type' => 'boolean']);
        $ints = array_fill_keys(self::DIMENSIONS, ['type' => 'integer']);

        return [
            'type' => 'object',
            'properties' => [
                'issues' => ['type' => 'array', 'items' => [
                    'type' => 'object',
                    'properties' => [
                        'field' => ['type' => 'string', 'enum' => self::FIELDS],
                        'original' => ['type' => 'string'],
                        'category' => ['type' => 'string', 'enum' => self::CATEGORIES],
                        'why' => ['type' => 'string'],
                        'revision' => ['type' => 'string'],
                        'meaning_changed' => ['type' => 'boolean'],
                    ],
                    'required' => ['field', 'original', 'category', 'why', 'revision', 'meaning_changed'],
                    'additionalProperties' => false,
                ]],
                'kept' => ['type' => 'array', 'items' => ['type' => 'string']],
                'acceptance' => ['type' => 'object', 'properties' => $bools, 'required' => self::ACCEPTANCE, 'additionalProperties' => false],
                'scores' => ['type' => 'object', 'properties' => $ints, 'required' => self::DIMENSIONS, 'additionalProperties' => false],
                'verdict' => ['type' => 'string', 'enum' => ['pass', 'fail']],
                'summary' => ['type' => 'string'],
            ],
            'required' => ['issues', 'kept', 'acceptance', 'scores', 'verdict', 'summary'],
            'additionalProperties' => false,
        ];
    }

    /**
     * Applies safe edits: meaning kept, original found exactly once, same numbers, same links and
     * placeholders. @return array{0: array, 1: array, 2: array, 3: array} doc, applied, flagged, unapplied
     */
    public static function applyEdits(array $doc, array $issues): array
    {
        $applied = $flagged = $unapplied = [];
        foreach ($issues as $i) {
            $field = $i['field'] ?? 'html';
            $orig = (string) ($i['original'] ?? '');
            $rev = (string) ($i['revision'] ?? '');
            if ($orig === '' || $orig === $rev || !array_key_exists($field, $doc)) {
                continue;
            }
            if (!empty($i['meaning_changed'])) {
                $flagged[] = $i + ['reason' => 'Đổi nghĩa: cần người biên tập quyết định'];
                continue;
            }
            if (self::digits($orig) !== self::digits($rev)) {
                $flagged[] = $i + ['reason' => 'Số liệu khác nhau giữa câu gốc và câu sửa'];
                continue;
            }
            if (self::protected($orig) !== self::protected($rev)) {
                $flagged[] = $i + ['reason' => 'Link hoặc placeholder bị thay đổi'];
                continue;
            }
            $count = substr_count((string) $doc[$field], $orig);
            if ($count !== 1) {
                $unapplied[] = $i + ['reason' => $count === 0 ? 'Không tìm thấy câu gốc' : 'Câu gốc xuất hiện nhiều lần'];
                continue;
            }
            $doc[$field] = str_replace($orig, $rev, (string) $doc[$field]);
            $applied[] = $i;
        }

        return [$doc, $applied, $flagged, $unapplied];
    }

    /** Deterministic checks on plain text: known translationese patterns and overused marketing wording. */
    public static function lint(string $text): array
    {
        $out = [];
        foreach (self::PATTERNS as $label => $re) {
            if (preg_match_all($re, $text, $m)) {
                $out[] = "Văn dịch / khuôn mẫu: {$label} (" . count($m[0]) . ')';
            }
        }
        $total = 0;
        $heavy = [];
        foreach (self::OVERUSED as $w) {
            $n = mb_substr_count(mb_strtolower($text), $w);
            $total += $n;
            if ($n > 1) {
                $heavy[] = "{$w} ×{$n}";
            }
        }
        if ($heavy || $total > 4) {
            $out[] = 'Từ quảng cáo dùng nhiều (' . $total . ' lần' . ($heavy ? ': ' . implode(', ', $heavy) : '') . ')';
        }
        if (preg_match_all('/không chỉ[^.!?]{0,80}mà còn/iu', $text, $m) && count($m[0]) > 1) {
            $out[] = 'Cấu trúc "không chỉ… mà còn…" lặp ' . count($m[0]) . ' lần';
        }

        return $out;
    }

    public static function plain(array $doc): string
    {
        $html = (string) ($doc['html'] ?? '');

        return trim(($doc['title'] ?? '') . "\n" . ($doc['lead'] ?? '') . "\n" . html_entity_decode(strip_tags(str_replace(['</p>', '</h2>', '</h3>', '</li>', '</th>', '</td>'], "\n", $html))) . "\n" . self::rawTableHeads($html));
    }

    /** Keeps table header markup visible to the lint (the "Thời gian trong ngày" pattern looks at <th>). */
    private static function rawTableHeads(string $html): string
    {
        preg_match_all('/<th[^>]*>.*?<\/th>/isu', $html, $m);

        return implode("\n", $m[0]);
    }

    private static function digits(string $s): string
    {
        preg_match_all('/\d+/', strip_tags($s), $m);

        return implode(',', $m[0]);
    }

    private static function protected(string $s): string
    {
        preg_match_all('/href="[^"]*"|\[(IMAGE|VIDEO|CẦN XÁC NHẬN)[^\]]*\]/u', $s, $m);

        return implode('|', $m[0]);
    }

    /** Official Vietnamese product names from the CMS, so the writer never invents translations. */
    public static function officialNames(): string
    {
        $lines = [];
        $add = function (string $label, $names) use (&$lines) {
            $names = collect($names)->filter()->unique()->values()->all();
            if ($names) {
                $lines[] = "- {$label}: " . implode('; ', $names);
            }
        };
        $add('Du thuyền', DB::table('app_cruise')->where('language_id', 1)->pluck('name'));
        $add('Hành trình', DB::table('app_itinerary')->where('language_id', 1)->pluck('name'));
        $add('Hoạt động trải nghiệm', DB::table('app_exp_activity')->where('language_id', 1)->pluck('name'));
        $add('Dịch vụ', DB::table('app_service')->where('language_id', 1)->pluck('name'));

        return implode("\n", $lines);
    }
}
