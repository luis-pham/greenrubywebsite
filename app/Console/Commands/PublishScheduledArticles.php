<?php

namespace App\Console\Commands;

use App\Support\FrontendRevalidator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Scheduled articles are published rows with a future publish_date; the site hides them until then.
 * The Next.js cache does not know when that moment passes, so this refreshes it for every article
 * whose publish_date fell since the previous run. Run every minute (host cron):
 *   * * * * * docker exec green-ruby-cms php artisan articles:publish-scheduled
 */
class PublishScheduledArticles extends Command
{
    protected $signature = 'articles:publish-scheduled';
    protected $description = 'Refresh the frontend cache for scheduled articles that just went live';

    private const STATE = 'editor-scheduled-last-run.txt';

    public function handle(): int
    {
        $now = now();
        $disk = Storage::disk('local');
        $since = $disk->exists(self::STATE) ? \Carbon\Carbon::parse(trim($disk->get(self::STATE))) : $now->copy()->subMinutes(5);

        $ids = DB::table('app_article')->where('is_published', 1)
            ->where('publish_date', '>', $since)->where('publish_date', '<=', $now)
            ->pluck('id')->all();

        if ($ids) {
            $tags = array_merge(['articles', 'legal', 'content'], array_map(fn ($id) => 'article:' . $id, $ids));
            if (!FrontendRevalidator::send($tags)) {
                // Leave the state untouched so the next run retries.
                $this->error('Revalidate failed for: ' . implode(', ', $ids));

                return self::FAILURE;
            }
            $this->info('Published: ' . implode(', ', $ids));
        }
        $disk->put(self::STATE, $now->toDateTimeString());

        return self::SUCCESS;
    }
}
