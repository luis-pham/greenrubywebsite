<?php

namespace App\Console\Commands;

use App\Services\Ai\ArticleWriter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\BackEnd\Entities\AppArticle;

/**
 * Works through plan items queued for AI drafting (ai_status = queued, set from the writing app), one at a
 * time, until the queue is empty. A file lock keeps a single runner; host cron starts it every minute:
 *   * * * * * docker exec -u www-data green-ruby-cms php artisan articles:ai-draft-run
 * Drafts are saved unpublished; nothing is ever published by this command.
 */
class AiDraftRun extends Command
{
    protected $signature = 'articles:ai-draft-run {--once : Draft at most one article}';
    protected $description = 'Draft queued content-plan articles with Claude (unpublished drafts)';

    public function handle(): int
    {
        $lock = fopen(storage_path('app/ai-draft.lock'), 'c');
        if (!$lock || !flock($lock, LOCK_EX | LOCK_NB)) {
            return self::SUCCESS; // another runner is working
        }

        // A crashed runner leaves items "running"; queue them again.
        DB::table('app_article_plan')->where('ai_status', 'running')->update(['ai_status' => 'queued']);

        if (!DB::table('app_article_plan')->where('ai_status', 'queued')->exists()) {
            return self::SUCCESS;
        }

        try {
            $writer = new ArticleWriter();
        } catch (\Throwable $e) {
            DB::table('app_article_plan')->where('ai_status', 'queued')->update(['ai_status' => 'error', 'ai_error' => $e->getMessage(), 'ai_finished_at' => now()]);
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        do {
            $plan = DB::table('app_article_plan')->where('ai_status', 'queued')
                ->orderBy('ai_queued_at')->orderBy('priority')->orderBy('id')->first();
            if (!$plan) {
                break;
            }
            if ($plan->article_id && AppArticle::where('id', $plan->article_id)->exists()) {
                // Started by hand in the meantime: never overwrite a human draft.
                DB::table('app_article_plan')->where('id', $plan->id)->update(['ai_status' => null, 'ai_error' => 'Bài đã có bản viết tay, bỏ qua.']);
                continue;
            }
            DB::table('app_article_plan')->where('id', $plan->id)->update(['ai_status' => 'running', 'ai_error' => null]);
            $this->info("Drafting {$plan->code}: {$plan->title}");
            $started = microtime(true);
            try {
                $id = $writer->draft($plan);
                DB::table('app_article_plan')->where('id', $plan->id)->update(['ai_status' => 'done', 'ai_finished_at' => now()]);
                $this->info(sprintf('  -> article %d in %ds', $id, microtime(true) - $started));
            } catch (\Throwable $e) {
                Log::warning('AI draft failed', ['plan' => $plan->code, 'error' => $e->getMessage()]);
                DB::table('app_article_plan')->where('id', $plan->id)->update(['ai_status' => 'error', 'ai_error' => mb_substr($e->getMessage(), 0, 1000), 'ai_finished_at' => now()]);
                $this->error("  -> {$e->getMessage()}");
                // Stop the run on account-level problems instead of failing every remaining item.
                if (preg_match('/authentication|permission|credit|billing|workspace|invalid x-api-key/i', $e->getMessage())) {
                    break;
                }
            }
        } while (!$this->option('once'));

        flock($lock, LOCK_UN);

        return self::SUCCESS;
    }
}
