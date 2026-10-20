<?php

namespace App\Console\Commands;

use App\Services\Ai\ArticleWriter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Rewrites an existing article in place with the AI writer (keeps id, slug, category and publish state;
 * the previous version is backed up in storage/app). The brief comes from a plan item; options override
 * it, e.g. for the Vietnamese counterpart of an English plan item:
 *   php artisan articles:ai-rewrite 223 --plan=L1 --lang=vi --title="…" --kw="…" --kw2="…" --links='[{"path":"/vi/…","label":"…","anchor":"…"}]'
 */
class AiRewrite extends Command
{
    protected $signature = 'articles:ai-rewrite {article} {--plan=} {--lang=} {--title=} {--kw=} {--kw2=} {--note=} {--links=}';
    protected $description = 'Rewrite an existing article in place with the AI writer';

    public function handle(): int
    {
        $article = DB::table('app_article')->where('id', $this->argument('article'))->first();
        if (!$article) {
            $this->error('Article not found');

            return self::FAILURE;
        }
        $plan = $this->option('plan') ? DB::table('app_article_plan')->where('code', $this->option('plan'))->first() : null;
        if (!$plan) {
            $this->error('Plan item not found (--plan=CODE)');

            return self::FAILURE;
        }
        $plan = clone $plan;
        if ($this->option('lang') && $this->option('lang') !== $plan->lang) {
            $plan->id = null; // a localized brief: don't relink the plan item
            $plan->lang = $this->option('lang');
            $plan->siblings = '[]';
        }
        foreach (['title' => 'title', 'kw' => 'primary_kw', 'kw2' => 'secondary_kw', 'note' => 'note', 'links' => 'money_links'] as $opt => $field) {
            if ($this->option($opt) !== null) {
                $plan->{$field} = $this->option($opt);
            }
        }
        $this->info("Rewriting article {$article->id} ({$plan->lang}) from plan {$plan->code}…");
        $started = microtime(true);
        $id = (new ArticleWriter())->draft($plan, (int) $article->id);
        $this->info(sprintf('  -> article %d rewritten in %ds', $id, microtime(true) - $started));

        return self::SUCCESS;
    }
}
