<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\Editor\ArticleController;
use App\Services\Ai\ArticleWriter;
use App\Services\Ai\VietnameseEditor;
use App\Support\FrontendRevalidator;
use App\Support\HtmlSanitizer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Runs the native Vietnamese editorial review on an existing Vietnamese article and saves the safe edits
 * in place (previous version backed up in storage/app). The report (applied, flagged, kept examples,
 * scores) goes to ai_meta.vi_review and, with --report, to a JSON file for before/after comparison.
 *   php artisan articles:ai-vi-edit 223 --report
 *   php artisan articles:ai-vi-edit 223 --dry-run --report
 */
class AiViEdit extends Command
{
    protected $signature = 'articles:ai-vi-edit {article} {--dry-run} {--report}';
    protected $description = 'Native Vietnamese editorial review of an existing article';

    public function handle(): int
    {
        $a = DB::table('app_article')->where('id', $this->argument('article'))->first();
        if (!$a || (int) $a->language_id !== 1) {
            $this->error('Vietnamese article not found');

            return self::FAILURE;
        }
        $doc = ['title' => $a->title, 'lead' => (string) $a->lead, 'seo_title' => (string) $a->seo_title, 'seo_description' => (string) $a->seo_description, 'html' => (string) $a->content];
        $writer = new ArticleWriter();
        $writer->resetUsage();
        $plan = DB::table('app_article_plan')->where('article_id', $a->id)->first();
        $context = ($plan ? (['I' => 'Bài thông tin / hướng dẫn', 'C' => 'Bài so sánh, tư vấn lựa chọn', 'T' => 'Bài tư vấn đặt chỗ'][$plan->intent] ?? 'Bài thông tin') : 'Bài hướng dẫn du lịch')
            . '. Chủ đề: ' . $a->title . ($a->focus_keyword ? '. Từ khoá chính: ' . $a->focus_keyword : '');

        $this->info("Reviewing article {$a->id}…");
        $res = (new VietnameseEditor($writer))->review($doc, $context);
        $report = $res['report'] + ['usage' => $writer->usage(), 'reviewed_at' => now()->toIso8601String(), 'lint_before' => VietnameseEditor::lint(VietnameseEditor::plain($doc))];

        $this->line(sprintf('Applied %d, flagged %d, unapplied %d. Verdict: %s. Cost: $%s',
            count($report['applied']), count($report['flagged']), count($report['unapplied']), $report['verdict'], $report['usage']['cost_usd']));

        if ($this->option('report')) {
            $path = "ai-vi-edit-{$a->id}-" . now()->format('YmdHis') . '.json';
            Storage::disk('local')->put($path, json_encode(['before' => $doc, 'after' => $res['doc'], 'report' => $report], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
            $this->line('Report: storage/app/' . $path);
        }
        if ($this->option('dry-run')) {
            return self::SUCCESS;
        }

        Storage::disk('local')->put("ai-vi-edit-backup-{$a->id}-" . now()->format('YmdHis') . '.json', json_encode((array) $a, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        $meta = json_decode((string) $a->ai_meta, true) ?: [];
        $meta['vi_review'] = $report;
        $after = $res['doc'];
        DB::table('app_article')->where('id', $a->id)->update([
            'title' => mb_substr($after['title'], 0, 255),
            'lead' => (string) HtmlSanitizer::clean($after['lead']),
            'seo_title' => mb_substr($after['seo_title'], 0, 65),
            'seo_description' => mb_substr($after['seo_description'], 0, 255),
            'content' => ArticleController::relativeOwnLinks((string) HtmlSanitizer::clean($after['html'])),
            'ai_meta' => json_encode($meta, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'updated_at' => now(),
        ]);
        FrontendRevalidator::send(['articles', 'article:' . $a->id]);
        $this->info('Saved.');

        return self::SUCCESS;
    }
}
