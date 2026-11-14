<?php

namespace App\Console\Commands;

use App\Services\Seo\IndexNow;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Submits new, updated and removed public URLs to IndexNow, found by diffing the sitemaps
 * against the previous run. The first run (no state yet) submits every sitemap URL. Host cron:
 *   *\/10 * * * * docker exec -u www-data green-ruby-cms php artisan seo:indexnow
 */
class IndexNowSubmit extends Command
{
    protected $signature = 'seo:indexnow {--dry-run : List the URLs without submitting or saving state} {--all : Submit every sitemap URL}';
    protected $description = 'Submit changed sitemap URLs to IndexNow (Bing and partners)';

    private const STATE = 'indexnow-state.json';

    public function handle(IndexNow $indexNow): int
    {
        if (!config('services.indexnow.key')) {
            $this->warn('IndexNow disabled (no key).');

            return self::SUCCESS;
        }

        $current = $indexNow->crawl();
        if ($current === null) {
            $this->error('Sitemap fetch failed; nothing submitted.');

            return self::FAILURE;
        }

        $disk = Storage::disk('local');
        $previous = $disk->exists(self::STATE) ? (json_decode($disk->get(self::STATE), true) ?: []) : [];
        $urls = $this->option('all') ? array_keys($current) : IndexNow::changes($previous, $current);

        if ($this->option('dry-run')) {
            foreach ($urls as $url) {
                $this->line($url);
            }
            $this->info(count($urls) . ' URL(s) would be submitted (' . count($current) . ' in sitemaps).');

            return self::SUCCESS;
        }

        if ($urls !== [] && !$indexNow->submit($urls)) {
            // State untouched: the next run retries the same URLs.
            $this->error('IndexNow submit failed for ' . count($urls) . ' URL(s).');

            return self::FAILURE;
        }

        $disk->put(self::STATE, json_encode($current, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        if ($urls !== []) {
            $this->info(now()->toDateTimeString() . ' submitted ' . count($urls) . ' URL(s).');
        }

        return self::SUCCESS;
    }
}
