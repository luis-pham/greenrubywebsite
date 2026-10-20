<?php

namespace App\Console\Commands;

use App\Services\Ai\ArticleWriter;
use App\Services\Ai\VietnameseEditor;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Rubric regression suite for the Vietnamese editorial pass (fixtures: resources/ai/tests/vi-fixtures.json).
 * Each fixture goes through VietnameseEditor::review(); the result is checked two ways:
 *  - deterministic: translationese removed (must_not_contain), facts kept verbatim (required_facts),
 *    already-natural text left (almost) unchanged;
 *  - rubric: one grader call judges every fixture against its expected characteristics and failure conditions.
 * Costs real API money (about $3–5 for the full set).
 *   php artisan articles:ai-vi-eval
 *   php artisan articles:ai-vi-eval --only=cmp-win,fleet-sister
 */
class AiViEval extends Command
{
    protected $signature = 'articles:ai-vi-eval {--only=} {--rounds=1}';
    protected $description = 'Run the Vietnamese editorial rubric regression suite';

    public function handle(): int
    {
        $fixtures = json_decode(file_get_contents(resource_path('ai/tests/vi-fixtures.json')), true)['fixtures'];
        if ($only = $this->option('only')) {
            $fixtures = array_values(array_filter($fixtures, fn ($f) => in_array($f['id'], explode(',', $only), true)));
        }
        $writer = new ArticleWriter();
        $writer->resetUsage();
        $editor = new VietnameseEditor($writer);

        $results = [];
        foreach ($fixtures as $f) {
            $this->line("… {$f['id']}");
            $doc = ['title' => '', 'lead' => '', 'seo_title' => '', 'seo_description' => '', 'html' => $f['input']];
            $res = $editor->review($doc, $f['type'] . '. Đoạn trích để kiểm thử biên tập.', (int) $this->option('rounds'));
            $out = $res['doc']['html'];
            $checks = [];
            foreach ($f['must_not_contain'] as $s) {
                // Still present but proposed in a flagged edit = handed to the human editor, which is the intended outcome.
                $held = collect($res['report']['flagged'])->contains(fn ($i) => mb_stripos($i['original'], $s) !== false);
                $checks[] = ['check' => "đã bỏ \"{$s}\"" . ($held ? ' (đề xuất chờ duyệt)' : ''), 'ok' => mb_stripos($out, $s) === false || $held];
            }
            foreach ($f['required_facts'] as $s) {
                $checks[] = ['check' => "giữ \"{$s}\"", 'ok' => mb_strpos($out, $s) !== false];
            }
            if (!empty($f['natural'])) {
                similar_text($f['input'], $out, $pct);
                $checks[] = ['check' => sprintf('câu tự nhiên giữ gần nguyên (%.0f%% giống)', $pct), 'ok' => $pct >= 85];
            }
            $results[$f['id']] = ['fixture' => $f, 'output' => $out, 'checks' => $checks, 'applied' => $res['report']['applied'], 'flagged' => $res['report']['flagged'], 'unapplied' => $res['report']['unapplied']];
        }

        $grades = $this->grade($writer, $results);
        $pass = 0;
        $rows = [];
        foreach ($results as $id => &$r) {
            $g = $grades[$id] ?? ['pass' => false, 'notes' => 'không có điểm của grader'];
            $r['rubric'] = $g;
            $det = !in_array(false, array_column($r['checks'], 'ok'), true);
            $r['pass'] = $det && $g['pass'];
            $pass += $r['pass'] ? 1 : 0;
            $failed = array_column(array_filter($r['checks'], fn ($c) => !$c['ok']), 'check');
            $rows[] = [$id, $det ? 'ok' : 'FAIL', $g['pass'] ? 'ok' : 'FAIL', count($r['applied']) . '/' . count($r['flagged']), mb_strimwidth(implode('; ', $failed) ?: $g['notes'], 0, 90, '…')];
        }
        unset($r);

        $this->table(['fixture', 'checks', 'rubric', 'applied/flagged', 'notes'], $rows);
        $usage = $writer->usage();
        $this->info(sprintf('Passed %d/%d. Cost $%s', $pass, count($results), $usage['cost_usd']));
        $path = 'ai-vi-eval-' . now()->format('YmdHis') . '.json';
        Storage::disk('local')->put($path, json_encode(['passed' => $pass, 'total' => count($results), 'usage' => $usage, 'results' => $results], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
        $this->line('Report: storage/app/' . $path);

        return $pass === count($results) ? self::SUCCESS : self::FAILURE;
    }

    /** One grader call over all fixtures: judges each output against its rubric, not against an exact string. */
    private function grade(ArticleWriter $writer, array $results): array
    {
        $items = [];
        foreach ($results as $id => $r) {
            $f = $r['fixture'];
            $items[] = ['id' => $id, 'loai_bai' => $f['type'], 'ban_goc' => $f['input'], 'ban_sua' => $r['output'],
                'can_dat' => $f['expected'], 'vi_du_chap_nhan' => $f['acceptable'], 'that_bai_neu' => $f['failure'],
                'van_tu_nhien_san' => !empty($f['natural']), 'de_xuat_bi_giu_lai' => array_map(fn ($i) => $i['original'] . ' → ' . $i['revision'] . ' (' . ($i['reason'] ?? '') . ')', $r['flagged'])];
        }
        $json = json_encode($items, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        $prompt = <<<TXT
Bạn là tổng biên tập người Việt, chấm kết quả biên tập tiếng Việt theo hướng dẫn biên tập trong system prompt.
Với mỗi mục: so bản gốc và bản sửa. Đạt (pass) khi bản sửa đáp ứng mọi điều trong can_dat, không rơi vào điều nào trong that_bai_neu, đọc lên tự nhiên như người Việt viết, và không đổi dữ kiện. Bản sửa không cần giống vi_du_chap_nhan; đó chỉ là ví dụ. Nếu van_tu_nhien_san là true thì việc giữ gần nguyên là đúng. Nếu một chỗ chưa sửa nhưng đã được đưa vào de_xuat_bi_giu_lai với đề xuất hợp lý (vì có thể đổi nghĩa), coi như xử lý đúng.
notes: một câu tiếng Việt nêu lý do, nhất là khi không đạt.

<cac_muc>
{$json}
</cac_muc>
TXT;
        $schema = ['type' => 'object', 'properties' => ['grades' => ['type' => 'array', 'items' => [
            'type' => 'object',
            'properties' => ['id' => ['type' => 'string'], 'pass' => ['type' => 'boolean'], 'notes' => ['type' => 'string']],
            'required' => ['id', 'pass', 'notes'], 'additionalProperties' => false,
        ]]], 'required' => ['grades'], 'additionalProperties' => false];
        $res = $writer->structuredCall($prompt, 'medium', $schema, 'vi');

        return collect($res['grades'] ?? [])->keyBy('id')->map(fn ($g) => ['pass' => (bool) $g['pass'], 'notes' => $g['notes']])->all();
    }
}
