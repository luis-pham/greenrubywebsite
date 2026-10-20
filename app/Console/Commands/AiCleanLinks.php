<?php

namespace App\Console\Commands;

use App\Services\Ai\ArticleWriter;
use App\Services\Ai\LinkPolicy;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Applies the current link policy (resources/ai/link-policy.txt) to unpublished AI drafts: unwraps links to
 * competitors and dead links, drops blocked sources, and records what changed in ai_meta for the editor.
 */
class AiCleanLinks extends Command
{
    protected $signature = 'articles:ai-clean-links';
    protected $description = 'Remove competitor and dead external links from unpublished AI drafts';

    public function handle(): int
    {
        $writer = new ArticleWriter();
        $policy = new LinkPolicy();
        foreach (DB::table('app_article')->whereNotNull('ai_meta')->where('is_published', 0)->get(['id', 'content', 'ai_meta']) as $a) {
            [$html, , $blocked, $dead] = $writer->checkLinks((string) $a->content, null);
            $meta = json_decode($a->ai_meta, true) ?: [];
            $sources = array_values(array_filter($meta['sources'] ?? [], fn ($s) => !$policy->isBlocked($s['url'] ?? '')));
            if ($html === $a->content && count($sources) === count($meta['sources'] ?? [])) {
                continue;
            }
            $meta['blocked_links'] = array_values(array_unique(array_merge($meta['blocked_links'] ?? [], $blocked)));
            $meta['dead_links'] = array_values(array_unique(array_merge($meta['dead_links'] ?? [], $dead)));
            $meta['sources'] = $sources;
            DB::table('app_article')->where('id', $a->id)->update([
                'content' => $html,
                'ai_meta' => json_encode($meta, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ]);
            $this->info("article {$a->id}: " . count($blocked) . ' blocked, ' . count($dead) . ' dead links removed');
        }

        return self::SUCCESS;
    }
}
